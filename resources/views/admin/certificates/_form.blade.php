<div class="space-y-6">
    @include('admin.partials.translatable-field', ['field' => 'title', 'model' => $certificate])
    <p class="text-xs text-gray-500 -mt-3">مثال: شهادة الأيزو 9001 لنظام إدارة الجودة.</p>

    @include('admin.partials.translatable-field', ['field' => 'issuer', 'model' => $certificate])
    <p class="text-xs text-gray-500 -mt-3">اختياري. الجهة التي منحت الشهادة، مثل: الهيئة المصرية العامة للمواصفات والجودة.</p>

    @include('admin.partials.translatable-field', ['field' => 'description', 'model' => $certificate, 'textarea' => true, 'rows' => 2])

    @include('admin.partials.image-upload', [
        'name' => 'image',
        'label' => 'صورة الشهادة',
        'current' => $certificate->image,
        'required' => ! $certificate->exists,
        'hint' => 'صورة واضحة للشهادة أو شعار الاعتماد، حتى 4MB',
        'previewClass' => 'w-32 h-40 object-cover',
    ])

    <div>
        <label for="issued_year" class="block text-sm font-bold text-gray-700 mb-2">سنة الحصول (اختياري)</label>
        <input type="number" name="issued_year" id="issued_year" min="1950" max="{{ now()->year + 1 }}" placeholder="{{ now()->year }}" value="{{ old('issued_year', $certificate->issued_year) }}"
            class="w-full sm:w-48 px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
        @error('issued_year')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    @include('admin.partials.order-and-status', ['model' => $certificate])
</div>
