@extends('admin.layouts.app')

@section('title', 'تعديل الكوبون: ' . $coupon->code)
@section('heading', 'تعديل الكوبون')
@section('subtitle')
    <span class="font-extrabold text-navy-900 tracking-wider" dir="ltr">{{ $coupon->code }}</span> · استُخدم {{ $coupon->times_used }} مرة
@endsection
@section('back', route('admin.coupons.index'))
@section('back_label', 'الكوبونات')

@section('content')
<div class="card p-6 max-w-4xl">
    <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.coupons._form', ['coupon' => $coupon])
        @include('admin.partials.form-actions', ['submit' => 'حفظ التعديلات', 'cancel' => route('admin.coupons.index')])
    </form>
</div>
@endsection
