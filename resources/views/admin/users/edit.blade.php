@extends('admin.layouts.app')

@section('title', 'تعديل بيانات المستخدم')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-right">
    <div class="flex items-center space-x-reverse space-x-6 mb-8 pb-8 border-b border-gray-100">
        @if($user->image)
            <img src="{{ asset('storage/' . $user->image) }}" class="w-20 h-20 rounded-full object-cover border border-gray-200 shadow-sm">
        @else
            <div class="w-20 h-20 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-black text-3xl">
                {{ substr($user->name, 0, 1) }}
            </div>
        @endif
        <div>
            <h3 class="text-2xl font-bold text-gray-800">تعديل "{{ $user->name }}"</h3>
            <p class="text-gray-400 font-medium">عضو منذ {{ $user->created_at?->format('Y/m') ?? 'غير متوفر' }}</p>
        </div>
    </div>

    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-2">الاسم الكامل</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                </div>
                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="role" class="block text-sm font-bold text-gray-700 mb-2">الصلاحية</label>
                    <select name="role" id="role"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                        <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>مستخدم</option>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>مسؤول</option>
                    </select>
                </div>
                <div>
                    <label for="phone_number" class="block text-sm font-bold text-gray-700 mb-2">رقم الهاتف</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $user->phone_number) }}"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6">
                <p class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    تغيير كلمة المرور (اختياري)
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <input type="password" name="password" placeholder="كلمة المرور الجديدة"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <input type="password" name="password_confirmation" placeholder="تأكيد كلمة المرور"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                </div>
            </div>

            <div>
                <label for="image" class="block text-sm font-bold text-gray-700 mb-2">تغيير صورة الحساب</label>
                <input type="file" name="image" id="image" accept="image/*"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-400 file:ml-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="pt-6 flex space-x-reverse space-x-4 border-t border-gray-100">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-2.5 rounded-lg font-bold transition-all shadow-md">
                    تحديث البيانات
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-10 py-2.5 text-gray-500 hover:bg-gray-100 rounded-lg transition-all font-bold">
                    إلغاء
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
