@props([
    'title',
    'value',
    'subtitle' => null,
    'icon',
    'color' => 'blue',
    'href' => null,
])

@php
    $iconColors = match($color) {
        'blue' => 'bg-blue-50 text-blue-600',
        'green' => 'bg-green-50 text-green-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'orange' => 'bg-orange-50 text-orange-600',
        'red' => 'bg-red-50 text-red-600',
        default => 'bg-slate-50 text-slate-600',
    };
@endphp

<div
    class="bg-white border border-slate-200 rounded-[10px] shadow-sm hover:shadow-md transition-shadow cursor-pointer p-5"
    @if($href)
        onclick="window.location.href='{{ $href }}'"
    @endif
>
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ $title }}</p>
            <p class="text-2xl font-bold text-slate-900">{{ $value }}</p>
            @if($subtitle)
                <p class="text-xs text-slate-500 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="flex-shrink-0 w-10 h-10 {{ $iconColors }} rounded-lg flex items-center justify-center">
            {!! $icon !!}
        </div>
    </div>
</div>
