@extends('admin.layouts.auth')

@section('title', 'تسجيل الدخول')
@section('heading', 'مرحباً بعودتك')
@section('subheading', 'سجّل الدخول للوصول إلى لوحة التحكم.')

@section('content')
<form action="{{ route('admin.login.post') }}" method="POST" class="space-y-5">
    @csrf
    <div>
        <label for="email" class="label">البريد الإلكتروني</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="admin@volta.com" autocomplete="username" autofocus dir="ltr" class="input h-12 text-left">
    </div>
    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="label mb-0">كلمة المرور</label>
            <a href="{{ route('admin.password.request') }}" class="text-xs font-bold text-brand-700 hover:underline">نسيت كلمة المرور؟</a>
        </div>
        <div class="relative">
            <input type="password" name="password" id="password" placeholder="••••••••" required autocomplete="current-password" dir="ltr" class="input h-12 text-left pr-12">
            <button type="button" onclick="const i = document.getElementById('password'); i.type = i.type === 'password' ? 'text' : 'password'; this.setAttribute('aria-pressed', i.type === 'text');" class="absolute right-1.5 top-1/2 -translate-y-1/2 icon-btn" aria-label="إظهار كلمة المرور" aria-pressed="false">
                <x-admin.icon name="eye" class="w-5 h-5" />
            </button>
        </div>
    </div>

    <button type="submit" class="btn-primary btn-lg w-full">
        تسجيل الدخول
        <x-admin.icon name="arrow-left" class="w-5 h-5" />
    </button>
</form>
@endsection
