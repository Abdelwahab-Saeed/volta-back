<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReadsListFilters;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    use ReadsListFilters;

    public function index(Request $request)
    {
        $filters = $this->listFilters($request, [
            'q' => 'search',
            'type' => Coupon::TYPES,
            'state' => Coupon::STATES,
        ]);

        $coupons = Coupon::query()
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where('code', 'like', "%{$term}%"))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['state'] ?? null, fn ($query, $state) => $query->inState($state))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.coupons.index', compact('coupons', 'filters'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => ['required', 'numeric', 'min:0', Rule::when($request->input('type') === 'percent', ['integer', 'max:100'])],
            'min_order_amount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        Coupon::create(Coupon::fromInput($request->all()));

        return redirect()->route('admin.coupons.index')->with('success', 'تم إضافة الكوبون بنجاح.');
    }

    public function show(Coupon $coupon)
    {
        return view('admin.coupons.show', compact('coupon'));
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:fixed,percent',
            'value' => ['required', 'numeric', 'min:0', Rule::when($request->input('type') === 'percent', ['integer', 'max:100'])],
            'min_order_amount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        $coupon->update(Coupon::fromInput($request->all(), $coupon->type));

        return redirect()->route('admin.coupons.index')->with('success', 'تم تحديث الكوبون بنجاح.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('admin.coupons.index')->with('success', 'تم حذف الكوبون بنجاح.');
    }
}
