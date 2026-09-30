{{--
    Shared by create and edit.
    $offer: the offer (a new, unsaved one on create), $products: selectable products,
    $selectedQuantities: [product_id => quantity per bundle set] for the offer's current products.

    The script at the bottom relies on: .type-fields / #fields-{type}, .bundle-only, .offer-product (data-price),
    quantities[{id}] inputs and #bundle-regular. Keep those names if you restyle.
--}}
@php
    $tz = \App\Http\Controllers\Admin\OfferController::ADMIN_TIMEZONE;
    $checkedIds = array_map('intval', old('products', array_keys($selectedQuantities)));
    $quantities = old('quantities', $selectedQuantities);
@endphp

{{-- 1. Basic info --}}
<section class="card p-6 space-y-5">
    <div>
        <h2 class="section-title"><span class="w-6 h-6 rounded-full bg-navy-900 text-white text-xs flex items-center justify-center">1</span> بيانات العرض</h2>
        <p class="hint mt-1">الاسم والوصف كما يظهران للعميل في صفحة العروض.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="offer-name-ar" class="label required">اسم العرض (عربي)</label>
            <input id="offer-name-ar" type="text" name="name_ar" value="{{ old('name_ar', $offer->name_ar) }}" class="input @error('name_ar') input-error @enderror" required>
        </div>
        <div>
            <label for="offer-name-en" class="label required">اسم العرض (إنجليزي)</label>
            <input id="offer-name-en" type="text" name="name_en" value="{{ old('name_en', $offer->name_en) }}" dir="ltr" class="input text-left @error('name_en') input-error @enderror" required>
        </div>
        <div>
            <label for="offer-desc-ar" class="label">الوصف (عربي)</label>
            <textarea id="offer-desc-ar" name="description_ar" rows="3" class="input resize-none">{{ old('description_ar', $offer->description_ar) }}</textarea>
        </div>
        <div>
            <label for="offer-desc-en" class="label">الوصف (إنجليزي)</label>
            <textarea id="offer-desc-en" name="description_en" rows="3" dir="ltr" class="input text-left resize-none">{{ old('description_en', $offer->description_en) }}</textarea>
        </div>
    </div>

    @include('admin.partials.image-upload', [
        'name' => 'image',
        'label' => 'صورة العرض',
        'current' => $offer->image,
        'accept' => 'image/*',
        'hint' => 'اختيارية — بدون صورة يظهر العرض بخلفية فولتا الكحلي',
        'previewClass' => 'w-40 h-28 object-cover',
    ])
</section>

