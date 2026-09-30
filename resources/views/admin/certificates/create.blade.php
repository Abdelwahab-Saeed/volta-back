@extends('admin.layouts.app')

@section('title', 'إضافة شهادة')
@section('back', route('admin.certificates.index'))
@section('back_label', 'الشهادات')

@section('content')
<div class="card p-6 max-w-3xl">
    <form action="{{ route('admin.certificates.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.certificates._form')
        @include('admin.partials.form-actions', ['submit' => 'حفظ الشهادة', 'cancel' => route('admin.certificates.index')])
    </form>
</div>
@endsection
