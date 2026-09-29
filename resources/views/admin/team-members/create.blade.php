@extends('admin.layouts.app')

@section('title', 'إضافة عضو للفريق')

@section('content')
<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-right">
    <form action="{{ route('admin.team-members.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.team-members._form')
        @include('admin.partials.form-actions', ['submit' => 'حفظ', 'cancel' => route('admin.team-members.index')])
    </form>
</div>
@endsection
