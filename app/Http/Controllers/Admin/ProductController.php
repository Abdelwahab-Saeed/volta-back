<?php

namespace App\Http\Controllers\Admin;

use App\Support\Money;
use App\Http\Controllers\Admin\Concerns\ManagesUploads;
use App\Http\Controllers\Admin\Concerns\ReadsListFilters;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ManagesUploads, ReadsListFilters;

    // The list marks stock at or below this as "only N left"
    public const LOW_STOCK = 5;

    public function index(Request $request)
    {
        // All categories, including switched-off ones, so their products can still be found
        $categories = Category::orderBy('name_ar')->pluck('name_ar', 'id');

        $filters = $this->listFilters($request, [
            'q' => 'search',
            'category' => $categories->keys()->all(),
            'status' => ['active', 'inactive'],
            'stock' => ['in', 'low', 'out'],
        ]);

        $products = Product::with('category')
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->whereTranslationLike(['name'], $term))
            ->when($filters['category'] ?? null, fn ($query, $id) => $query->where('category_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status === 'active'))
            ->when($filters['stock'] ?? null, fn ($query, $stock) => match ($stock) {
                'in' => $query->where('stock', '>', 0),
                'low' => $query->whereBetween('stock', [1, self::LOW_STOCK]),
                'out' => $query->where('stock', '<=', 0),
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'filters', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('status', true)->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'discount_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:10240',
            'preview_url' => 'nullable|url',
            'status' => 'boolean',
        ]);

        $data = Money::fromPoundsFields($request->except(['image']), ['price', 'discount_price', 'cost_price', 'shipping_cost']);
        $data['status'] = $request->has('status');

        $data['image'] = $this->replaceUpload($request, 'image', null, 'uploads/products');

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'تم إضافة المنتج بنجاح.');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('status', true)->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'features', 'extraImages']);
        return view('admin.products.show', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name_ar' => 'sometimes|required|string|max:255',
            'name_en' => 'sometimes|required|string|max:255',
            'description_ar' => 'sometimes|nullable|string',
            'description_en' => 'sometimes|nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'discount' => 'sometimes|nullable|numeric|min:0|max:100',
            'discount_price' => 'sometimes|nullable|numeric|min:0',
            'cost_price' => 'sometimes|nullable|numeric|min:0',
            'shipping_cost' => 'sometimes|nullable|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'image' => 'nullable|image|max:10240',
            'preview_url' => 'nullable|url',
            'status' => 'sometimes|boolean',
        ]);

        $data = Money::fromPoundsFields($request->except(['image']), ['price', 'discount_price', 'cost_price', 'shipping_cost']);
        $data['status'] = $request->has('status');

        $data['image'] = $this->replaceUpload($request, 'image', $product->image, 'uploads/products');

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'تم تحديث المنتج بنجاح.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'تم حذف المنتج بنجاح.');
    }
}
