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
                    @if($item->product)
                        <img src="{{ asset('storage/' . $item->product->image) }}" class="w-16 h-16 rounded-lg object-cover bg-gray-50 ml-6">
                    @else
                        <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center ml-6 text-gray-400 text-xs">لا يوجد صورة</div>
                    @endif
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-900">{{ $item->product->name ?? 'منتج محذوف' }}</h4>
                        <p class="text-sm text-gray-500">الكمية: {{ $item->quantity }} × {{ number_format($item->price, 2) }} ج.م</p>
                    </div>
                    <div class="text-left">
                        <p class="font-bold text-gray-900">{{ number_format($item->price, 2) }} ج.م</p>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="p-6 bg-gray-50 border-t border-gray-100">
                <div class="flex justify-between text-sm mb-3 text-gray-600">
                    <span class="text-lg">الإجمالي الفرعي (قبل الخصم)</span>
                    <span class="text-lg font-medium">{{ number_format($order->subtotal, 2) }} ج.م</span>
                </div>
                
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-sm mb-2 text-green-600 font-bold bg-green-50 p-2 rounded">
                    <span class="text-lg">خصم الكوبون</span>
                    <span class="text-lg">{{ number_format($order->discount_amount, 2) }}- ج.م</span>
                </div>
                @endif

                @if($order->offer_discount > 0)
                <div class="flex justify-between text-sm mb-2 text-blue-600 font-bold bg-blue-50 p-2 rounded">
                    <span class="text-lg">خصم العرض</span>
                    <span class="text-lg">{{ number_format($order->offer_discount, 2) }}- ج.م</span>
                </div>
                @endif

                <div class="flex justify-between text-sm mb-4 text-gray-600">
                    <span class="text-lg">تكلفة الشحن</span>
                    <span class="text-gray-600 text-lg">{{ number_format($order->shipping_cost, 2) }} ج.م</span>
                </div>
                
                <div class="flex justify-between text-xl font-black text-primary border-t border-gray-200 pt-4">
                    <span>الإجمالي النهائي (بعد الخصم)</span>
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
                    <p class="font-semibold text-gray-800">{{ $order->user?->name ?? $order->full_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">البريد الإلكتروني</p>
                    <p class="font-semibold text-gray-800">{{ $order->user?->email ?? 'غير متوفر' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">رقم الهاتف</p>
                    <p class="font-semibold text-gray-800">{{ $order->phone_number ?? 'غير متوفر' }}</p>
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
                    رقم الهاتف: {{ $order->phone_number ?? 'غير متوفر' }}
                </p>
                <p class="text-sm text-gray-600 leading-relaxed">
                    عنوان الشحن:{{ $order->address_line }}
                </p>
            </div>
        </div>

        @if($order->offer_id)
        <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 ml-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                تفاصيل العرض المطبق
            </h3>
            <div class="space-y-3">
                @if($order->offer?->image)
                    <img src="{{ asset('storage/' . $order->offer->image) }}" class="w-full h-28 rounded-xl object-cover">
                @endif
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">اسم العرض</p>
                    <p class="font-bold text-gray-800">{{ $order->offer->name_ar ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">نوع العرض</p>
                    @php
                        $tl = match($order->offer?->type) {
                            'percentage'    => 'نسبة مئوية',
                            'fixed'         => 'مبلغ ثابت',
                            'bundle'        => 'باقة منتجات',
                            'buy_x_get_y'   => 'اشترِ X احصل Y',
                            'spend_x_get_y' => 'اصرف X احصل Y',
                            default         => $order->offer?->type ?? '—',
                        };
                    @endphp
                    <p class="font-bold text-blue-600">{{ $tl }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">الخصم المحصّل</p>
                    <p class="font-black text-green-600 text-lg">{{ number_format($order->offer_discount, 2) }} ج.م</p>
                </div>
                @if($order->offer?->expires_at)
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">تاريخ انتهاء العرض</p>
                    <p class="font-semibold text-gray-700">{{ $order->offer->expires_at->format('Y/m/d H:i') }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">تحديث الحالة</h3>
            <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                @csrf
                @method('PUT')
                <select name="status" class="w-full px-4 py-3 rounded-xl border-none font-black text-sm mb-4 focus:ring-0 transition-all outline-none cursor-pointer">
                    @foreach(App\Enums\OrderStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ $order->status === $status->value ? 'selected' : '' }}>
                            @switch($status->value)
                                @case('pending') قيد الانتظار @break
                                @case('processing') قيد التجهيز @break
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
