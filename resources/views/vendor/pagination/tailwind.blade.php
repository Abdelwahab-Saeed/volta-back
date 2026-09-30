{{-- Admin pagination (overrides Laravel's default Tailwind view). Arabic labels, RTL arrows. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="التنقل بين الصفحات" class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-sm text-slate-500">
            @if ($paginator->firstItem())
                عرض <span class="font-bold text-navy-900">{{ $paginator->firstItem() }}</span>–<span class="font-bold text-navy-900">{{ $paginator->lastItem() }}</span>
                من <span class="font-bold text-navy-900">{{ $paginator->total() }}</span>
            @else
                {{ $paginator->count() }} عنصر
            @endif
        </p>

        <ul class="flex items-center gap-1">
            {{-- Previous (on the start side in RTL) --}}
            <li>
                @if ($paginator->onFirstPage())
                    <span class="icon-btn opacity-40 pointer-events-none" aria-disabled="true" aria-label="السابق">
                        <x-admin.icon name="arrow-right" class="w-4 h-4" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="icon-btn border border-slate-200 bg-white" aria-label="السابق">
                        <x-admin.icon name="arrow-right" class="w-4 h-4" />
                    </a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="px-2 text-slate-400">{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="{{ $page == $paginator->currentPage() ? '' : 'hidden sm:block' }}">
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 rounded-lg bg-navy-900 text-white text-sm font-bold">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-100" aria-label="الصفحة {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="icon-btn border border-slate-200 bg-white" aria-label="التالي">
                        <x-admin.icon name="arrow-left" class="w-4 h-4" />
                    </a>
                @else
                    <span class="icon-btn opacity-40 pointer-events-none" aria-disabled="true" aria-label="التالي">
                        <x-admin.icon name="arrow-left" class="w-4 h-4" />
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
