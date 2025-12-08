@props([
    'variant' => 'default',
    'title' => null,
])

@php
    $variants = [
        'default' => 'bg-white text-slate-950 border-slate-200',
        'destructive' => 'border-red-500/50 text-red-500 [&>svg]:text-red-500',
        'success' => 'border-green-500/50 bg-green-50 text-green-900 [&>svg]:text-green-600',
        'warning' => 'border-yellow-500/50 bg-yellow-50 text-yellow-900 [&>svg]:text-yellow-600',
        'info' => 'border-blue-500/50 bg-blue-50 text-blue-900 [&>svg]:text-blue-600',
    ];

    $classes = 'relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-slate-950 ' . ($variants[$variant] ?? $variants['default']);
@endphp

<div {{ $attributes->merge(['class' => $classes]) }} role="alert">
    @if($title)
        <h5 class="mb-1 font-medium leading-none tracking-tight">{{ $title }}</h5>
    @endif
    <div class="text-sm [&_p]:leading-relaxed">
        {{ $slot }}
    </div>
</div>
