@extends('admin.layouts.app')

@section('title', 'طلب #' . $order->id)
@section('heading')
    طلب <bdi dir="ltr">#{{ $order->id }}</bdi>
@endsection
@section('back', route('admin.orders.index'))
@section('back_label', 'الطلبات')
@section('subtitle')
    تم الطلب {{ $order->created_at?->format('Y/m/d · H:i') }}
    <span class="mx-1 text-slate-300">|</span>
    <x-admin.order-status :status="$order->status" />
@endsection

@php
    use App\Support\Money;
    $statusLabels = ['pending' => 'قيد الانتظار', 'processing' => 'قيد التجهيز', 'shipped' => 'تم الشحن', 'delivered' => 'تم التوصيل', 'cancelled' => 'ملغي'];
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
    {{-- Items and totals --}}
    <div class="lg:col-span-2 space-y-6">
        <section class="card overflow-hidden">
            <div class="card-header">
                <h2 class="card-title">منتجات الطلب</h2>
                <span class="badge-neutral">{{ $order->items->count() }} منتج</span>
            </div>
            <ul class="divide-y divide-slate-100">
                @foreach($order->items as $item)
                    <li class="flex items-center gap-4 px-5 py-4">
                        @if($item->product)
                            <img src="{{ asset('storage/' . $item->product->image) }}" alt="" class="thumb w-14 h-14">
                        @else
                            <span class="thumb-empty w-14 h-14"><x-admin.icon name="cube" class="w-6 h-6" /></span>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-navy-900 truncate">{{ $item->product->name ?? 'منتج محذوف' }}</p>
                            <p class="text-sm text-slate-500 mt-0.5">
                                <span class="chip tabular-nums">{{ $item->quantity }} ×</span>
                                {{ Money::format($item->price) }} ج.م
                            </p>
                        </div>
                        {{-- Line total; gift items from an offer are stored with a zero total --}}
                        @if($item->total === 0)
                            <span class="badge-success">هدية</span>
                        @else
                            <x-admin.money :value="$item->total ?? $item->price * $item->quantity" class="text-base" />
                        @endif
                    </li>
                @endforeach
            </ul>
            <dl class="border-t border-slate-100 bg-slate-50/70 px-5 py-4 space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-600">الإجمالي الفرعي (قبل الخصم)</dt>
                    <dd class="font-bold text-slate-800 tabular-nums">{{ Money::format($order->subtotal) }} ج.م</dd>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-emerald-700">
                        <dt class="font-semibold">خصم الكوبون</dt>
                        <dd class="font-bold tabular-nums">- {{ Money::format($order->discount_amount) }} ج.م</dd>
                    </div>
                @endif
                @if($order->offer_discount > 0)
                    <div class="flex justify-between text-emerald-700">
                        <dt class="font-semibold">خصم العرض</dt>
                        <dd class="font-bold tabular-nums">- {{ Money::format($order->offer_discount) }} ج.م</dd>
                    </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-slate-600">تكلفة الشحن</dt>
                    <dd class="font-bold text-slate-800 tabular-nums">{{ Money::format($order->shipping_cost) }} ج.م</dd>
                </div>
                <div class="flex justify-between items-baseline pt-3 border-t border-slate-200">
                    <dt class="text-base font-extrabold text-navy-900">الإجمالي النهائي</dt>
                    <dd class="text-xl font-extrabold text-navy-900 tabular-nums">{{ Money::format($order->total_amount) }} <span class="text-sm text-slate-400">ج.م</span></dd>
                </div>
            </dl>
        </section>

        @if($order->offer_snapshot || $order->offer_id)
            @php
                // The offer as it was when bought; falls back to the live offer for orders placed before snapshots existed.
                $snap = $order->offer_snapshot ?? [];
                $offerName = $snap['name_ar'] ?? $order->offer?->name_ar ?? '—';
                $offerType = $snap['type'] ?? $order->offer?->type;
            @endphp
            <section class="card overflow-hidden">
                <div class="card-header">
                    <h2 class="card-title flex items-center gap-2"><x-admin.icon name="tag" class="w-5 h-5 text-brand-600" /> العرض المشترى</h2>
                    <span class="badge-info">{{ \App\Models\Offer::typeLabel($offerType, 'ar') }}</span>
                </div>
                <div class="card-body flex flex-col sm:flex-row gap-5">
                    @if($order->offer?->image)
                        <img src="{{ asset('storage/' . $order->offer->image) }}" alt="" class="w-full sm:w-40 h-28 rounded-xl object-cover">
                    @endif
                    <dl class="flex-1 grid sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-xs font-bold text-slate-400">اسم العرض</dt>
                            <dd class="mt-1 font-bold text-navy-900">{{ $offerName }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold text-slate-400">الخصم المحصّل</dt>
                            <dd class="mt-1 font-extrabold text-emerald-600 text-base">{{ Money::format($order->offer_discount) }} ج.م</dd>
                        </div>
                        @if(!empty($snap))
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-bold text-slate-400">التفاصيل وقت الشراء</dt>
                                <dd class="mt-1 text-slate-700 leading-relaxed">
                                    @if($snap['type'] === 'bundle')
                                        باقة بسعر {{ Money::format($snap['bundle_price']) }} ج.م:
                                        {{ collect($snap['products'])->map(fn ($p) => $p['quantity'] . ' × ' . $p['name_ar'])->implode(' + ') }}
                                    @else
                                        اشترِ {{ $snap['buy_quantity'] }} واحصل على {{ $snap['get_quantity'] }}
                                        {{ $snap['get_product_id'] ? 'هدية' : ($snap['get_discount_percent'] == 100 ? 'مجاناً' : 'بخصم ' . $snap['get_discount_percent'] . '%') }}
                                    @endif
                                    — عدد المرات: {{ $snap['sets'] }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </section>
        @endif
    </div>

    {{-- Status, customer, shipping --}}
    <div class="space-y-6 lg:sticky lg:top-24">
        <section class="card">
            <div class="card-header">
                <h2 class="card-title">حالة الطلب</h2>
                <x-admin.order-status :status="$order->status" />
            </div>
            <form id="order-status-form" action="{{ route('admin.orders.update', $order) }}" method="POST" class="card-body space-y-3" onsubmit="return confirmCancelOnSubmit(this)">
                @csrf
                @method('PUT')
                <label for="order-status" class="label">تغيير الحالة إلى</label>
                <select id="order-status" name="status" class="input" data-current="{{ $order->status }}">
                    @foreach(App\Enums\OrderStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ $order->status === $status->value ? 'selected' : '' }}>{{ $statusLabels[$status->value] ?? ucfirst($status->value) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-dark w-full">
                    <x-admin.icon name="check" class="w-4 h-4" />
                    حفظ الحالة
                </button>
                <p class="hint">إلغاء الطلب يعيد كمياته إلى المخزون.</p>
            </form>
        </section>

        <section class="card">
            <div class="card-header">
                <h2 class="card-title flex items-center gap-2"><x-admin.icon name="users" class="w-5 h-5 text-slate-400" /> العميل</h2>
                @if($order->user)
                    <span class="badge-navy">لديه حساب</span>
                @else
                    <span class="badge-neutral">زائر</span>
                @endif
            </div>
            <dl class="card-body space-y-3 text-sm">
                <div>
                    <dt class="text-xs font-bold text-slate-400">الاسم</dt>
                    <dd class="mt-0.5 font-bold text-navy-900">{{ $order->user?->name ?? $order->full_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400">رقم الهاتف</dt>
                    <dd class="mt-0.5 flex items-center justify-between gap-2">
                        <span class="font-bold text-navy-900" dir="ltr">{{ $order->phone_number ?? 'غير متوفر' }}</span>
                        @if($order->phone_number)
                            <a href="tel:{{ $order->phone_number }}" class="btn-secondary btn-sm"><x-admin.icon name="phone" class="w-3.5 h-3.5" /> اتصال</a>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400">البريد الإلكتروني</dt>
                    <dd class="mt-0.5 font-semibold text-slate-700 break-all" dir="ltr">{{ $order->user?->email ?? 'غير متوفر' }}</dd>
                </div>
            </dl>
        </section>

        <section class="card">
            <div class="card-header">
                <h2 class="card-title flex items-center gap-2"><x-admin.icon name="map-pin" class="w-5 h-5 text-slate-400" /> الشحن</h2>
            </div>
            <dl class="card-body space-y-3 text-sm">
                <div>
                    <dt class="text-xs font-bold text-slate-400">المستلم</dt>
                    <dd class="mt-0.5 font-bold text-navy-900">{{ $order->full_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400">المدينة / المحافظة</dt>
                    <dd class="mt-0.5 font-semibold text-slate-700">{{ $order->city }}، {{ $order->state }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400">العنوان</dt>
                    <dd class="mt-0.5 font-semibold text-slate-700 leading-relaxed">{{ $order->address_line }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400">طريقة الشحن</dt>
                    <dd class="mt-0.5 font-semibold text-slate-700">{{ $order->shipping_way }}</dd>
                </div>
            </dl>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Cancelling puts the items back in stock, so ask before saving that one.
    function confirmCancelOnSubmit(form) {
        const select = form.querySelector('select[name=status]');
        if (select.value !== 'cancelled' || select.dataset.current === 'cancelled') return true;
        confirmAction(form.id, 'سيتم إلغاء الطلب وإرجاع كمياته إلى المخزون.', 'إلغاء الطلب؟');
        return false;
    }
</script>
@endpush
