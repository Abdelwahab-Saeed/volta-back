<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Traits\ApiResponse;

class CheckoutController extends Controller
{
    use ApiResponse;

    /**
     * Handle the incoming checkout request.
     */
    protected $priceCalculator;

    public function __construct(\App\Services\PriceCalculator $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * Handle the incoming checkout request.
     */
    public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $validationRules = [
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'phone_number_backup' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'address_line' => 'nullable|string|max:255',
            'shipping_way' => 'required|string|in:home,office,pickup',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'notes' => 'nullable|string',
        ];

        if (!$user) {
            $validationRules['items'] = 'required|array|min:1';
            $validationRules['items.*.product_id'] = 'required|exists:products,id';
            $validationRules['items.*.quantity'] = 'required|integer|min:1';
        }

        $request->validate($validationRules);

        $cartItems = collect();

        if ($user) {
            // Eager load cart with items and products (and bundle offers) to eliminate N+1 queries
            $cart = $user->cart()->with('items.product.bundleOffers')->first();

            if (!$cart || $cart->items->isEmpty()) {
                return $this->errorResponse('السلة فارغة حالياً', 400);
            }
            $cartItems = $cart->items;
        } else {
            // For guest, hydrate items from request
            foreach ($request->items as $itemData) {
                // Eager load offers
                $product = Product::with('bundleOffers')->find($itemData['product_id']);
                
                // Create a temporary object mimicking a CartItem logic
                $cartItem = new \stdClass();
                $cartItem->product = $product;
                $cartItem->product_id = $product->id;
                $cartItem->quantity = $itemData['quantity'];
                
                // We don't set price_snapshot here, we calculate it below
                
                $cartItems->push($cartItem);
            }
        }

        // Calculate Subtotal using PriceCalculator (Source of Truth)
        $subtotal = 0;
        $processedItems = collect(); // Store calculated items to avoid recalculation

        foreach ($cartItems as $item) {
            $calculation = $this->priceCalculator->calculate($item->product, $item->quantity);
            
            // Apply the calculated price
            $item->price_snapshot = $calculation['final_unit_price']; 
            // Also store total for this item
            $item->total_line_price = $calculation['total_price'];
            
            $subtotal += $item->total_line_price;
            $processedItems->push($item);
        }

        // Apply Coupon with improved validation
        $discountAmount = 0;
        $coupon = null;

        if ($request->coupon_code) {
            $coupon = Coupon::where('code', $request->coupon_code)->first();
            
            // Check if user has already used this coupon (Only for authenticated users)
            if ($user && $coupon && $coupon->hasBeenUsedByUser($user->id)) {
                return $this->errorResponse('لقد قمت باستخدام هذا الكوبون من قبل', 422);
            }
            
            // Check max uses limit
            if ($coupon && $coupon->max_uses && $coupon->times_used >= $coupon->max_uses) {
                return $this->errorResponse('عذراً، وصل الكوبون للحد الأقصى من الاستخدام', 422);
            }
            
            if ($coupon && $coupon->isValid($subtotal)) {
                $discountAmount = $coupon->calculateDiscount($subtotal);
            } else {
                return $this->errorResponse('الكوبون غير صالح أو انتهت صلاحيته', 422);
            }
        }

        $shippingCost = 30.00; // Fixed shipping cost for now as per design
        $totalAmount = max(0, $subtotal - $discountAmount) + $shippingCost;

        try {
            DB::beginTransaction();

            // Validate stock availability for all items before creating order
            foreach ($processedItems as $item) {
                if ($item->product->stock < $item->quantity) {
                    DB::rollBack();
                    return $this->errorResponse("المخزون غير كافٍ للمنتج: {$item->product->name}", 422, [
                        'product' => $item->product->name,
                        'available' => $item->product->stock,
                        'requested' => $item->quantity,
                    ]);
                }
            }

            // Create Order with enum values
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'full_name' => $request->full_name,
                'phone_number' => $request->phone_number,
                'phone_number_backup' => $request->phone_number_backup,
                'city' => $request->city,
                'state' => $request->state,
                'address_line' => $request->address_line,
                'shipping_way' => $request->shipping_way,
                'status' => OrderStatus::PENDING->value,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'coupon_code' => $coupon ? $coupon->code : null,
            ]);

            // Create Order Items using calculated prices
            foreach ($processedItems as $item) {
                // $item->price_snapshot was updated in the calculation loop
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price_snapshot, // This is now the discounted unit price
                    'total' => $item->total_line_price,
                ]);

                // Decrement product stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Update Coupon Usage
            if ($coupon) {
                $coupon->increment('times_used');
                
                // Record that this user has used this coupon if authenticated
                if ($user) {
                    $coupon->users()->attach($user->id, ['order_id' => $order->id]);
                }
            }

            // Clear Cart if authenticated
            if ($user && isset($cart)) {
                $cart->items()->delete();
            }

            DB::commit();

            return $this->successResponse($order->load('items.product'), 'تم إتمام الطلب بنجاح', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('حدث خطأ أثناء إتمام الطلب', 500, ['error' => $e->getMessage()]);
        }
    }
}
