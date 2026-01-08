{{-- Component class handles all logic - see App\View\Components\Dashboard\RevenueConcentrationWidget --}}
<div class="bg-white border border-slate-200 rounded-xl shadow-sm">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-100">
        <h3 class="text-base font-semibold text-slate-900">{{ __('app.Revenue Concentration') }}</h3>
        <a href="{{ route('clients.index') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium transition-colors">{{ __('View all') }} →</a>
    </div>
    <div class="p-6">
    <div class="flex items-baseline justify-between gap-2 mb-4">
        <div class="flex items-baseline gap-2">
            <p class="text-3xl font-bold text-slate-900">{{ number_format($revenueConcentration, 1) }}%</p>
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $colorClasses['badge'] }}">
                {{ $riskLabel }}
            </span>
        </div>
        <div class="flex-shrink-0 w-10 h-10 {{ $colorClasses['bg'] }} {{ $colorClasses['text'] }} rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
    </div>

        <div class="pt-3 border-t border-slate-200 space-y-2">
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
</div>
