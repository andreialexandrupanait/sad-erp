@props(['column', 'label'])

@php
    $currentSort = request('sort');
    $currentDir = request('dir', 'asc');
    $isActive = $currentSort === $column;
    $nextDir = ($isActive && $currentDir === 'asc') ? 'desc' : 'asc';

    // Preserve all current query parameters and update sort/dir
    $queryParams = array_merge(request()->except(['sort', 'dir']), [
        'sort' => $column,
        'dir' => $nextDir
    ]);

    // Check if this column should be right-aligned
    $isRightAligned = str_contains($attributes->get('class', ''), 'text-right');
    $justifyClass = $isRightAligned ? 'justify-end' : '';
@endphp

<th {{ $attributes->merge(['class' => 'h-12 px-4 text-left align-middle font-medium text-slate-500']) }}>
    <a href="{{ route(Route::currentRouteName(), $queryParams) }}"
       class="flex items-center gap-1 hover:text-slate-900 transition-colors group {{ $justifyClass }} {{ $isActive ? 'text-slate-900 font-semibold' : '' }}">
        <span>{{ $label }}</span>

        @if($isActive)
            @if($currentDir === 'asc')
                <!-- Ascending Arrow -->
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                </svg>
            @else
                <!-- Descending Arrow -->
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            @endif
        @else
            <!-- Sort Icon (shown on hover) -->
            <svg class="w-4 h-4 opacity-0 group-hover:opacity-50 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
            </svg>
        @endif
    </a>
</th>
