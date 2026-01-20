@extends('admin.layouts.app')

@section('title', 'إضافة منتج جديد')

@section('content')
<div class="max-w-4xl bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-right">
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Basic Info -->
            <div class="space-y-6">
                <h3 class="text-lg font-bold text-gray-800 border-b border-gray-50 pb-2">المعلومات الأساسية</h3>
                
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-2">اسم المنتج</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="مثلاً: آيفون 15 برو"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-bold text-gray-700 mb-2">القسم</label>
                    <select name="category_id" id="category_id"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                        <option value="">اختر القسم</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-sm font-bold text-gray-700 mb-2">الوصف</label>
                    <textarea name="description" id="description" rows="5" placeholder="اكتب وصفاً تفصيلياً للمنتج..."
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Pricing & Inventory -->
            <div class="space-y-6">
                <h3 class="text-lg font-bold text-gray-800 border-b border-gray-50 pb-2">الأسعار والمخزون</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-bold text-gray-700 mb-2">السعر (ج.م)</label>
                        <input type="number" name="price" id="price" value="{{ old('price') }}"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label for="discount" class="block text-sm font-bold text-gray-700 mb-2">الخصم (%)</label>
                        <input type="number" name="discount" id="discount" value="{{ old('discount', 0) }}"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                </div>

                <div>
                    <label for="stock" class="block text-sm font-bold text-gray-700 mb-2">كمية المخزون</label>
                    <input type="number" name="stock" id="stock" value="{{ old('stock', 0) }}"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                </div>

                <div>
                    <label for="image" class="block text-sm font-bold text-gray-700 mb-2">صورة المنتج</label>
                    <input type="file" name="image" id="image" accept="image/*"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-400 file:ml-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <!-- Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="status" id="status" value="1" checked
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                    <label for="status" class="mr-2 text-sm font-bold text-gray-700">متاح للبيع الفوري</label>
                </div>
            </div>
        </div>

        <div class="mt-12 flex space-x-reverse space-x-4 border-t border-gray-100 pt-8">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-bold transition-all shadow-md">
                إضافة المنتج
            </button>
            <a href="{{ route('admin.products.index') }}" class="px-8 py-3 text-gray-500 hover:bg-gray-100 rounded-lg transition-all font-bold">
                إلغاء
            </a>
        </div>
    </form>
</div>
@endsection
