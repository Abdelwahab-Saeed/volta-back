@extends('admin.layouts.app')

@section('title', 'تعديل شهادة')

@section('content')
<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-right">
    <form action="{{ route('admin.certificates.update', $certificate) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.certificates._form')
        @include('admin.partials.form-actions', ['submit' => 'تحديث الشهادة', 'cancel' => route('admin.certificates.index')])
    </form>
</div>
@endsection
