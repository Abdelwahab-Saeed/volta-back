{{--
    Shared by create and edit.
    $offer: the offer (a new, unsaved one on create), $products: selectable products,
    $selectedQuantities: [product_id => quantity per bundle set] for the offer's current products.
--}}
@php
    $tz = \App\Http\Controllers\Admin\OfferController::ADMIN_TIMEZONE;
    $checkedIds = array_map('intval', old('products', array_keys($selectedQuantities)));
    $quantities = old('quantities', $selectedQuantities);
@endphp

{{-- Basic Info --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-bold text-gray-700 mb-2">اسم العرض (عربي) <span class="text-red-500">*</span></label>
        <input type="text" name="name_ar" value="{{ old('name_ar', $offer->name_ar) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" required>
    </div>
    <div>
        <label class="block text-sm font-bold text-gray-700 mb-2">اسم العرض (إنجليزي) <span class="text-red-500">*</span></label>
        <input type="text" name="name_en" value="{{ old('name_en', $offer->name_en) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" required>
    </div>
    <div>
        <label class="block text-sm font-bold text-gray-700 mb-2">الوصف (عربي)</label>
        <textarea name="description_ar" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description_ar', $offer->description_ar) }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-bold text-gray-700 mb-2">الوصف (إنجليزي)</label>
        <textarea name="description_en" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description_en', $offer->description_en) }}</textarea>
    </div>
</div>

{{-- Image --}}
<div>
    <label class="block text-sm font-bold text-gray-700 mb-2">صورة العرض</label>
    @if($offer->image)
        <div class="mb-3 flex items-center gap-3">
            <img src="{{ asset('storage/' . $offer->image) }}" class="w-20 h-20 rounded-xl object-cover border border-gray-200">
            <p class="text-xs text-gray-500">الصورة الحالية — ارفع صورة جديدة للاستبدال</p>
        </div>
    @endif
    <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-bold hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl p-2">
</div>

{{-- Type --}}
<div>
    <label class="block text-sm font-bold text-gray-700 mb-3">نوع العرض <span class="text-red-500">*</span></label>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach([
            'bundle' => ['باقة بسعر ثابت', 'مثال: 3 قطع من منتج بـ 250، أو منتج A + منتج B بـ 150', 'bg-orange-50 border-orange-200 text-orange-700'],
            'buy_x_get_y' => ['اشترِ X واحصل على Y', 'مثال: اشترِ 2 والثالثة مجاناً أو بنصف السعر، أو اشترِ 2 واحصل على منتج هدية', 'bg-green-50 border-green-200 text-green-700'],
        ] as $type => [$label, $hint, $classes])
        <label class="cursor-pointer">
            <input type="radio" name="type" value="{{ $type }}" {{ old('type', $offer->type) === $type ? 'checked' : '' }} class="hidden peer" onchange="showTypeFields(this.value)" required>
            <div class="p-4 border-2 rounded-xl text-center transition-all peer-checked:ring-2 peer-checked:ring-offset-1 {{ $classes }} peer-checked:border-current peer-checked:shadow-md border-gray-200 hover:border-current">
                <div class="text-sm font-bold">{{ $label }}</div>
                <div class="text-xs mt-1 opacity-80">{{ $hint }}</div>
            </div>
        </label>
        @endforeach
    </div>
    <p class="text-xs text-gray-500 mt-2">الخصم على السلة كلها (نسبة أو مبلغ، أو "اصرف X واحصل على خصم") يُعمل ككوبون مع حد أدنى للطلب.</p>
</div>

{{-- Type-specific fields --}}
<div id="fields-bundle" class="type-fields hidden">
    <div class="p-5 bg-orange-50 rounded-xl border border-orange-100">
        <h4 class="font-bold text-orange-800 mb-4">إعدادات الباقة</h4>
        <label class="block text-sm font-bold text-gray-700 mb-2">سعر الباقة الإجمالي (ج.م) <span class="text-red-500">*</span></label>
        <input type="number" name="bundle_price" value="{{ old('bundle_price', \App\Support\Money::toPounds($offer->bundle_price)) }}" min="0" step="0.01" class="w-full md:w-48 px-4 py-3 border border-orange-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400">
        <p class="text-xs text-orange-700 mt-2">اختر المنتجات بالأسفل وحدد كمية كل منتج في الباقة. السعر العادي للباقة: <span id="bundle-regular" class="font-bold">0</span> ج.م — سعر الباقة يجب أن يكون أقل منه.</p>
    </div>
</div>

<div id="fields-buy_x_get_y" class="type-fields hidden">
    <div class="p-5 bg-green-50 rounded-xl border border-green-100">
        <h4 class="font-bold text-green-800 mb-4">إعدادات اشترِ X واحصل على Y</h4>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">اشترِ (الكمية)</label>
                <input type="number" name="buy_quantity" value="{{ old('buy_quantity', $offer->buy_quantity) }}" min="1" class="w-full px-4 py-3 border border-green-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">احصل على (الكمية)</label>
                <input type="number" name="get_quantity" value="{{ old('get_quantity', $offer->get_quantity) }}" min="1" class="w-full px-4 py-3 border border-green-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">نسبة الخصم على هذه الكمية (%)</label>
                <input type="number" name="get_discount_percent" value="{{ old('get_discount_percent', $offer->get_discount_percent ?? 100) }}" min="1" max="100" step="1" class="w-full px-4 py-3 border border-green-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400">
                <p class="text-xs text-green-700 mt-1">100 = مجاناً، 50 = بنصف السعر</p>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">المنتج الهدية</label>
                <select name="get_product_id" class="w-full px-4 py-3 border border-green-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                    <option value="">-- نفس المنتج --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('get_product_id', $offer->get_product_id) == $product->id ? 'selected' : '' }}>{{ $product->name_ar }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <p class="text-xs text-green-700 mt-3">مثال: اشترِ 2 واحصل على 1 = العميل يشتري 3 قطع من نفس المنتج، واحدة منهم بالخصم. لو اخترت منتجاً هدية مختلفاً، يُضاف للطلب مجاناً (100% دائماً). المنتجات بالأسفل هي المنتجات التي يختار العميل منها.</p>
    </div>
</div>

{{-- Products --}}
<div>
    <label class="block text-sm font-bold text-gray-700 mb-3">منتجات العرض <span class="text-red-500">*</span></label>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-80 overflow-y-auto p-3 border border-gray-200 rounded-xl">
        @foreach($products as $product)
        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors">
            <label class="flex items-center gap-2 cursor-pointer flex-1">
                <input type="checkbox" name="products[]" value="{{ $product->id }}" data-price="{{ \App\Support\Money::toPounds($product->final_price) }}"
                    {{ in_array($product->id, $checkedIds) ? 'checked' : '' }}
                    onchange="updateBundleRegular()"
                    class="offer-product w-4 h-4 rounded text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="text-sm text-gray-700 font-medium">{{ $product->name_ar }}</span>
                <span class="text-xs text-gray-400">{{ \App\Support\Money::format($product->final_price) }} ج.م</span>
            </label>
            <label class="bundle-only hidden items-center gap-1 text-xs text-gray-500">
                الكمية
                <input type="number" name="quantities[{{ $product->id }}]" value="{{ $quantities[$product->id] ?? 1 }}" min="1" max="100"
                    oninput="updateBundleRegular()"
                    class="w-16 px-2 py-1 border border-gray-200 rounded-lg text-sm">
            </label>
        </div>
        @endforeach
    </div>
</div>

{{-- Schedule & Status --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div>
        <label class="block text-sm font-bold text-gray-700 mb-2">تاريخ البداية (بتوقيت القاهرة)</label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $offer->starts_at?->timezone($tz)->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-sm font-bold text-gray-700 mb-2">تاريخ الانتهاء (بتوقيت القاهرة)</label>
        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $offer->expires_at?->timezone($tz)->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer pb-3">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $offer->is_active ? '1' : '0') == '1' ? 'checked' : '' }} class="w-5 h-5 rounded text-blue-600 border-gray-300 focus:ring-blue-500">
            <span class="text-sm font-bold text-gray-700">العرض نشط</span>
        </label>
    </div>
</div>

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
    document.querySelectorAll('.offer-product:checked').forEach(box => {
        const qty = document.querySelector(`input[name="quantities[${box.value}]"]`);
        total += parseFloat(box.dataset.price) * (parseInt(qty?.value, 10) || 1);
    });
    const el = document.getElementById('bundle-regular');
    if (el) el.textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name="type"]:checked');
    if (checked) showTypeFields(checked.value);
});
</script>
