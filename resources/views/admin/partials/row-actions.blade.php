{{-- Edit + delete buttons for a table row. Params: $edit (url), $destroy (url), $id (unique form id), $confirm (message) --}}
<div class="flex items-center justify-end gap-1">
    <a href="{{ $edit }}" class="icon-btn-primary" data-tip="تعديل" aria-label="تعديل">
        <x-admin.icon name="edit" class="w-[18px] h-[18px]" />
    </a>
    <form id="{{ $id }}" action="{{ $destroy }}" method="POST" class="inline-block">
        @csrf
        @method('DELETE')
        <button type="button" onclick="confirmAction('{{ $id }}', '{{ $confirm }}')" class="icon-btn-danger" data-tip="حذف" aria-label="حذف">
            <x-admin.icon name="trash" class="w-[18px] h-[18px]" />
        </button>
    </form>
</div>
