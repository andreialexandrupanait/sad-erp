@props(['monthlySubscriptionCost', 'annualProjectedCost', 'activeSubscriptionsCount', 'pausedSubscriptionsCount', 'cancelledSubscriptionsCount'])

<div
    x-data="{
        monthlyCost: {{ $monthlySubscriptionCost }},
        monthlyCostFormatted: '{{ number_format($monthlySubscriptionCost, 0) }}',
        annualCost: {{ $annualProjectedCost }},
        annualCostFormatted: '{{ number_format($annualProjectedCost, 0) }}',
        activeCount: {{ $activeSubscriptionsCount }},
        pausedCount: {{ $pausedSubscriptionsCount }},
        cancelledCount: {{ $cancelledSubscriptionsCount }},
        loading: false
    }"
    class="bg-white border border-slate-200 rounded-xl shadow-sm"
>
    <div class="flex items-center justify-between px-4 md:px-6 py-3 md:py-4 border-b border-slate-200 bg-slate-100">
        <h3 class="text-base font-semibold text-slate-900">{{ __('app.Subscription Costs') }}</h3>
        <a href="{{ route('subscriptions.index') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium transition-colors">{{ __('View all') }} &rarr;</a>
    </div>
    <div class="p-4 md:p-6 relative">
        {{-- Loading Overlay --}}
        <div x-show="loading" x-transition.opacity class="absolute inset-0 bg-white/70 flex items-center justify-center z-20">
            <svg class="animate-spin h-6 w-6 text-slate-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        {{-- Info note about current data --}}
        <div class="relative z-10 pb-3 mb-3 border-b border-slate-200/60">
            <span class="text-xs text-slate-400">Abonamente active curente</span>
        </div>

        <div class="flex items-baseline justify-between gap-2 mb-4">
            <div class="flex items-baseline gap-2">
                <p class="text-2xl md:text-3xl font-bold text-slate-900" x-text="monthlyCostFormatted"></p>
                <span class="text-xs text-slate-500">RON/{{ __('app.month') }}</span>
            </div>
            <div class="flex-shrink-0 w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-200 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ __('app.Annual Projected') }}</span>
                <span class="font-semibold text-slate-900" x-text="annualCostFormatted"></span>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span class="text-xs font-medium text-slate-600" x-text="activeCount"></span>
                </div>
                <template x-if="pausedCount > 0">
                    <div class="flex items-center gap-1.5">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                        <span class="text-xs font-medium text-slate-600" x-text="pausedCount"></span>
                    </div>
                </template>
                <template x-if="cancelledCount > 0">
                    <div class="flex items-center gap-1.5">
                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                        <span class="text-xs font-medium text-slate-600" x-text="cancelledCount"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
