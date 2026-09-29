@extends('admin.layouts.app')

@section('title', 'تعديل بيانات عضو')

@section('content')
<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-right">
    <form action="{{ route('admin.team-members.update', $member) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.team-members._form')
        @include('admin.partials.form-actions', ['submit' => 'تحديث', 'cancel' => route('admin.team-members.index')])
    </form>
</div>
@endsection
