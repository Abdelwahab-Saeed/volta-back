@extends('admin.layouts.app')

@section('title', 'تعديل المنتج: ' . $product->name)
@section('heading', $product->name)
@section('subtitle', 'تعديل تفاصيل المنتج وأسعاره ومخزونه.')
@section('back', route('admin.products.index'))
@section('back_label', 'المنتجات')

@section('actions')
    <a href="{{ route('admin.products.show', $product) }}" class="btn-secondary"><x-admin.icon name="eye" class="w-4 h-4" /> عرض</a>
    <a href="{{ route('admin.products.features.index', $product->id) }}" class="btn-secondary"><x-admin.icon name="list" class="w-4 h-4" /> المميزات</a>
    <a href="{{ route('admin.products.images.index', $product->id) }}" class="btn-secondary"><x-admin.icon name="photo" class="w-4 h-4" /> معرض الصور</a>
@endsection

@section('content')
<form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @include('admin.products._form', ['product' => $product])

    <div class="sticky bottom-0 z-10 mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3 bg-white/90 backdrop-blur border-t border-slate-200 flex items-center gap-3">
        <button type="submit" class="btn-primary sm:min-w-[10rem]">
            <x-admin.icon name="check" class="w-4 h-4" />
            حفظ التعديلات
        </button>
        <a href="{{ route('admin.products.index') }}" class="btn-ghost">إلغاء</a>
    </div>
</form>
@endsection
