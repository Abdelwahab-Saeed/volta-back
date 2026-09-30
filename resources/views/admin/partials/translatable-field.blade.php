{{--
    Arabic + English inputs for one translatable field ({field}_ar / {field}_en), side by side on wide screens.
    Params: $field ('name' | 'title' | 'description' ...), $model (nullable, for edit forms),
            $textarea (bool, default false), $rows (int, default 4), $stacked (bool: one above the other, for narrow columns).
--}}
@php($textarea = $textarea ?? false)
<div class="grid gap-4 {{ ($stacked ?? false) ? '' : ($textarea ? 'lg:grid-cols-2' : 'sm:grid-cols-2') }}">
    @foreach (['ar' => ['rtl', 'ع'], 'en' => ['ltr', 'EN']] as $locale => [$dir, $tag])
        @php($input = "{$field}_{$locale}")
        <div>
            <label for="{{ $input }}" class="label flex items-center gap-2">
                <span class="inline-flex items-center justify-center min-w-[1.75rem] h-5 px-1 rounded-md bg-slate-100 text-[10px] font-extrabold text-slate-500">{{ $tag }}</span>
                {{ __("admin.{$input}") }}
            </label>
            @if ($textarea)
                <textarea name="{{ $input }}" id="{{ $input }}" rows="{{ $rows ?? 4 }}" dir="{{ $dir }}"
                    class="input {{ $dir === 'ltr' ? 'text-left' : '' }} @error($input) input-error @enderror">{{ old($input, ($model ?? null)?->{$input}) }}</textarea>
            @else
                <input type="text" name="{{ $input }}" id="{{ $input }}" value="{{ old($input, ($model ?? null)?->{$input}) }}" dir="{{ $dir }}"
                    class="input {{ $dir === 'ltr' ? 'text-left' : '' }} @error($input) input-error @enderror">
            @endif
            @error($input)
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
</div>
