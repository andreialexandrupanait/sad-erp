@props([
    'variant' => 'default',
])

@php
    $variants = [
        'default' => 'border-transparent bg-slate-900 text-slate-50 hover:bg-slate-900/80',
        'secondary' => 'border-transparent bg-slate-100 text-slate-900 hover:bg-slate-100/80',
        'destructive' => 'border-transparent bg-red-500 text-slate-50 hover:bg-red-500/80',
        'outline' => 'text-slate-950 border-slate-200',
        'success' => 'border-transparent bg-green-500 text-white hover:bg-green-500/80',
        'warning' => 'border-transparent bg-yellow-500 text-white hover:bg-yellow-500/80',
        // Color variants for credential types and other uses
        'blue' => 'border-transparent bg-blue-100 text-blue-800 hover:bg-blue-200',
        'purple' => 'border-transparent bg-purple-100 text-purple-800 hover:bg-purple-200',
        'orange' => 'border-transparent bg-orange-100 text-orange-800 hover:bg-orange-200',
        'green' => 'border-transparent bg-emerald-100 text-emerald-800 hover:bg-emerald-200',
        'slate' => 'border-transparent bg-slate-100 text-slate-600 hover:bg-slate-200',
    ];

    $classes = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2 ' . ($variants[$variant] ?? $variants['default']);
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
