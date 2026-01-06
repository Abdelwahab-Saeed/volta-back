@extends('admin.layouts.app')

@section('title', 'إضافة مستخدم جديد')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-right">
    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-2">الاسم الكامل</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="أحمد محمد"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                </div>
                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="example@mail.com"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="role" class="block text-sm font-bold text-gray-700 mb-2">الصلاحية</label>
                    <select name="role" id="role"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>مستخدم</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>مسؤول</option>
                    </select>
                </div>
                <div>
                    <label for="phone_number" class="block text-sm font-bold text-gray-700 mb-2">رقم الهاتف</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" placeholder="0123456789"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">كلمة المرور</label>
                    <input type="password" name="password" id="password"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                </div>
            </div>

            <div>
                <label for="image" class="block text-sm font-bold text-gray-700 mb-2">صورة الحساب</label>
                <input type="file" name="image" id="image" accept="image/*"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-400 file:ml-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="pt-6 flex space-x-reverse space-x-4 border-t border-gray-100">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg font-bold transition-all shadow-md">
                    حفظ البيانات
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-8 py-2.5 text-gray-500 hover:bg-gray-100 rounded-lg transition-all font-bold">
                    إلغاء
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
