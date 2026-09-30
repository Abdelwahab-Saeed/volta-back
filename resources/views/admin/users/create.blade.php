@extends('admin.layouts.app')

@section('title', 'إضافة مستخدم جديد')
@section('back', route('admin.users.index'))
@section('back_label', 'المستخدمون')

@section('content')
<div class="card p-6 max-w-4xl">
    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.users._form')
        @include('admin.partials.form-actions', ['submit' => 'حفظ البيانات', 'cancel' => route('admin.users.index')])
    </form>
</div>
@endsection
