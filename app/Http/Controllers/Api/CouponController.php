<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Coupon::all());
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

        return response()->json($coupon, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        return response()->json($coupon);
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

        return response()->json($coupon);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return response()->json(['message' => 'Coupon deleted']);
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
            return response()->json(['message' => 'Invalid coupon code'], 404);
        }

        // 1. Check if user has already used this coupon (if user is logged in)
        if ($user && $coupon->hasBeenUsedByUser($user->id)) {
            return response()->json(['message' => 'You have already used this coupon'], 422);
        }

        // 2. Check max uses limit
        if ($coupon->max_uses && $coupon->times_used >= $coupon->max_uses) {
            return response()->json(['message' => 'Coupon usage limit reached'], 422);
        }

        // 3. General validity (expiry, min amount)
        if (!$coupon->isValid($request->cart_total)) {
             // isValid check might return false for other reasons, let's trust it or improve it to return reason
             // For now, consistent generic message or we can check specific conditions if needed
             // But isValid checks dates and min_amount.
             return response()->json(['message' => 'Coupon is not valid for this order (check minimum amount or expiry)'], 422);
        }

        return response()->json([
            'message' => 'Coupon applied successfully',
            'coupon' => $coupon,
            'discount_amount' => $coupon->calculateDiscount($request->cart_total),
        ]);
    }
}
