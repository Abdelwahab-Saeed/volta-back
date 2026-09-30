@extends('admin.layouts.app')

@section('title', 'إدارة مميزات المنتج: ' . $product->name)
@section('heading', 'مميزات المنتج')
@section('subtitle', $product->name . ' — النقاط التي تظهر للعميل في صفحة المنتج.')
@section('back', route('admin.products.index'))
@section('back_label', 'المنتجات')

@section('actions')
    <a href="{{ route('admin.products.edit', $product) }}" class="btn-secondary"><x-admin.icon name="edit" class="w-4 h-4" /> تعديل المنتج</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
    {{-- Add --}}
    <section class="card lg:sticky lg:top-24">
        <div class="card-header">
            <h2 class="card-title">إضافة ميزة</h2>
        </div>
        <form action="{{ route('admin.products.features.store', $product->id) }}" method="POST" class="card-body space-y-4">
            @csrf
            @include('admin.partials.translatable-field', ['field' => 'name', 'stacked' => true])
            <button type="submit" class="btn-primary w-full">
                <x-admin.icon name="plus" class="w-4 h-4" />
                إضافة الميزة
            </button>
        </form>
    </section>

    {{-- List --}}
    <section class="card lg:col-span-2 overflow-hidden">
        <div class="card-header">
            <h2 class="card-title">المميزات الحالية</h2>
            <span class="badge-neutral">{{ $product->features->count() }} ميزة</span>
        </div>

        @forelse($product->features as $feature)
            <div class="flex items-start gap-3 px-5 py-4 border-b border-slate-100 last:border-b-0">
                <form action="{{ route('admin.features.update', $feature->id) }}" method="POST" class="flex-1 grid sm:grid-cols-[1fr_1fr_auto] gap-2 items-center">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name_ar" value="{{ $feature->name_ar }}" dir="rtl" required
                        placeholder="{{ __('admin.name_ar') }}" aria-label="{{ __('admin.name_ar') }}" class="input h-10">
                    <input type="text" name="name_en" value="{{ $feature->name_en }}" dir="ltr" required
                        placeholder="{{ __('admin.name_en') }}" aria-label="{{ __('admin.name_en') }}" class="input h-10 text-left">
                    <button type="submit" class="btn-secondary h-10">
                        <x-admin.icon name="check" class="w-4 h-4" />
                        حفظ
                    </button>
                </form>

                <form id="delete-feature-{{ $feature->id }}" action="{{ route('admin.features.destroy', $feature->id) }}" method="POST" class="shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmAction('delete-feature-{{ $feature->id }}', 'هل أنت متأكد من حذف هذه الميزة؟')" class="icon-btn-danger h-10 w-10" data-tip="حذف" aria-label="حذف">
                        <x-admin.icon name="trash" class="w-[18px] h-[18px]" />
                    </button>
                </form>
            </div>
        @empty
            <x-admin.empty-state icon="list" title="لا توجد مميزات بعد" text="أضف أول ميزة من النموذج المجاور." />
        @endforelse
    </section>
</div>
@endsection
