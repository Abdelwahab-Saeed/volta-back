{{-- Search + filter bar above an admin list. The form is GET, so the filters live in the URL (links, back button, pagination).
     $filters holds only the active values (see ReadsListFilters). Selects and dates apply as soon as they change.
     <x-admin.filters :filters="$filters" :total="$orders->total()" placeholder="..." dates
         :selects="['status' => ['label' => 'الحالة', 'options' => ['pending' => 'قيد الانتظار']]]" /> --}}
@props(['filters' => [], 'selects' => [], 'placeholder' => 'بحث…', 'dates' => false, 'total' => null])
<form method="GET" action="{{ url()->current() }}" role="search" {{ $attributes->merge(['class' => 'card p-3 sm:p-4 mb-4']) }}>
    <div class="flex flex-wrap items-center gap-2.5">
        <div class="relative w-full lg:w-auto lg:flex-1 lg:min-w-[16rem]">
            <label for="filter-q" class="sr-only">{{ $placeholder }}</label>
            <x-admin.icon name="search" class="w-4 h-4 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input id="filter-q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="{{ $placeholder }}" class="input pr-10">
        </div>

        @foreach ($selects as $name => $select)
            <div class="flex-1 min-w-[9rem] sm:flex-none sm:w-44">
                <label for="filter-{{ $name }}" class="sr-only">{{ $select['label'] }}</label>
                <select id="filter-{{ $name }}" name="{{ $name }}" onchange="this.form.submit()"
                        class="input {{ isset($filters[$name]) ? 'border-brand-400 bg-brand-50/40 font-bold text-brand-800' : '' }}">
                    <option value="">{{ $select['label'] }}: الكل</option>
                    @foreach ($select['options'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters[$name] ?? null) === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach

        @if ($dates)
            @foreach (['from' => 'من', 'to' => 'إلى'] as $name => $label)
                <label class="flex-1 min-w-[10rem] sm:flex-none flex items-center gap-2 text-xs font-bold text-slate-500">
                    {{ $label }}
                    <input type="date" name="{{ $name }}" value="{{ $filters[$name] ?? '' }}" onchange="this.form.submit()"
                           class="input sm:w-40 {{ isset($filters[$name]) ? 'border-brand-400 bg-brand-50/40' : '' }}">
                </label>
            @endforeach
        @endif

        <div class="flex items-center gap-2">
            <button type="submit" class="btn-dark h-11">
                <x-admin.icon name="search" class="w-4 h-4" />
                بحث
            </button>
            @if ($filters)
                <a href="{{ url()->current() }}" class="btn-ghost h-11">
                    <x-admin.icon name="x" class="w-4 h-4" />
                    مسح الفلاتر
                </a>
            @endif
        </div>
    </div>

    @if ($filters && $total !== null)
        <p class="mt-3 text-xs font-bold text-slate-500" role="status">
            عدد النتائج: <span class="tabular-nums text-navy-900">{{ number_format($total) }}</span>
        </p>
    @endif
</form>
