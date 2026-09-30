<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    @include('admin.partials.head')
    <title>@yield('title') · فولتا</title>
</head>
<body class="min-h-screen bg-slate-100 lg:grid lg:grid-cols-2">
    {{-- Brand panel --}}
    <aside class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-navy-900 p-12 text-white">
        <div aria-hidden="true" class="absolute -top-32 -left-32 w-[28rem] h-[28rem] rounded-full bg-brand-500/25 blur-3xl"></div>
        <div aria-hidden="true" class="absolute -bottom-40 -right-24 w-[26rem] h-[26rem] rounded-full bg-teal-400/15 blur-3xl"></div>
        <div aria-hidden="true" class="absolute inset-0 opacity-[0.06] [background-image:linear-gradient(white_1px,transparent_1px),linear-gradient(90deg,white_1px,transparent_1px)] [background-size:40px_40px]"></div>

        <img src="{{ asset('images/admin-logo-white.png') }}" alt="Volta" class="relative h-9 w-auto self-start">

        <div class="relative max-w-md">
            <p class="text-sm font-bold text-brand-300 mb-3">لوحة تحكم المتجر</p>
            <h2 class="text-4xl font-extrabold leading-tight">كل ما تحتاجه لإدارة متجر فولتا في مكان واحد.</h2>
            <ul class="mt-8 space-y-3 text-slate-300">
                <li class="flex items-center gap-3"><x-admin.icon name="orders" class="w-5 h-5 text-brand-300" /> متابعة الطلبات وتحديث حالتها</li>
                <li class="flex items-center gap-3"><x-admin.icon name="cube" class="w-5 h-5 text-brand-300" /> المنتجات والأسعار والمخزون</li>
                <li class="flex items-center gap-3"><x-admin.icon name="tag" class="w-5 h-5 text-brand-300" /> العروض والكوبونات ومحتوى الموقع</li>
            </ul>
        </div>

        <p class="relative text-xs text-slate-400">© {{ date('Y') }} Volta</p>
    </aside>

    {{-- Form --}}
    <main class="flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-md">
            <img src="{{ asset('Logo.png') }}" alt="Volta" class="h-9 w-auto mx-auto mb-8 lg:hidden">
            <div class="card shadow-pop p-8 sm:p-10">
                <div class="mb-8">
                    <h1 class="text-2xl font-extrabold text-navy-900">@yield('heading')</h1>
                    <p class="mt-1.5 text-sm text-slate-500">@yield('subheading')</p>
                </div>

                @if(session('success'))
                    <div class="mb-6 flex gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-sm font-semibold text-emerald-800" role="status">
                        <x-admin.icon name="check-circle" class="w-5 h-5 text-emerald-600" />
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 flex gap-2.5 rounded-xl border border-red-200 bg-red-50 p-3.5 text-sm font-semibold text-red-800" role="alert">
                        <x-admin.icon name="warning" class="w-5 h-5 text-red-600" />
                        {{ $errors->first() }}
                    </div>
                @endif

                @yield('content')
            </div>
            <p class="mt-6 text-center text-xs text-slate-400">منصة فولتا للتجارة الإلكترونية</p>
        </div>
    </main>
</body>
</html>
