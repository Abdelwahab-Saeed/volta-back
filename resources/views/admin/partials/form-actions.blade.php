{{-- Save / cancel row at the bottom of a form card. Params: $submit (button label), $cancel (url) --}}
<div class="card-footer flex flex-col-reverse sm:flex-row sm:items-center gap-3 -mx-6 -mb-6 mt-8 px-6">
    <button type="submit" class="btn-primary sm:min-w-[9rem]">
        <x-admin.icon name="check" class="w-4 h-4" />
        {{ $submit }}
    </button>
    <a href="{{ $cancel }}" class="btn-ghost">إلغاء</a>
</div>
