@extends('admin.layouts.app')

@section('title', 'إضافة مقال جديد')
@section('back', route('admin.posts.index'))
@section('back_label', 'المقالات')

@section('content')
<div class="card p-6 max-w-5xl">
    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.posts._form')
        @include('admin.partials.form-actions', ['submit' => 'نشر المقال', 'cancel' => route('admin.posts.index')])
    </form>
</div>
@endsection
