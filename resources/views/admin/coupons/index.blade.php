@extends('admin.layouts.app')

@section('title', 'إدارة الكوبونات')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 space-y-4 sm:space-y-0 text-right">
    <div>
        <h1 class="text-2xl font-black text-gray-900">الكوبونات</h1>
        <p class="text-gray-500 font-medium">إدارة خصومات المتجر والعروض الترويجية.</p>
    </div>
    <a href="{{ route('admin.coupons.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all font-bold flex items-center justify-center sm:w-auto">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        إضافة كوبون جديد
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-right whitespace-nowrap">
            <thead class="bg-gray-50/50 text-gray-500 text-xs uppercase font-bold border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">الكود</th>
                    <th class="px-6 py-4 text-center">النوع</th>
                    <th class="px-6 py-4 text-center">القيمة</th>
                    <th class="px-6 py-4 text-center">الحد الأدنى</th>
                    <th class="px-6 py-4 text-center">الاستخدام</th>
                    <th class="px-6 py-4 text-center">تاريخ الانتهاء</th>
                    <th class="px-6 py-4 text-left">العمليات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($coupons as $coupon)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <span class="font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-lg border border-blue-100 select-all">{{ $coupon->code }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $coupon->type === 'percent' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ $coupon->type === 'percent' ? 'نسبة مئوية' : 'مبلغ ثابت' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center font-bold text-gray-800">
                        {{ $coupon->value }} {{ $coupon->type === 'percent' ? '%' : 'ر.س' }}
                    </td>
                    <td class="px-6 py-4 text-center text-gray-600">
                        {{ $coupon->min_order_amount ?: 'بدون حد' }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex flex-col items-center">
                            <span class="text-xs font-bold text-gray-700">{{ $coupon->times_used }} / {{ $coupon->max_uses ?: '∞' }}</span>
                            <div class="w-16 h-1.5 bg-gray-100 rounded-full mt-1 overflow-hidden">
                                @php
                                    $percent = $coupon->max_uses ? ($coupon->times_used / $coupon->max_uses) * 100 : 0;
                                @endphp
                                <div class="h-full bg-blue-500" style="width: {{ min($percent, 100) }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($coupon->expires_at)
                            <span class="text-sm font-medium {{ $coupon->expires_at->isPast() ? 'text-red-500' : 'text-gray-600' }}">
                                {{ $coupon->expires_at->format('Y-m-d') }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">لا يوجد</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-left">
                        <div class="flex items-center justify-start space-x-reverse space-x-2">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="inline-flex p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors border border-transparent hover:border-blue-100" title="تعديل">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form id="delete-coupon-{{ $coupon->id }}" action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmAction('delete-coupon-{{ $coupon->id }}', 'هل أنت متأكد من حذف هذا الكوبون؟ لا يمكن التراجع عن هذا الإجراء.')" 
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition-colors border border-transparent hover:border-red-100" title="حذف">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                            <p class="text-lg font-black mb-2">لا توجد كوبونات حالياً</p>
                            <a href="{{ route('admin.coupons.create') }}" class="text-blue-600 hover:text-blue-700 font-bold underline transition-colors">أضف أول كوبون الآن</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($coupons->hasPages())
    <div class="p-6 bg-gray-50/50 border-t border-gray-100">
        {{ $coupons->links() }}
    </div>
    @endif
</div>
@endsection
