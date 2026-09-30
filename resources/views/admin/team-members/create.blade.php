@extends('admin.layouts.app')

@section('title', 'إضافة عضو للفريق')
@section('back', route('admin.team-members.index'))
@section('back_label', 'فريق العمل')

@section('content')
<div class="card p-6 max-w-3xl">
    <form action="{{ route('admin.team-members.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.team-members._form')
        @include('admin.partials.form-actions', ['submit' => 'حفظ', 'cancel' => route('admin.team-members.index')])
    </form>
</div>
@endsection
