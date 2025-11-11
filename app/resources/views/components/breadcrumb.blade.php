@props(['items' => [], 'current' => null, 'auto' => true])

@php
    // If items is a string, convert to array
    if (is_string($items)) {
        $items = explode('>', $items);
        $items = array_map('trim', $items);
    }

    // If no items provided but current is provided, just show current
    if (empty($items) && $current) {
        $items = [$current];
    }

    // Auto-generate breadcrumbs if empty and auto is enabled
    if (empty($items) && $auto) {
        $items = \App\Services\BreadcrumbService::generate();
    }

    // Fallback to dashboard if still empty
    if (empty($items)) {
        $items = [['label' => 'Dashboard', 'url' => route('dashboard')]];
    }
@endphp

<nav class="flex items-center gap-1.5 text-xs" aria-label="Breadcrumb">
    @foreach($items as $index => $item)
        @if($index > 0)
            <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        @endif

        @if(is_array($item))
            {{-- Item with URL --}}
            @if($loop->last)
                <span class="text-slate-600 font-medium whitespace-nowrap">
                    {{ $item['label'] }}
                </span>
            @else
                <a href="{{ $item['url'] }}" class="text-slate-500 hover:text-slate-700 transition-colors whitespace-nowrap">
                    {{ $item['label'] }}
                </a>
            @endif
        @else
            {{-- Simple string item --}}
            @if($loop->last)
                <span class="text-slate-600 font-medium whitespace-nowrap">
                    {{ $item }}
                </span>
            @else
                <span class="text-slate-500 whitespace-nowrap">
                    {{ $item }}
                </span>
            @endif
        @endif
    @endforeach
</nav>
