@extends('admin.layouts.app')

@section('title', 'تعديل شريك أو عميل')

@section('content')
<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-right">
    <form action="{{ route('admin.partners.update', $partner) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.partners._form')
        @include('admin.partials.form-actions', ['submit' => 'تحديث', 'cancel' => route('admin.partners.index')])
    </form>
</div>
@endsection
