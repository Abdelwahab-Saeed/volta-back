@extends('admin.layouts.app')

@section('title', 'تعديل العرض: ' . $offer->name_ar)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">تعديل العرض</h3>
            <a href="{{ route('admin.offers.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                رجوع
            </a>
        </div>

        <form action="{{ route('admin.offers.update', $offer) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-8">
            @csrf
            @method('PUT')

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
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    @foreach(['percentage' => ['نسبة مئوية', 'bg-purple-50 border-purple-200 text-purple-700'], 'fixed' => ['مبلغ ثابت', 'bg-blue-50 border-blue-200 text-blue-700'], 'bundle' => ['باقة منتجات', 'bg-orange-50 border-orange-200 text-orange-700'], 'buy_x_get_y' => ['اشترِ X احصل Y', 'bg-green-50 border-green-200 text-green-700'], 'spend_x_get_y' => ['اصرف X احصل Y', 'bg-pink-50 border-pink-200 text-pink-700']] as $type => $info)
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="{{ $type }}" {{ old('type', $offer->type) === $type ? 'checked' : '' }} class="hidden peer" onchange="showTypeFields(this.value)" required>
                        <div class="p-3 border-2 rounded-xl text-center text-xs font-bold transition-all peer-checked:ring-2 peer-checked:ring-offset-1 {{ $info[1] }} peer-checked:border-current peer-checked:shadow-md border-gray-200 hover:border-current">
                            {{ $info[0] }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Type-specific fields --}}
            <div id="fields-percentage" class="type-fields hidden">
                <div class="p-5 bg-purple-50 rounded-xl border border-purple-100">
                    <h4 class="font-bold text-purple-800 mb-4">إعدادات الخصم النسبي</h4>
                    <label class="block text-sm font-bold text-gray-700 mb-2">نسبة الخصم (%)</label>
                    <input type="number" name="value" value="{{ old('value', $offer->value) }}" min="1" max="100" step="0.01" class="w-full md:w-48 px-4 py-3 border border-purple-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-400">
                </div>
            </div>

            <div id="fields-fixed" class="type-fields hidden">
                <div class="p-5 bg-blue-50 rounded-xl border border-blue-100">
                    <h4 class="font-bold text-blue-800 mb-4">إعدادات الخصم الثابت</h4>
                    <label class="block text-sm font-bold text-gray-700 mb-2">مبلغ الخصم (ج.م)</label>
                    <input type="number" name="value" value="{{ old('value', $offer->value) }}" min="0" step="0.01" class="w-full md:w-48 px-4 py-3 border border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            <div id="fields-bundle" class="type-fields hidden">
                <div class="p-5 bg-orange-50 rounded-xl border border-orange-100">
                    <h4 class="font-bold text-orange-800 mb-4">إعدادات الباقة</h4>
                    <label class="block text-sm font-bold text-gray-700 mb-2">سعر الباقة الإجمالي (ج.م)</label>
                    <input type="number" name="bundle_price" value="{{ old('bundle_price', $offer->bundle_price) }}" min="0" step="0.01" class="w-full md:w-48 px-4 py-3 border border-orange-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
            </div>

            <div id="fields-buy_x_get_y" class="type-fields hidden">
                <div class="p-5 bg-green-50 rounded-xl border border-green-100">
                    <h4 class="font-bold text-green-800 mb-4">إعدادات اشترِ X واحصل على Y</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">اشترِ (الكمية)</label>
                            <input type="number" name="buy_quantity" value="{{ old('buy_quantity', $offer->buy_quantity) }}" min="1" class="w-full px-4 py-3 border border-green-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">احصل على (مجاناً)</label>
                            <input type="number" name="get_quantity" value="{{ old('get_quantity', $offer->get_quantity) }}" min="1" class="w-full px-4 py-3 border border-green-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">المنتج المجاني</label>
                            <select name="get_product_id" class="w-full px-4 py-3 border border-green-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                                <option value="">-- نفس المنتج --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('get_product_id', $offer->get_product_id) == $product->id ? 'selected' : '' }}>{{ $product->name_ar }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div id="fields-spend_x_get_y" class="type-fields hidden">
                <div class="p-5 bg-pink-50 rounded-xl border border-pink-100">
                    <h4 class="font-bold text-pink-800 mb-4">إعدادات اصرف X واحصل على خصم Y</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">الحد الأدنى للإنفاق (ج.م)</label>
                            <input type="number" name="min_spend" value="{{ old('min_spend', $offer->min_spend) }}" min="0" step="0.01" class="w-full px-4 py-3 border border-pink-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">قيمة الخصم (ج.م)</label>
                            <input type="number" name="discount_amount" value="{{ old('discount_amount', $offer->discount_amount) }}" min="0" step="0.01" class="w-full px-4 py-3 border border-pink-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Products --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-3">المنتجات المشمولة بالعرض</label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 max-h-64 overflow-y-auto p-3 border border-gray-200 rounded-xl">
                    @foreach($products as $product)
                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50 transition-colors">
                        <input type="checkbox" name="products[]" value="{{ $product->id }}"
                            {{ in_array($product->id, old('products', $selectedIds)) ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-blue-600 border-gray-300 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 font-medium">{{ $product->name_ar }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Schedule & Status --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">تاريخ البداية</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $offer->starts_at?->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">تاريخ الانتهاء</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $offer->expires_at?->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-3 cursor-pointer pb-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $offer->is_active ? '1' : '0') == '1' ? 'checked' : '' }} class="w-5 h-5 rounded text-blue-600 border-gray-300 focus:ring-blue-500">
                        <span class="text-sm font-bold text-gray-700">العرض نشط</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.offers.index') }}" class="px-6 py-3 border border-gray-200 rounded-xl text-gray-700 font-bold hover:bg-gray-50 transition-all">إلغاء</a>
                <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition-all shadow-sm">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

<script>
function showTypeFields(type) {
    document.querySelectorAll('.type-fields').forEach(el => el.classList.add('hidden'));
    const target = document.getElementById('fields-' + type);
    if (target) target.classList.remove('hidden');
}
document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name="type"]:checked');
    if (checked) showTypeFields(checked.value);
});
</script>
@endsection
