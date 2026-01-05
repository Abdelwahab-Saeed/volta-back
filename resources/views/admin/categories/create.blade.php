@extends('admin.layouts.app')

@section('title', 'إضافة قسم جديد')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-right">
    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="space-y-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">اسم القسم</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="مثلاً: هواتف ذكية"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-bold text-gray-700 mb-2">وصف القسم</label>
                <textarea name="description" id="description" rows="4" placeholder="اكتب وصفاً مختصراً للقسم..."
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">{{ old('description') }}</textarea>
            </div>

            <!-- Image -->
            <div>
                <label for="image" class="block text-sm font-bold text-gray-700 mb-2">صورة القسم</label>
                <input type="file" name="image" id="image" accept="image/*"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-400 file:ml-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <!-- Status -->
            <div class="flex items-center">
                <input type="checkbox" name="status" id="status" value="1" checked
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                <label for="status" class="mr-2 text-sm font-bold text-gray-700">تفعيل القسم</label>
            </div>

            <div class="pt-4 flex space-x-reverse space-x-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg font-bold transition-all shadow-md">
                    حفظ القسم
                </button>
                <a href="{{ route('admin.categories.index') }}" class="px-8 py-2.5 text-gray-500 hover:bg-gray-100 rounded-lg transition-all font-bold">
                    إلغاء
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
