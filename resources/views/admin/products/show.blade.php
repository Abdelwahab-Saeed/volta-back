@extends('admin.layouts.app')

@section('title', 'تفاصيل المنتج: ' . $product->name)

@section('content')
<div class="max-w-5xl mx-auto space-y-6 text-right">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-2xl bg-gray-50 border border-gray-100 overflow-hidden flex-shrink-0">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 line-clamp-1">{{ $product->name }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm font-bold text-gray-500">{{ $product->category->name ?? 'بدون قسم' }}</span>
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $product->status ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $product->status ? 'نشط' : 'متوقف' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <a href="{{ route('admin.products.edit', $product) }}" class="flex-1 md:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all shadow-sm hover:shadow-md">
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    تعديل
                </a>
                <a href="{{ route('admin.products.index') }}" class="flex-1 md:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-xl font-bold transition-all border border-gray-100">
                    عودة للقائمة
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Details Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                    <h3 class="font-black text-gray-800 flex items-center">
                        <svg class="w-5 h-5 ml-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        معلومات المنتج
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">الوصف</h4>
                        <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    </div>

                    @if($product->preview_url)
                    <div class="pt-6 border-t border-gray-50">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">رابط الفيديو (Preview)</h4>
                        <a href="{{ $product->preview_url }}" target="_blank" class="inline-flex items-center px-4 py-3 bg-rose-50 text-rose-700 rounded-2xl hover:bg-rose-100 transition-colors w-full group">
                            <div class="w-10 h-10 rounded-xl bg-rose-100 group-hover:bg-rose-200 flex items-center justify-center ml-3 transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"></path></svg>
                            </div>
                            <div class="flex-1 text-right">
                                <div class="font-bold text-sm">مشاهدة الفيديو التعريفي</div>
                                <div class="text-xs opacity-75 truncate" dir="ltr">{{ $product->preview_url }}</div>
                            </div>
                            <svg class="w-5 h-5 opacity-50 group-hover:translate-x-[-4px] transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Features Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                    <h3 class="font-black text-gray-800 flex items-center">
                        <svg class="w-5 h-5 ml-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        المميزات الإضافية
                    </h3>
                </div>
                <div class="p-6">
                    @forelse($product->features as $feature)
                        <div class="flex items-center py-3 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                            <div class="w-2 h-2 rounded-full bg-blue-500 ml-3"></div>
                            <span class="text-gray-700 font-medium">{{ $feature->name }}</span>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-400 text-sm">لا توجد مميزات مضافة لهذا المنتج</div>
                    @endforelse
                    <div class="mt-4">
                        <a href="{{ route('admin.products.features.index', $product->id) }}" class="text-blue-600 text-sm font-bold hover:underline">إدارة المميزات →</a>
                    </div>
                </div>
            </div>

            <!-- Images Gallery -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                    <h3 class="font-black text-gray-800 flex items-center">
                        <svg class="w-5 h-5 ml-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        معرض الصور
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @forelse($product->extraImages as $image)
                            <div class="aspect-square rounded-xl overflow-hidden border border-gray-100 bg-gray-50">
                                <img src="{{ asset('storage/' . $image->image) }}" class="w-full h-full object-cover">
                            </div>
                        @empty
                            <div class="col-span-full text-center py-6 text-gray-400 text-sm">لا توجد صور إضافية</div>
                        @endforelse
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('admin.products.images.index', $product->id) }}" class="text-blue-600 text-sm font-bold hover:underline">إدارة معرض الصور →</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Pricing Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400 text-sm font-bold">السعر النهائي</span>
                        <div class="text-3xl font-black text-blue-600">{{ number_format($product->final_price, 2) }} <span class="text-sm">ج.م</span></div>
                    </div>
                    
                    <div class="space-y-2 pt-4 border-t border-gray-50">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">السعر الأصلي</span>
                            <span class="font-bold text-gray-800">{{ number_format($product->price, 2) }} ج.م</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">سعر التكلفة</span>
                            <span class="font-bold text-gray-800">{{ number_format($product->cost_price, 2) }} ج.م</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">الربح المتوقع</span>
                            <span class="font-bold text-emerald-600">{{ number_format($product->final_price - $product->cost_price, 2) }} ج.م</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-black text-gray-800">المخزون</h4>
                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold">{{ $product->stock }} متوفر</span>
                    </div>
                    
                    <div class="w-full bg-gray-100 rounded-full h-2 mb-2">
                        @php
                            $stockPercentage = min(100, ($product->stock / 50) * 100);
                        @endphp
                        <div class="h-2 rounded-full {{ $product->stock <= 5 ? 'bg-red-500' : 'bg-blue-500' }}" style="width: {{ $stockPercentage }}%"></div>
                    </div>
                    <p class="text-[10px] text-gray-400">يعتمد المؤشر على سعة افتراضية 50 قطعة</p>
                </div>
            </div>

            <!-- Stats Card (Placeholders or calculated) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 space-y-4">
                    <h4 class="font-black text-gray-800 mb-2">إحصائيات سريعة</h4>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 font-bold">إجمالي المبيعات</div>
                            <div class="text-lg font-black text-gray-900">{{ $product->orderItems()->sum('quantity') }} قطعة</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
