@extends('admin.layouts.app')

@section('title', 'تعديل المستخدم: ' . $user->name)
@section('heading', $user->name)
@section('subtitle')
    <span dir="ltr">{{ $user->email }}</span> · انضم {{ $user->created_at?->format('Y/m/d') ?? '—' }}
@endsection
@section('back', route('admin.users.index'))
@section('back_label', 'المستخدمون')

@section('content')
<div class="card p-6 max-w-4xl">
    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.users._form', ['user' => $user])
        @include('admin.partials.form-actions', ['submit' => 'حفظ التعديلات', 'cancel' => route('admin.users.index')])
    </form>
</div>
@endsection
