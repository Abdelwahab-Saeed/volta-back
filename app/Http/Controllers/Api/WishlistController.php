<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Traits\ApiResponse;

class WishlistController extends Controller
{
    use ApiResponse;

    protected $metaService;

    public function __construct(\App\Services\MetaService $metaService)
    {
        $this->metaService = $metaService;
    }

    /**
     * Get all products in the user's wishlist.
     */
    public function index()
    {
        $products = Auth::user()->wishlist()->visible()->with('category')->get();

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
        $wishlisted = $user->wishlist()->where('product_id', $request->product_id)->exists();

        // A product that is no longer sold can still be removed from the list, but not added to it
        if (!$wishlisted && !\App\Models\Product::visible()->whereKey($request->product_id)->exists()) {
            return $this->errorResponse(__('api.product_unavailable'), 422);
        }

        $user->wishlist()->toggle($request->product_id);

        return $this->successResponse(null, 'تم تحديث قائمة المفضلة بنجاح');
    }
}
