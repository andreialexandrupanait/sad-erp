@props([
    'variant' => 'default',
    'size' => 'default',
    'type' => 'button',
    'loading' => false,
    'loadingText' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50';

    $variants = [
        'default' => 'bg-slate-900 text-slate-50 hover:bg-slate-900/90 shadow',
        'destructive' => 'bg-red-500 text-slate-50 hover:bg-red-500/90 shadow-sm',
        'destructive-outline' => 'border border-red-300 bg-white text-red-600 hover:bg-red-50 shadow-sm',
        'outline' => 'border border-slate-200 bg-white text-slate-900 hover:bg-slate-100 hover:text-slate-900 shadow-sm',
        'secondary' => 'bg-slate-100 text-slate-900 hover:bg-slate-100/80 shadow-sm',
        'ghost' => 'hover:bg-slate-100 hover:text-slate-900',
        'link' => 'text-slate-900 underline-offset-4 hover:underline',
        'success' => 'bg-green-600 text-white hover:bg-green-700 shadow-sm',
        'warning' => 'bg-amber-500 text-white hover:bg-amber-600 shadow-sm',
    ];

    $sizes = [
        'default' => 'h-10 px-4 py-2',
        'sm' => 'h-9 rounded-md px-3',
        'lg' => 'h-11 rounded-md px-8',
        'xl' => 'h-12 rounded-md px-10 text-base',
        'icon' => 'h-10 w-10',
        'icon-sm' => 'h-8 w-8',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['default']);
@endphp

<button
    {{ $attributes->merge(['type' => $type, 'class' => $classes]) }}
    @if($loading)
        disabled
        aria-disabled="true"
        aria-busy="true"
    @endif
>
    @if($loading)
        <x-ui.spinner size="sm" class="mr-2" aria-hidden="true" />
        <span aria-live="polite">{{ $loadingText ?? $slot }}</span>
    @else
        {{ $slot }}
    @endif
</button>
