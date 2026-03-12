@extends('admin.layouts.app')

@section('title', 'إدارة صور المنتج: ' . $product->name)

@section('content')
<div class="max-w-6xl mx-auto space-y-8 text-right">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-reverse space-x-4">
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" class="w-16 h-16 rounded-xl object-cover border border-gray-100">
            @endif
            <div>
                <h3 class="text-xl font-bold text-gray-800">{{ $product->name }}</h3>
                <p class="text-gray-500 text-sm">أضف صوراً إضافية للمنتج لتظهر في معرض الصور.</p>
            </div>
        </div>
        <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-all font-bold text-sm">
            العودة للمنتجات
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h4 class="text-lg font-bold text-gray-800 mb-6">إضافة صور جديدة</h4>
        <form action="{{ route('admin.products.images.store', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="border-2 border-dashed border-gray-200 rounded-2xl p-12 text-center hover:border-blue-400 transition-colors bg-gray-50/50">
                <input type="file" name="images[]" id="images" multiple accept="image/*" class="hidden" onchange="updateFileName(this)">
                <label for="images" class="cursor-pointer">
                    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <p class="text-gray-700 font-bold mb-1">اضغط لاختيار الصور أو اسحبها هنا</p>
                    <p class="text-gray-400 text-xs">يمكنك اختيار أكثر من صورة (PNG, JPG حتى 2MB للملف)</p>
                    <div id="file-list" class="mt-4 text-blue-600 text-sm font-bold"></div>
                </label>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold transition-all shadow-md">
                    رفع الصور المختارة
                </button>
            </div>
        </form>
    </div>

    <!-- Current Images Grid -->
    <div class="space-y-6">
        <h4 class="text-lg font-bold text-gray-800 flex items-center">
            <svg class="w-5 h-5 ml-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            معرض الصور الحالي ({{ $product->extraImages->count() }})
        </h4>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
            @forelse($product->extraImages as $image)
                <div class="group relative aspect-square bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <img src="{{ asset('storage/' . $image->image) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <form id="delete-image-{{ $image->id }}" action="{{ route('admin.images.destroy', $image->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmAction('delete-image-{{ $image->id }}', 'هل أنت متأكد من حذف هذه الصورة؟')" 
                                class="bg-white/20 hover:bg-white/40 text-white p-3 rounded-xl backdrop-blur-md transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl border border-dashed border-gray-200 p-12 text-center text-gray-400">
                    <p class="font-medium">لا توجد صور إضافية بعد.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function updateFileName(input) {
    const list = document.getElementById('file-list');
    list.innerHTML = '';
    if (input.files.length > 0) {
        list.innerHTML = `تم اختيار ${input.files.length} ملفات`;
    }
}
</script>
@endsection
