@extends('admin.layouts.app')

@section('title', 'لوحة التحكم')
@section('heading', 'أهلاً، ' . auth()->user()->name)
@section('subtitle', 'نظرة سريعة على المتجر اليوم، ' . now(\App\Http\Controllers\Admin\OfferController::ADMIN_TIMEZONE)->locale('ar')->translatedFormat('l j F Y'))

@section('actions')
    <a href="{{ route('admin.orders.index') }}" class="btn-secondary">
        <x-admin.icon name="orders" class="w-4 h-4" />
        كل الطلبات
    </a>
    <a href="{{ route('admin.products.create') }}" class="btn-primary">
        <x-admin.icon name="plus" class="w-4 h-4" />
        منتج جديد
    </a>
@endsection

@php
    use App\Support\Money;

    $status = $insights['status_counts'];
    $pending = $status['pending'] ?? 0;
    $daily = $insights['daily_orders'];

    // Chart scale: a clean top tick above the busiest day, in pounds.
    $maxPounds = max(1, max(array_map(fn ($d) => $d['total'], $daily)) / 100);
    $magnitude = 10 ** floor(log10($maxPounds));
    $step = collect([1, 2, 2.5, 5, 10])->map(fn ($m) => $m * $magnitude)->first(fn ($s) => $maxPounds / $s <= 4);
    $top = ceil($maxPounds / $step) * $step;
    $ticks = collect(range(0, (int) round($top / $step)))->map(fn ($i) => $i * $step)->reverse()->values();
    $periodTotal = array_sum(array_column($daily, 'total'));
    $periodCount = array_sum(array_column($daily, 'count'));

    $tiles = [
        ['label' => 'الإيرادات المحققة', 'hint' => 'الطلبات التي تم توصيلها', 'value' => Money::format($stats['total_revenue']), 'unit' => 'ج.م', 'icon' => 'money', 'tone' => 'bg-emerald-50 text-emerald-600'],
        ['label' => 'إجمالي الطلبات', 'hint' => $pending . ' قيد الانتظار', 'value' => number_format($stats['total_orders']), 'icon' => 'orders', 'tone' => 'bg-brand-50 text-brand-600', 'url' => route('admin.orders.index')],
        ['label' => 'المنتجات', 'hint' => $stats['total_categories'] . ' قسم', 'value' => number_format($stats['total_products']), 'icon' => 'cube', 'tone' => 'bg-violet-50 text-violet-600', 'url' => route('admin.products.index')],
        ['label' => 'المستخدمون', 'hint' => 'الحسابات المسجلة', 'value' => number_format($stats['total_users']), 'icon' => 'users', 'tone' => 'bg-amber-50 text-amber-600', 'url' => route('admin.users.index')],
        ['label' => 'قيمة المخزون', 'hint' => 'سعر التكلفة × الكمية', 'value' => Money::format($stats['total_expenses']), 'unit' => 'ج.م', 'icon' => 'wallet', 'tone' => 'bg-slate-100 text-slate-600'],
    ];

    $statusRows = [
        'pending'    => ['قيد الانتظار', 'bg-amber-500'],
        'processing' => ['قيد التجهيز', 'bg-brand-500'],
        'shipped'    => ['تم الشحن', 'bg-violet-500'],
        'delivered'  => ['تم التوصيل', 'bg-emerald-500'],
        'cancelled'  => ['ملغي', 'bg-red-400'],
    ];
    $statusTotal = max(1, $status->sum());
@endphp

@section('content')
{{-- Needs attention --}}
@if($pending > 0 || $insights['low_stock_count'] > 0)
    <div class="grid gap-3 sm:grid-cols-2 mb-6">
        @if($pending > 0)
            <a href="{{ route('admin.orders.index') }}" class="group flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50/70 px-5 py-4 hover:bg-amber-50 transition">
                <span class="w-11 h-11 rounded-xl bg-white text-amber-600 flex items-center justify-center shadow-sm"><x-admin.icon name="clock" class="w-6 h-6" /></span>
                <span class="flex-1">
                    <span class="block font-extrabold text-amber-900">{{ $pending }} {{ $pending == 1 ? 'طلب جديد ينتظر' : 'طلبات تنتظر' }} التأكيد</span>
                    <span class="block text-sm text-amber-800/80">راجعها وحدّث حالتها ليتابع العميل طلبه.</span>
                </span>
                <x-admin.icon name="arrow-left" class="w-5 h-5 text-amber-700 transition group-hover:-translate-x-1" />
            </a>
        @endif
        @if($insights['low_stock_count'] > 0)
            <a href="#low-stock" class="group flex items-center gap-4 rounded-2xl border border-red-200 bg-red-50/60 px-5 py-4 hover:bg-red-50 transition">
                <span class="w-11 h-11 rounded-xl bg-white text-red-600 flex items-center justify-center shadow-sm"><x-admin.icon name="warning" class="w-6 h-6" /></span>
                <span class="flex-1">
                    <span class="block font-extrabold text-red-900">{{ $insights['low_stock_count'] }} منتج قارب على النفاد</span>
                    <span class="block text-sm text-red-800/80">المخزون 5 قطع أو أقل.</span>
                </span>
                <x-admin.icon name="arrow-left" class="w-5 h-5 text-red-700 transition group-hover:-translate-x-1" />
            </a>
        @endif
    </div>
