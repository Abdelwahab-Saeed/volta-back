@extends('admin.layouts.app')

@section('title', 'إضافة قسم جديد')
@section('back', route('admin.categories.index'))
@section('back_label', 'الأقسام')

@section('content')
<div class="card p-6 max-w-3xl">
    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.categories._form')
        @include('admin.partials.form-actions', ['submit' => 'حفظ القسم', 'cancel' => route('admin.categories.index')])
    </form>
</div>
@endsection
