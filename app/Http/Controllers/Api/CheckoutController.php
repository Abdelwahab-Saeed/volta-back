<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Api\Concerns\PlacesOrders;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\OrderPlacer;
use App\Services\PriceCalculator;
use App\Support\Money;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Checkout of the cart (or, for guests, the items sent in the request). Offers are not bought here:
 * they have their own checkout (OfferCheckoutController). Cart-wide discounts are coupons.
 */
class CheckoutController extends Controller
{
    use ApiResponse, PlacesOrders;

    public function __construct(private PriceCalculator $priceCalculator, private OrderPlacer $orderPlacer)
    {
    }

    public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $validationRules = $this->customerRules() + [
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'offer_id'    => 'prohibited',
        ];

        if (!$user) {
            $validationRules['items'] = 'required|array|min:1';
            $validationRules['items.*.product_id'] = 'required|exists:products,id';
            $validationRules['items.*.quantity'] = 'required|integer|min:1';
        }

        $request->validate($validationRules, [
            'offer_id.prohibited' => 'العروض تُشترى مباشرة من صفحة العرض، وليس من السلة.',
        ]);

        if ($existing = $this->orderPlacer->findByIdempotencyKey($this->idempotencyKey($request))) {
            return $this->orderCreatedResponse($existing, replayed: true);
        }

        $cart = null;
        $cartItems = collect();

        if ($user) {
            // Only what the cart page shows: lines of hidden or deleted products are neither shown nor charged
            $cart = $user->cart?->loadAvailableItems();

            if (!$cart || $cart->items->isEmpty()) {
                return $this->errorResponse('السلة فارغة حالياً', 400);
            }
            $cartItems = $cart->items;
        } else {
            // For guest, hydrate items from request. The guest cart lives in the browser, so it can still hold
            // products that were hidden or deleted since: refuse the order and say which ones.
            $requestedIds = collect($request->items)->pluck('product_id');
            $products = Product::visible()->whereIn('id', $requestedIds)->get()->keyBy('id');
            $unavailableIds = $requestedIds->unique()->reject(fn ($id) => $products->has($id))->values();

            if ($unavailableIds->isNotEmpty()) {
                $names = Product::withTrashed()->whereIn('id', $unavailableIds)->get()->pluck('name')->implode(', ');

                return $this->errorResponse(__('api.products_unavailable', ['names' => $names]), 422, [
                    'code' => 'product_unavailable',
                    'product_ids' => $unavailableIds->map(fn ($id) => (int) $id)->all(),
                ]);
            }

            foreach ($request->items as $itemData) {
                $product = $products[$itemData['product_id']];

                $cartItem = new \stdClass();
                $cartItem->product = $product;
                $cartItem->product_id = $product->id;
                $cartItem->quantity = $itemData['quantity'];

                $cartItems->push($cartItem);
            }
        }

        // Calculate Subtotal using PriceCalculator (Source of Truth)
        $subtotal = 0;
        $lines = collect();

        foreach ($cartItems as $item) {
            $calculation = $this->priceCalculator->calculate($item->product, $item->quantity);

            $item->price_snapshot = $calculation['final_unit_price'];
            $item->total_line_price = $calculation['total_price'];

            $subtotal += $item->total_line_price;
            $lines->push($item);
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

        $shippingCost = $this->priceCalculator->shippingCost($lines);
        $totalAmount = max(0, $subtotal - $discountAmount) + $shippingCost;

        if ($this->totalChanged($request, $totalAmount)) {
            return $this->errorResponse('تغيّرت الأسعار، راجع الإجمالي الجديد قبل إتمام الطلب', 409, [
                'subtotal' => Money::toPounds($subtotal),
                'discount_amount' => Money::toPounds($discountAmount),
                'shipping_cost' => Money::toPounds($shippingCost),
                'total_amount' => Money::toPounds($totalAmount),
            ]);
        }

        try {
            $order = $this->orderPlacer->place(
                $this->customerAttributes($request, $user) + [
                    'subtotal'        => $subtotal,
                    'shipping_cost'   => $shippingCost,
                    'discount_amount' => $discountAmount,
                    'total_amount'    => $totalAmount,
                    'coupon_code'     => $coupon?->code,
                ],
                $lines,
                collect(),
                function ($order) use ($coupon, $user, $cart) {
                    if ($coupon) {
                        $coupon->increment('times_used');

                        // Record that this user has used this coupon if authenticated
                        if ($user) {
                            $coupon->users()->attach($user->id, ['order_id' => $order->id]);
                        }
                    }

                    // Clear Cart if authenticated
                    $cart?->items()->delete();
                }
            );
        } catch (InsufficientStockException $e) {
            return $this->insufficientStockResponse($e, 'المخزون غير كافٍ للمنتج: ');
        } catch (\Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء إتمام الطلب', 500, ['error' => $e->getMessage()]);
        }

        return $this->orderCreatedResponse($order, replayed: $order->wasRecentlyCreated === false);
    }
}
