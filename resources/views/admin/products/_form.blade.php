{{--
    Shared by create and edit. $product is null on create.
    Money fields are pounds in the form (the controller converts to piasters), same as before.
--}}
@php
    use App\Support\Money;
    $product = $product ?? null;
@endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
    {{-- Main column --}}
    <div class="xl:col-span-2 space-y-6">
        <section class="card p-6 space-y-5">
            <div>
                <h2 class="section-title">المعلومات الأساسية</h2>
                <p class="hint mt-1">الاسم والوصف كما يظهران للعميل بالعربية والإنجليزية.</p>
            </div>

            @include('admin.partials.translatable-field', ['field' => 'name', 'model' => $product])

            <div>
                <label for="category_id" class="label">القسم</label>
                <select name="category_id" id="category_id" class="input @error('category_id') input-error @enderror">
                    <option value="">اختر القسم</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product?->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            @include('admin.partials.translatable-field', ['field' => 'description', 'model' => $product, 'textarea' => true, 'rows' => 6])
        </section>

        <section class="card p-6 space-y-5">
            <div>
                <h2 class="section-title">الصورة والفيديو</h2>
                <p class="hint mt-1">الصورة الرئيسية للمنتج. الصور الإضافية تُدار من "معرض الصور" بعد الحفظ.</p>
            </div>

            @include('admin.partials.image-upload', [
                'name' => 'image',
                'label' => $product ? 'الصورة الرئيسية' : 'صورة المنتج',
                'current' => $product?->image,
                'accept' => 'image/*',
                'hint' => 'PNG أو JPG، يفضل بخلفية بيضاء ومقاس مربع',
                'previewClass' => 'w-36 h-36 object-contain',
            ])

            <div>
                <label for="preview_url" class="label">رابط الفيديو (اختياري)</label>
                <div class="relative">
                    <input type="url" name="preview_url" id="preview_url" value="{{ old('preview_url', $product?->preview_url) }}" placeholder="https://youtube.com/watch?v=..." dir="ltr"
                        class="input text-left pl-10 @error('preview_url') input-error @enderror">
                    <x-admin.icon name="play" class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                </div>
                @error('preview_url')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </section>
    </div>

    {{-- Side column --}}
    <div class="space-y-6 xl:sticky xl:top-24">
        <section class="card p-6 space-y-5">
            <h2 class="section-title">السعر</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="price" class="label">السعر الأصلي</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $product ? Money::toPounds($product->price) : null) }}" class="input pl-12 tabular-nums @error('price') input-error @enderror" oninput="updatePriceSummary()">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">ج.م</span>
                    </div>
                </div>
                <div>
                    <label for="discount_price" class="label">بعد الخصم</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="discount_price" id="discount_price" value="{{ old('discount_price', $product ? Money::toPounds($product->discount_price) : null) }}" class="input pl-12 tabular-nums @error('discount_price') input-error @enderror" oninput="updatePriceSummary()">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">ج.م</span>
                    </div>
                </div>
            </div>
            <p class="hint -mt-2">اترك "بعد الخصم" فارغاً أو 0 إن لم يكن هناك خصم.</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="cost_price" class="label">سعر التكلفة</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="cost_price" id="cost_price" value="{{ old('cost_price', $product ? Money::toPounds($product->cost_price) : null) }}" class="input pl-12 tabular-nums @error('cost_price') input-error @enderror" oninput="updatePriceSummary()">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">ج.م</span>
                    </div>
                </div>
                <div>
                    <label for="shipping_cost" class="label">تكلفة الشحن</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="shipping_cost" id="shipping_cost" value="{{ old('shipping_cost', $product ? Money::toPounds($product->shipping_cost) : 0) }}" class="input pl-12 tabular-nums @error('shipping_cost') input-error @enderror">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">ج.م</span>
                    </div>
                </div>
            </div>

            {{-- Live helper only, nothing here is submitted --}}
            <div id="price-summary" class="rounded-xl bg-slate-50 border border-slate-200 p-3.5 text-sm space-y-1.5 hidden">
                <div class="flex justify-between"><span class="text-slate-500">سعر البيع للعميل</span><span id="ps-final" class="font-extrabold text-navy-900 tabular-nums"></span></div>
                <div class="flex justify-between"><span class="text-slate-500">الربح المتوقع للقطعة</span><span id="ps-profit" class="font-extrabold tabular-nums"></span></div>
            </div>
        </section>

        <section class="card p-6 space-y-5">
            <h2 class="section-title">المخزون والظهور</h2>
            <div>
                <label for="stock" class="label">الكمية المتاحة</label>
                <input type="number" name="stock" id="stock" value="{{ old('stock', $product ? $product->stock : 0) }}" class="input tabular-nums @error('stock') input-error @enderror">
                @error('stock')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <label class="switch">
                <input type="checkbox" name="status" id="status" value="1" class="peer sr-only" {{ $product ? ($product->status ? 'checked' : '') : 'checked' }}>
                <span class="switch-track"></span>
                <span>
                    <span class="block text-sm font-bold text-slate-700">متاح للبيع</span>
                    <span class="block text-xs text-slate-500">أوقفه لإخفائه من المتجر دون حذفه</span>
                </span>
            </label>
        </section>
    </div>
</div>

@push('scripts')
<script>
    function updatePriceSummary() {
        const val = id => parseFloat(document.getElementById(id).value) || 0;
        const price = val('price'), discount = val('discount_price'), cost = val('cost_price');
        const box = document.getElementById('price-summary');
        if (!price) { box.classList.add('hidden'); return; }
        const final = discount > 0 ? discount : price;
        const profit = final - cost;
        const fmt = n => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ج.م';
        document.getElementById('ps-final').textContent = fmt(final);
        const profitEl = document.getElementById('ps-profit');
        profitEl.textContent = cost ? fmt(profit) : '—';
        profitEl.className = 'font-extrabold tabular-nums ' + (!cost ? 'text-slate-400' : profit >= 0 ? 'text-emerald-600' : 'text-red-600');
        box.classList.remove('hidden');
    }
    document.addEventListener('DOMContentLoaded', updatePriceSummary);
</script>
@endpush
