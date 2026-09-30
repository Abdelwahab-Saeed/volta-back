{{-- Display order + "shown on the site" toggle shared by the home page content forms. Params: $model, $activeLabel --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-start">
    <div>
        <label for="sort_order" class="label">الترتيب</label>
        <input type="number" name="sort_order" id="sort_order" min="0" value="{{ old('sort_order', $model->sort_order ?? 0) }}" class="input w-full sm:w-40 @error('sort_order') input-error @enderror">
        <p class="hint">الرقم الأصغر يظهر أولاً.</p>
        @error('sort_order')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <span class="label">الظهور</span>
        <label class="switch h-11">
            <input type="checkbox" name="is_active" value="1" class="peer sr-only" {{ old('is_active', $model->is_active ?? true) ? 'checked' : '' }}>
            <span class="switch-track"></span>
            <span class="text-sm font-bold text-slate-700">{{ $activeLabel ?? 'يظهر في الموقع' }}</span>
        </label>
    </div>
</div>
