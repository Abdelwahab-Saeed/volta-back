{{--
    File input for one image, with the current image (edit forms) and a live preview of the picked file.
    Params: $name, $label, $current (stored path or null), $required (bool), $hint (string),
            $previewClass (classes for the preview image, default a wide rectangle), $accept (file types).
--}}
@php($previewClass = $previewClass ?? 'w-full h-40 object-contain')
<div>
    <span class="label {{ ($required ?? false) ? 'required' : '' }}">{{ $label }}</span>
    <div class="flex flex-col sm:flex-row gap-4 items-stretch">
        <div class="shrink-0 rounded-2xl border border-slate-200 bg-slate-50 p-2 flex items-center justify-center {{ ($current ?? null) ? '' : 'hidden' }}" id="{{ $name }}-preview-box">
            <img id="{{ $name }}-preview" src="{{ ($current ?? null) ? asset('storage/' . $current) : '' }}" alt="" class="{{ $previewClass }} rounded-xl">
        </div>
        <label for="{{ $name }}" class="dropzone flex-1">
            <span class="w-11 h-11 rounded-full bg-white shadow-sm text-brand-600 flex items-center justify-center">
                <x-admin.icon name="upload" class="w-5 h-5" />
            </span>
            <span class="text-sm font-bold text-navy-900">{{ ($current ?? null) ? 'اختر صورة جديدة للاستبدال' : 'اضغط لاختيار صورة' }}</span>
            <span id="{{ $name }}-filename" class="text-xs font-semibold text-brand-700 hidden"></span>
            @isset($hint)<span class="text-xs text-slate-500">{{ $hint }}</span>@endisset
            <input id="{{ $name }}" name="{{ $name }}" type="file" class="sr-only" accept="{{ $accept ?? 'image/png,image/jpeg,image/webp,image/gif' }}"
                onchange="(function(input){ const f = input.files && input.files[0]; if (!f) return; document.getElementById('{{ $name }}-preview').src = URL.createObjectURL(f); document.getElementById('{{ $name }}-preview-box').classList.remove('hidden'); const n = document.getElementById('{{ $name }}-filename'); n.textContent = f.name; n.classList.remove('hidden'); })(this)">
        </label>
    </div>
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
