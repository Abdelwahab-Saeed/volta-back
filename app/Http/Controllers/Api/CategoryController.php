<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Traits\ApiResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    // GET ALL
    public function index()
    {
        return $this->successResponse(Category::latest()->get(), 'تم جلب الأقسام بنجاح');
    }

    // CREATE
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'status' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        return $this->successResponse($category, 'تم إضافة القسم بنجاح', 201);
    }

    // SHOW
    public function show(Category $category)
    {
        return $this->successResponse($category, 'تم جلب بيانات القسم بنجاح');
    }

    // UPDATE
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'status' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return $this->successResponse($category, 'تم تحديث بيانات القسم بنجاح');
    }

    // DELETE
    public function destroy(Category $category)
    {
        $category->delete();

        return $this->successResponse(null, 'تم حذف القسم بنجاح');
    }
}

