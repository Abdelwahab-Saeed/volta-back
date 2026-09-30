{{-- Shared by create and edit. $category is null on create. --}}
@php($category = $category ?? null)
<div class="space-y-6">
    @include('admin.partials.translatable-field', ['field' => 'name', 'model' => $category])

    @include('admin.partials.translatable-field', ['field' => 'description', 'model' => $category, 'textarea' => true])

    @include('admin.partials.image-upload', [
        'name' => 'image',
        'label' => 'صورة القسم',
        'current' => $category?->image,
        'accept' => 'image/*',
        'hint' => 'صورة مربعة تظهر في شريط الأقسام بالصفحة الرئيسية',
        'previewClass' => 'w-28 h-28 object-cover',
    ])

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-start">
        <div>
            <label for="category_order" class="label">الترتيب (اختياري)</label>
            <input type="number" name="category_order" id="category_order" value="{{ old('category_order', $category?->category_order) }}" placeholder="مثال: 1" class="input sm:w-40 @error('category_order') input-error @enderror">
            <p class="hint">يحدد مكان القسم في المتجر.</p>
        </div>
        <div>
            <span class="label">الحالة</span>
            <label class="switch h-11">
                <input type="checkbox" name="status" id="status" value="1" class="peer sr-only" {{ $category ? ($category->status ? 'checked' : '') : 'checked' }}>
                <span class="switch-track"></span>
                <span class="text-sm font-bold text-slate-700">تفعيل القسم</span>
            </label>
        </div>
    </div>
</div>
