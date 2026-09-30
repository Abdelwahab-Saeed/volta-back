@extends('admin.layouts.app')

@section('title', 'إضافة شريك أو عميل')
@section('back', route('admin.partners.index'))
@section('back_label', 'الشركاء والعملاء')

@section('content')
<div class="card p-6 max-w-3xl">
    <form action="{{ route('admin.partners.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.partners._form')
        @include('admin.partials.form-actions', ['submit' => 'حفظ', 'cancel' => route('admin.partners.index')])
    </form>
</div>
@endsection
