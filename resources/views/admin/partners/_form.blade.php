<div class="space-y-6">
    @include('admin.partials.translatable-field', ['field' => 'name', 'model' => $partner])
    <p class="hint -mt-4">يكفي إدخال الاسم بلغة واحدة. الاسم يُستخدم كنص بديل للشعار وعند المرور عليه.</p>

    <div>
        <span class="label">النوع</span>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach (['partner' => ['شريك', 'موردين وعلامات تجارية نتعامل معها'], 'client' => ['عميل', 'شركات وجهات نفذنا لها أعمالاً']] as $value => [$label, $help])
                <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-slate-200 cursor-pointer transition hover:border-slate-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50">
                    <input type="radio" name="type" value="{{ $value }}" class="mt-1 text-brand-600 focus:ring-brand-500" {{ old('type', $partner->type) === $value ? 'checked' : '' }}>
                    <span><span class="block font-bold text-navy-900">{{ $label }}</span><span class="block text-xs text-slate-500">{{ $help }}</span></span>
                </label>
            @endforeach
        </div>
        @error('type')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    @include('admin.partials.image-upload', [
        'name' => 'logo',
        'label' => 'الشعار',
        'current' => $partner->logo,
        'required' => ! $partner->exists,
        'hint' => 'PNG بخلفية شفافة يفضل، حتى 10MB',
        'previewClass' => 'w-40 h-20 object-contain',
    ])

    <div>
        <label for="website_url" class="label">رابط الموقع (اختياري)</label>
        <input type="url" name="website_url" id="website_url" dir="ltr" placeholder="https://" value="{{ old('website_url', $partner->website_url) }}"
            class="input text-left">
        @error('website_url')<p class="field-error">{{ $message }}</p>@enderror
    </div>

    @include('admin.partials.order-and-status', ['model' => $partner])
</div>
