<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Traits\ApiResponse;

class ProductController extends Controller
{
    use ApiResponse;

    // GET PRODUCTS (public for users)
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $sort = $request->get('sort', 'desc');
        $limit = $request->get('limit', 10);

        $products = $query
            ->orderBy('price', $sort)
            ->paginate($limit);

        return $this->successResponse([
            'items' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'per_page'     => $products->perPage(),
                'total_items'  => $products->total(),
                'total_pages'  => $products->lastPage(),
                'from'         => $products->firstItem(),
                'to'           => $products->lastItem(),
                'has_next'     => $products->hasMorePages(),
                'has_prev'     => $products->currentPage() > 1,
            ],
        ], 'تم جلب المنتجات بنجاح');
    }

    // STORE (ADMIN)
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return $this->successResponse($product, 'تم إضافة المنتج بنجاح', 201);
    }

    // SHOW
    public function show(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        return $this->successResponse($product->load('category'), 'تم جلب بيانات المنتج بنجاح');
    }

    // UPDATE (ADMIN)
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return $this->successResponse($product, 'تم تحديث بيانات المنتج بنجاح');
    }

    // DELETE (ADMIN)
    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return $this->successResponse(null, 'تم حذف المنتج بنجاح');
    }

    /**
     * Get best selling products.
     */
    public function bestSelling(Request $request)
    {
        $limit = $request->get('limit', 10);

        $products = Product::with('category')
            ->withCount(['orderItems as total_sold' => function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw('sum(quantity)'));
            }])
            ->orderByDesc('total_sold')
            ->take($limit)
            ->get();

        return $this->successResponse($products, 'تم جلب المنتجات الأكثر مبيعاً بنجاح');
    }
}
