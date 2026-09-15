<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductFeature;
use Illuminate\Http\Request;

class ProductFeatureController extends Controller
{
    public function index($productId)
    {
        $product = Product::with('features')->findOrFail($productId);
        return view('admin.products.features.index', compact('product'));
    }

    public function store(Request $request, $productId)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        ProductFeature::create([
            'product_id' => $productId,
            ...$data,
        ]);

        return redirect()->back()->with('success', 'تم إضافة الميزة بنجاح.');
    }

    public function update(Request $request, ProductFeature $feature)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        $feature->update($data);

        return redirect()->back()->with('success', 'تم تحديث الميزة بنجاح.');
    }

    public function destroy(ProductFeature $feature)
    {
        $feature->delete();
        return redirect()->back()->with('success', 'تم حذف الميزة بنجاح.');
    }
}
