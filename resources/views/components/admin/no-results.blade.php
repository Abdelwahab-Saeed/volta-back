{{-- Empty list because of the search/filters (not because there is no data yet). --}}
<x-admin.empty-state icon="search" title="لا توجد نتائج مطابقة" text="جرّب كلمة بحث أخرى أو غيّر الفلاتر.">
    <a href="{{ url()->current() }}" class="btn-secondary mt-5">
        <x-admin.icon name="x" class="w-4 h-4" />
        مسح الفلاتر
    </a>
</x-admin.empty-state>
