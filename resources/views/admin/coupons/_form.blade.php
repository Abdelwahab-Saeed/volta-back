{{-- Shared by create and edit. $coupon is null on create. Amounts are pounds in the form, as before. --}}
@php
    $coupon = $coupon ?? null;
    $type = old('type', $coupon?->type ?? 'fixed');
@endphp
<div class="space-y-8">
    <section class="space-y-5">
        <h2 class="section-title">الكود والخصم</h2>
        <div>
            <label for="code" class="label required">كود الكوبون</label>
            <input type="text" name="code" id="code" value="{{ old('code', $coupon?->code) }}" placeholder="مثلاً: SAVE20" dir="ltr"
                class="input text-left font-extrabold tracking-wider uppercase sm:w-72 @error('code') input-error @enderror">
            <p class="hint">الكود الذي يكتبه العميل عند الدفع.</p>
            @error('code')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <span class="label">نوع الخصم</span>
            <div class="grid grid-cols-2 gap-3 sm:w-[28rem]">
                @foreach (['fixed' => ['مبلغ ثابت', 'مثال: خصم 50 ج.م'], 'percent' => ['نسبة مئوية', 'مثال: خصم 10%']] as $value => [$label, $example])
                    <label class="relative flex flex-col gap-0.5 rounded-xl border-2 border-slate-200 p-3.5 cursor-pointer transition hover:border-slate-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50">
                        <input type="radio" name="type" value="{{ $value }}" class="sr-only" {{ $type === $value ? 'checked' : '' }} onchange="updateCouponUnit()">
                        <span class="text-sm font-bold text-navy-900">{{ $label }}</span>
                        <span class="text-xs text-slate-500">{{ $example }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="value" class="label required">قيمة الخصم</label>
                <div class="relative">
                    <input type="number" name="value" id="value" value="{{ old('value', $coupon ? ($coupon->type === 'fixed' ? \App\Support\Money::toPounds($coupon->value) : $coupon->value) : null) }}" placeholder="مثلاً: 10"
                        class="input pl-12 tabular-nums @error('value') input-error @enderror">
                    <span id="value-unit" class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">ج.م</span>
                </div>
                @error('value')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="min_order_amount" class="label">الحد الأدنى للطلب</label>
                <div class="relative">
                    <input type="number" name="min_order_amount" id="min_order_amount" value="{{ old('min_order_amount', $coupon ? \App\Support\Money::toPounds($coupon->min_order_amount) : null) }}" placeholder="اختياري"
                        class="input pl-12 tabular-nums @error('min_order_amount') input-error @enderror">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">ج.م</span>
                </div>
                <p class="hint">اتركه فارغاً ليعمل الكوبون على أي طلب.</p>
            </div>
        </div>
    </section>

    <section class="space-y-5 pt-6 border-t border-slate-100">
        <h2 class="section-title">حدود الاستخدام والمدة</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label for="max_uses" class="label">أقصى عدد للاستخدام</label>
                <input type="number" name="max_uses" id="max_uses" value="{{ old('max_uses', $coupon?->max_uses) }}" placeholder="بلا حد" class="input tabular-nums @error('max_uses') input-error @enderror">
            </div>
            <div>
                <label for="starts_at" class="label">يبدأ في</label>
                <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at', $coupon?->starts_at?->format('Y-m-d\TH:i')) }}" class="input @error('starts_at') input-error @enderror">
            </div>
            <div>
                <label for="expires_at" class="label">ينتهي في</label>
                <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at', $coupon?->expires_at?->format('Y-m-d\TH:i')) }}" class="input @error('expires_at') input-error @enderror">
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    function updateCouponUnit() {
        const type = document.querySelector('input[name=type]:checked')?.value;
        document.getElementById('value-unit').textContent = type === 'percent' ? '%' : 'ج.م';
    }
    document.addEventListener('DOMContentLoaded', updateCouponUnit);
</script>
@endpush
