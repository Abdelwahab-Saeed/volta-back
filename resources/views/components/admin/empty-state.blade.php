{{-- Friendly empty state for lists. <x-admin.empty-state icon="cube" title="..." text="..." :action="route(...)" action-label="..." /> --}}
@props(['icon' => 'inbox', 'title', 'text' => null, 'action' => null, 'actionLabel' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center px-6 py-14']) }}>
    <span class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-4">
        <x-admin.icon :name="$icon" class="w-7 h-7" />
    </span>
    <p class="text-base font-extrabold text-navy-900">{{ $title }}</p>
    @if($text)
        <p class="mt-1 text-sm text-slate-500 max-w-sm">{{ $text }}</p>
    @endif
    @if($action)
        <a href="{{ $action }}" class="btn-primary mt-5">
            <x-admin.icon name="plus" class="w-4 h-4" />
            {{ $actionLabel }}
        </a>
    @endif
    {{ $slot }}
</div>