{{-- 2. Type and its settings --}}
<section class="card p-6 space-y-5">
    <div>
        <h2 class="section-title"><span class="w-6 h-6 rounded-full bg-navy-900 text-white text-xs flex items-center justify-center">2</span> نوع العرض</h2>
        <p class="hint mt-1">الخصم على السلة كلها (نسبة أو مبلغ، أو "اصرف X واحصل على خصم") يُعمل ككوبون مع حد أدنى للطلب.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach([
            'bundle' => ['باقة بسعر ثابت', 'مثال: 3 قطع من منتج بـ 250، أو منتج A + منتج B بـ 150', 'cube'],
            'buy_x_get_y' => ['اشترِ X واحصل على Y', 'مثال: اشترِ 2 والثالثة مجاناً أو بنصف السعر، أو اشترِ 2 واحصل على منتج هدية', 'sparkles'],
        ] as $type => [$label, $hint, $icon])
        <label class="cursor-pointer">
            <input type="radio" name="type" value="{{ $type }}" {{ old('type', $offer->type) === $type ? 'checked' : '' }} class="hidden peer" onchange="showTypeFields(this.value)" required>
            <div class="h-full flex gap-3 p-4 rounded-2xl border-2 border-slate-200 transition hover:border-slate-300 peer-checked:border-brand-500 peer-checked:bg-brand-50/50 peer-checked:shadow-sm">
                <span class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-brand-600 flex items-center justify-center shrink-0"><x-admin.icon :name="$icon" class="w-5 h-5" /></span>
                <span>
                    <span class="block text-sm font-extrabold text-navy-900">{{ $label }}</span>
                    <span class="block text-xs text-slate-500 mt-1 leading-relaxed">{{ $hint }}</span>
                </span>
            </div>
        </label>
        @endforeach
    </div>

    {{-- Type-specific fields --}}
    <div id="fields-bundle" class="type-fields hidden">
        <div class="rounded-2xl border border-brand-200 bg-brand-50/40 p-5">
            <h3 class="font-extrabold text-navy-900 mb-4">إعدادات الباقة</h3>
            <label for="bundle-price" class="label required">سعر الباقة الإجمالي</label>
            <div class="relative w-full md:w-56">
                <input id="bundle-price" type="number" name="bundle_price" value="{{ old('bundle_price', \App\Support\Money::toPounds($offer->bundle_price)) }}" min="0" step="0.01" class="input pl-12 tabular-nums">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">ج.م</span>
            </div>
            <p class="hint">اختر المنتجات بالأسفل وحدد كمية كل منتج في الباقة. السعر العادي للباقة: <span id="bundle-regular" class="font-extrabold text-navy-900">0</span> ج.م — سعر الباقة يجب أن يكون أقل منه.</p>
        </div>
    </div>

    <div id="fields-buy_x_get_y" class="type-fields hidden">
        <div class="rounded-2xl border border-brand-200 bg-brand-50/40 p-5">
            <h3 class="font-extrabold text-navy-900 mb-4">إعدادات اشترِ X واحصل على Y</h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="buy-qty" class="label">اشترِ (الكمية)</label>
                    <input id="buy-qty" type="number" name="buy_quantity" value="{{ old('buy_quantity', $offer->buy_quantity) }}" min="1" class="input tabular-nums">
                </div>
                <div>
                    <label for="get-qty" class="label">احصل على (الكمية)</label>
                    <input id="get-qty" type="number" name="get_quantity" value="{{ old('get_quantity', $offer->get_quantity) }}" min="1" class="input tabular-nums">
                </div>
                <div>
                    <label for="get-discount" class="label">نسبة الخصم عليها</label>
                    <div class="relative">
                        <input id="get-discount" type="number" name="get_discount_percent" value="{{ old('get_discount_percent', $offer->get_discount_percent ?? 100) }}" min="1" max="100" step="1" class="input pl-9 tabular-nums">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">%</span>
                    </div>
                    <p class="hint">100 = مجاناً، 50 = بنصف السعر</p>
                </div>
                <div>
                    <label for="get-product" class="label">المنتج الهدية</label>
                    <select id="get-product" name="get_product_id" class="input">
                        <option value="">-- نفس المنتج --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('get_product_id', $offer->get_product_id) == $product->id ? 'selected' : '' }}>{{ $product->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p class="hint mt-3">مثال: اشترِ 2 واحصل على 1 = العميل يشتري 3 قطع من نفس المنتج، واحدة منهم بالخصم. لو اخترت منتجاً هدية مختلفاً، يُضاف للطلب مجاناً (100% دائماً). المنتجات بالأسفل هي المنتجات التي يختار العميل منها.</p>
        </div>
    </div>
</section>

{{-- 3. Products --}}
<section class="card p-6 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <h2 class="section-title"><span class="w-6 h-6 rounded-full bg-navy-900 text-white text-xs flex items-center justify-center">3</span> <span class="required">منتجات العرض</span></h2>
            <p class="hint mt-1">المحدد: <span id="offer-selected-count" class="font-extrabold text-navy-900">0</span> منتج</p>
        </div>
        <div class="relative sm:w-72">
            <input type="search" id="offer-product-search" placeholder="ابحث عن منتج…" class="input h-10 pr-9" oninput="filterOfferProducts(this.value)" aria-label="ابحث عن منتج">
            <x-admin.icon name="search" class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-96 overflow-y-auto p-2 rounded-2xl border border-slate-200 bg-slate-50/50">
        @foreach($products as $product)
        <div class="offer-product-row flex items-center gap-3 p-2.5 rounded-xl bg-white border border-slate-200/70 has-[:checked]:border-brand-400 has-[:checked]:bg-brand-50/60 transition" data-name="{{ mb_strtolower($product->name_ar . ' ' . $product->name_en) }}">
            <label class="flex items-center gap-2.5 cursor-pointer flex-1 min-w-0">
                <input type="checkbox" name="products[]" value="{{ $product->id }}" data-price="{{ \App\Support\Money::toPounds($product->final_price) }}"
                    {{ in_array($product->id, $checkedIds) ? 'checked' : '' }}
                    onchange="updateBundleRegular()"
                    class="offer-product checkbox">
                <span class="text-sm text-slate-800 font-semibold truncate">{{ $product->name_ar }}</span>
                <span class="text-xs text-slate-400 whitespace-nowrap tabular-nums">{{ \App\Support\Money::format($product->final_price) }} ج.م</span>
            </label>
            <label class="bundle-only hidden items-center gap-1.5 text-xs font-bold text-slate-500">
                الكمية
                <input type="number" name="quantities[{{ $product->id }}]" value="{{ $quantities[$product->id] ?? 1 }}" min="1" max="100"
                    oninput="updateBundleRegular()"
                    class="input h-8 w-16 px-2 text-center tabular-nums">
            </label>
        </div>
        @endforeach
    </div>
</section>

{{-- 4. Schedule & status --}}
<section class="card p-6 space-y-5">
    <h2 class="section-title"><span class="w-6 h-6 rounded-full bg-navy-900 text-white text-xs flex items-center justify-center">4</span> المدة والتفعيل</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-end">
        <div>
            <label for="offer-starts" class="label">تاريخ البداية (بتوقيت القاهرة)</label>
            <input id="offer-starts" type="datetime-local" name="starts_at" value="{{ old('starts_at', $offer->starts_at?->timezone($tz)->format('Y-m-d\TH:i')) }}" class="input">
        </div>
        <div>
            <label for="offer-expires" class="label">تاريخ الانتهاء (بتوقيت القاهرة)</label>
            <input id="offer-expires" type="datetime-local" name="expires_at" value="{{ old('expires_at', $offer->expires_at?->timezone($tz)->format('Y-m-d\TH:i')) }}" class="input">
        </div>
        <div class="pb-2.5">
            <label class="switch">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $offer->is_active ? '1' : '0') == '1' ? 'checked' : '' }} class="peer sr-only">
                <span class="switch-track"></span>
                <span class="text-sm font-bold text-slate-700">العرض نشط</span>
            </label>
        </div>
    </div>
    <p class="hint">اترك التاريخين فارغين ليبدأ العرض فوراً ولا ينتهي.</p>
