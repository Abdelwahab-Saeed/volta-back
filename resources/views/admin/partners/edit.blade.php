@extends('admin.layouts.app')

@section('title', 'تعديل شريك أو عميل')
@section('back', route('admin.partners.index'))
@section('back_label', 'الشركاء والعملاء')

@section('content')
<div class="card p-6 max-w-3xl">
    <form action="{{ route('admin.partners.update', $partner) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.partners._form')
        @include('admin.partials.form-actions', ['submit' => 'تحديث', 'cancel' => route('admin.partners.index')])
    </form>
</div>
@endsection
