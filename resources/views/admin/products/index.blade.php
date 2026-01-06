@extends('admin.layouts.app')

@section('title', 'إدارة المنتجات')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 space-y-4 sm:space-y-0 text-right">
    <div>
        <p class="text-gray-500 font-medium">تحكم في مخزون وأسعار منتجاتك بكل سهولة.</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all font-bold flex items-center justify-center sm:w-auto">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        إضافة منتج جديد
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right whitespace-nowrap">
            <thead class="bg-gray-50/50 text-gray-400 text-xs uppercase font-bold border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">المنتج</th>
                    <th class="px-6 py-4 text-center">القسم</th>
                    <th class="px-6 py-4 text-center">السعر</th>
                    <th class="px-6 py-4 text-center">المخزون</th>
                    <th class="px-6 py-4 text-center">الحالة</th>
                    <th class="px-6 py-4 text-left">العمليات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12 ml-4">
                                @if($product->image)
                                    <img class="h-12 w-12 rounded-xl object-cover bg-gray-100 shadow-sm" src="{{ asset('storage/' . $product->image) }}" alt="">
                                @else
                                    <div class="h-12 w-12 bg-gray-50 rounded-xl flex items-center justify-center text-gray-300 border border-dashed border-gray-200">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="max-w-[200px] sm:max-w-xs">
                                <div class="text-sm font-black text-gray-900 truncate">{{ $product->name }}</div>
                                <div class="text-xs text-gray-400 truncate">{{ Str::limit($product->description, 40) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold text-gray-700 bg-gray-100 px-3 py-1 rounded-lg">{{ $product->category->name ?? 'غير محدد' }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="text-sm font-black text-slate-900">{{ number_format($product->price, 2) }} ج.م</div>
                        @if($product->discount > 0)
                            <div class="text-[10px] text-green-600 font-bold bg-green-50 px-2 py-0.5 rounded-full inline-block mt-1">خصم {{ (int)$product->discount }}%</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold {{ $product->stock <= 5 ? 'text-red-500 bg-red-50' : 'text-gray-600 bg-gray-50' }} px-3 py-1 rounded-lg">
                            {{ $product->stock }} <span class="text-xs opacity-75">متوفر</span>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $product->status ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full ml-2 {{ $product->status ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                            {{ $product->status ? 'متاح' : 'متوقف' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-left">
                        <div class="flex items-center justify-start space-x-reverse space-x-2">
                            <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors border border-transparent hover:border-blue-100" title="تعديل">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form id="delete-product-{{ $product->id }}" action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmAction('delete-product-{{ $product->id }}', 'هل أنت متأكد من أرشفة هذا المنتج؟')" 
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition-colors border border-transparent hover:border-red-100" title="حذف">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            <p class="text-lg font-black mb-2">لا توجد منتجات حالياً</p>
                            <a href="{{ route('admin.products.create') }}" class="text-blue-600 hover:text-blue-700 font-bold underline transition-colors">أضف أول منتج لمتجرك الآن</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="p-6 bg-gray-50/50 border-t border-gray-100">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
