<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfferController extends Controller
{
    public function index()
    {
        $offers = Offer::withCount('products')->latest()->paginate(15);
        return view('admin.offers.index', compact('offers'));
    }

    public function create()
    {
        $products = Product::where('status', true)->orderBy('name_ar')->get();
        return view('admin.offers.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ar'         => 'required|string|max:255',
            'name_en'         => 'required|string|max:255',
            'description_ar'  => 'nullable|string',
            'description_en'  => 'nullable|string',
            'image'           => 'nullable|image|max:5120',
            'type'            => 'required|in:percentage,fixed,bundle,buy_x_get_y,spend_x_get_y',
            'value'           => 'required_if:type,percentage,fixed|nullable|numeric|min:0',
            'buy_quantity'    => 'required_if:type,buy_x_get_y|nullable|integer|min:1',
            'get_quantity'    => 'required_if:type,buy_x_get_y|nullable|integer|min:1',
            'get_product_id'  => 'nullable|exists:products,id',
            'min_spend'       => 'required_if:type,spend_x_get_y|nullable|numeric|min:0',
            'discount_amount' => 'required_if:type,spend_x_get_y|nullable|numeric|min:0',
            'bundle_price'    => 'required_if:type,bundle|nullable|numeric|min:0',
            'starts_at'       => 'nullable|date',
            'expires_at'      => 'nullable|date|after_or_equal:starts_at',
            'is_active'       => 'boolean',
            'products'        => 'nullable|array',
            'products.*'      => 'exists:products,id',
        ]);

        $data = $request->except(['image', 'products', '_token']);
        $data['is_active'] = $request->boolean('is_active', true);

        // Convert Egypt local time (UTC+3) → UTC for correct active-scope comparisons
        if (!empty($data['starts_at'])) {
            $data['starts_at'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $data['starts_at'], 'Africa/Cairo')->utc();
        }
        if (!empty($data['expires_at'])) {
            $data['expires_at'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $data['expires_at'], 'Africa/Cairo')->utc();
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('offers', 'public');
        }

        $offer = Offer::create($data);

        if ($request->filled('products')) {
            $offer->products()->sync($request->products);
        }

        return redirect()->route('admin.offers.index')->with('success', 'تم إنشاء العرض بنجاح.');
    }

    public function show(Offer $offer)
    {
        $offer->load('products');
        return view('admin.offers.show', compact('offer'));
    }

    public function edit(Offer $offer)
    {
        $products       = Product::where('status', true)->orderBy('name_ar')->get();
        $selectedIds    = $offer->products->pluck('id')->toArray();
        return view('admin.offers.edit', compact('offer', 'products', 'selectedIds'));
    }

    public function update(Request $request, Offer $offer)
    {
        $request->validate([
            'name_ar'         => 'required|string|max:255',
            'name_en'         => 'required|string|max:255',
            'description_ar'  => 'nullable|string',
            'description_en'  => 'nullable|string',
            'image'           => 'nullable|image|max:5120',
            'type'            => 'required|in:percentage,fixed,bundle,buy_x_get_y,spend_x_get_y',
            'value'           => 'required_if:type,percentage,fixed|nullable|numeric|min:0',
            'buy_quantity'    => 'required_if:type,buy_x_get_y|nullable|integer|min:1',
            'get_quantity'    => 'required_if:type,buy_x_get_y|nullable|integer|min:1',
            'get_product_id'  => 'nullable|exists:products,id',
            'min_spend'       => 'required_if:type,spend_x_get_y|nullable|numeric|min:0',
            'discount_amount' => 'required_if:type,spend_x_get_y|nullable|numeric|min:0',
            'bundle_price'    => 'required_if:type,bundle|nullable|numeric|min:0',
            'starts_at'       => 'nullable|date',
            'expires_at'      => 'nullable|date|after_or_equal:starts_at',
            'is_active'       => 'boolean',
            'products'        => 'nullable|array',
            'products.*'      => 'exists:products,id',
        ]);

        $data = $request->except(['image', 'products', '_token', '_method']);
        $data['is_active'] = $request->boolean('is_active', true);

        // Convert Egypt local time (UTC+3) → UTC
        if (!empty($data['starts_at'])) {
            $data['starts_at'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $data['starts_at'], 'Africa/Cairo')->utc();
        }
        if (!empty($data['expires_at'])) {
            $data['expires_at'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $data['expires_at'], 'Africa/Cairo')->utc();
        }

        if ($request->hasFile('image')) {
            if ($offer->image) {
                Storage::disk('public')->delete($offer->image);
            }
            $data['image'] = $request->file('image')->store('offers', 'public');
        }

        $offer->update($data);
        $offer->products()->sync($request->products ?? []);

        return redirect()->route('admin.offers.index')->with('success', 'تم تحديث العرض بنجاح.');
    }

    public function destroy(Offer $offer)
    {
        if ($offer->image) {
            Storage::disk('public')->delete($offer->image);
        }
        $offer->delete();
        return redirect()->route('admin.offers.index')->with('success', 'تم حذف العرض بنجاح.');
    }
}
