<div class="space-y-6">
    @include('admin.partials.translatable-field', ['field' => 'name', 'model' => $member])
    @include('admin.partials.translatable-field', ['field' => 'role', 'model' => $member])
    <p class="hint -mt-4">مثال: مهندس تركيبات، مدير المبيعات.</p>

    @include('admin.partials.translatable-field', ['field' => 'bio', 'model' => $member, 'textarea' => true, 'rows' => 2])
    <p class="hint -mt-4">اختياري. سطر أو سطران عن الخبرة أو التخصص.</p>

    @include('admin.partials.image-upload', [
        'name' => 'photo',
        'label' => 'الصورة الشخصية (اختياري)',
        'current' => $member->photo,
        'required' => false,
        'hint' => 'صورة مربعة يفضل، حتى 2MB. بدون صورة يظهر الحرف الأول من الاسم.',
        'previewClass' => 'w-24 h-24 object-cover rounded-full',
    ])
    @if($member->photo)
        <label class="flex items-center gap-2 -mt-3 cursor-pointer w-fit">
            <input type="checkbox" name="remove_photo" value="1" class="checkbox text-red-600 focus:ring-red-500">
            <span class="text-sm text-slate-600">حذف الصورة الحالية</span>
        </label>
    @endif

    <div>
        <label for="linkedin_url" class="label">رابط LinkedIn (اختياري)</label>
        <input type="url" name="linkedin_url" id="linkedin_url" dir="ltr" placeholder="https://www.linkedin.com/in/..." value="{{ old('linkedin_url', $member->linkedin_url) }}"
            class="input text-left">
        @error('linkedin_url')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    @include('admin.partials.order-and-status', ['model' => $member])
</div>
