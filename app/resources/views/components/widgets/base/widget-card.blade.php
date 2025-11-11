@props([
    'title' => null,
    'icon' => null,
    'color' => 'slate',
    'clickable' => false,
    'href' => null,
    'gradient' => false,
])

@php
    $baseClasses = 'rounded-xl shadow-sm transition-all duration-200';
    $hoverClasses = $clickable ? 'hover:shadow-lg cursor-pointer' : '';

    $colorClasses = match($color) {
        'green' => $gradient ? 'bg-gradient-to-br from-green-500 to-emerald-600 text-white' : 'bg-white border border-slate-200',
        'red' => $gradient ? 'bg-gradient-to-br from-red-500 to-rose-600 text-white' : 'bg-white border border-slate-200',
        'blue' => $gradient ? 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white' : 'bg-white border border-slate-200',
        'purple' => $gradient ? 'bg-gradient-to-br from-purple-500 to-violet-600 text-white' : 'bg-white border border-slate-200',
        'orange' => $gradient ? 'bg-gradient-to-br from-orange-500 to-red-600 text-white' : 'bg-orange-50 border border-orange-200',
        'yellow' => $gradient ? 'bg-gradient-to-br from-yellow-500 to-orange-600 text-white' : 'bg-yellow-50 border border-yellow-200',
        default => 'bg-white border border-slate-200',
    };

    $classes = trim("{$baseClasses} {$hoverClasses} {$colorClasses}");
@endphp

<div
    {{ $attributes->merge(['class' => $classes]) }}
    @if($clickable && $href)
        onclick="window.location.href='{{ $href }}'"
    @endif
>
    @if($title || $icon)
        <div class="flex items-center justify-between px-6 py-4 border-b {{ $gradient ? 'border-white/20' : 'border-slate-200' }}">
            <h3 class="text-base font-semibold {{ $gradient ? 'text-white' : 'text-slate-900' }}">
                {{ $title }}
            </h3>
            @if($icon)
                <div class="flex-shrink-0">
                    {!! $icon !!}
                </div>
            @endif
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>
</div>
