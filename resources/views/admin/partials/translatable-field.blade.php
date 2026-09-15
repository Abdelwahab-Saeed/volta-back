{{--
    Arabic + English inputs for one translatable field ({field}_ar / {field}_en).
    Params: $field ('name' | 'title' | 'description'), $model (nullable, for edit forms),
            $textarea (bool, default false), $rows (int, default 4).
--}}
@php($textarea = $textarea ?? false)
<div class="space-y-4">
    @foreach (['ar' => 'rtl', 'en' => 'ltr'] as $locale => $dir)
        @php($input = "{$field}_{$locale}")
        <div>
            <label for="{{ $input }}" class="block text-sm font-bold text-gray-700 mb-2">{{ __("admin.{$input}") }}</label>
            @if ($textarea)
                <textarea name="{{ $input }}" id="{{ $input }}" rows="{{ $rows ?? 4 }}" dir="{{ $dir }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none {{ $dir === 'ltr' ? 'text-left' : '' }}">{{ old($input, ($model ?? null)?->{$input}) }}</textarea>
            @else
                <input type="text" name="{{ $input }}" id="{{ $input }}" value="{{ old($input, ($model ?? null)?->{$input}) }}" dir="{{ $dir }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none {{ $dir === 'ltr' ? 'text-left' : '' }}">
            @endif
            @error($input)
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
</div>
