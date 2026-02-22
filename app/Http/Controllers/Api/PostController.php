<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiResponse;

class PostController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $posts = Post::latest()->paginate($limit);

        return $this->successResponse([
            'posts' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'per_page'     => $posts->perPage(),
                'total_items'  => $posts->total(),
                'total_pages'  => $posts->lastPage(),
                'has_next'     => $posts->hasMorePages(),
                'has_prev'     => $posts->currentPage() > 1,
            ],
        ], __('api.posts_fetched'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create($validated);

        return $this->successResponse($post, __('api.post_created'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return $this->successResponse($post, __('api.post_fetched'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $validated['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($validated);

        return $this->successResponse($post, __('api.post_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete();

        return $this->successResponse(null, __('api.post_deleted'));
    }
}
