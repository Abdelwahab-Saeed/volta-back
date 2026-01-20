<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBundleOffer;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class ProductBundleOfferController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     * url: GET api/products/{product}/bundle-offers
     */
    public function index($productId)
    {
        $product = Product::findOrFail($productId);
        
        // Admin gets all offers, active or inactive
        $offers = $product->bundleOffers()->orderBy('quantity', 'asc')->get();

        return $this->successResponse($offers, 'تم جلب عروض الباقة بنجاح');
    }

    /**
     * Store a newly created resource in storage.
     * url: POST api/products/{product}/bundle-offers
     */
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:2', // Bundle usually implies > 1
            'bundle_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        // Check for duplicate quantity
        if ($product->bundleOffers()->where('quantity', $validated['quantity'])->exists()) {
            return $this->errorResponse('يوجد عرض لهذا المنتج بنفس الكمية بالفعل', 422);
        }

        $offer = $product->bundleOffers()->create($validated);

        return $this->successResponse($offer, 'تم إنشاء عرض الباقة بنجاح', 201);
    }

    /**
     * Update the specified resource in storage.
     * url: PUT api/bundle-offers/{offer}
     */
    public function update(Request $request, $id)
    {
        $offer = ProductBundleOffer::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'integer|min:2',
            'bundle_price' => 'numeric|min:0',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['quantity']) && $validated['quantity'] != $offer->quantity) {
             // Check for duplicate if changing quantity
             $exists = ProductBundleOffer::where('product_id', $offer->product_id)
                ->where('quantity', $validated['quantity'])
                ->where('id', '!=', $offer->id)
                ->exists();
                
             if ($exists) {
                 return $this->errorResponse('يوجد عرض لهذا المنتج بنفس الكمية بالفعل', 422);
             }
        }

        $offer->update($validated);

        return $this->successResponse($offer, 'تم تحديث عرض الباقة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     * url: DELETE api/bundle-offers/{offer}
     */
    public function destroy($id)
    {
        $offer = ProductBundleOffer::findOrFail($id);
        $offer->delete();

        return $this->successResponse(null, 'تم حذف عرض الباقة بنجاح');
    }
}
