@props([
    'variant' => 'default',
    'animate' => true,
])

@php
    $variants = [
        'default' => 'h-4 rounded',
        'circle' => 'rounded-full',
        'text' => 'h-4 rounded w-3/4',
        'title' => 'h-6 rounded w-1/2',
        'avatar' => 'h-10 w-10 rounded-full',
        'button' => 'h-10 w-24 rounded-md',
        'card' => 'h-32 rounded-lg',
        'image' => 'aspect-video rounded-lg',
    ];

    $variantClass = $variants[$variant] ?? $variants['default'];
@endphp

<div
    {{ $attributes->merge([
        'class' => "bg-slate-200 $variantClass" . ($animate ? ' animate-pulse' : ''),
    ]) }}
></div>
