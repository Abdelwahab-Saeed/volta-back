{{--
    File input for one image, with the current image (edit forms) and a live preview of the picked file.
    Params: $name, $label, $current (stored path or null), $required (bool), $hint (string),
            $previewClass (classes for the preview box, default a wide rectangle).
--}}
@php($previewClass = $previewClass ?? 'w-full h-40 object-contain')
<div>
    <label for="{{ $name }}" class="block text-sm font-bold text-gray-700 mb-2">{{ $label }} @if($required ?? false)<span class="text-red-500">*</span>@endif</label>
    <div class="flex flex-col sm:flex-row gap-4 items-start">
        <div class="shrink-0 rounded-xl border border-gray-100 bg-gray-50 p-2 {{ ($current ?? null) ? '' : 'hidden' }}" id="{{ $name }}-preview-box">
            <img id="{{ $name }}-preview" src="{{ ($current ?? null) ? asset('storage/' . $current) : '' }}" alt="" class="{{ $previewClass }} rounded-lg">
        </div>
        <label for="{{ $name }}" class="flex-1 w-full cursor-pointer flex flex-col items-center justify-center px-6 py-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-blue-400 transition-colors text-center">
            <svg class="h-10 w-10 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span class="mt-2 text-sm font-bold text-blue-600">{{ ($current ?? null) ? 'اختر صورة جديدة' : 'ارفع صورة' }}</span>
            @isset($hint)<span class="mt-1 text-xs text-gray-500">{{ $hint }}</span>@endisset
            <input id="{{ $name }}" name="{{ $name }}" type="file" class="sr-only" accept="image/png,image/jpeg,image/webp,image/gif"
                onchange="(function(input){ const f = input.files && input.files[0]; if (!f) return; const img = document.getElementById('{{ $name }}-preview'); img.src = URL.createObjectURL(f); document.getElementById('{{ $name }}-preview-box').classList.remove('hidden'); })(this)">
        </label>
    </div>
    @error($name)
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
