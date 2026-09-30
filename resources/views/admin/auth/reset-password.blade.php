@extends('admin.layouts.auth')

@section('title', 'تعيين كلمة المرور')
@section('heading', 'تعيين كلمة مرور جديدة')
@section('subheading', 'اختر كلمة مرور قوية ثم أكّدها.')

@section('content')
<form action="{{ route('admin.password.update') }}" method="POST" class="space-y-5">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div>
        <label for="email" class="label">البريد الإلكتروني</label>
        <input type="email" name="email" id="email" value="{{ $email ?? old('email') }}" required dir="ltr" class="input h-12 text-left">
    </div>

    <div>
        <label for="password" class="label">كلمة المرور الجديدة</label>
        <input type="password" name="password" id="password" placeholder="••••••••" required autocomplete="new-password" dir="ltr" class="input h-12 text-left">
    </div>

    <div>
        <label for="password_confirmation" class="label">تأكيد كلمة المرور</label>
        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••••" required autocomplete="new-password" dir="ltr" class="input h-12 text-left">
    </div>

    <button type="submit" class="btn-primary btn-lg w-full">
        <x-admin.icon name="lock" class="w-5 h-5" />
        تغيير كلمة المرور
    </button>
</form>
@endsection
