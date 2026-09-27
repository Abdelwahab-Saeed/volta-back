<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Api\Concerns\PlacesOrders;
use App\Http\Controllers\Controller;
use App\Http\Resources\OfferQuoteResource;
use App\Models\Offer;
use App\Services\OfferPricing;
use App\Services\OrderPlacer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Buying an offer directly: offer page → offer checkout → order. The cart is never read or changed,
 * and no coupon or second offer can be added.
 *
 * The whole selection (offer_id, sets, product_id) is in the request, so the frontend can keep it in the URL:
 * back/forward, refresh and shared links all work without any client-side state.
 */
class OfferCheckoutController extends Controller
{
    use ApiResponse, PlacesOrders;

    public function __construct(private OfferPricing $pricing, private OrderPlacer $orderPlacer)
    {
    }

    public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        // array_merge (not +) so these rules override the shared ones, e.g. expected_total becomes required.
        $request->validate(array_merge($this->customerRules(), [
            'offer_id'       => 'required|integer',
            'sets'           => 'required|integer|min:1|max:' . OfferPricing::MAX_SETS,
            'product_id'     => 'nullable|integer',
            'expected_total' => 'required|numeric|min:0', // required here: the customer always saw a quote first
        ]));

        if ($existing = $this->orderPlacer->findByIdempotencyKey($this->idempotencyKey($request))) {
            return $this->orderCreatedResponse($existing, replayed: true);
        }

        $offer = Offer::active()->find($request->offer_id);
        if (!$offer) {
            return $this->errorResponse('العرض غير متاح أو انتهت صلاحيته', 422, ['code' => 'offer_unavailable']);
        }

        $quote = $this->pricing->quote($offer, (int) $request->sets, $request->filled('product_id') ? (int) $request->product_id : null);

        if (!$quote['purchasable']) {
            return $this->errorResponse($quote['issues'][0]['message'], 422, [
                'code' => $quote['issues'][0]['code'],
                'quote' => new OfferQuoteResource($quote),
            ]);
        }

        // The offer or product prices changed after the customer saw the total: show the new quote instead of charging it.
        if ($this->totalChanged($request, $quote['total'])) {
            return $this->errorResponse('تغيّر سعر العرض، راجع الإجمالي الجديد قبل إتمام الطلب', 409, [
                'code' => 'price_changed',
                'quote' => new OfferQuoteResource($quote),
            ]);
        }

        try {
            $order = $this->orderPlacer->place(
                $this->customerAttributes($request, $user) + [
                    'subtotal'        => $quote['subtotal'],
                    'shipping_cost'   => $quote['shipping'],
                    'discount_amount' => 0,
                    'total_amount'    => $quote['total'],
                    'offer_id'        => $offer->id,
                    'offer_discount'  => $quote['discount'],
                    'offer_snapshot'  => $offer->snapshot($quote['sets'], $quote['product_id']),
                ],
                $quote['lines'],
                $quote['gifts'],
            );
        } catch (InsufficientStockException $e) {
            return $this->insufficientStockResponse($e, 'الكمية المطلوبة غير متوفرة حالياً: ');
        } catch (\Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء إتمام الطلب', 500, ['error' => $e->getMessage()]);
        }

        return $this->orderCreatedResponse($order, replayed: $order->wasRecentlyCreated === false);
    }
}
