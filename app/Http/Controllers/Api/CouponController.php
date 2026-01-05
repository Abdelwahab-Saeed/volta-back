<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Traits\ApiResponse;

class CouponController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->successResponse(Coupon::all(), 'تم جلب الكوبونات بنجاح');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        $coupon = Coupon::create($validated);

        return $this->successResponse($coupon, 'تم إضافة الكوبون بنجاح', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        return $this->successResponse($coupon, 'تم جلب بيانات الكوبون بنجاح');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => ['sometimes', 'string', Rule::unique('coupons')->ignore($coupon->id)],
            'type' => 'sometimes|in:fixed,percent',
            'value' => 'sometimes|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        $coupon->update($validated);

        return $this->successResponse($coupon, 'تم تحديث بيانات الكوبون بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return $this->successResponse(null, 'تم حذف الكوبون بنجاح');
    }

    /**
     * Apply a coupon to verify its validity and calculate discount.
     * Does NOT increment usage counts.
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'cart_total' => 'required|numeric|min:0',
        ]);

        $user = auth('sanctum')->user(); // Get user if authenticated

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return $this->errorResponse('كود الكوبون غير صحيح', 404);
        }

        // 1. Check if user has already used this coupon (if user is logged in)
        if ($user && $coupon->hasBeenUsedByUser($user->id)) {
            return $this->errorResponse('لقد قمت باستخدام هذا الكوبون من قبل', 422);
        }

        // 2. Check max uses limit
        if ($coupon->max_uses && $coupon->times_used >= $coupon->max_uses) {
            return $this->errorResponse('عذراً، وصل الكوبون للحد الأقصى من الاستخدام', 422);
        }

        // 3. General validity (expiry, min amount)
        if (!$coupon->isValid($request->cart_total)) {
             return $this->errorResponse('الكوبون غير صالح لهذا الطلب (تحقق من الحد الأدنى أو تاريخ انتهاء الصلاحية)', 422);
        }

        return $this->successResponse([
            'coupon' => $coupon,
            'discount_amount' => $coupon->calculateDiscount($request->cart_total),
        ], 'تم تطبيق الكوبون بنجاح');
    }
}
