<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    @include('admin.partials.head')
    <title>@yield('title', 'لوحة التحكم') · فولتا</title>
</head>
@php
    $admin = auth()->user();
    $unreadCount = $admin->unreadNotifications->count();
    $pendingOrders = \App\Models\Order::where('status', \App\Enums\OrderStatus::PENDING->value)->count();

    // One list drives the sidebar and the quick search (Ctrl+K).
    // 'active' is the route-name pattern that highlights the item.
    $navGroups = [
        'نظرة عامة' => [
            ['label' => 'الرئيسية', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'home'],
            ['label' => 'الإشعارات', 'route' => 'admin.notifications.index', 'active' => 'admin.notifications.*', 'icon' => 'bell', 'badge' => $unreadCount],
        ],
        'المبيعات' => [
            ['label' => 'الطلبات', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*', 'icon' => 'orders', 'badge' => $pendingOrders, 'badgeHint' => 'طلبات قيد الانتظار'],
            ['label' => 'المنتجات المباعة', 'route' => 'admin.reports.sold_products', 'active' => 'admin.reports.*', 'icon' => 'chart'],
        ],
        'الكتالوج' => [
            ['label' => 'المنتجات', 'route' => 'admin.products.index', 'active' => ['admin.products.*', 'admin.features.*', 'admin.images.*'], 'icon' => 'cube'],
            ['label' => 'الأقسام', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*', 'icon' => 'folder'],
            ['label' => 'العروض', 'route' => 'admin.offers.index', 'active' => 'admin.offers.*', 'icon' => 'tag'],
            ['label' => 'الكوبونات', 'route' => 'admin.coupons.index', 'active' => 'admin.coupons.*', 'icon' => 'ticket'],
        ],
        'محتوى الموقع' => [
            ['label' => 'البانرات', 'route' => 'admin.banners.index', 'active' => 'admin.banners.*', 'icon' => 'photo'],
            ['label' => 'المقالات', 'route' => 'admin.posts.index', 'active' => 'admin.posts.*', 'icon' => 'newspaper'],
            ['label' => 'الشركاء والعملاء', 'route' => 'admin.partners.index', 'active' => 'admin.partners.*', 'icon' => 'handshake'],
            ['label' => 'الشهادات', 'route' => 'admin.certificates.index', 'active' => 'admin.certificates.*', 'icon' => 'badge'],
            ['label' => 'فريق العمل', 'route' => 'admin.team-members.index', 'active' => 'admin.team-members.*', 'icon' => 'team'],
        ],
        'الإدارة' => [
            ['label' => 'المستخدمون', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => 'users'],
            ['label' => 'الإعدادات', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*', 'icon' => 'cog'],
        ],
    ];

    $quickCreate = [
        ['label' => 'منتج جديد', 'route' => 'admin.products.create', 'icon' => 'cube'],
        ['label' => 'عرض جديد', 'route' => 'admin.offers.create', 'icon' => 'tag'],
        ['label' => 'كوبون جديد', 'route' => 'admin.coupons.create', 'icon' => 'ticket'],
        ['label' => 'قسم جديد', 'route' => 'admin.categories.create', 'icon' => 'folder'],
        ['label' => 'بانر جديد', 'route' => 'admin.banners.create', 'icon' => 'photo'],
        ['label' => 'مقال جديد', 'route' => 'admin.posts.create', 'icon' => 'newspaper'],
    ];

    $paletteItems = collect($navGroups)->flatMap(fn ($items, $group) => collect($items)->map(fn ($item) => [
        'label' => $item['label'], 'group' => $group, 'url' => route($item['route']), 'icon' => $item['icon'],
    ]))->merge(collect($quickCreate)->map(fn ($item) => [
        'label' => $item['label'], 'group' => 'إضافة سريعة', 'url' => route($item['route']), 'icon' => 'plus',
    ]))->values();

    $initial = mb_substr($admin->name, 0, 1);
@endphp
<body class="bg-slate-100/70">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:right-3 focus:z-[200] btn-primary">تخطي إلى المحتوى</a>

    <div class="min-h-screen lg:flex">
        {{-- Sidebar backdrop (mobile) --}}
        <div id="sidebar-overlay" class="fixed inset-0 bg-navy-950/60 backdrop-blur-sm z-30 hidden lg:hidden transition-opacity duration-300 opacity-0" onclick="toggleSidebar()"></div>

        {{-- Sidebar --}}
        {{-- The navy column stretches the full page height on desktop; its content sticks to the viewport while scrolling --}}
        <aside id="sidebar" class="fixed inset-y-0 right-0 z-40 w-72 lg:w-64 xl:w-72 bg-navy-900 text-slate-300 transform translate-x-full lg:translate-x-0 lg:static lg:inset-auto lg:z-auto transition-transform duration-300 ease-out shrink-0">
          <div class="h-full lg:h-screen lg:sticky lg:top-0 flex flex-col">
            <div class="relative flex items-center justify-between h-16 px-5 border-b border-white/5">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/admin-logo-white.png') }}" alt="Volta" class="h-6 w-auto">
                    <span class="text-[11px] font-bold text-brand-300 bg-white/5 rounded-md px-1.5 py-0.5">لوحة التحكم</span>
                </a>
                <button type="button" onclick="toggleSidebar()" class="lg:hidden icon-btn text-slate-400 hover:bg-white/10 hover:text-white" aria-label="إغلاق القائمة">
                    <x-admin.icon name="x" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6 [scrollbar-width:thin] [scrollbar-color:theme(colors.navy.700)_transparent]" aria-label="القائمة الرئيسية">
                @foreach ($navGroups as $group => $items)
                    <div>
                        <p class="px-3 mb-1.5 text-[11px] font-bold tracking-wide text-slate-500">{{ $group }}</p>
                        <ul class="space-y-0.5">
                            @foreach ($items as $item)
                                @php($isActive = request()->routeIs(...(array) $item['active']))
                                <li>
                                    <a href="{{ route($item['route']) }}"
                                       @if($isActive) aria-current="page" @endif
                                       class="group relative flex items-center gap-3 h-10 px-3 rounded-xl text-sm font-semibold transition-colors {{ $isActive ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">
                                        @if($isActive)
                                            <span class="absolute right-0 top-2 bottom-2 w-1 rounded-l-full bg-gradient-to-b from-brand-400 to-teal-400"></span>
                                        @endif
                                        <x-admin.icon :name="$item['icon']" class="w-5 h-5 {{ $isActive ? 'text-brand-300' : 'text-slate-500 group-hover:text-slate-300' }}" />
                                        <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                        @if(!empty($item['badge']))
                                            <span class="min-w-[1.5rem] h-6 px-1.5 inline-flex items-center justify-center rounded-full text-[11px] font-extrabold {{ $isActive ? 'bg-brand-500 text-white' : 'bg-brand-500/20 text-brand-200' }}" title="{{ $item['badgeHint'] ?? '' }}">
                                                {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>

            <div class="p-3 border-t border-white/5">
                <a href="{{ config('app.frontend_url') }}" target="_blank" rel="noopener" class="flex items-center gap-3 h-10 px-3 rounded-xl text-sm font-semibold hover:bg-white/5 hover:text-white transition-colors">
                    <x-admin.icon name="globe" class="w-5 h-5 text-slate-500" />
                    <span class="flex-1">زيارة المتجر</span>
                    <x-admin.icon name="external" class="w-4 h-4 text-slate-500" />
                </a>
                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
                <button type="button" onclick="confirmAction('logout-form', 'هل أنت متأكد من رغبتك في تسجيل الخروج؟', 'تسجيل الخروج', 'danger')" class="w-full flex items-center gap-3 h-10 px-3 rounded-xl text-sm font-semibold text-red-300 hover:bg-red-500/10 hover:text-red-200 transition-colors">
                    <x-admin.icon name="logout" class="w-5 h-5" />
                    تسجيل الخروج
                </button>
            </div>
          </div>
        </aside>

        {{-- Main --}}
        <div class="flex-1 min-w-0 flex flex-col">
            <header class="sticky top-0 z-20 bg-white/85 backdrop-blur-md border-b border-slate-200/80">
                <div class="flex items-center gap-3 h-16 px-4 sm:px-6 lg:px-8">
                    <button type="button" onclick="toggleSidebar()" class="lg:hidden icon-btn -mr-2" aria-label="فتح القائمة">
                        <x-admin.icon name="menu" class="w-6 h-6" />
                    </button>

                    {{-- Quick search (Ctrl+K) --}}
                    <button type="button" onclick="openPalette()" class="flex items-center gap-2 h-10 w-full max-w-xs px-3 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-400 hover:border-slate-300 hover:bg-white transition">
                        <x-admin.icon name="search" class="w-4 h-4" />
                        <span class="flex-1 text-right">انتقل إلى صفحة…</span>
                        <span class="hidden sm:flex items-center gap-1" dir="ltr"><span class="kbd">Ctrl</span><span class="kbd">K</span></span>
                    </button>

                    <div class="flex items-center gap-1.5 sm:gap-2 mr-auto">
                        {{-- Quick create --}}
                        <div class="relative" data-dropdown>
                            <button type="button" class="btn-primary h-10 px-3 sm:px-4" onclick="toggleDropdown(this)" aria-haspopup="true" aria-expanded="false">
                                <x-admin.icon name="plus" class="w-5 h-5" />
                                <span class="hidden sm:inline">إضافة</span>
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 mt-2 w-56 card shadow-pop p-1.5 z-30">
                                @foreach ($quickCreate as $item)
                                    <a href="{{ route($item['route']) }}" class="flex items-center gap-3 px-3 h-10 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-navy-900">
                                        <x-admin.icon :name="$item['icon']" class="w-4 h-4 text-slate-400" />
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Notifications --}}
                        <a href="{{ route('admin.notifications.index') }}" class="icon-btn w-10 h-10 relative" data-tip="الإشعارات" aria-label="الإشعارات{{ $unreadCount ? " ({$unreadCount} غير مقروءة)" : '' }}">
                            <x-admin.icon name="bell" class="w-6 h-6" />
                            @if($unreadCount > 0)
                                <span class="absolute top-1 right-1 min-w-[1.1rem] h-[1.1rem] px-1 flex items-center justify-center text-[10px] font-extrabold text-white bg-red-500 ring-2 ring-white rounded-full">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                            @endif
                        </a>

                        {{-- Account --}}
                        <div class="relative" data-dropdown>
                            <button type="button" onclick="toggleDropdown(this)" class="flex items-center gap-2.5 h-10 pr-1 pl-2 rounded-xl hover:bg-slate-100 transition" aria-haspopup="true" aria-expanded="false">
                                @if($admin->image)
                                    <img src="{{ asset('storage/' . $admin->image) }}" alt="" class="w-8 h-8 rounded-lg object-cover">
                                @else
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-navy-800 text-white flex items-center justify-center text-sm font-extrabold">{{ $initial }}</span>
                                @endif
                                <span class="hidden md:flex flex-col items-start leading-tight">
                                    <span class="text-sm font-bold text-navy-900 max-w-[9rem] truncate">{{ $admin->name }}</span>
                                    <span class="text-[11px] text-slate-400 font-semibold">مسؤول</span>
                                </span>
                                <x-admin.icon name="chevron-down" class="hidden md:block w-4 h-4 text-slate-400" />
                            </button>
                            <div class="dropdown-menu hidden absolute left-0 mt-2 w-60 card shadow-pop p-1.5 z-30">
                                <div class="px-3 py-2.5 border-b border-slate-100 mb-1">
                                    <p class="text-sm font-bold text-navy-900 truncate">{{ $admin->name }}</p>
                                    <p class="text-xs text-slate-500 truncate" dir="ltr">{{ $admin->email }}</p>
                                </div>
                                <a href="{{ route('admin.users.edit', $admin) }}" class="flex items-center gap-3 px-3 h-10 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100">
                                    <x-admin.icon name="users" class="w-4 h-4 text-slate-400" /> حسابي
                                </a>
                                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 h-10 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100">
                                    <x-admin.icon name="cog" class="w-4 h-4 text-slate-400" /> الإعدادات
                                </a>
                                <button type="button" onclick="confirmAction('logout-form', 'هل أنت متأكد من رغبتك في تسجيل الخروج؟', 'تسجيل الخروج', 'danger')" class="w-full flex items-center gap-3 px-3 h-10 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-50">
                                    <x-admin.icon name="logout" class="w-4 h-4" /> تسجيل الخروج
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main id="main-content" class="flex-1 min-w-0 px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                {{-- Page header: title, optional subtitle / back link, and page actions on the end side --}}
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
                    <div class="min-w-0">
                        @hasSection('back')
                            <a href="@yield('back')" class="inline-flex items-center gap-1.5 text-sm font-bold text-slate-500 hover:text-navy-900 mb-2">
                                <x-admin.icon name="arrow-right" class="w-4 h-4" />
                                @yield('back_label', 'رجوع')
                            </a>
                        @endif
                        {{-- 'heading' overrides the visible title when the <title> text is not what the page should show --}}
                        <h1 class="text-2xl font-extrabold text-navy-900 leading-tight truncate">
                            @hasSection('heading') @yield('heading') @else @yield('title', 'لوحة التحكم') @endif
                        </h1>
                        @hasSection('subtitle')
                            <p class="mt-1 text-sm text-slate-500">@yield('subtitle')</p>
                        @endif
                    </div>
                    @hasSection('actions')
                        <div class="flex flex-wrap items-center gap-2 shrink-0">@yield('actions')</div>
                    @endif
                </div>

                @if($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 flex gap-3" role="alert">
                        <x-admin.icon name="warning" class="w-6 h-6 text-red-500" />
                        <div class="min-w-0">
                            <p class="font-bold text-red-800">راجع البيانات التالية قبل الحفظ:</p>
                            <ul class="mt-1.5 space-y-1 text-sm text-red-700 list-disc pr-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="px-4 sm:px-6 lg:px-8 py-5 text-xs text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-2 border-t border-slate-200/70">
                <span>© {{ date('Y') }} Volta · جميع الحقوق محفوظة</span>
                <span dir="ltr">Developed and Maintained by <a href="https://falak-innovation.com/" target="_blank" rel="noopener" class="font-bold hover:text-brand-700">Falak Innovation</a></span>
            </footer>
        </div>
    </div>

    {{-- Success toast --}}
    @if(session('success'))
        <div id="toast" class="fixed top-20 left-4 right-4 sm:right-auto sm:w-96 z-[90] transition-all duration-300" role="status" aria-live="polite">
            <div class="card shadow-pop flex items-start gap-3 p-4 border-emerald-200">
                <span class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <x-admin.icon name="check" class="w-5 h-5" stroke="2.5" />
                </span>
                <p class="flex-1 pt-1.5 text-sm font-bold text-navy-900">{{ session('success') }}</p>
                <button type="button" onclick="hideToast()" class="icon-btn w-8 h-8" aria-label="إغلاق">
                    <x-admin.icon name="x" class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif

    {{-- Confirmation dialog (confirmAction) --}}
    <div id="confirm-modal" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div id="modal-backdrop" class="fixed inset-0 bg-navy-950/60 backdrop-blur-sm" aria-hidden="true" onclick="closeConfirmModal()"></div>
        <div class="fixed inset-0 flex items-end sm:items-center justify-center p-4 pointer-events-none">
            <div class="pointer-events-auto w-full sm:max-w-md card shadow-pop overflow-hidden">
                <div class="p-6 flex gap-4">
                    <div id="modal-icon-container" class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                        <svg id="modal-icon" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="min-w-0 pt-1">
                        <h3 class="text-lg font-extrabold text-navy-900" id="modal-title">تأكيد الإجراء</h3>
                        <p class="mt-1.5 text-sm text-slate-600 leading-relaxed" id="modal-message">هل أنت متأكد من رغبتك في تنفيذ هذا الإجراء؟</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-start gap-2">
                    <button type="button" id="confirm-btn" class="btn bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-500 sm:min-w-[7rem]">تأكيد</button>
                    <button type="button" onclick="closeConfirmModal()" class="btn-secondary sm:min-w-[7rem]">إلغاء</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick search (Ctrl+K) --}}
    <div id="palette" class="fixed inset-0 z-[110] hidden" role="dialog" aria-modal="true" aria-label="البحث السريع">
        <div class="fixed inset-0 bg-navy-950/60 backdrop-blur-sm" onclick="closePalette()"></div>
        <div class="relative mx-auto mt-[12vh] w-[calc(100%-2rem)] max-w-lg card shadow-pop overflow-hidden">
            <div class="flex items-center gap-3 px-4 border-b border-slate-100">
                <x-admin.icon name="search" class="w-5 h-5 text-slate-400" />
                <input id="palette-input" type="text" autocomplete="off" placeholder="اكتب اسم الصفحة… (طلبات، منتجات، كوبون)" class="flex-1 h-14 bg-transparent text-base text-navy-900 placeholder:text-slate-400 focus:outline-none" aria-controls="palette-list">
                <span class="kbd">Esc</span>
            </div>
            <ul id="palette-list" class="max-h-80 overflow-y-auto p-2" role="listbox"></ul>
        </div>
    </div>

    <script>
        let currentFormId = null;

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const isHidden = sidebar.classList.contains('translate-x-full');

            if (isHidden) {
                sidebar.classList.remove('translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => {
                    overlay.classList.remove('opacity-0');
                    overlay.classList.add('opacity-100');
                }, 10);
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.add('translate-x-full');
                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0');
                setTimeout(() => {
                    overlay.classList.add('hidden');
                }, 300);
                document.body.style.overflow = '';
            }
        }

        // Same signature as before: pages call confirmAction(formId, message, title, type) and the form
        // with that id is submitted on confirm. type 'danger' (default) = red, anything else = brand blue.
        function confirmAction(formId, message = 'هل أنت متأكد؟', title = 'تأكيد الإجراء', type = 'danger') {
            currentFormId = formId;
            document.getElementById('modal-message').innerText = message;
            document.getElementById('modal-title').innerText = title;

            const danger = type === 'danger';
            const iconContainer = document.getElementById('modal-icon-container');
            const icon = document.getElementById('modal-icon');
            const confirmBtn = document.getElementById('confirm-btn');

            iconContainer.classList.toggle('bg-red-100', danger);
            iconContainer.classList.toggle('bg-brand-100', !danger);
            icon.classList.toggle('text-red-600', danger);
            icon.classList.toggle('text-brand-700', !danger);
            confirmBtn.classList.toggle('bg-red-600', danger);
            confirmBtn.classList.toggle('hover:bg-red-700', danger);
            confirmBtn.classList.toggle('bg-brand-600', !danger);
            confirmBtn.classList.toggle('hover:bg-brand-700', !danger);

            document.getElementById('confirm-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            confirmBtn.focus();

            confirmBtn.onclick = function () {
                confirmDismissCallback = null;
                if (currentFormId) {
                    confirmBtn.disabled = true;
                    document.getElementById(currentFormId).submit();
                }
            };
        }

        // Optional: called when the dialog is dismissed without confirming (e.g. to reset a select).
        let confirmDismissCallback = null;
        function onConfirmDismiss(callback) { confirmDismissCallback = callback; }

        function closeConfirmModal() {
            if (document.getElementById('confirm-modal').classList.contains('hidden')) return;
            if (confirmDismissCallback) { confirmDismissCallback(); confirmDismissCallback = null; }
            document.getElementById('confirm-modal').classList.add('hidden');
            document.getElementById('confirm-btn').disabled = false;
            document.body.style.overflow = '';
            currentFormId = null;
        }

        // Image inputs (partials/image-upload): preview the picked file before the form is sent.
        // A cancelled picker, a refused file or undo=true clears the pick and shows the current image again.
        function pickImage(input, undo = false) {
            const el = suffix => document.getElementById(input.id + '-' + suffix);
            const preview = el('preview');
            const error = el('pick-error');
            const file = undo ? null : input.files?.[0];
            const maxKb = Number(input.dataset.maxKb || 0);

            let problem = '';
            if (file && !file.type.startsWith('image/')) {
                problem = 'الملف المختار ليس صورة، اختر ملف صورة.';
            } else if (file && maxKb && file.size > maxKb * 1024) {
                problem = `حجم الصورة ${(file.size / 1048576).toFixed(1)}MB أكبر من الحد المسموح (${Math.round(maxKb / 1024)}MB)، اختر صورة أصغر.`;
            }
            error.textContent = problem;
            error.classList.toggle('hidden', !problem);

            const picked = Boolean(file) && !problem;
            if (!picked) input.value = ''; // a refused file must not be sent

            if (preview.src.startsWith('blob:')) URL.revokeObjectURL(preview.src);
            const src = picked ? URL.createObjectURL(file) : preview.dataset.current;
            preview.src = src;
            el('preview-box').classList.toggle('hidden', !src);
            const tag = el('preview-tag');
            tag.textContent = picked ? 'معاينة الصورة الجديدة' : 'الصورة الحالية';
            tag.classList.toggle('text-brand-700', picked);
            tag.classList.toggle('text-slate-500', !picked);
            el('filename').textContent = picked ? `${file.name} — ${(file.size / 1048576).toFixed(1)}MB` : '';
            el('filename').classList.toggle('hidden', !picked);
            el('undo').classList.toggle('hidden', !picked);
        }

        // Previews with data-size="1600x600": warn when the image has another shape, so it will be cropped.
        function checkImageShape(img) {
            const note = document.getElementById(img.id.replace(/-preview$/, '') + '-shape-note');
            const [w, h] = img.dataset.size.split('x').map(Number);
            const { naturalWidth: nw, naturalHeight: nh } = img;
            const off = nw && nh && Math.abs((nw / nh) / (w / h) - 1) > 0.1;
            note.querySelector('span').textContent = off
                ? `مقاس الصورة ${nw}×${nh} وشكلها مختلف عن المقاس المناسب ${w}×${h}، لذلك سيظهر في المتجر الجزء الموجود في المعاينة فقط.`
                : '';
            note.classList.toggle('hidden', !off);
        }
        // The current image may finish loading before this script runs.
        document.querySelectorAll('img[data-size]').forEach(img => img.complete && img.naturalWidth && checkImageShape(img));

        // Dropdowns (quick create, account menu): one open at a time, closed by outside click or Esc.
        function toggleDropdown(button) {
            const menu = button.parentElement.querySelector('.dropdown-menu');
            const open = menu.classList.contains('hidden');
            closeDropdowns();
            if (open) {
                menu.classList.remove('hidden');
                button.setAttribute('aria-expanded', 'true');
            }
        }
        function closeDropdowns() {
            document.querySelectorAll('[data-dropdown]').forEach(el => {
                el.querySelector('.dropdown-menu').classList.add('hidden');
                el.querySelector('[aria-expanded]')?.setAttribute('aria-expanded', 'false');
            });
        }
        document.addEventListener('click', e => { if (!e.target.closest('[data-dropdown]')) closeDropdowns(); });

        // Success toast hides itself after a few seconds.
        function hideToast() {
            const toast = document.getElementById('toast');
            if (!toast) return;
            toast.classList.add('opacity-0', '-translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }
        setTimeout(hideToast, 5000);

        // Quick search over the sidebar pages and the quick-create links.
        const paletteItems = @json($paletteItems);
        let paletteIndex = 0;
        let paletteResults = [];

        function renderPalette(query = '') {
            const q = query.trim().toLowerCase();
            paletteResults = paletteItems.filter(item => !q || item.label.toLowerCase().includes(q) || item.group.toLowerCase().includes(q));
            paletteIndex = Math.min(paletteIndex, Math.max(paletteResults.length - 1, 0));
            const list = document.getElementById('palette-list');
            if (!paletteResults.length) {
                list.innerHTML = '<li class="px-3 py-8 text-center text-sm text-slate-500">لا توجد نتائج</li>';
                return;
            }
            list.innerHTML = paletteResults.map((item, i) => `
                <li role="option" aria-selected="${i === paletteIndex}">
                    <a href="${item.url}" class="flex items-center gap-3 px-3 h-11 rounded-lg text-sm ${i === paletteIndex ? 'bg-brand-50 text-brand-800' : 'text-slate-700 hover:bg-slate-50'}">
                        <span class="flex-1 font-bold">${item.label}</span>
                        <span class="text-xs text-slate-400">${item.group}</span>
                    </a>
                </li>`).join('');
        }
        function openPalette() {
            const input = document.getElementById('palette-input');
            document.getElementById('palette').classList.remove('hidden');
            input.value = '';
            paletteIndex = 0;
            renderPalette();
            setTimeout(() => input.focus(), 10);
        }
        function closePalette() {
            document.getElementById('palette').classList.add('hidden');
        }
        document.getElementById('palette-input').addEventListener('input', e => { paletteIndex = 0; renderPalette(e.target.value); });
        document.getElementById('palette-input').addEventListener('keydown', e => {
            if (e.key === 'ArrowDown') { e.preventDefault(); paletteIndex = Math.min(paletteIndex + 1, paletteResults.length - 1); renderPalette(e.target.value); }
            if (e.key === 'ArrowUp') { e.preventDefault(); paletteIndex = Math.max(paletteIndex - 1, 0); renderPalette(e.target.value); }
            if (e.key === 'Enter' && paletteResults[paletteIndex]) { window.location = paletteResults[paletteIndex].url; }
        });

        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); openPalette(); }
            if (e.key === 'Escape') { closePalette(); closeConfirmModal(); closeDropdowns(); }
        });
    </script>
    @stack('scripts')
</body>
</html>
