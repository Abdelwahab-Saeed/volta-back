@extends('admin.layouts.app')

@section('title', 'تعديل القسم: ' . $category->name)
@section('heading', $category->name)
@section('subtitle', 'تعديل بيانات القسم.')
@section('back', route('admin.categories.index'))
@section('back_label', 'الأقسام')

@section('content')
<div class="card p-6 max-w-3xl">
    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.categories._form', ['category' => $category])
        @include('admin.partials.form-actions', ['submit' => 'حفظ التعديلات', 'cancel' => route('admin.categories.index')])
    </form>
</div>
@endsection
