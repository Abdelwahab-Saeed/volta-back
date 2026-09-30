@extends('admin.layouts.app')

@section('title', 'إضافة كوبون جديد')
@section('subtitle', 'الكوبونات خصومات على السلة كلها. خصومات المنتجات تُعمل من "العروض".')
@section('back', route('admin.coupons.index'))
@section('back_label', 'الكوبونات')

@section('content')
<div class="card p-6 max-w-4xl">
    <form action="{{ route('admin.coupons.store') }}" method="POST">
        @csrf
        @include('admin.coupons._form')
        @include('admin.partials.form-actions', ['submit' => 'حفظ الكوبون', 'cancel' => route('admin.coupons.index')])
    </form>
</div>
@endsection
