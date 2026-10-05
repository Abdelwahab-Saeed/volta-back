{{-- Shared by create and edit. $banner is null on create. --}}
@php($banner = $banner ?? null)
<div class="space-y-6">
    @include('admin.partials.image-upload', [
        'name' => 'image',
        'label' => 'صورة البانر',
        'current' => $banner?->image,
        'required' => ! $banner,
        'accept' => 'image/*',
        'hint' => 'PNG أو JPG حتى 10MB. المقاس المناسب عرضي (مثلاً 1600×600).',
        'previewClass' => 'w-full sm:w-80 h-32 object-cover',
    ])

    @include('admin.partials.translatable-field', ['field' => 'title', 'model' => $banner])
    <p class="hint -mt-3">العنوان يُستخدم كنص بديل للصورة (لقارئات الشاشة ومحركات البحث).</p>

    @include('admin.partials.translatable-field', ['field' => 'description', 'model' => $banner, 'textarea' => true, 'rows' => 3])

    <div>
        <span class="label">الظهور</span>
        <label class="switch">
            <input type="checkbox" name="status" id="status" value="1" class="peer sr-only" {{ $banner ? (old('status', $banner->status) ? 'checked' : '') : 'checked' }}>
            <span class="switch-track"></span>
            <span class="text-sm font-bold text-slate-700">تفعيل البانر في الصفحة الرئيسية</span>
        </label>
    </div>
</div>
