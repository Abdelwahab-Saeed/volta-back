<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Get all products in the user's wishlist.
     */
    public function index()
    {
        $products = Auth::user()->wishlist()->with('category')->get();

        return response()->json($products);
    }

    /**
     * Toggle a product in/out of the user's wishlist.
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = Auth::user();
        $user->wishlist()->toggle($request->product_id);

        return response()->json(['message' => 'Wishlist updated successfully']);
    }
}
