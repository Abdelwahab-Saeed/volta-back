@extends('admin.layouts.app')

@section('title', 'إدارة الطلبات')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden text-right">
    <div class="p-6 border-b border-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between space-y-2 sm:space-y-0 text-sm">
        <p class="text-gray-500 font-medium italic">تابع حالات الشحن وقم بإدارة طلبات العملاء.</p>
        <div class="flex items-center text-xs text-gray-400 font-bold bg-gray-50 px-3 py-1 rounded-lg">
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            تحديث الحالة تلقائي عند الاختيار
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap">
            <thead class="bg-gray-50/50 text-gray-400 text-xs uppercase font-bold border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 text-right">رقم الطلب</th>
                    <th class="px-6 py-4 text-right">العميل</th>
                    <th class="px-6 py-4 text-center">إجمالي المبلغ</th>
                    <th class="px-6 py-4 text-center">الحالة</th>
                    <th class="px-6 py-4 text-center">التاريخ</th>
                    <th class="px-6 py-4 text-left">العمليات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-6 py-4">
                        <span class="font-black text-slate-900 {{ $order->status === 'cancelled' ? 'line-through text-gray-400 opacity-50' : '' }}">
                            #{{ $order->id }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-black text-gray-900">{{ $order->user->name }}</div>
                        <div class="text-[10px] text-gray-400 font-medium">{{ $order->user->email }}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-black text-slate-900 bg-slate-50 px-3 py-1 rounded-lg">
                            {{ number_format($order->total_amount, 2) }} ج.م
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="inline-block relative">
                            @csrf
                            @method('PUT')
                            <select name="status" onchange="this.form.submit()" 
                                class="text-[11px] font-black px-4 py-1.5 rounded-full border-none outline-none cursor-pointer shadow-sm transition-all appearance-none pr-8 pl-4
                                {{ $order->status === 'delivered' ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 
                                   ($order->status === 'cancelled' ? 'bg-red-100 text-red-700 hover:bg-red-200' : 
                                   ($order->status === 'shipped' ? 'bg-blue-100 text-blue-700 hover:bg-blue-200' : 'bg-amber-100 text-amber-700 hover:bg-amber-200')) }}">
                                @foreach(App\Enums\OrderStatus::cases() as $status)
                                    <option value="{{ $status->value }}" {{ $order->status === $status->value ? 'selected' : '' }}>
                                        @switch($status->value)
                                            @case('pending') قيد الانتظار @break
                                            @case('shipped') تم الشحن @break
                                            @case('delivered') تم التوصيل @break
                                            @case('cancelled') ملغي @break
                                            @default {{ $status->value }}
                                        @endswitch
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-current opacity-50">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm text-gray-400 font-bold">
                            {{ $order->created_at->format('Y/m/d') }}
                            <span class="text-[10px] block opacity-75 font-medium">{{ $order->created_at->format('H:i') }}</span>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-left">
                        <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition-colors border border-transparent hover:border-blue-100" title="عرض التفاصيل">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 font-bold italic">لا توجد طلبات حالياً.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-6 bg-gray-50/50 border-t border-gray-100">
        {{ $orders->links() }}
    </div>
</div>
@endsection
