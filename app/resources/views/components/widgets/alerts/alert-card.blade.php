@props([
    'title',
    'items',
    'type' => 'warning', // warning, error, info
    'emptyMessage' => __('No items'),
])

@php
    $iconColors = match($type) {
        'warning' => 'text-orange-600',
        'error' => 'text-red-600',
        'info' => 'text-blue-600',
        default => 'text-slate-600',
    };

    $icon = match($type) {
        'warning' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
        'error' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'info' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        default => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    };
@endphp

<div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
    <div class="flex items-center gap-2 px-4 md:px-6 py-3 md:py-4 border-b border-slate-200 bg-slate-100">
        <div class="{{ $iconColors }}">
            {!! $icon !!}
        </div>
        <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
    </div>
    <div class="p-4 md:p-6">
        @if($items && $items->count() > 0)
            <div class="space-y-2">
                {{ $slot }}
            </div>
        @else
            <p class="text-sm text-slate-500 text-center py-4">{{ $emptyMessage }}</p>
        @endif
    </div>
</div>
