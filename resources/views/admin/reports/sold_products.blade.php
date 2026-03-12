@extends('admin.layouts.app')

@section('title', 'المنتجات المباعة')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800">قائمة المنتجات المباعة (الطلبات التي تم توصيلها فقط)</h3>
        <span class="bg-emerald-50 text-emerald-600 text-xs font-bold px-3 py-1 rounded-full">{{ $soldProducts->count() }} منتج فريد</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-right">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold">
                <tr>
                    <th class="px-6 py-4">المنتج</th>
                    <th class="px-6 py-4">الكمية المباعة</th>
                    <th class="px-6 py-4">متوسط سعر البيع</th>
                    <th class="px-6 py-4">إجمالي الإيرادات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($soldProducts as $item)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if($item->product && $item->product->image)
                                <img src="{{ asset('storage/' . $item->product->image) }}" class="w-10 h-10 rounded-lg object-cover ml-3 border border-gray-100">
                            @else
                                <div class="w-10 h-10 bg-gray-100 rounded-lg ml-3 flex items-center justify-center text-gray-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            <div>
                                <p class="font-bold text-gray-900">{{ $item->product?->name ?? 'منتج محذوف' }}</p>
                                <p class="text-xs text-gray-400">#{{ $item->product_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 font-bold text-gray-800">{{ $item->total_quantity }} وحدة</td>
                    <td class="px-6 py-4 font-bold text-blue-600">
                        {{ number_format($item->total_revenue / $item->total_quantity, 2) }} ج.م
                    </td>
                    <td class="px-6 py-4 font-black text-emerald-600">
                        {{ number_format($item->total_revenue, 2) }} ج.م
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 font-medium">لا توجد بيانات مبيعات متوفرة.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
