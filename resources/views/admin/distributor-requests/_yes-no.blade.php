{{-- Yes/No choice as two pill buttons. Params: $name, $label, $value (bool|null) --}}
@php($current = old($name, is_null($value) ? null : ($value ? '1' : '0')))
<fieldset>
    <legend class="label">{{ $label }}</legend>
    <div class="flex gap-2">
        @foreach(['1' => 'نعم', '0' => 'لا'] as $optionValue => $optionLabel)
            <label class="cursor-pointer">
                <input type="radio" name="{{ $name }}" value="{{ $optionValue }}" class="peer sr-only" @checked((string) $current === (string) $optionValue)>
                <span class="inline-flex h-10 min-w-[5.5rem] items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-600 transition
                             hover:border-slate-300 peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-800
                             peer-focus-visible:ring-4 peer-focus-visible:ring-brand-500/15">
                    {{ $optionLabel }}
                </span>
            </label>
        @endforeach
    </div>
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
</fieldset>
