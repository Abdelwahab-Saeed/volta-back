@extends('admin.layouts.app')

@section('title', 'تعديل العرض: ' . $offer->name_ar)
@section('heading', $offer->name_ar)
@section('subtitle', 'تعديل إعدادات العرض ومنتجاته.')
@section('back', route('admin.offers.index'))
@section('back_label', 'العروض')

@section('content')
<form action="{{ route('admin.offers.update', $offer) }}" method="POST" enctype="multipart/form-data" class="max-w-5xl space-y-6">
    @csrf
    @method('PUT')

    @if($offer->orders()->exists())
        <div class="flex gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <x-admin.icon name="info" class="w-5 h-5 text-amber-600" />
            <p>تم شراء هذا العرض من قبل. التعديل يسري على الطلبات الجديدة فقط؛ الطلبات السابقة محفوظ فيها العرض كما كان وقت الشراء.</p>
        </div>
    @endif

    @include('admin.offers._form')

    <div class="sticky bottom-0 z-10 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3 bg-white/90 backdrop-blur border-t border-slate-200 flex items-center gap-3">
        <button type="submit" class="btn-primary sm:min-w-[10rem]"><x-admin.icon name="check" class="w-4 h-4" /> حفظ التعديلات</button>
        <a href="{{ route('admin.offers.index') }}" class="btn-ghost">إلغاء</a>
    </div>
</form>
@endsection
