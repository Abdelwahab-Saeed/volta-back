{{-- Params: $submit (button label), $cancel (url) --}}
<div class="pt-4 flex gap-4">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-2.5 rounded-xl font-bold transition-all shadow-md">{{ $submit }}</button>
    <a href="{{ $cancel }}" class="px-10 py-2.5 text-gray-500 hover:bg-gray-100 rounded-xl transition-all font-bold">إلغاء</a>
</div>
