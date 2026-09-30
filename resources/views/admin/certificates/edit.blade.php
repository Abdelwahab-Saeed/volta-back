@extends('admin.layouts.app')

@section('title', 'تعديل شهادة')
@section('back', route('admin.certificates.index'))
@section('back_label', 'الشهادات')

@section('content')
<div class="card p-6 max-w-3xl">
    <form action="{{ route('admin.certificates.update', $certificate) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.certificates._form')
        @include('admin.partials.form-actions', ['submit' => 'تحديث الشهادة', 'cancel' => route('admin.certificates.index')])
    </form>
</div>
@endsection
