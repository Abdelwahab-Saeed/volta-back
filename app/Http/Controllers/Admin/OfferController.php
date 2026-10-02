<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReadsListFilters;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OfferController extends Controller
{
    use ReadsListFilters;

    // Admins enter offer dates in Egypt local time; they are stored in UTC (the app timezone).
    public const ADMIN_TIMEZONE = 'Africa/Cairo';

    public function index(Request $request)
    {
        $filters = $this->listFilters($request, [
            'q' => 'search',
            'type' => Offer::TYPES,
            'state' => Offer::STATES,
        ]);

        $offers = Offer::withCount('products')
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->whereTranslationLike(['name'], $term))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['state'] ?? null, fn ($query, $state) => $query->inState($state))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.offers.index', compact('offers', 'filters'));
    }

    public function create()
    {
        $offer = new Offer(['is_active' => true, 'get_discount_percent' => 100]);
        $products = $this->selectableProducts();
        $selectedQuantities = [];

        return view('admin.offers.create', compact('offer', 'products', 'selectedQuantities'));
    }

    public function store(Request $request)
    {
        $products = $this->validateOffer($request);

        $data = $this->offerData($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('offers', 'public');
        }

        $offer = Offer::create($data);
        $offer->products()->sync($products);

        return redirect()->route('admin.offers.index')->with('success', 'تم إنشاء العرض بنجاح.');
    }

    public function edit(Offer $offer)
    {
        $products = $this->selectableProducts();
        $selectedQuantities = $offer->products->mapWithKeys(fn ($p) => [$p->id => (int) $p->pivot->quantity])->all();

        return view('admin.offers.edit', compact('offer', 'products', 'selectedQuantities'));
    }

    public function update(Request $request, Offer $offer)
    {
        $products = $this->validateOffer($request);

        $data = $this->offerData($request);

        if ($request->hasFile('image')) {
            if ($offer->image) {
                Storage::disk('public')->delete($offer->image);
            }
            $data['image'] = $request->file('image')->store('offers', 'public');
        }

        $offer->update($data);
        $offer->products()->sync($products);

        return redirect()->route('admin.offers.index')->with('success', 'تم تحديث العرض بنجاح.');
    }

    public function destroy(Offer $offer)
    {
        // Soft delete: the image stays with the row
        $offer->delete();
        return redirect()->route('admin.offers.index')->with('success', 'تم حذف العرض بنجاح.');
    }

    private function selectableProducts()
    {
        return Product::where('status', true)->orderBy('name_ar')->get();
    }

    /**
     * Validate the form and return the product sync payload: [product_id => ['quantity' => n]].
     */
    private function validateOffer(Request $request): array
    {
        $type = $request->input('type');

        $request->validate([
            'name_ar'              => 'required|string|max:255',
            'name_en'              => 'required|string|max:255',
            'description_ar'       => 'nullable|string',
            'description_en'       => 'nullable|string',
            'image'                => 'nullable|image|max:5120',
            'type'                 => ['required', Rule::in(Offer::TYPES)],
            'bundle_price'         => 'required_if:type,bundle|nullable|numeric|min:0.01',
            'buy_quantity'         => 'required_if:type,buy_x_get_y|nullable|integer|min:1',
            'get_quantity'         => 'required_if:type,buy_x_get_y|nullable|integer|min:1',
            'get_product_id'       => 'nullable|exists:products,id',
            // A different gift product is always free; a percentage only makes sense for units of the same product.
            'get_discount_percent' => ['nullable', 'integer', 'min:1', 'max:100', Rule::when($request->filled('get_product_id'), 'in:100')],
            'starts_at'            => 'nullable|date',
            'expires_at'           => 'nullable|date|after_or_equal:starts_at',
            'is_active'            => 'boolean',
            'products'             => 'required|array|min:1',
            'products.*'           => 'distinct|exists:products,id',
            'quantities'           => 'nullable|array',
            'quantities.*'         => 'nullable|integer|min:1|max:100',
        ], [
            'get_discount_percent.in' => 'المنتج الهدية المختلف يكون مجانياً دائماً (100%).',
            'products.required'       => 'اختر منتجاً واحداً على الأقل للعرض.',
        ]);

        $productIds = array_map('intval', $request->input('products'));

        if ($type !== 'bundle') {
            return array_fill_keys($productIds, ['quantity' => 1]);
        }

        $quantities = collect($productIds)->mapWithKeys(fn ($id) => [$id => (int) ($request->input("quantities.$id") ?: 1)]);

        if ($quantities->sum() < 2) {
            throw ValidationException::withMessages(['products' => 'الباقة يجب أن تحتوي على قطعتين على الأقل (منتجين، أو كمية 2 من منتج واحد).']);
        }

        // A bundle must actually save the customer money at today's prices.
        $regular = Product::whereIn('id', $productIds)->get()->sum(fn (Product $p) => $p->final_price * $quantities[$p->id]);
        if (Money::fromPounds($request->bundle_price) >= $regular) {
            throw ValidationException::withMessages([
                'bundle_price' => 'سعر الباقة يجب أن يكون أقل من سعرها العادي (' . Money::format($regular) . ' ج.م).',
            ]);
        }

        return $quantities->map(fn ($quantity) => ['quantity' => $quantity])->all();
    }

    /**
     * Validated form input → stored units (piasters, UTC dates). Fields of the other type are cleared.
     */
    private function offerData(Request $request): array
    {
        $data = $request->only([
            'name_ar', 'name_en', 'description_ar', 'description_en', 'type',
            'bundle_price', 'buy_quantity', 'get_quantity', 'get_product_id',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['get_discount_percent'] = (int) ($request->input('get_discount_percent') ?: 100);
        $data['starts_at'] = $this->toUtc($request->input('starts_at'));
        $data['expires_at'] = $this->toUtc($request->input('expires_at'));

        if ($data['type'] === 'bundle') {
            $data = array_merge($data, ['buy_quantity' => null, 'get_quantity' => null, 'get_product_id' => null, 'get_discount_percent' => 100]);
        } else {
            $data['bundle_price'] = null;
        }

        return Offer::fromInput($data);
    }

    private function toUtc(?string $local): ?Carbon
    {
        return $local ? Carbon::createFromFormat('Y-m-d\TH:i', $local, self::ADMIN_TIMEZONE)->utc() : null;
    }
}
