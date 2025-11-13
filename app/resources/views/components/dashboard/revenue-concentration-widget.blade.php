@props(['revenueConcentration', 'topThreeClientsRevenue', 'yearlyRevenue'])

@php
    $riskLevel = $revenueConcentration >= 50 ? 'high' : ($revenueConcentration >= 30 ? 'medium' : 'low');
    $colorClasses = [
        'high' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'badge' => 'bg-red-100 text-red-700'],
        'medium' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-600', 'badge' => 'bg-yellow-100 text-yellow-700'],
        'low' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'badge' => 'bg-green-100 text-green-700'],
    ][$riskLevel];
    $riskLabel = [
        'high' => __('app.High Risk'),
        'medium' => __('app.Medium Risk'),
        'low' => __('app.Low Risk'),
    ][$riskLevel];
@endphp

<div class="bg-white border border-slate-200 rounded-xl shadow-sm hover:shadow-md transition-shadow p-5">
    <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">{{ __('app.Revenue Concentration') }}</p>
        </div>
        <div class="flex-shrink-0 w-10 h-10 {{ $colorClasses['bg'] }} {{ $colorClasses['text'] }} rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
    </div>

    <div class="flex items-baseline gap-2 mb-4">
        <p class="text-3xl font-bold text-slate-900">{{ number_format($revenueConcentration, 1) }}%</p>
        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $colorClasses['badge'] }}">
            {{ $riskLabel }}
        </span>
    </div>

    <div class="pt-3 border-t border-slate-200 space-y-1">
        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500">{{ __('app.Top 3 Clients') }}</span>
            <span class="font-semibold text-slate-900">{{ number_format($topThreeClientsRevenue, 0) }}</span>
        </div>
        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500">{{ __('app.Total Revenue') }}</span>
            <span class="font-semibold text-slate-900">{{ number_format($yearlyRevenue, 0) }}</span>
        </div>
    </div>
</div>
