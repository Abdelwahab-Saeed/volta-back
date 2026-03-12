@extends('admin.layouts.app')

@section('title', 'إدارة مميزات المنتج: ' . $product->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-8 text-right">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-reverse space-x-4">
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" class="w-16 h-16 rounded-xl object-cover border border-gray-100">
            @endif
            <div>
                <h3 class="text-xl font-bold text-gray-800">{{ $product->name }}</h3>
                <p class="text-gray-500 text-sm">إضافة وتعديل مميزات المنتج التي تظهر للمستخدم.</p>
            </div>
        </div>
        <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-all font-bold text-sm">
            العودة للمنتجات
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Add Feature Form -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-8">
                <h4 class="text-lg font-bold text-gray-800 mb-6">إضافة ميزة جديدة</h4>
                <form action="{{ route('admin.products.features.store', $product->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-bold text-gray-700 mb-2">اسم الميزة</label>
                        <input type="text" name="name" id="name" required placeholder="مثلاً: يدعم الشحن السريع"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-md">
                        إضافة الميزة
                    </button>
                </form>
            </div>
        </div>

        <!-- Features List -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                    <h4 class="text-lg font-bold text-gray-800">المميزات الحالية</h4>
                    <span class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full">{{ $product->features->count() }} مميزات</span>
                </div>
                
                <div class="divide-y divide-gray-100">
                    @forelse($product->features as $feature)
                        <div class="p-6 flex items-center justify-between hover:bg-gray-50/50 transition-colors">
                            <form action="{{ route('admin.features.update', $feature->id) }}" method="POST" class="flex-grow flex items-center ml-4">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $feature->name }}" 
                                    class="flex-grow bg-transparent border focus:ring-0 focus:outline-none text-gray-700 font-medium py-1">
                                <button type="submit" class="text-blue-500 hover:text-blue-700 text-xs font-bold mr-2">حفظ</button>
                            </form>
                            
                            <form id="delete-feature-{{ $feature->id }}" action="{{ route('admin.features.destroy', $feature->id) }}" method="POST" class="flex-shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmAction('delete-feature-{{ $feature->id }}', 'هل أنت متأكد من حذف هذه الميزة؟')" 
                                    class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-12 text-center text-gray-500">
                            <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="font-bold">لا توجد مميزات مضافة لهذا المنتج بعد.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
