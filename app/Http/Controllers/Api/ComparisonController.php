<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComparisonController extends Controller
{
    /**
     * Get all products in the user's comparison list.
     */
    public function index()
    {
        $products = Auth::user()->comparisonList()->with('category')->get();

        return response()->json($products);
    }

    /**
     * Add a product to the user's comparison list.
     * Maximum of 2 products allowed.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = Auth::user();
        
        // Check if the product is already in the comparison list
        if ($user->comparisonList()->where('product_id', $request->product_id)->exists()) {
            return response()->json(['message' => 'Product is already in comparison list'], 422);
        }

        // Check if the limit of 2 is reached
        if ($user->comparisonList()->count() >= 2) {
            return response()->json(['message' => 'Comparison list is full. Maximum of 2 products allowed.'], 422);
        }

        $user->comparisonList()->attach($request->product_id);

        return response()->json(['message' => 'Product added to comparison list successfully']);
    }

    /**
     * Remove a product from the user's comparison list.
     */
    public function destroy($productId)
    {
        $user = Auth::user();
        $user->comparisonList()->detach($productId);

        return response()->json(['message' => 'Product removed from comparison list successfully']);
    }
}
