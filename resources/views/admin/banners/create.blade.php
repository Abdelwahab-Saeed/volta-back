@extends('admin.layouts.app')

@section('title', 'إضافة بانر جديد')
@section('back', route('admin.banners.index'))
@section('back_label', 'البانرات')

@section('content')
<div class="card p-6 max-w-3xl">
    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.banners._form')
        @include('admin.partials.form-actions', ['submit' => 'حفظ البانر', 'cancel' => route('admin.banners.index')])
    </form>
</div>
@endsection
