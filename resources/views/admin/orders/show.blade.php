@extends('admin.layouts.app')

@section('title', 'طلب #' . $order->id)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 text-right">
    <!-- منتجات الطلب -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">منتجات الطلب</h3>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600">
                    {{ $order->items->count() }} منتجات
                </span>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($order->items as $item)
                <div class="p-6 flex items-center">
                    <img src="{{ asset('storage/' . $item->product->image) }}" class="w-16 h-16 rounded-lg object-cover bg-gray-50 ml-6">
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-900">{{ $item->product->name }}</h4>
                        <p class="text-sm text-gray-500">الكمية: {{ $item->quantity }} × {{ number_format($item->price, 2) }} ج.م</p>
                    </div>
                    <div class="text-left">
                        <p class="font-bold text-gray-900">{{ number_format($item->total_price, 2) }} ج.م</p>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100">
                <div class="flex justify-between text-sm mb-2 text-gray-600">
                    <span>الخصم المطبق</span>
                    <span class="text-red-500">{{ number_format($order->discount_amount, 2) }}- ج.م</span>
                </div>
                <div class="flex justify-between text-lg font-black text-gray-900">
                    <span>الإجمالي النهائي</span>
                    <span>{{ number_format($order->total_amount, 2) }} ج.م</span>
                </div>
            </div>
        </div>
    </div>

    <!-- معلومات العميل والشحن -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-5 h-5 ml-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                بيانات العميل
            </h3>
            <div class="space-y-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">الاسم</p>
                    <p class="font-semibold text-gray-800">{{ $order->user->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">البريد الإلكتروني</p>
                    <p class="font-semibold text-gray-800">{{ $order->user->email }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">رقم الهاتف</p>
                    <p class="font-semibold text-gray-800">{{ $order->phone_number }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-5 h-5 ml-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                عنوان الشحن
            </h3>
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                <p class="font-bold text-gray-800 mb-1">{{ $order->full_name }}</p>
                <p class="text-sm text-gray-600 leading-relaxed">
                    {{ $order->city }}، {{ $order->state }}<br>
                    طريقة الشحن: {{ $order->shipping_way }}<br>
                    هاتف بديل: {{ $order->backup_phone_number ?? 'غير متوفر' }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">تحديث الحالة</h3>
            <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                @csrf
                @method('PUT')
                <select name="status" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 mb-4 focus:ring-2 focus:ring-blue-500 transition-all outline-none">
                    @foreach(App\Enums\OrderStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ $order->status === $status->value ? 'selected' : '' }}>
                            @switch($status->value)
                                @case('pending') قيد الانتظار @break
                                @case('shipped') تم الشحن @break
                                @case('delivered') تم التوصيل @break
                                @case('cancelled') ملغي @break
                                @default {{ ucfirst($status->value) }}
                            @endswitch
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-slate-900 text-white py-2.5 rounded-lg font-bold hover:bg-slate-800 transition-all shadow-md">
                    حفظ الحالة
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
