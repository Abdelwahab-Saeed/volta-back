<div class="space-y-6">
    @include('admin.partials.translatable-field', ['field' => 'name', 'model' => $partner])
    <p class="text-xs text-gray-500 -mt-3">يكفي إدخال الاسم بلغة واحدة. الاسم يُستخدم كنص بديل للشعار وعند المرور عليه.</p>

    <div>
        <span class="block text-sm font-bold text-gray-700 mb-2">النوع</span>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach (['partner' => ['شريك', 'موردين وعلامات تجارية نتعامل معها'], 'client' => ['عميل', 'شركات وجهات نفذنا لها أعمالاً']] as $value => [$label, $help])
                <label class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50">
                    <input type="radio" name="type" value="{{ $value }}" class="mt-1 text-blue-600" {{ old('type', $partner->type) === $value ? 'checked' : '' }}>
                    <span><span class="block font-bold text-gray-900">{{ $label }}</span><span class="block text-xs text-gray-500">{{ $help }}</span></span>
                </label>
            @endforeach
        </div>
        @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    @include('admin.partials.image-upload', [
        'name' => 'logo',
        'label' => 'الشعار',
        'current' => $partner->logo,
        'required' => ! $partner->exists,
        'hint' => 'PNG بخلفية شفافة يفضل، حتى 2MB',
        'previewClass' => 'w-40 h-20 object-contain',
    ])

    <div>
        <label for="website_url" class="block text-sm font-bold text-gray-700 mb-2">رابط الموقع (اختياري)</label>
        <input type="url" name="website_url" id="website_url" dir="ltr" placeholder="https://" value="{{ old('website_url', $partner->website_url) }}"
            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none text-left">
        @error('website_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    @include('admin.partials.order-and-status', ['model' => $partner])
</div>
