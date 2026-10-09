{{-- Shared by create and edit. $banner is null on create. --}}
@php($banner = $banner ?? null)
<div class="space-y-6">
    {{-- The store (volta-app HomeCarousel) shows the banner at exactly these shapes: image on tablets and
         computers, image_mobile on phones. Keep these sizes in step with it. --}}
    <div class="flex gap-3 rounded-2xl border border-brand-200 bg-brand-50 p-4 text-sm text-navy-900">
        <x-admin.icon name="info" class="w-5 h-5 shrink-0 text-brand-600" />
        <div class="space-y-2 leading-relaxed">
            <p class="font-bold">يظهر البانر في المتجر بصورتين حتى يظهر كاملاً على كل الشاشات:</p>
            <ul class="list-disc pr-5 space-y-1">
                <li><strong>صورة الكمبيوتر: <span dir="ltr">1920×600</span> بكسل.</strong> تظهر على الكمبيوتر والتابلت.</li>
                <li><strong>صورة الموبايل: <span dir="ltr">1080×630</span> بكسل.</strong> تظهر على الموبايل. إذا لم تُرفع، يعرض الموبايل صورة الكمبيوتر بعد قص جانبيها، وقد يختفي جزء من الكلام.</li>
            </ul>
            <p>صمّم الصورتين بهذين المقاسين بالضبط لتظهرا كاملتين. اترك حوالي <span dir="ltr">80</span> بكسل في أسفل كل صورة بدون كلام مهم، لأن نقاط التنقل بين البانرات (وشريط المميزات على الكمبيوتر) تغطيها.</p>
        </div>
    </div>

    @include('admin.partials.image-upload', [
        'name' => 'image',
        'label' => 'صورة الكمبيوتر (1920×600)',
        'current' => $banner?->image,
        'required' => ! $banner,
        'accept' => 'image/*',
        'hint' => 'PNG أو JPG حتى 10MB، بمقاس 1920×600 بكسل.',
        'wide' => true,
        'size' => [1920, 600],
        'previewClass' => 'w-full aspect-[1920/600] object-cover',
    ])

    <div>
        @include('admin.partials.image-upload', [
            'name' => 'image_mobile',
            'label' => 'صورة الموبايل (1080×630) — يُنصح بها',
            'current' => $banner?->image_mobile,
            'accept' => 'image/*',
            'hint' => 'PNG أو JPG حتى 10MB، بمقاس 1080×630 بكسل.',
            'wide' => true,
            'size' => [1080, 630],
            'previewClass' => 'w-full max-w-xs aspect-[1080/630] object-cover',
        ])
        @if($banner?->image_mobile)
            <label class="flex items-center gap-2 mt-3 cursor-pointer w-fit">
                <input type="checkbox" name="remove_image_mobile" value="1" class="checkbox text-red-600 focus:ring-red-500">
                <span class="text-sm text-slate-600">حذف صورة الموبايل (يعرض الموبايل صورة الكمبيوتر بدلاً منها)</span>
            </label>
        @endif
    </div>

    @include('admin.partials.translatable-field', ['field' => 'title', 'model' => $banner])
    <p class="hint -mt-3">العنوان يُستخدم كنص بديل للصورة (لقارئات الشاشة ومحركات البحث).</p>

    @include('admin.partials.translatable-field', ['field' => 'description', 'model' => $banner, 'textarea' => true, 'rows' => 3])

    <div>
        <label for="redirect_url" class="label">رابط التوجيه (اختياري)</label>
        <input type="text" name="redirect_url" id="redirect_url" dir="ltr" placeholder="https://… أو /offers/3" value="{{ old('redirect_url', $banner?->redirect_url) }}"
            class="input text-left">
        <p class="hint">الصفحة التي يفتحها العميل عند الضغط على البانر: رابط كامل يبدأ بـ https:// أو مسار داخل المتجر يبدأ بـ /. اتركه فارغاً ليكون البانر صورة فقط.</p>
        @error('redirect_url')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    <div>
        <span class="label">الظهور</span>
        <label class="switch">
            <input type="checkbox" name="status" id="status" value="1" class="peer sr-only" {{ $banner ? (old('status', $banner->status) ? 'checked' : '') : 'checked' }}>
            <span class="switch-track"></span>
            <span class="text-sm font-bold text-slate-700">تفعيل البانر في الصفحة الرئيسية</span>
        </label>
    </div>
</div>
