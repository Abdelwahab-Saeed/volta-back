@extends('admin.layouts.app')

@section('title', 'الطلبات')
@section('subtitle', 'تابع طلبات العملاء وحدّث حالتها. تغيير الحالة من القائمة يُحفظ فوراً.')

@php
    $statusLabels = [
        'pending' => 'قيد الانتظار',
        'processing' => 'قيد التجهيز',
        'shipped' => 'تم الشحن',
        'delivered' => 'تم التوصيل',
        'cancelled' => 'ملغي',
    ];
    // Tint of the inline status picker, so the list reads at a glance.
    $statusTone = [
        'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
        'processing' => 'bg-brand-50 text-brand-800 border-brand-200',
        'shipped' => 'bg-violet-50 text-violet-800 border-violet-200',
        'delivered' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'cancelled' => 'bg-red-50 text-red-800 border-red-200',
    ];
@endphp

@section('content')
<x-admin.filters :filters="$filters" :total="$orders->total()" dates
    placeholder="رقم الطلب، اسم العميل، الهاتف أو البريد"
    :selects="[
        'status' => ['label' => 'الحالة', 'options' => $statusLabels],
        'discount' => ['label' => 'الخصم', 'options' => ['offer' => 'بعرض', 'coupon' => 'بكوبون', 'none' => 'بدون خصم']],
    ]" />

<div class="card overflow-hidden">
    @if($orders->isEmpty() && $filters)
        <x-admin.no-results />
    @elseif($orders->isEmpty())
        <x-admin.empty-state icon="orders" title="لا توجد طلبات حالياً" text="ستظهر طلبات العملاء هنا فور وصولها." />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الطلب</th>
                        <th>العميل</th>
                        <th>الإجمالي</th>
                        <th>العرض</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th class="text-left">التفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-extrabold hover:text-brand-700 {{ $order->status === 'cancelled' ? 'line-through text-slate-400' : 'text-navy-900' }}">#{{ $order->id }}</a>
                        </td>
                        <td>
                            <div class="font-bold text-slate-800 whitespace-nowrap">{{ $order->user?->name ?? $order->full_name }}</div>
                            <div class="text-xs text-slate-400" dir="ltr">{{ $order->phone_number ?? $order->user?->email ?? '—' }}</div>
                        </td>
                        <td><x-admin.money :value="$order->total_amount" /></td>
                        <td>
                            @if($order->offer_snapshot || $order->offer_id)
                                <div class="flex flex-col items-start gap-1">
                                    <span class="badge-info max-w-[11rem] truncate">{{ $order->offer_snapshot['name_ar'] ?? $order->offer?->name_ar ?? 'عرض' }}</span>
                                    <span class="text-xs font-bold text-emerald-600 whitespace-nowrap">وفّر {{ \App\Support\Money::format($order->offer_discount) }} ج.م</span>
                                </div>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td>
                            <form id="status-form-{{ $order->id }}" action="{{ route('admin.orders.update', $order) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <label class="sr-only" for="status-{{ $order->id }}">حالة الطلب #{{ $order->id }}</label>
                                <select id="status-{{ $order->id }}" name="status" data-current="{{ $order->status }}" onchange="changeOrderStatus(this)"
                                    class="h-9 pr-3 pl-8 rounded-full border text-xs font-extrabold cursor-pointer appearance-none bg-no-repeat focus:outline-none focus:ring-4 focus:ring-brand-500/15 {{ $statusTone[$order->status] ?? 'bg-slate-50 text-slate-700 border-slate-200' }}"
                                    style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='currentColor' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: left 0.5rem center; background-size: 1.1em;">
                                    @foreach(App\Enums\OrderStatus::cases() as $status)
                                        <option value="{{ $status->value }}" {{ $order->status === $status->value ? 'selected' : '' }}>{{ $statusLabels[$status->value] ?? $status->value }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="whitespace-nowrap">
                            <div class="font-semibold text-slate-700">{{ $order->created_at->format('Y/m/d') }}</div>
                            <div class="text-xs text-slate-400">{{ $order->created_at->format('H:i') }}</div>
                        </td>
                        <td class="text-left">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn-secondary btn-sm">
                                <x-admin.icon name="eye" class="w-4 h-4" />
                                عرض
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="card-footer">{{ $orders->links() }}</div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Status changes save straight away. Cancelling puts the items back in stock, so it asks first.
    function changeOrderStatus(select) {
        if (select.value !== 'cancelled') {
            select.form.submit();
            return;
        }
        onConfirmDismiss(() => { select.value = select.dataset.current; });
        confirmAction(select.form.id, 'سيتم إلغاء الطلب وإرجاع كمياته إلى المخزون.', 'إلغاء الطلب؟');
    }
</script>
@endpush
