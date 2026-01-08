@props(['title', 'value', 'icon' => null, 'trend' => null, 'trendDirection' => 'up', 'color' => 'primary'])

@php
    $colorClasses = [
        'primary' => 'bg-primary-50 text-primary-600',
        'green' => 'bg-green-50 text-green-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'orange' => 'bg-orange-50 text-orange-600',
        'red' => 'bg-red-50 text-red-600',
        'yellow' => 'bg-yellow-50 text-yellow-600',
    ];

    $iconBgClass = $colorClasses[$color] ?? $colorClasses['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm border border-slate-200 p-6 hover:shadow-md transition-all duration-200']) }}>
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-600 mb-1">{{ $title }}</p>
            <p class="text-3xl font-bold text-gray-900 mb-2">{{ $value }}</p>

            @if ($trend)
                <div class="flex items-center gap-1">
                    @if ($trendDirection === 'up')
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <span class="text-sm font-medium text-green-600">{{ $trend }}</span>
                    @elseif ($trendDirection === 'down')
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        </svg>
                        <span class="text-sm font-medium text-red-600">{{ $trend }}</span>
                    @else
                        <span class="text-sm font-medium text-gray-500">{{ $trend }}</span>
                    @endif
                </div>
            @endif
        </div>

        @if ($icon)
            <div class="flex-shrink-0 w-12 h-12 {{ $iconBgClass }} rounded-xl flex items-center justify-center">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>
