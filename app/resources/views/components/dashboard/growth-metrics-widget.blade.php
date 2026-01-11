@props(['revenueGrowth', 'expenseGrowth', 'clientGrowth', 'newClientsThisMonth', 'newClientsLastMonth'])

<div
    x-data="{
        revenueGrowth: {{ $revenueGrowth }},
        expenseGrowth: {{ $expenseGrowth }},
        clientGrowth: {{ $clientGrowth }},
        newClientsThisMonth: {{ $newClientsThisMonth }},
        loading: false,

        formatGrowth(value) {
            return Math.abs(value).toFixed(1) + '%';
        },

        async fetchData(event) {
            this.loading = true;
            try {
                const params = new URLSearchParams({ period: event.detail.period });
                if (event.detail.from) params.append('from', event.detail.from);
                if (event.detail.to) params.append('to', event.detail.to);

                const response = await fetch('{{ route('widgets.financial-summary') }}?' + params.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Growth metrics are calculated month-over-month, so period change just refreshes display
                // In a full implementation, you'd calculate growth for the selected period
            } catch (error) {
                console.error('Failed to fetch growth metrics:', error);
            } finally {
                this.loading = false;
            }
        }
    }"
    @period-changed-growth.window="fetchData($event)"
    class="bg-white border border-slate-200 rounded-xl shadow-sm"
>
    <div class="flex items-center justify-between px-4 md:px-6 py-3 md:py-4 border-b border-slate-200 bg-slate-100">
        <h3 class="text-base font-semibold text-slate-900">{{ __('app.Month-over-Month Growth') }}</h3>
        <a href="{{ route('financial.dashboard') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium transition-colors">{{ __('View all') }} &rarr;</a>
    </div>
    <div class="p-4 md:p-6 relative">
        {{-- Loading Overlay --}}
        <div x-show="loading" x-transition.opacity class="absolute inset-0 bg-white/70 flex items-center justify-center z-20">
            <svg class="animate-spin h-6 w-6 text-slate-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        {{-- Period Selector at top --}}
        <div class="relative z-30 pb-3 mb-3 border-b border-slate-200/60">
            <x-widgets.period-selector selected="current_month" widget-id="growth" />
        </div>

        <div class="space-y-3">
            {{-- Revenue Growth --}}
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-600">{{ __('app.Revenue') }}</span>
                <div class="flex items-center gap-1">
                    <template x-if="revenueGrowth > 0">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                    <template x-if="revenueGrowth < 0">
                        <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                    <span class="text-sm font-bold"
                          :class="revenueGrowth > 0 ? 'text-green-600' : (revenueGrowth < 0 ? 'text-red-600' : 'text-slate-600')"
                          x-text="revenueGrowth === 0 ? '0%' : formatGrowth(revenueGrowth)"></span>
                </div>
            </div>

            {{-- Expense Growth --}}
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-600">{{ __('app.Expenses') }}</span>
                <div class="flex items-center gap-1">
                    <template x-if="expenseGrowth > 0">
                        <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                    <template x-if="expenseGrowth < 0">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                    <span class="text-sm font-bold"
                          :class="expenseGrowth > 0 ? 'text-red-600' : (expenseGrowth < 0 ? 'text-green-600' : 'text-slate-600')"
                          x-text="expenseGrowth === 0 ? '0%' : formatGrowth(expenseGrowth)"></span>
                </div>
            </div>

            {{-- Client Growth --}}
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-600">{{ __('app.New Clients') }}</span>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400" x-text="newClientsThisMonth"></span>
                    <template x-if="clientGrowth > 0">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                    <template x-if="clientGrowth < 0">
                        <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                    <span class="text-sm font-bold"
                          :class="clientGrowth > 0 ? 'text-green-600' : (clientGrowth < 0 ? 'text-red-600' : 'text-slate-600')"
                          x-text="clientGrowth === 0 ? '0%' : Math.abs(clientGrowth).toFixed(0) + '%'"></span>
                </div>
            </div>
        </div>
    </div>
</div>
