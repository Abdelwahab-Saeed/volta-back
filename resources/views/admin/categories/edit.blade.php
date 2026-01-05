@extends('admin.layouts.app')

@section('title', 'تعديل القسم')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-right">
    <div class="flex items-center space-x-reverse space-x-4 mb-8">
        @if($category->image)
            <img src="{{ asset('storage/' . $category->image) }}" class="w-20 h-20 rounded-xl object-cover border border-gray-200">
        @endif
        <div>
            <h3 class="text-xl font-bold font-gray-800">تعديل "{{ $category->name }}"</h3>
            <p class="text-gray-400 text-sm font-medium">قم بتحديث بيانات القسم والصورة المرافقة.</p>
        </div>
    </div>

    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">اسم القسم</label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-bold text-gray-700 mb-2">الوصف</label>
                <textarea name="description" id="description" rows="4" 
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">{{ old('description', $category->description) }}</textarea>
            </div>

            <!-- Image -->
            <div>
                <label for="image" class="block text-sm font-bold text-gray-700 mb-2">تغيير الصورة</label>
                <input type="file" name="image" id="image" accept="image/*"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-400 file:ml-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-2 text-xs text-gray-400 italic">اتركه فارغاً للاحتفاظ بالصورة الحالية.</p>
            </div>

            <!-- Status -->
            <div class="flex items-center">
                <input type="checkbox" name="status" id="status" value="1" {{ $category->status ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                <label for="status" class="mr-2 text-sm font-bold text-gray-700">القسم نشط</label>
            </div>

            <div class="pt-4 flex space-x-reverse space-x-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg font-bold transition-all shadow-md">
                    تحديث البيانات
                </button>
                <a href="{{ route('admin.categories.index') }}" class="px-8 py-2.5 text-gray-500 hover:bg-gray-100 rounded-lg transition-all font-bold">
                    إلغاء
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
