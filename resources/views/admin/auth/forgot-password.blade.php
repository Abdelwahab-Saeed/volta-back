<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استعادة كلمة المرور - فولتا</title>
    <link rel="icon" type="image/png" href="{{ asset('Logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Tajawal', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8">
        <div class="bg-white rounded-2xl shadow-2xl p-10 mt-12">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">فولتا <span class="text-blue-600">استعادة المرور</span></h1>
                <p class="text-slate-500 font-medium">أدخل بريدك الإلكتروني لتلقي رابط الاستعادة</p>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border-r-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm text-sm text-right">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm text-sm text-right">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('admin.password.email') }}" method="POST" class="space-y-6 text-right">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="email" placeholder="admin@volta.com" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-left">
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-blue-200 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                    إرسال رابط الاستعادة
                </button>
            </form>

            <div class="mt-8 text-center">
                <a href="{{ route('admin.login') }}" class="text-blue-600 hover:underline font-bold text-sm">العودة لتسجيل الدخول</a>
            </div>
        </div>
        <p class="text-center text-slate-500 text-xs mt-8 uppercase tracking-widest font-bold">منصة فولتا للتجارة الإلكترونية &copy; 2026</p>
    </div>
</body>
</html>
