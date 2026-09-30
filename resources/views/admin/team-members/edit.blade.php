@extends('admin.layouts.app')

@section('title', 'تعديل بيانات عضو')
@section('back', route('admin.team-members.index'))
@section('back_label', 'فريق العمل')

@section('content')
<div class="card p-6 max-w-3xl">
    <form action="{{ route('admin.team-members.update', $member) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.team-members._form')
        @include('admin.partials.form-actions', ['submit' => 'تحديث', 'cancel' => route('admin.team-members.index')])
    </form>
</div>
@endsection
