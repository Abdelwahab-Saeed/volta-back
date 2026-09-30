@extends('admin.layouts.app')

@section('title', 'المنتجات المباعة')
@section('subtitle', 'مبيعات كل منتج من الطلبات التي تم توصيلها فقط.')

@php
    $totalQuantity = $soldProducts->sum('total_quantity');
    $totalRevenue = $soldProducts->sum('total_revenue');
    $maxRevenue = max(1, $soldProducts->max('total_revenue') ?? 1);
@endphp

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card p-5">
        <p class="text-sm font-bold text-slate-500">منتجات مختلفة</p>
        <p class="mt-2 text-2xl font-extrabold text-navy-900 tabular-nums">{{ number_format($soldProducts->count()) }}</p>
    </div>
    <div class="card p-5">
        <p class="text-sm font-bold text-slate-500">القطع المباعة</p>
        <p class="mt-2 text-2xl font-extrabold text-navy-900 tabular-nums">{{ number_format($totalQuantity) }}</p>
    </div>
    <div class="card p-5">
        <p class="text-sm font-bold text-slate-500">إجمالي الإيرادات</p>
        <p class="mt-2 text-2xl font-extrabold text-navy-900 tabular-nums">{{ \App\Support\Money::format($totalRevenue) }} <span class="text-sm text-slate-400">ج.م</span></p>
    </div>
</div>

<div class="card overflow-hidden">
    @if($soldProducts->isEmpty())
        <x-admin.empty-state icon="chart" title="لا توجد بيانات مبيعات بعد" text="تظهر هنا المنتجات بعد توصيل أول طلب." />
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>الكمية المباعة</th>
                        <th>متوسط سعر البيع</th>
                        <th>إجمالي الإيرادات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($soldProducts as $item)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3 min-w-[14rem]">
                                @if($item->product && $item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" alt="" class="thumb">
                                @else
                                    <span class="thumb-empty"><x-admin.icon name="cube" class="w-5 h-5" /></span>
                                @endif
                                <div>
                                    <p class="font-bold text-navy-900">{{ $item->product?->name ?? 'منتج محذوف' }}</p>
                                    <p class="text-xs text-slate-400">#{{ $item->product_id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap"><span class="font-extrabold text-navy-900 tabular-nums">{{ number_format($item->total_quantity) }}</span> <span class="text-xs text-slate-400">قطعة</span></td>
                        <td><x-admin.money :value="$item->total_revenue / $item->total_quantity" class="font-bold text-slate-700" /></td>
                        <td>
                            <div class="flex items-center gap-3 min-w-[12rem]">
                                <x-admin.money :value="$item->total_revenue" class="w-28 shrink-0" />
                                <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden" aria-hidden="true">
                                    <div class="h-full rounded-full bg-brand-600" style="width: {{ $item->total_revenue / $maxRevenue * 100 }}%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
