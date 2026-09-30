@extends('admin.layouts.auth')

@section('title', 'استعادة كلمة المرور')
@section('heading', 'استعادة كلمة المرور')
@section('subheading', 'أدخل بريدك الإلكتروني وسنرسل لك رابطاً لتعيين كلمة مرور جديدة.')

@section('content')
<form action="{{ route('admin.password.email') }}" method="POST" class="space-y-5">
    @csrf
    <div>
        <label for="email" class="label">البريد الإلكتروني</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="admin@volta.com" required autofocus dir="ltr" class="input h-12 text-left">
    </div>

    <button type="submit" class="btn-primary btn-lg w-full">
        <x-admin.icon name="mail" class="w-5 h-5" />
        إرسال رابط الاستعادة
    </button>
</form>

<a href="{{ route('admin.login') }}" class="mt-6 flex items-center justify-center gap-1.5 text-sm font-bold text-slate-500 hover:text-navy-900">
    <x-admin.icon name="arrow-right" class="w-4 h-4" />
    العودة لتسجيل الدخول
</a>
@endsection
