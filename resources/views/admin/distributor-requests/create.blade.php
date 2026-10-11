@extends('admin.layouts.app')

@section('title', 'إضافة طلب موزع')
@section('subtitle', 'لتسجيل تاجر تواصل معكم بالهاتف أو في المعرض.')
@section('back', route('admin.distributor-requests.index'))
@section('back_label', 'طلبات الموزعين')

@section('content')
<div class="card p-6 max-w-4xl">
    <form action="{{ route('admin.distributor-requests.store') }}" method="POST">
        @csrf
        @include('admin.distributor-requests._form')
        @include('admin.partials.form-actions', ['submit' => 'حفظ', 'cancel' => route('admin.distributor-requests.index')])
    </form>
</div>
@endsection
