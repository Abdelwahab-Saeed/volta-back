<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function index($productId)
    {
        $product = Product::with('extraImages')->findOrFail($productId);
        return view('admin.products.images.index', compact('product'));
    }

    public function store(Request $request, $productId)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('uploads/products', 'public');
                ProductImage::create([
                    'product_id' => $productId,
                    'image' => $path,
                ]);
            }
        }

        return redirect()->back()->with('success', 'تم إضافة الصور بنجاح.');
    }

    public function destroy(ProductImage $image)
    {
        // Soft delete: the file stays with the row
        $image->delete();
        return redirect()->back()->with('success', 'تم حذف الصورة بنجاح.');
    }
}
