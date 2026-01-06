@extends('admin.layouts.app')

@section('title', 'تعديل البانر')

@section('content')
<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-right">
    <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-bold text-gray-700 mb-2">عنوان البانر</label>
                <input type="text" name="title" id="title" value="{{ old('title', $banner->title) }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none font-bold">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-bold text-gray-700 mb-2">الوصف</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">{{ old('description', $banner->description) }}</textarea>
            </div>

            <!-- Current Image -->
            @if($banner->image)
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">الصورة الحالية</label>
                <img src="{{ asset('storage/' . $banner->image) }}" class="w-full h-48 object-cover rounded-xl border border-gray-100 shadow-sm mb-2">
            </div>
            @endif

            <!-- New Image -->
            <div>
                <label for="image" class="block text-sm font-bold text-gray-700 mb-2">تحديث الصورة (اختياري)</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-blue-400 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="image" class="relative cursor-pointer bg-white rounded-md font-bold text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                <span>ارفع صورة جديدة</span>
                                <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                            </label>
                            <p class="pr-1">أو اسحب وأفلت</p>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, GIF حتى 2MB</p>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="flex items-center">
                <input type="checkbox" name="status" id="status" value="1" {{ old('status', $banner->status) ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                <label for="status" class="mr-2 text-sm font-bold text-gray-700">تفعيل البانر</label>
            </div>

            <div class="pt-4 flex space-x-reverse space-x-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-2.5 rounded-xl font-bold transition-all shadow-md">
                    تحديث البانر
                </button>
                <a href="{{ route('admin.banners.index') }}" class="px-10 py-2.5 text-gray-500 hover:bg-gray-100 rounded-xl transition-all font-bold">
                    إلغاء
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
