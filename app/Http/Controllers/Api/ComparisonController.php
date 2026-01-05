<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Traits\ApiResponse;

class ComparisonController extends Controller
{
    use ApiResponse;

    /**
     * Get all products in the user's comparison list.
     */
    public function index()
    {
        $products = Auth::user()->comparisonList()->with('category')->get();

        return $this->successResponse($products, 'تم جلب قائمة المقارنة بنجاح');
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
            return $this->errorResponse('المنتج موجود بالفعل في قائمة المقارنة', 422);
        }

        // Check if the limit of 2 is reached
        if ($user->comparisonList()->count() >= 2) {
            return $this->errorResponse('عذراً، يمكنك إضافة منتجين فقط للمقارنة', 422);
        }

        $user->comparisonList()->attach($request->product_id);

        return $this->successResponse(null, 'تم إضافة المنتج لقائمة المقارنة بنجاح');
    }

    /**
     * Remove a product from the user's comparison list.
     */
    public function destroy($productId)
    {
        $user = Auth::user();
        $user->comparisonList()->detach($productId);

        return $this->successResponse(null, 'تم إزالة المنتج من قائمة المقارنة بنجاح');
    }
}