@endif

{{-- KPI tiles --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
    @foreach ($tiles as $tile)
        @php($tag = isset($tile['url']) ? 'a' : 'div')
        <{{ $tag }} @isset($tile['url']) href="{{ $tile['url'] }}" @endisset class="card p-5 flex flex-col gap-4 {{ isset($tile['url']) ? 'hover:border-brand-300 hover:shadow-pop transition' : '' }} {{ $loop->first ? 'col-span-2 lg:col-span-1' : '' }}">
            <div class="flex items-center justify-between gap-2">
                <span class="text-sm font-bold text-slate-500">{{ $tile['label'] }}</span>
                <span class="w-10 h-10 rounded-xl flex items-center justify-center {{ $tile['tone'] }}"><x-admin.icon :name="$tile['icon']" class="w-5 h-5" /></span>
            </div>
            <div>
                <p class="text-2xl font-extrabold text-navy-900 tabular-nums leading-none">
                    {{ $tile['value'] }} @isset($tile['unit'])<span class="text-sm font-bold text-slate-400">{{ $tile['unit'] }}</span>@endisset
                </p>
                <p class="mt-2 text-xs font-semibold text-slate-400">{{ $tile['hint'] }}</p>
            </div>
        </{{ $tag }}>
    @endforeach
</div>

<div class="grid gap-6 xl:grid-cols-3 mb-6">
    {{-- Daily orders chart --}}
    <section class="card xl:col-span-2" aria-labelledby="chart-title">
        <div class="card-header">
            <div>
                <h2 id="chart-title" class="card-title">قيمة الطلبات اليومية</h2>
                <p class="card-subtitle">آخر {{ count($daily) }} يوماً، بدون الطلبات الملغاة</p>
            </div>
            <div class="flex gap-6 text-left">
                <div>
                    <p class="text-xs font-bold text-slate-400">الإجمالي</p>
                    <p class="text-lg font-extrabold text-navy-900 tabular-nums">{{ Money::format($periodTotal) }} <span class="text-xs text-slate-400">ج.م</span></p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400">عدد الطلبات</p>
                    <p class="text-lg font-extrabold text-navy-900 tabular-nums">{{ number_format($periodCount) }}</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($periodCount === 0)
                <x-admin.empty-state icon="chart" title="لا توجد طلبات في آخر أسبوعين" text="سيظهر الرسم هنا بمجرد وصول الطلبات." class="py-10" />
            @else
                <div class="flex gap-3" dir="ltr">
                    {{-- Y axis --}}
                    <div class="flex flex-col justify-between h-56 pb-6 text-[11px] font-semibold text-slate-400 tabular-nums text-right w-14 shrink-0">
                        @foreach ($ticks as $tick)
                            <span class="leading-none -translate-y-1/2 first:translate-y-0 last:translate-y-0">{{ number_format($tick) }}</span>
                        @endforeach
                    </div>
                    {{-- Plot --}}
                    <div class="relative flex-1 h-56">
                        <div class="absolute inset-x-0 top-0 bottom-6 flex flex-col justify-between pointer-events-none" aria-hidden="true">
                            @foreach ($ticks as $tick)
                                <div class="border-t border-slate-100 {{ $loop->last ? 'border-slate-200' : '' }}"></div>
                            @endforeach
                        </div>
                        <div class="absolute inset-0 flex items-stretch">
                            @foreach ($daily as $day)
                                @php($height = $day['total'] / 100 / $top * 100)
                                <div class="group relative flex-1 flex flex-col items-center justify-end pb-6 cursor-default" tabindex="0"
                                     aria-label="{{ $day['date']->locale('ar')->translatedFormat('j F') }}: {{ $day['count'] }} طلب بقيمة {{ Money::format($day['total']) }} جنيه">
                                    <div class="w-full max-w-[24px] rounded-t-[4px] bg-brand-600 transition-colors group-hover:bg-brand-700 group-focus:bg-brand-700 {{ $day['total'] > 0 ? 'min-h-[3px]' : '' }}" style="height: {{ $height }}%"></div>
                                    <span class="absolute bottom-0 text-[11px] font-semibold {{ $day['date']->isToday() ? 'text-navy-900' : 'text-slate-400' }}">{{ $day['date']->format('j') }}</span>
                                    {{-- Tooltip --}}
                                    <div class="absolute hidden group-hover:block group-focus:block z-10 whitespace-nowrap rounded-lg bg-navy-950 px-3 py-2 text-xs text-white shadow-pop" dir="rtl" style="bottom: calc(24px + (100% - 24px) * {{ $height / 100 }} + 8px)">
                                        <p class="font-bold text-slate-300">{{ $day['date']->locale('ar')->translatedFormat('l j F') }}</p>
                                        <p class="mt-0.5"><span class="font-extrabold">{{ Money::format($day['total']) }}</span> ج.م · {{ $day['count'] }} طلب</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <details class="mt-4 group/details">
                    <summary class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 cursor-pointer hover:text-navy-900 select-none">
                        <x-admin.icon name="list" class="w-4 h-4" /> عرض كجدول
                    </summary>
                    <div class="mt-3 table-wrap rounded-xl border border-slate-200">
                        <table class="data-table">
                            <thead><tr><th>اليوم</th><th>عدد الطلبات</th><th>القيمة</th></tr></thead>
                            <tbody>
                                @foreach (array_reverse($daily) as $day)
                                    <tr>
                                        <td>{{ $day['date']->locale('ar')->translatedFormat('l j F') }}</td>
                                        <td class="tabular-nums">{{ $day['count'] }}</td>
                                        <td><x-admin.money :value="$day['total']" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        </div>
    </section>

    {{-- Orders by status --}}
    <section class="card" aria-labelledby="status-title">
        <div class="card-header">
            <div>
                <h2 id="status-title" class="card-title">الطلبات حسب الحالة</h2>
                <p class="card-subtitle">كل الطلبات منذ البداية</p>
            </div>
        </div>
        <ul class="card-body space-y-4">
            @foreach ($statusRows as $key => [$label, $bar])
                @php($count = $status[$key] ?? 0)
                <li>
                    <div class="flex items-center justify-between text-sm mb-1.5">
                        <span class="flex items-center gap-2 font-bold text-slate-700">
                            <span class="w-2.5 h-2.5 rounded-full {{ $bar }}"></span>{{ $label }}
                        </span>
                        <span class="font-extrabold text-navy-900 tabular-nums">{{ number_format($count) }} <span class="text-xs font-semibold text-slate-400">({{ round($count / $statusTotal * 100) }}%)</span></span>
                    </div>
                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full {{ $bar }}" style="width: {{ $count / $statusTotal * 100 }}%"></div>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
</div>

<div class="grid gap-6 xl:grid-cols-3">
    {{-- Recent orders --}}
    <section class="card xl:col-span-2 overflow-hidden" aria-labelledby="recent-title">
        <div class="card-header">
            <h2 id="recent-title" class="card-title">أحدث الطلبات</h2>
            <a href="{{ route('admin.orders.index') }}" class="link text-sm">عرض كل الطلبات</a>
        </div>
        @if($stats['recent_orders']->isEmpty())
            <x-admin.empty-state icon="orders" title="لا توجد طلبات بعد" text="ستظهر الطلبات الجديدة هنا فور وصولها." />
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>الطلب</th>
                            <th>العميل</th>
                            <th>الحالة</th>
                            <th>الإجمالي</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['recent_orders'] as $order)
                            <tr class="cursor-pointer" onclick="window.location='{{ route('admin.orders.show', $order) }}'">
                                <td><a href="{{ route('admin.orders.show', $order) }}" class="font-extrabold text-navy-900 hover:text-brand-700 {{ $order->status === 'cancelled' ? 'line-through text-slate-400' : '' }}">#{{ $order->id }}</a></td>
                                <td class="font-semibold text-slate-700 whitespace-nowrap">{{ $order->user?->name ?? $order->full_name }}</td>
                                <td><x-admin.order-status :status="$order->status" /></td>
                                <td><x-admin.money :value="$order->total_amount" /></td>
                                <td class="text-slate-500 whitespace-nowrap">{{ $order->created_at?->diffForHumans() ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Low stock --}}
    <section id="low-stock" class="card scroll-mt-24" aria-labelledby="stock-title">
        <div class="card-header">
            <div>
                <h2 id="stock-title" class="card-title">منتجات قاربت على النفاد</h2>
                <p class="card-subtitle">المخزون 5 قطع أو أقل</p>
            </div>
        </div>
        @if($insights['low_stock']->isEmpty())
            <x-admin.empty-state icon="check-circle" title="المخزون بحالة جيدة" text="لا يوجد منتج بمخزون منخفض." class="py-10" />
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($insights['low_stock'] as $product)
                    <li>
                        <a href="{{ route('admin.products.edit', $product) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 transition">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="" class="thumb w-10 h-10">
                            @else
                                <span class="thumb-empty w-10 h-10"><x-admin.icon name="cube" class="w-5 h-5" /></span>
                            @endif
                            <span class="flex-1 min-w-0 text-sm font-bold text-slate-700 truncate">{{ $product->name }}</span>
                            <span class="{{ $product->stock <= 0 ? 'badge-danger' : 'badge-warning' }}">{{ $product->stock <= 0 ? 'نفد' : $product->stock . ' متبقي' }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            @if($insights['low_stock_count'] > $insights['low_stock']->count())
                <div class="card-footer text-center">
                    <a href="{{ route('admin.products.index') }}" class="link text-sm">+ {{ $insights['low_stock_count'] - $insights['low_stock']->count() }} منتجات أخرى</a>
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
