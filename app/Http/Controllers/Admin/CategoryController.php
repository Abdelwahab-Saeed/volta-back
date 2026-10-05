<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesUploads;
use App\Http\Controllers\Admin\Concerns\ReadsListFilters;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ManagesUploads, ReadsListFilters;

    public function index(Request $request)
    {
        $filters = $this->listFilters($request, [
            'q' => 'search',
            'status' => ['active', 'inactive'],
        ]);

        $categories = Category::withCount('products')
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->whereTranslationLike(['name'], $term))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status === 'active'))
            ->orderByRaw('category_order IS NULL, category_order ASC')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'filters'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'status' => 'boolean',
            'category_order' => 'nullable|integer',
        ]);

        $data = $request->except(['image']);
        $data['status'] = $request->has('status');

        $data['image'] = $this->replaceUpload($request, 'image', null, 'uploads/categories');

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'تم إضافة القسم بنجاح.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'status' => 'boolean',
            'category_order' => 'nullable|integer',
        ]);

        $data = $request->except(['image']);
        $data['status'] = $request->has('status');

        $data['image'] = $this->replaceUpload($request, 'image', $category->image, 'uploads/categories');

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'تم تحديث القسم بنجاح.');
    }

    public function destroy(Category $category)
    {
        // For soft delete, we usually just call delete()
        // Note: Image remains in storage for future potential restore
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'تم حذف القسم بنجاح.');
    }

    public function updateOrder(Request $request, Category $category)
    {
        $request->validate([
            'category_order' => 'nullable|integer',
        ]);

        $category->update([
            'category_order' => $request->category_order,
        ]);

        return back()->with('success', 'تم تحديث الترتيب بنجاح.');
    }
}
