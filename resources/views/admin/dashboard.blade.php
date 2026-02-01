@extends('admin.layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stat Card -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center">
            <div class="p-3 bg-blue-50 rounded-lg text-blue-600 ml-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">إجمالي المستخدمين</p>
                <p class="text-2xl font-bold">{{ $stats['total_users'] }}</p>
            </div>
        </div>
    </div>

    <!-- Stat Card -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center">
            <div class="p-3 bg-purple-50 rounded-lg text-purple-600 ml-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">إجمالي المنتجات</p>
                <p class="text-2xl font-bold">{{ $stats['total_products'] }}</p>
            </div>
        </div>
    </div>

    <!-- Stat Card -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center">
            <div class="p-3 bg-green-50 rounded-lg text-green-600 ml-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">إجمالي الطلبات</p>
                <p class="text-2xl font-bold">{{ $stats['total_orders'] }}</p>
            </div>
        </div>
    </div>

    <!-- Stat Card -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center">
            <div class="p-3 bg-amber-50 rounded-lg text-amber-600 ml-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V15m0 1v-8m0 0H8m4 0h4m-4 8H8m4 0h4"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">إجمالي الإيرادات</p>
                <p class="text-2xl font-bold"> {{ number_format($stats['total_revenue'], 2) }} ج.م</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800">الطلبات الأخيرة</h3>
        <a href="{{ route('admin.orders.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-bold">عرض الكل</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-right">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold">
                <tr>
                    <th class="px-6 py-4">رقم الطلب</th>
                    <th class="px-6 py-4">العميل</th>
                    <th class="px-6 py-4">الحالة</th>
                    <th class="px-6 py-4">الإجمالي</th>
                    <th class="px-6 py-4 text-left">التاريخ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($stats['recent_orders'] as $order)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 font-bold text-gray-900 {{ $order->status === 'cancelled' ? 'text-gray-400 line-through' : '' }}">
                        #{{ $order->id }}
                    </td>
                    <td class="px-6 py-4">{{ $order->user?->name ?? $order->full_name }}</td>
                    <td class="px-6 py-4">
                        <span class="px-4 py-1.5 rounded-full text-xs font-black
                            {{ $order->status === 'delivered' ? 'bg-emerald-100 text-emerald-700' : 
                               ($order->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : 
                               ($order->status === 'shipped' ? 'bg-sky-100 text-sky-700' : 'bg-orange-100 text-orange-700')) }}">
                            @switch($order->status)
                                @case('delivered') تم التوصيل @break
                                @case('cancelled') ملغي @break
                                @case('shipped') تم الشحن @break
                                @default قيد الانتظار
                            @endswitch
                        </span>
                    </td>
                    <td class="px-6 py-4 font-bold text-gray-800">{{ number_format($order->total_amount, 2) }} ج.م</td>
                    <td class="px-6 py-4 text-gray-400 text-sm text-left">{{ $order->created_at?->format('Y/m/d') ?? 'غير متوفر' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 font-medium">لا توجد طلبات حالياً.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
