@extends('admin.layouts.app')

@section('title', 'تعديل طلب موزع')
@section('back', route('admin.distributor-requests.show', $distributorRequest))
@section('back_label', $distributorRequest->name)

@section('content')
<div class="card p-6 max-w-4xl">
    <form action="{{ route('admin.distributor-requests.update', $distributorRequest) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.distributor-requests._form')
        @include('admin.partials.form-actions', ['submit' => 'تحديث', 'cancel' => route('admin.distributor-requests.show', $distributorRequest)])
    </form>
</div>
@endsection
