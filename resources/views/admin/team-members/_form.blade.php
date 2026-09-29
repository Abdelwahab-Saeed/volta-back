<div class="space-y-6">
    @include('admin.partials.translatable-field', ['field' => 'name', 'model' => $member])
    @include('admin.partials.translatable-field', ['field' => 'role', 'model' => $member])
    <p class="text-xs text-gray-500 -mt-3">مثال: مهندس تركيبات، مدير المبيعات.</p>

    @include('admin.partials.translatable-field', ['field' => 'bio', 'model' => $member, 'textarea' => true, 'rows' => 2])
    <p class="text-xs text-gray-500 -mt-3">اختياري. سطر أو سطران عن الخبرة أو التخصص.</p>

    @include('admin.partials.image-upload', [
        'name' => 'photo',
        'label' => 'الصورة الشخصية (اختياري)',
        'current' => $member->photo,
        'required' => false,
        'hint' => 'صورة مربعة يفضل، حتى 2MB. بدون صورة يظهر الحرف الأول من الاسم.',
        'previewClass' => 'w-24 h-24 object-cover rounded-full',
    ])
    @if($member->photo)
        <label class="flex items-center gap-2 -mt-3 cursor-pointer">
            <input type="checkbox" name="remove_photo" value="1" class="w-4 h-4 text-red-600 border-gray-300 rounded">
            <span class="text-sm text-gray-600">حذف الصورة الحالية</span>
        </label>
    @endif

    <div>
        <label for="linkedin_url" class="block text-sm font-bold text-gray-700 mb-2">رابط LinkedIn (اختياري)</label>
        <input type="url" name="linkedin_url" id="linkedin_url" dir="ltr" placeholder="https://www.linkedin.com/in/..." value="{{ old('linkedin_url', $member->linkedin_url) }}"
            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none text-left">
        @error('linkedin_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    @include('admin.partials.order-and-status', ['model' => $member])
</div>
