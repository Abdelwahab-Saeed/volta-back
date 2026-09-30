@extends('admin.layouts.app')

@section('title', 'الإعدادات')
@section('subtitle', 'روابط التواصل والعنوان الذي يظهر في تذييل المتجر.')

@php
    $socials = [
        'facebook'  => ['فيسبوك', 'Facebook', 'https://facebook.com/...', '<path d="M12 2C6.477 2 2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.879V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.989C18.343 21.129 22 16.99 22 12c0-5.523-4.477-10-10-10z"/>'],
        'instagram' => ['إنستغرام', 'Instagram', 'https://instagram.com/...', '<path d="M12 2.2c3.2 0 3.6 0 4.8.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 3.2-1.7 4.8-4.9 4.9-1.3.1-1.6.1-4.8.1s-3.6 0-4.8-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8C2.4 3.9 4 2.4 7.2 2.3 8.4 2.2 8.8 2.2 12 2.2zM12 7a5 5 0 100 10 5 5 0 000-10zm0 8.2a3.2 3.2 0 110-6.4 3.2 3.2 0 010 6.4zm5.2-9.6a1.2 1.2 0 100 2.4 1.2 1.2 0 000-2.4z"/>'],
        'twitter'   => ['تويتر / X', 'Twitter / X', 'https://x.com/...', '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>'],
        'youtube'   => ['يوتيوب', 'YouTube', 'https://youtube.com/...', '<path d="M23.5 6.2a3 3 0 00-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 00.5 6.2 31 31 0 000 12a31 31 0 00.5 5.8 3 3 0 002.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 002.1-2.1A31 31 0 0024 12a31 31 0 00-.5-5.8zM9.6 15.6V8.4l6.3 3.6-6.3 3.6z"/>'],
        'tiktok'    => ['تيك توك', 'TikTok', 'https://tiktok.com/@...', '<path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/>'],
    ];
@endphp

@section('content')
<form action="{{ route('admin.settings.store') }}" method="POST" class="max-w-4xl space-y-6">
    @csrf

    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">وسائل التواصل الاجتماعي</h2>
                <p class="card-subtitle">تظهر أيقونة كل رابط تضيفه في تذييل المتجر. اترك الحقل فارغاً لإخفاء الأيقونة.</p>
            </div>
        </div>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach ($socials as $key => [$labelAr, $labelEn, $placeholder, $svg])
                <div>
                    <label for="{{ $key }}" class="label">{{ $labelAr }} <span class="text-slate-400 font-semibold">({{ $labelEn }})</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $svg !!}</svg>
                        </span>
                        <input type="url" name="{{ $key }}" id="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}" placeholder="{{ $placeholder }}" dir="ltr"
                            class="input pl-10 text-left @error($key) input-error @enderror">
                    </div>
                    @error($key)
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">العنوان</h2>
                <p class="card-subtitle">يظهر في تذييل المتجر حسب لغة الزائر.</p>
            </div>
        </div>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach (['ar' => ['rtl', 'مثال: القاهرة، مصر'], 'en' => ['ltr', 'e.g. Cairo, Egypt']] as $locale => [$dir, $placeholder])
                @php($input = "location_{$locale}")
                <div>
                    <label for="{{ $input }}" class="label">{{ __("admin.{$input}") }}</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 {{ $dir === 'rtl' ? 'right-0 pr-3' : 'left-0 pl-3' }} flex items-center text-slate-400 pointer-events-none">
                            <x-admin.icon name="map-pin" class="w-5 h-5" />
                        </span>
                        <input type="text" name="{{ $input }}" id="{{ $input }}" value="{{ old($input, $settings[$input] ?? '') }}" dir="{{ $dir }}" placeholder="{{ $placeholder }}"
                            class="input {{ $dir === 'rtl' ? 'pr-10' : 'pl-10 text-left' }} @error($input) input-error @enderror">
                    </div>
                    @error($input)
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    </section>

    <div class="flex">
        <button type="submit" class="btn-primary btn-lg">
            <x-admin.icon name="check" class="w-5 h-5" />
            حفظ الإعدادات
        </button>
    </div>
</form>
@endsection
