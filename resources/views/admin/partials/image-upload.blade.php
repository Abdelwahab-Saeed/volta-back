{{--
    File input for one image, with the current image (edit forms) and a live preview of the picked file
    before the form is sent. "Undo" drops the pick and shows the current image again. Script: pickImage() in the layout.
    Params: $name, $label, $current (stored path or null), $required (bool), $hint (string),
            $previewClass (classes for the preview image, default a wide rectangle), $accept (file types),
            $maxKb (size limit checked before sending, default 10240 = the server's image rule),
            $wide (bool: full-width preview above the picker, for wide images like banners),
            $size ([width, height] the image is shown at; a picked image of another shape gets a cropping note).
--}}
@php($previewClass = $previewClass ?? 'w-full h-40 object-contain')
@php($currentUrl = ($current ?? null) ? asset('storage/' . $current) : '')
@php($wide = $wide ?? false)
<div>
    <span class="label {{ ($required ?? false) ? 'required' : '' }}">{{ $label }}</span>
    <div class="flex flex-col {{ $wide ? '' : 'sm:flex-row' }} gap-4 items-stretch">
        <div class="{{ $wide ? 'w-full' : 'shrink-0 items-center' }} flex flex-col gap-1.5 {{ $currentUrl ? '' : 'hidden' }}" id="{{ $name }}-preview-box">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2 flex items-center justify-center">
                <img id="{{ $name }}-preview" src="{{ $currentUrl }}" data-current="{{ $currentUrl }}" alt="" class="{{ $previewClass }} rounded-xl"
                    @isset($size) data-size="{{ implode('x', $size) }}" onload="checkImageShape(this)" @endisset>
            </div>
            <span id="{{ $name }}-preview-tag" class="text-xs font-semibold {{ $currentUrl ? 'text-slate-500' : 'text-brand-700' }}">{{ $currentUrl ? 'الصورة الحالية' : 'معاينة' }}</span>
            @isset($size)
                <p id="{{ $name }}-shape-note" class="hidden flex items-start gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    <x-admin.icon name="warning" class="w-4 h-4 shrink-0" />
                    <span></span>
                </p>
            @endisset
        </div>
        <div class="flex-1 flex flex-col gap-2">
            <label for="{{ $name }}" class="dropzone flex-1">
                <span class="w-11 h-11 rounded-full bg-white shadow-sm text-brand-600 flex items-center justify-center">
                    <x-admin.icon name="upload" class="w-5 h-5" />
                </span>
                <span class="text-sm font-bold text-navy-900">{{ $currentUrl ? 'اختر صورة جديدة للاستبدال' : 'اضغط لاختيار صورة' }}</span>
                <span id="{{ $name }}-filename" class="text-xs font-semibold text-brand-700 hidden" dir="ltr"></span>
                @isset($hint)<span class="text-xs text-slate-500">{{ $hint }}</span>@endisset
                <input id="{{ $name }}" name="{{ $name }}" type="file" class="sr-only" accept="{{ $accept ?? 'image/png,image/jpeg,image/webp,image/gif' }}"
                    data-max-kb="{{ $maxKb ?? 10240 }}" onchange="pickImage(this)">
            </label>
            <p id="{{ $name }}-pick-error" class="field-error hidden mt-0" role="alert"></p>
            <button type="button" id="{{ $name }}-undo" onclick="pickImage(document.getElementById('{{ $name }}'), true)" class="btn-ghost btn-sm self-start hidden">
                <x-admin.icon name="x" class="w-4 h-4" />
                {{ $currentUrl ? 'تراجع والإبقاء على الصورة الحالية' : 'إلغاء اختيار الصورة' }}
            </button>
        </div>
    </div>
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
