<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferQuoteResource;
use App\Models\Offer;
use App\Services\OfferPricing;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    use ApiResponse;

    /**
     * Return all active offers (paginated).
     */
    public function index(Request $request)
    {
        $offers = Offer::active()
            ->with(['products' => function ($q) {
                $q->select('products.id', 'name_ar', 'name_en', 'image', 'price', 'discount_price', 'discount');
            }])
            ->latest()
            ->paginate(12);

        return $this->successResponse($offers, 'تم جلب العروض بنجاح');
    }

    /**
     * Return a single active offer with its products (each with pivot.quantity per bundle set).
     */
    public function show($id)
    {
        $offer = Offer::active()
            ->with(['products.extraImages', 'freeProduct'])
            ->findOrFail($id);

        return $this->successResponse($offer, 'تم جلب تفاصيل العرض بنجاح');
    }

    /**
     * Return all active offers without pagination (for home page banners etc.)
     */
    public function all()
    {
        $offers = Offer::active()
            ->with(['products:products.id,name_ar,name_en,image,price,discount_price'])
            ->latest()
            ->get();

        return $this->successResponse($offers, 'تم جلب العروض بنجاح');
    }

    /**
     * What buying this offer costs right now. The offer page shows this, and sends its total back as
     * expected_total when checking out.
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
            return $this->errorResponse('العرض غير متاح أو انتهت صلاحيته', 404, ['code' => 'offer_unavailable']);
        }

        $quote = $pricing->quote($offer, (int) $request->input('sets', 1), $request->filled('product_id') ? (int) $request->product_id : null);

        return $this->successResponse(new OfferQuoteResource($quote), 'تم حساب العرض بنجاح');
    }
}
