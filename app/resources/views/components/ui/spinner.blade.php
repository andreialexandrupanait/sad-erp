@props([
    'size' => 'default',
    'color' => 'current',
    'label' => __('Loading...'),
])

@php
    $sizes = [
        'xs' => 'h-3 w-3',
        'sm' => 'h-4 w-4',
        'default' => 'h-5 w-5',
        'md' => 'h-6 w-6',
        'lg' => 'h-8 w-8',
        'xl' => 'h-10 w-10',
    ];

    $colors = [
        'current' => 'text-current',
        'primary' => 'text-slate-900',
        'white' => 'text-white',
        'gray' => 'text-slate-400',
        'blue' => 'text-blue-600',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['default'];
    $colorClass = $colors[$color] ?? $colors['current'];
@endphp

<svg
    {{ $attributes->merge(['class' => "animate-spin $sizeClass $colorClass"]) }}
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    role="status"
    aria-live="polite"
    aria-label="{{ $label }}"
>
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
</svg>
