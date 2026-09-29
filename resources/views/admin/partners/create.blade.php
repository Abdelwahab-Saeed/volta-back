@extends('admin.layouts.app')

@section('title', 'إضافة شريك أو عميل')

@section('content')
<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-right">
    <form action="{{ route('admin.partners.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.partners._form')
        @include('admin.partials.form-actions', ['submit' => 'حفظ', 'cancel' => route('admin.partners.index')])
    </form>
</div>
@endsection
