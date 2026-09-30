@extends('admin.layouts.app')

@section('title', 'إدارة صور المنتج: ' . $product->name)
@section('heading', 'معرض الصور')
@section('subtitle', $product->name . ' — صور إضافية تظهر في صفحة المنتج.')
@section('back', route('admin.products.index'))
@section('back_label', 'المنتجات')

@section('actions')
    <a href="{{ route('admin.products.edit', $product) }}" class="btn-secondary"><x-admin.icon name="edit" class="w-4 h-4" /> تعديل المنتج</a>
@endsection

@section('content')
<div class="space-y-6">
    <section class="card">
        <form action="{{ route('admin.products.images.store', $product->id) }}" method="POST" enctype="multipart/form-data" class="card-body space-y-4">
            @csrf
            <label for="images" class="dropzone py-10">
                <span class="w-12 h-12 rounded-full bg-white shadow-sm text-brand-600 flex items-center justify-center">
                    <x-admin.icon name="upload" class="w-6 h-6" />
                </span>
                <span class="text-sm font-bold text-navy-900">اضغط لاختيار الصور</span>
                <span class="text-xs text-slate-500">يمكنك اختيار أكثر من صورة (PNG, JPG حتى 2MB للملف)</span>
                <input type="file" name="images[]" id="images" multiple accept="image/*" class="sr-only" onchange="updateFileName(this)">
                <span id="file-list" class="text-sm font-bold text-brand-700"></span>
            </label>
            <div id="upload-previews" class="grid grid-cols-4 sm:grid-cols-8 gap-2"></div>
            <div class="flex justify-end">
                <button type="submit" class="btn-primary">
                    <x-admin.icon name="upload" class="w-4 h-4" />
                    رفع الصور المختارة
                </button>
            </div>
        </form>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">الصور الحالية</h2>
            <span class="badge-neutral">{{ $product->extraImages->count() }} صورة</span>
        </div>
        @if($product->extraImages->isEmpty())
            <x-admin.empty-state icon="photo" title="لا توجد صور إضافية بعد" text="ارفع صوراً من الأعلى لتظهر في معرض المنتج." />
        @else
            <div class="card-body grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($product->extraImages as $image)
                    <div class="group relative aspect-square rounded-2xl overflow-hidden border border-slate-200 bg-slate-50">
                        <img src="{{ asset('storage/' . $image->image) }}" alt="" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        <form id="delete-image-{{ $image->id }}" action="{{ route('admin.images.destroy', $image->id) }}" method="POST" class="absolute top-2 left-2">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmAction('delete-image-{{ $image->id }}', 'هل أنت متأكد من حذف هذه الصورة؟')"
                                class="w-9 h-9 rounded-lg bg-white/95 text-red-600 shadow flex items-center justify-center opacity-100 sm:opacity-0 group-hover:opacity-100 focus:opacity-100 transition hover:bg-red-600 hover:text-white" aria-label="حذف الصورة">
                                <x-admin.icon name="trash" class="w-4 h-4" />
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>

<script>
function updateFileName(input) {
    const list = document.getElementById('file-list');
    const previews = document.getElementById('upload-previews');
    list.innerHTML = '';
    previews.innerHTML = '';
    if (input.files.length > 0) {
        list.innerHTML = `تم اختيار ${input.files.length} ملفات`;
        [...input.files].forEach(file => {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = 'aspect-square w-full rounded-lg object-cover border border-slate-200';
            previews.appendChild(img);
        });
    }
}
</script>
@endsection
