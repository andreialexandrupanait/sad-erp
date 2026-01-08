@props([
    'value' => 0,
    'max' => 100,
    'size' => 'default',
    'color' => 'primary',
    'showLabel' => false,
    'animated' => false,
    'striped' => false,
])

@php
    $percentage = $max > 0 ? min(100, max(0, ($value / $max) * 100)) : 0;

    $sizes = [
        'xs' => 'h-1',
        'sm' => 'h-2',
        'default' => 'h-3',
        'lg' => 'h-4',
        'xl' => 'h-6',
    ];

    $colors = [
        'primary' => 'bg-slate-900',
        'blue' => 'bg-blue-600',
        'green' => 'bg-green-600',
        'red' => 'bg-red-600',
        'amber' => 'bg-amber-500',
        'purple' => 'bg-purple-600',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['default'];
    $colorClass = $colors[$color] ?? $colors['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($showLabel)
        <div class="flex justify-between mb-1.5">
            <span class="text-sm font-medium text-slate-700">{{ $slot }}</span>
            <span class="text-sm font-medium text-slate-500">{{ number_format($percentage, 0) }}%</span>
        </div>
    @endif

    <div class="w-full bg-slate-200 rounded-full overflow-hidden {{ $sizeClass }}">
        <div
            class="{{ $colorClass }} {{ $sizeClass }} rounded-full transition-all duration-500 ease-out
                {{ $striped ? 'bg-gradient-to-r from-transparent via-white/20 to-transparent bg-[length:20px_100%]' : '' }}
                {{ $animated && $striped ? 'animate-[progress-stripes_1s_linear_infinite]' : '' }}"
            style="width: {{ $percentage }}%"
            role="progressbar"
            aria-valuenow="{{ $value }}"
            aria-valuemin="0"
            aria-valuemax="{{ $max }}"
        ></div>
    </div>
</div>

@if($animated && $striped)
@once
@push('scripts')
<style>
    @keyframes progress-stripes {
        0% { background-position: 20px 0; }
        100% { background-position: 0 0; }
    }
</style>
@endpush
@endonce
@endif
