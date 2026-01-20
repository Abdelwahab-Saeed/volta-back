@extends('admin.layouts.app')

@section('title', 'تعديل عرض الباقة')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-black text-gray-900">تعديل عرض الباقة</h2>
            <p class="text-gray-500 font-medium mt-1">{{ $product->name }}</p>
        </div>
        <a href="{{ route('admin.products.offers.index', $product->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-5 py-2.5 rounded-xl transition-all font-bold flex items-center justify-center">
            إلغاء
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <form action="{{ route('admin.products.offers.update', [$product->id, $offer->id]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">الكمية بالضبط</label>
                    <div class="relative">
                        <input type="number" name="quantity" value="{{ old('quantity', $offer->quantity) }}" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-100 transition-shadow bg-gray-50/50" required min="2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 font-bold text-xs">
                            قطع
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">السعر الإجمالي (للكل)</label>
                    <div class="relative">
                        <input type="number" name="bundle_price" value="{{ old('bundle_price', $offer->bundle_price) }}" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-100 transition-shadow bg-gray-50/50" required min="0" step="0.01">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 font-bold text-xs">
                            ج.م
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="form-checkbox rounded text-blue-600 h-5 w-5 border-gray-300 focus:ring-blue-500" {{ $offer->is_active ? 'checked' : '' }}>
                        <span class="mr-3 font-bold text-gray-700">تفعيل العرض</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-gray-100 mt-4">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                        حفظ التعديلات
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