</section>

<script>
function showTypeFields(type) {
    // Hidden sections are disabled too, so their inputs are not submitted.
    document.querySelectorAll('.type-fields').forEach(el => {
        el.classList.add('hidden');
        el.querySelectorAll('input, select').forEach(input => input.disabled = true);
    });
    const target = document.getElementById('fields-' + type);
    if (target) {
        target.classList.remove('hidden');
        target.querySelectorAll('input, select').forEach(input => input.disabled = false);
    }
    // Per-product quantities only mean something for a bundle.
    document.querySelectorAll('.bundle-only').forEach(el => {
        el.classList.toggle('hidden', type !== 'bundle');
        el.classList.toggle('flex', type === 'bundle');
    });
    updateBundleRegular();
}

// The bundle's price without the offer, so the admin sets a bundle price below it.
function updateBundleRegular() {
    let total = 0;
    const checked = document.querySelectorAll('.offer-product:checked');
    checked.forEach(box => {
        const qty = document.querySelector(`input[name="quantities[${box.value}]"]`);
        total += parseFloat(box.dataset.price) * (parseInt(qty?.value, 10) || 1);
    });
    const el = document.getElementById('bundle-regular');
    if (el) el.textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const count = document.getElementById('offer-selected-count');
    if (count) count.textContent = checked.length;
}

// Only hides rows on screen; hidden checked products are still submitted.
function filterOfferProducts(query) {
    const q = query.trim().toLowerCase();
    document.querySelectorAll('.offer-product-row').forEach(row => {
        row.classList.toggle('hidden', q !== '' && !row.dataset.name.includes(q));
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name="type"]:checked');
    if (checked) showTypeFields(checked.value);
    updateBundleRegular();
});
</script>
