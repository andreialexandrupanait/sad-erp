@props(['currentMonthProfitMargin', 'yearlyProfitMargin'])

@php
    $currentColor = $currentMonthProfitMargin >= 20 ? 'green' : ($currentMonthProfitMargin >= 10 ? 'yellow' : 'red');
    $colorClasses = [
        'green' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'badge' => 'bg-green-100 text-green-700'],
        'yellow' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-600', 'badge' => 'bg-yellow-100 text-yellow-700'],
        'red' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'badge' => 'bg-red-100 text-red-700'],
    ][$currentColor];
    $statusLabel = $currentMonthProfitMargin >= 20 ? __('app.Excellent') : ($currentMonthProfitMargin >= 10 ? __('app.Good') : __('app.Low'));
@endphp

<div class="bg-white border border-slate-200 rounded-xl shadow-sm hover:shadow-md transition-shadow p-5">
    <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ __('app.Profit Margin') }}</p>
        </div>
        <div class="flex-shrink-0 w-10 h-10 {{ $colorClasses['bg'] }} {{ $colorClasses['text'] }} rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
    </div>

    <div class="flex items-baseline gap-2 mb-4">
        <p class="text-3xl font-bold text-slate-900">{{ number_format($currentMonthProfitMargin, 1) }}%</p>
        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $colorClasses['badge'] }}">
            {{ $statusLabel }}
        </span>
    </div>

    <div class="pt-3 border-t border-slate-200 space-y-1">
        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500">{{ __('app.Current Month') }}</span>
            <span class="font-semibold text-slate-900">{{ number_format($currentMonthProfitMargin, 1) }}%</span>
        </div>
        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500">{{ __('app.Yearly Average') }}</span>
            <span class="font-semibold text-slate-900">{{ number_format($yearlyProfitMargin, 1) }}%</span>
        </div>
    </div>
</div>
