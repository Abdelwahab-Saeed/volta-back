@extends('admin.layouts.app')

@section('title', 'تعديل الكوبون')

@section('content')
<div class="max-w-4xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-right">
    <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Code -->
            <div class="md:col-span-2">
                <label for="code" class="block text-sm font-bold text-gray-700 mb-2">كود الكوبون</label>
                <input type="text" name="code" id="code" value="{{ old('code', $coupon->code) }}" placeholder="مثلاً: SAVE20"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none font-black uppercase">
            </div>

            <!-- Type -->
            <div>
                <label for="type" class="block text-sm font-bold text-gray-700 mb-2">نوع الخصم</label>
                <select name="type" id="type"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none bg-white">
                    <option value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'selected' : '' }}>مبلغ ثابت</option>
                    <option value="percent" {{ old('type', $coupon->type) == 'percent' ? 'selected' : '' }}>نسبة مئوية</option>
                </select>
            </div>

            <!-- Value -->
            <div>
                <label for="value" class="block text-sm font-bold text-gray-700 mb-2">قيمة الخصم</label>
                <input type="number" name="value" id="value" value="{{ old('value', $coupon->value) }}" placeholder="مثلاً: 10"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
            </div>

            <!-- Min Order Amount -->
            <div>
                <label for="min_order_amount" class="block text-sm font-bold text-gray-700 mb-2">الحد الأدنى للطلب</label>
                <input type="number" name="min_order_amount" id="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" placeholder="اختياري"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
            </div>

            <!-- Max Uses -->
            <div>
                <label for="max_uses" class="block text-sm font-bold text-gray-700 mb-2">أقصى عدد للاستخدام</label>
                <input type="number" name="max_uses" id="max_uses" value="{{ old('max_uses', $coupon->max_uses) }}" placeholder="اختياري"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
            </div>

            <!-- Starts At -->
            <div>
                <label for="starts_at" class="block text-sm font-bold text-gray-700 mb-2">يبدأ في</label>
                <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
            </div>

            <!-- Expires At -->
            <div>
                <label for="expires_at" class="block text-sm font-bold text-gray-700 mb-2">ينتهي في</label>
                <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
            </div>

            <div class="md:col-span-2 pt-4 flex space-x-reverse space-x-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-xl font-bold transition-all shadow-md">
                    تحديث الكوبون
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="px-8 py-2.5 text-gray-500 hover:bg-gray-100 rounded-xl transition-all font-bold">
                    إلغاء
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
