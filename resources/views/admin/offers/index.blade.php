@extends('admin.layouts.app')

@section('title', 'إدارة العروض')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden text-right">
    <div class="p-6 border-b border-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between space-y-3 sm:space-y-0">
        <p class="text-gray-500 font-medium italic">إدارة عروض المتجر وخصوماته المتنوعة.</p>
        <a href="{{ route('admin.offers.create') }}" class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-all shadow-sm">
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            إضافة عرض جديد
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap">
            <thead class="bg-gray-50/50 text-gray-400 text-xs uppercase font-bold border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 text-right">العرض</th>
                    <th class="px-6 py-4 text-center">النوع</th>
                    <th class="px-6 py-4 text-center">المنتجات</th>
                    <th class="px-6 py-4 text-center">الحالة</th>
                    <th class="px-6 py-4 text-center">تاريخ الانتهاء</th>
                    <th class="px-6 py-4 text-left">العمليات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($offers as $offer)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($offer->image)
                                <img src="{{ asset('storage/' . $offer->image) }}" class="w-12 h-12 rounded-xl object-cover border border-gray-100 flex-shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                </div>
                            @endif
                            <div>
                                <p class="font-bold text-gray-900 text-sm">{{ $offer->name_ar }}</p>
                                <p class="text-xs text-gray-400">{{ $offer->name_en }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @php
                            $typeBadge = match($offer->type) {
                                'percentage'    => ['label' => 'نسبة مئوية', 'class' => 'bg-purple-100 text-purple-700'],
                                'fixed'         => ['label' => 'مبلغ ثابت', 'class' => 'bg-blue-100 text-blue-700'],
                                'bundle'        => ['label' => 'باقة', 'class' => 'bg-orange-100 text-orange-700'],
                                'buy_x_get_y'   => ['label' => 'اشترِ X احصل Y', 'class' => 'bg-green-100 text-green-700'],
                                'spend_x_get_y' => ['label' => 'اصرف X احصل Y', 'class' => 'bg-pink-100 text-pink-700'],
                                default         => ['label' => $offer->type, 'class' => 'bg-gray-100 text-gray-700'],
                            };
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $typeBadge['class'] }}">{{ $typeBadge['label'] }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="font-bold text-gray-700 bg-gray-100 px-3 py-1 rounded-lg text-sm">{{ $offer->products_count }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($offer->isCurrentlyActive())
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">● نشط</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-600">● غير نشط</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($offer->expires_at)
                            <span class="text-sm text-gray-500 font-medium">{{ $offer->expires_at->format('Y/m/d') }}</span>
                        @else
                            <span class="text-xs text-gray-400 italic">بلا انتهاء</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-left">
                        <div class="flex items-center gap-2 justify-end">
                            <a href="{{ route('admin.offers.edit', $offer) }}" class="inline-flex p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors border border-transparent hover:border-blue-100" title="تعديل">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form id="delete-offer-{{ $offer->id }}" action="{{ route('admin.offers.destroy', $offer) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button" onclick="confirmAction('delete-offer-{{ $offer->id }}', 'هل أنت متأكد من حذف هذا العرض؟', 'حذف العرض')" class="inline-flex p-2 text-red-500 hover:bg-red-50 rounded-xl transition-colors border border-transparent hover:border-red-100" title="حذف">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            <p class="text-gray-400 font-bold italic">لا توجد عروض حالياً.</p>
                            <a href="{{ route('admin.offers.create') }}" class="text-sm text-blue-600 font-bold hover:underline">أضف أول عرض</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-6 bg-gray-50/50 border-t border-gray-100">
        {{ $offers->links() }}
    </div>
</div>
@endsection
