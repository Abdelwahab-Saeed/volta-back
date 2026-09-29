{{-- Display order + "shown on the site" toggle shared by the home page content forms. Params: $model, $activeLabel --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-end">
    <div>
        <label for="sort_order" class="block text-sm font-bold text-gray-700 mb-2">الترتيب</label>
        <input type="number" name="sort_order" id="sort_order" min="0" value="{{ old('sort_order', $model->sort_order ?? 0) }}"
            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
        <p class="text-xs text-gray-500 mt-1">الرقم الأصغر يظهر أولاً.</p>
        @error('sort_order')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <label class="flex items-center gap-2 pb-3 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $model->is_active ?? true) ? 'checked' : '' }}
            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
        <span class="text-sm font-bold text-gray-700">{{ $activeLabel ?? 'يظهر في الموقع' }}</span>
    </label>
</div>
