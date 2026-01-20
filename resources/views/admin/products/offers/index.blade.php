@extends('admin.layouts.app')

@section('title', 'إدارة عروض الباقة: ' . $product->name)

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 space-y-4 sm:space-y-0 text-right">
    <div>
        <h2 class="text-2xl font-black text-gray-900">عروض الباقة: {{ $product->name }}</h2>
        <p class="text-gray-500 font-medium mt-1">إضافة وإدارة العروض الخاصة بالكميات لهذا المنتج.</p>
    </div>
    <a href="{{ route('admin.products.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-5 py-2.5 rounded-xl transition-all font-bold flex items-center justify-center sm:w-auto">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        عودة للمنتجات
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Form Section -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
            <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center">
                <svg class="w-6 h-6 ml-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                إضافة عرض جديد
            </h3>

            <form action="{{ route('admin.products.offers.store', $product->id) }}" method="POST">
                @csrf
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">الكمية بالضبط</label>
                        <div class="relative">
                            <input type="number" name="quantity" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-100 transition-shadow bg-gray-50/50" required min="2" placeholder="مثلاً: 3">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 font-bold text-xs">
                                قطع
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">متى يتم تفعيل هذا السعر الثابت؟</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">السعر الإجمالي (للكل)</label>
                        <div class="relative">
                            <input type="number" name="bundle_price" class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-100 transition-shadow bg-gray-50/50" required min="0" step="0.01" placeholder="مثلاً: 250">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 font-bold text-xs">
                                ج.م
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">السعر الكلي لهذه المجموعة</p>
                    </div>

                    <div class="pt-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="form-checkbox rounded text-blue-600 h-5 w-5 border-gray-300 focus:ring-blue-500" checked>
                            <span class="mr-3 font-bold text-gray-700">تفعيل العرض فوراً</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                        حفظ العرض
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- List Section -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right whitespace-nowrap">
                    <thead class="bg-gray-50/50 text-gray-400 text-xs uppercase font-bold border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">الكمية</th>
                            <th class="px-6 py-4 text-center">السعر الإجمالي</th>
                            <th class="px-6 py-4 text-center">سعر القطعة</th>
                            <th class="px-6 py-4 text-center">التوفير</th>
                            <th class="px-6 py-4 text-center">الحالة</th>
                            <th class="px-6 py-4 text-left">حذف</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($offers as $offer)
                            @php
                                $unitPrice = $offer->bundle_price / $offer->quantity;
                                $originalTotal = $product->final_price * $offer->quantity;
                                $saving = $originalTotal - $offer->bundle_price;
                            @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-black text-gray-900 text-lg">{{ $offer->quantity }}</span> 
                                <span class="text-xs text-gray-500">قطع</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="font-black text-blue-600 text-lg">{{ number_format($offer->bundle_price, 2) }} ج.م</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-sm font-bold text-gray-600">{{ number_format($unitPrice, 2) }} ج.م</div>
                                <div class="text-xs text-gray-400 line-through">{{ number_format($product->final_price, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($saving > 0)
                                    <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full">
                                        توفير {{ number_format($saving, 2) }} ج.م
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($offer->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        مفعل
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                                        غير مفعل
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-left">
                                <div class="flex items-center justify-end space-x-reverse space-x-2">
                                    <a href="{{ route('admin.products.offers.edit', [$product->id, $offer->id]) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors border border-transparent hover:border-blue-100" title="تعديل">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form action="{{ route('admin.products.offers.destroy', [$product->id, $offer->id]) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا العرض؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition-colors border border-transparent hover:border-red-100" title="حذف">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <p class="font-bold">لا توجد عروض مضافة لهذا المنتج.</p>
                                <p class="text-sm mt-1">استخدم النموذج لإضافة أول عرض باقة.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
