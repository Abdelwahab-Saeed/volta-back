<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBundleOffer;
use Illuminate\Http\Request;

class ProductBundleOfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Product $product)
    {
        $offers = $product->bundleOffers;
        return view('admin.products.offers.index', compact('product', 'offers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:2',
            'bundle_price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Check for duplicate quantity
        if ($product->bundleOffers()->where('quantity', $validated['quantity'])->exists()) {
             return back()->with('error', 'يوجد عرض لهذا المنتج بنفس الكمية بالفعل')->withInput();
        }

        $product->bundleOffers()->create([
            'quantity' => $validated['quantity'],
            'bundle_price' => $validated['bundle_price'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.products.offers.index', $product->id)
            ->with('success', 'تم إضافة عرض الباقة بنجاح');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product, ProductBundleOffer $offer)
    {
        return view('admin.products.offers.edit', compact('product', 'offer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product, ProductBundleOffer $offer)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:2',
            'bundle_price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Check for duplicate quantity if changed
        if ($offer->quantity != $validated['quantity']) {
            if ($product->bundleOffers()->where('quantity', $validated['quantity'])->exists()) {
                 return back()->with('error', 'يوجد عرض لهذا المنتج بنفس الكمية بالفعل')->withInput();
            }
        }

        $offer->update([
            'quantity' => $validated['quantity'],
            'bundle_price' => $validated['bundle_price'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.products.offers.index', $product->id)
            ->with('success', 'تم تحديث عرض الباقة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, ProductBundleOffer $offer)
    {
        $offer->delete();

        return redirect()->route('admin.products.offers.index', $product->id)
            ->with('success', 'تم حذف عرض الباقة بنجاح');
    }
}
