@extends('admin.layouts.app')

@section('title', 'تعديل البانر')
@section('back', route('admin.banners.index'))
@section('back_label', 'البانرات')

@section('content')
<div class="card p-6 max-w-3xl">
    <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.banners._form', ['banner' => $banner])
        @include('admin.partials.form-actions', ['submit' => 'تحديث البانر', 'cancel' => route('admin.banners.index')])
    </form>
</div>
@endsection
