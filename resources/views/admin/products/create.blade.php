@extends('admin.layouts.app')

@section('title', 'إضافة منتج جديد')
@section('back', route('admin.products.index'))
@section('back_label', 'المنتجات')

@section('content')
<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    @include('admin.products._form')

    <div class="sticky bottom-0 z-10 mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3 bg-white/90 backdrop-blur border-t border-slate-200 flex items-center gap-3">
        <button type="submit" class="btn-primary sm:min-w-[10rem]">
            <x-admin.icon name="check" class="w-4 h-4" />
            إضافة المنتج
        </button>
        <a href="{{ route('admin.products.index') }}" class="btn-ghost">إلغاء</a>
    </div>
</form>
@endsection
