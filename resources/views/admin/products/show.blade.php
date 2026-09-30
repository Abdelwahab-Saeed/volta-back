@extends('admin.layouts.app')

@section('title', 'تفاصيل المنتج: ' . $product->name)
@section('heading', $product->name)
@section('back', route('admin.products.index'))
@section('back_label', 'المنتجات')
@section('subtitle')
    {{ $product->category->name ?? 'بدون قسم' }}
    <span class="mx-1 text-slate-300">|</span>
    <span class="{{ $product->status ? 'badge-success' : 'badge-neutral' }} badge-dot">{{ $product->status ? 'نشط' : 'متوقف' }}</span>
@endsection

@section('actions')
    <a href="{{ route('admin.products.edit', $product) }}" class="btn-primary"><x-admin.icon name="edit" class="w-4 h-4" /> تعديل</a>
@endsection

@php
    use App\Support\Money;
    $sold = $product->orderItems()->sum('quantity');
    $stockPercentage = min(100, ($product->stock / 50) * 100);
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
    <div class="lg:col-span-2 space-y-6">
        <section class="card overflow-hidden">
            <div class="flex flex-col sm:flex-row">
                <div class="sm:w-64 shrink-0 bg-slate-50 border-b sm:border-b-0 sm:border-l border-slate-100 flex items-center justify-center p-6">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="" class="max-h-56 w-auto object-contain rounded-xl">
                    @else
                        <x-admin.icon name="photo" class="w-16 h-16 text-slate-300" />
                    @endif
                </div>
                <div class="card-body flex-1">
                    <h2 class="section-title mb-3">الوصف</h2>
                    <div class="text-sm text-slate-600 leading-loose">
                        {!! nl2br(e($product->description)) !!}
                    </div>

                    @if($product->preview_url)
                        <a href="{{ $product->preview_url }}" target="_blank" rel="noopener" class="mt-5 flex items-center gap-3 rounded-xl border border-slate-200 p-3 hover:border-brand-300 hover:bg-brand-50/40 transition group">
                            <span class="w-10 h-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center"><x-admin.icon name="play" class="w-6 h-6" /></span>
                            <span class="flex-1 min-w-0">
                                <span class="block text-sm font-bold text-navy-900">مشاهدة الفيديو التعريفي</span>
                                <span class="block text-xs text-slate-400 truncate" dir="ltr">{{ $product->preview_url }}</span>
                            </span>
                            <x-admin.icon name="external" class="w-4 h-4 text-slate-400" />
                        </a>
                    @endif
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2 class="card-title">المميزات</h2>
                <a href="{{ route('admin.products.features.index', $product->id) }}" class="btn-secondary btn-sm"><x-admin.icon name="edit" class="w-3.5 h-3.5" /> إدارة المميزات</a>
            </div>
            <div class="card-body">
                @forelse($product->features as $feature)
                    @if($loop->first)<ul class="grid sm:grid-cols-2 gap-2.5">@endif
                    <li class="flex items-start gap-2.5 text-sm text-slate-700">
                        <x-admin.icon name="check-circle" class="w-5 h-5 text-brand-600" />
                        <span>{{ $feature->name }}</span>
                    </li>
                    @if($loop->last)</ul>@endif
                @empty
                    <p class="text-sm text-slate-500">لا توجد مميزات مضافة لهذا المنتج.</p>
                @endforelse
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <h2 class="card-title">معرض الصور <span class="text-slate-400 font-bold">({{ $product->extraImages->count() }})</span></h2>
                <a href="{{ route('admin.products.images.index', $product->id) }}" class="btn-secondary btn-sm"><x-admin.icon name="photo" class="w-3.5 h-3.5" /> إدارة الصور</a>
            </div>
            <div class="card-body">
                @if($product->extraImages->isEmpty())
                    <p class="text-sm text-slate-500">لا توجد صور إضافية.</p>
                @else
                    <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                        @foreach($product->extraImages as $image)
                            <div class="aspect-square rounded-xl overflow-hidden border border-slate-200 bg-slate-50">
                                <img src="{{ asset('storage/' . $image->image) }}" alt="" class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>

    <div class="space-y-6 lg:sticky lg:top-24">
        <section class="card">
            <div class="card-body">
                <p class="text-xs font-bold text-slate-400">سعر البيع</p>
                <p class="mt-1 text-3xl font-extrabold text-navy-900 tabular-nums">{{ Money::format($product->final_price) }} <span class="text-sm text-slate-400">ج.م</span></p>
                <dl class="mt-5 pt-4 border-t border-slate-100 space-y-2.5 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">السعر الأصلي</dt><dd class="font-bold text-slate-800 tabular-nums">{{ Money::format($product->price) }} ج.م</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">سعر التكلفة</dt><dd class="font-bold text-slate-800 tabular-nums">{{ Money::format($product->cost_price) }} ج.م</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">الربح المتوقع</dt><dd class="font-extrabold text-emerald-600 tabular-nums">{{ Money::format($product->final_price - $product->cost_price) }} ج.م</dd></div>
                </dl>
            </div>
        </section>

        <section class="card">
            <div class="card-body">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="section-title">المخزون</h2>
                    <span class="{{ $product->stock <= 0 ? 'badge-danger' : ($product->stock <= 5 ? 'badge-warning' : 'badge-neutral') }}">{{ $product->stock }} متوفر</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full {{ $product->stock <= 5 ? 'bg-red-500' : 'bg-brand-600' }}" style="width: {{ $stockPercentage }}%"></div>
                </div>
                <p class="hint">المؤشر يقيس على سعة افتراضية 50 قطعة.</p>
            </div>
        </section>

        <section class="card">
            <div class="card-body flex items-center gap-4">
                <span class="w-11 h-11 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center"><x-admin.icon name="orders" class="w-6 h-6" /></span>
                <div>
                    <p class="text-xs font-bold text-slate-400">إجمالي المبيعات</p>
                    <p class="text-lg font-extrabold text-navy-900 tabular-nums">{{ number_format($sold) }} قطعة</p>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
