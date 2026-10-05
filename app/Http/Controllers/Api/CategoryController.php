<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Traits\ApiResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    // GET ALL
    public function index()
    {
        $categories = Category::where('status', true)->orderByRaw('category_order IS NULL, category_order ASC')->latest()->get();
        return $this->successResponse(\App\Http\Resources\CategoryResource::collection($categories), __('api.categories_fetched'));
    }

    // CREATE
    public function store(Request $request, ImageUploader $images)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'status' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $images->store($request->file('image'), 'categories');
        }

        $category = Category::create($data);

        return $this->successResponse(new \App\Http\Resources\CategoryResource($category), 'تم إضافة القسم بنجاح', 201);
    }

    // SHOW
    public function show(Category $category)
    {
        if (!$category->status) {
            return $this->errorResponse(__('api.category_unavailable'), 404);
        }

        return $this->successResponse(new \App\Http\Resources\CategoryResource($category), __('api.category_fetched'));
    }

    // UPDATE
    public function update(Request $request, Category $category, ImageUploader $images)
    {
        $data = $request->validate([
            'name_ar' => 'sometimes|required|string|max:255',
            'name_en' => 'sometimes|required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'status' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $images->store($request->file('image'), 'categories');
        }

        $category->update($data);

        return $this->successResponse(new \App\Http\Resources\CategoryResource($category), 'تم تحديث بيانات القسم بنجاح');
    }

    // DELETE
    public function destroy(Category $category)
    {
        $category->delete();

        return $this->successResponse(null, 'تم حذف القسم بنجاح');
    }
}

