<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferQuoteResource;
use App\Http\Resources\OfferResource;
use App\Models\Offer;
use App\Services\OfferPricing;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * Public offers. Every offer comes as an OfferResource: localized texts and prices ready to show,
 * so the frontend never needs to know the offer types.
 */
class OfferController extends Controller
{
    use ApiResponse;

    /**
     * Return all active offers (paginated).
     * url: GET api/offers?page=1
     */
    public function index(Request $request)
    {
        $offers = $this->activeOffers()->paginate(12);

        return $this->successResponse([
            'data' => OfferResource::collection($offers->items()),
            'current_page' => $offers->currentPage(),
            'last_page' => $offers->lastPage(),
            'per_page' => $offers->perPage(),
            'total' => $offers->total(),
        ], __('api.offers_fetched'));
    }

    /**
     * Return all active offers without pagination (home page section).
     * url: GET api/offers/all
     */
    public function all()
    {
        return $this->successResponse(OfferResource::collection($this->activeOffers()->get()), __('api.offers_fetched'));
    }

    /**
     * Return a single active offer.
     * url: GET api/offers/{id}
     */
    public function show($id)
    {
        $offer = $this->activeOffers()->find($id);
        if (!$offer) {
            return $this->errorResponse(__('offers.unavailable'), 404, ['code' => 'offer_unavailable']);
        }

        return $this->successResponse(new OfferResource($offer), __('api.offer_fetched'));
    }

    /**
     * What buying this offer costs right now. The offer checkout page shows this, and sends its total back as
     * expected_total when placing the order.
     * url: GET api/offers/{id}/quote?sets=1&product_id=
     */
    public function quote(Request $request, OfferPricing $pricing, $id)
    {
        $request->validate([
            'sets'       => 'nullable|integer|min:1|max:' . OfferPricing::MAX_SETS,
            'product_id' => 'nullable|integer',
        ]);

        $offer = Offer::active()->find($id);
        if (!$offer) {
            return $this->errorResponse(__('offers.unavailable'), 404, ['code' => 'offer_unavailable']);
        }

        $quote = $pricing->quote($offer, (int) $request->input('sets', 1), $request->filled('product_id') ? (int) $request->product_id : null);

        return $this->successResponse(new OfferQuoteResource($quote), __('api.offer_quoted'));
    }

    /**
     * Newest first. id breaks ties: many offers can share a created_at (e.g. the ones migrated from the old
     * system in one run), and without a unique order pages could repeat or skip offers.
     * Deleted products are loaded too, so an offer whose product was removed shows as unavailable
     * instead of silently shrinking.
     */
    private function activeOffers()
    {
        return Offer::active()
            ->with(['products' => fn ($q) => $q->withTrashed()->with('category'), 'freeProduct'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }
}
