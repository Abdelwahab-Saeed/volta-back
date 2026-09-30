@extends('admin.layouts.app')

@section('title', 'تعديل المقال')
@section('subtitle', $post->title)
@section('back', route('admin.posts.index'))
@section('back_label', 'المقالات')

@section('content')
<div class="card p-6 max-w-5xl">
    <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.posts._form', ['post' => $post])
        @include('admin.partials.form-actions', ['submit' => 'حفظ التعديلات', 'cancel' => route('admin.posts.index')])
    </form>
</div>
@endsection
