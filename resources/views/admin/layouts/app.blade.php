<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - Volta</title>
    <link rel="icon" type="image/png" href="{{ asset('Logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Tajawal', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="min-h-screen flex relative">
        <!-- Sidebar Backdrop (Mobile) -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/50 z-20 hidden lg:hidden transition-opacity duration-300 opacity-0" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 right-0 z-30 w-64 bg-slate-900 text-white transform translate-x-full lg:translate-x-0 lg:static lg:inset-0 transition-transform duration-300 ease-in-out flex-shrink-0">
            <div class="p-6 flex items-center justify-between lg:block">
                <h1 class="text-2xl font-bold tracking-tight">فولتا <span class="text-blue-500 text-sm">للمسؤولين</span></h1>
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <nav class="mt-6 px-4 space-y-2 overflow-y-auto max-h-[calc(100vh-160px)]">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center p-3 rounded-xl hover:bg-slate-800 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    لوحة التحكم
                </a>
                <a href="{{ route('admin.categories.index') }}" class="flex items-center p-3 rounded-xl hover:bg-slate-800 transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-slate-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    الأقسام
                </a>
                <a href="{{ route('admin.products.index') }}" class="flex items-center p-3 rounded-xl hover:bg-slate-800 transition-colors {{ request()->routeIs('admin.products.*') ? 'bg-slate-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    المنتجات
                </a>
                <a href="{{ route('admin.orders.index') }}" class="flex items-center p-3 rounded-xl hover:bg-slate-800 transition-colors {{ request()->routeIs('admin.orders.*') ? 'bg-slate-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    الطلبات
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center p-3 rounded-xl hover:bg-slate-800 transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    المستخدمين
                </a>
                <a href="{{ route('admin.coupons.index') }}" class="flex items-center p-3 rounded-xl hover:bg-slate-800 transition-colors {{ request()->routeIs('admin.coupons.*') ? 'bg-slate-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                    الكوبونات
                </a>
                <a href="{{ route('admin.banners.index') }}" class="flex items-center p-3 rounded-xl hover:bg-slate-800 transition-colors {{ request()->routeIs('admin.banners.*') ? 'bg-slate-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                    البانرات
                </a>
            </nav>
            <div class="absolute bottom-0 w-64 p-4 text-right">
                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
                <button type="button" onclick="confirmAction('logout-form', 'هل أنت متأكد من رغبتك في تسجيل الخروج؟', 'تسجيل الخروج', 'danger')" class="flex items-center w-full p-3 rounded-xl hover:bg-red-900 transition-colors text-red-400 font-bold">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    تسجيل الخروج
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen overflow-y-auto w-full">
            <!-- Header -->
            <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 p-4 sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <button onclick="toggleSidebar()" class="lg:hidden ml-4 p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h2 class="text-xl font-black text-gray-800">@yield('title', 'لوحة الإدارة')</h2>
                    </div>
                    <div class="flex items-center space-x-reverse space-x-4">
                        <div class="hidden sm:flex flex-col text-left ml-3">
                            <span class="text-xs text-gray-400 font-bold">المسؤول</span>
                            <span class="text-sm text-gray-700 font-black">{{ auth()->user()->name }}</span>
                        </div>
                        @if(auth()->user()->image)
                            <img src="{{ asset('storage/' . auth()->user()->image) }}" class="w-10 h-10 rounded-xl object-cover ring-2 ring-gray-50 shadow-sm">
                        @else
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-md flex items-center justify-center font-black">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 sm:p-8">
                @if(session('success'))
                    <div class="bg-green-50 border-r-4 border-green-500 text-green-700 p-4 mb-8 rounded-xl shadow-sm text-right flex items-center" role="alert">
                        <svg class="w-5 h-5 ml-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <p class="font-bold">{{ session('success') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-50 border-r-4 border-red-500 text-red-700 p-4 mb-8 rounded-xl shadow-sm text-right">
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 ml-3 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                            <p class="font-bold text-lg">خطأ في البيانات:</p>
                        </div>
                        <ul class="list-disc list-inside mr-8">
                            @foreach ($errors->all() as $error)
                                <li class="font-medium">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Global Confirmation Modal -->
    <div id="confirm-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div id="modal-backdrop" class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-75" aria-hidden="true" onclick="closeConfirmModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start sm:flex-row-reverse">
                        <div id="modal-icon-container" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10 sm:ml-4">
                            <svg id="modal-icon" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:text-right flex-1">
                            <h3 class="text-xl leading-6 font-black text-gray-900" id="modal-title">تأكيد الإجراء</h3>
                            <div class="mt-2 text-right">
                                <p class="text-gray-500 font-medium" id="modal-message">هل أنت متأكد من رغبتك في تنفيذ هذا الإجراء؟</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse space-x-reverse space-x-3">
                    <button type="button" id="confirm-btn" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none transition-all sm:w-auto">
                        تأكيد
                    </button>
                    <button type="button" onclick="closeConfirmModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none transition-all sm:mt-0 sm:w-auto">
                        إلغاء
                    </button>
                </div>
            </div>
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
                document.body.style.overflow = 'auto';
            }
        }
        function confirmAction(formId, message = 'هل أنت متأكد؟', title = 'تأكيد الإجراء', type = 'danger') {
            currentFormId = formId;
            document.getElementById('modal-message').innerText = message;
            document.getElementById('modal-title').innerText = title;
            
            const iconContainer = document.getElementById('modal-icon-container');
            const icon = document.getElementById('modal-icon');
            const confirmBtn = document.getElementById('confirm-btn');
            
            if (type === 'danger') {
                iconContainer.classList.add('bg-red-100');
                iconContainer.classList.remove('bg-blue-100');
                icon.classList.add('text-red-600');
                icon.classList.remove('text-blue-600');
                confirmBtn.classList.add('bg-red-600', 'hover:bg-red-700');
                confirmBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            } else {
                iconContainer.classList.add('bg-blue-100');
                iconContainer.classList.remove('bg-red-100');
                icon.classList.add('text-blue-600');
                icon.classList.remove('text-red-600');
                confirmBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                confirmBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
            }

            document.getElementById('confirm-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            confirmBtn.onclick = function() {
                if (currentFormId) {
                    document.getElementById(currentFormId).submit();
                }
            };
        }

        function closeConfirmModal() {
            document.getElementById('confirm-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            currentFormId = null;
        }
    </script>
</body>
</html>
