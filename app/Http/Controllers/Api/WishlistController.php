<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Traits\ApiResponse;

class WishlistController extends Controller
{
    use ApiResponse;

    /**
     * Get all products in the user's wishlist.
     */
    public function index()
    {
        $products = Auth::user()->wishlist()->with('category')->get();

        return $this->successResponse($products, 'تم جلب قائمة المفضلة بنجاح');
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

        return $this->successResponse(null, 'تم تحديث قائمة المفضلة بنجاح');
    }
}
