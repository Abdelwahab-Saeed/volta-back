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
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'phone_number_backup' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'shipping_way' => 'required|string|in:home,office,pickup',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        
        // Eager load cart with items and products to eliminate N+1 queries
        $cart = $user->cart()->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return $this->errorResponse('السلة فارغة حالياً', 400);
        }

        // Calculate Subtotal using price snapshots
        $subtotal = 0;
        foreach ($cart->items as $item) {
            $effectivePrice = $item->getEffectivePrice();
            $subtotal += $effectivePrice * $item->quantity;
        }

        // Apply Coupon with improved validation
        $discountAmount = 0;
        $coupon = null;

        if ($request->coupon_code) {
            $coupon = Coupon::where('code', $request->coupon_code)->first();
            
            // Check if user has already used this coupon
            if ($coupon && $coupon->hasBeenUsedByUser($user->id)) {
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
            foreach ($cart->items as $item) {
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
                'user_id' => $user->id,
                'full_name' => $request->full_name,
                'phone_number' => $request->phone_number,
                'phone_number_backup' => $request->phone_number_backup,
                'city' => $request->city,
                'state' => $request->state,
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

            // Create Order Items using price snapshots
            foreach ($cart->items as $item) {
                $effectivePrice = $item->getEffectivePrice();
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $effectivePrice,
                    'total' => $effectivePrice * $item->quantity,
                ]);

                // Decrement product stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Update Coupon Usage
            if ($coupon) {
                $coupon->increment('times_used');
                
                // Record that this user has used this coupon
                $coupon->users()->attach($user->id, ['order_id' => $order->id]);
            }

            // Clear Cart
            $cart->items()->delete();

            DB::commit();

            return $this->successResponse($order->load('items.product'), 'تم إتمام الطلب بنجاح', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('حدث خطأ أثناء إتمام الطلب', 500, ['error' => $e->getMessage()]);
        }
    }
}
