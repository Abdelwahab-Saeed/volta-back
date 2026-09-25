<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
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
     * Return a single active offer with its products.
     */
    public function show($id)
    {
        $offer = Offer::active()
            ->with(['products.extraImages', 'products.bundleOffers', 'freeProduct'])
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
}
