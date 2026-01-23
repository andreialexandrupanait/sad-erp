{{-- Component class handles all logic - see App\View\Components\Dashboard\ProfitMarginWidget --}}
<div
    x-data="{
        profitMargin: {{ $currentMonthProfitMargin }},
        profitMarginFormatted: '{{ number_format($currentMonthProfitMargin, 1) }}%',
        statusLabel: '{{ $statusLabel }}',
        colorLevel: '{{ $currentColor }}',
        loading: false,

        // Thresholds from config
        thresholds: {
            excellent: {{ config('dashboard.profit_margin.thresholds.excellent', 30) }},
            good: {{ config('dashboard.profit_margin.thresholds.good', 15) }}
        },

        get badgeClasses() {
            const colors = {
                excellent: 'bg-green-100 text-green-800',
                good: 'bg-blue-100 text-blue-800',
                low: 'bg-red-100 text-red-800'
            };
            return colors[this.colorLevel] || colors.low;
        },

        get iconBgClasses() {
            const colors = {
                excellent: 'bg-green-100 text-green-600',
                good: 'bg-blue-100 text-blue-600',
                low: 'bg-red-100 text-red-600'
            };
            return colors[this.colorLevel] || colors.low;
        },

        determineColorLevel(margin) {
            if (margin >= this.thresholds.excellent) return 'excellent';
            if (margin >= this.thresholds.good) return 'good';
            return 'low';
        },

        getStatusLabel(margin) {
            if (margin >= this.thresholds.excellent) return '{{ __('app.Excellent') }}';
            if (margin >= this.thresholds.good) return '{{ __('app.Good') }}';
            return '{{ __('app.Low') }}';
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

                if (response.ok) {
                    const data = await response.json();
                    this.profitMargin = data.profit_margin;
                    this.profitMarginFormatted = data.profit_margin.toFixed(1) + '%';
                    this.colorLevel = this.determineColorLevel(data.profit_margin);
                    this.statusLabel = this.getStatusLabel(data.profit_margin);
                }
            } catch (error) {
                console.error('Failed to fetch profit margin:', error);
            } finally {
                this.loading = false;
            }
        }
    }"
    @period-changed-profit-margin.window="fetchData($event)"
    class="bg-white border border-slate-200 rounded-xl shadow-sm"
>
    <div class="flex items-center justify-between px-4 md:px-6 py-3 md:py-4 border-b border-slate-200 bg-slate-100">
        <h3 class="text-base font-semibold text-slate-900">{{ __('app.Profit Margin') }}</h3>
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
            <x-widgets.period-selector selected="current_year" widget-id="profit-margin" />
        </div>

        <div class="flex items-baseline justify-between gap-2 mb-4">
            <div class="flex items-baseline gap-2">
                <p class="text-2xl md:text-3xl font-bold text-slate-900" x-text="profitMarginFormatted"></p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold transition-colors"
                      :class="badgeClasses"
                      x-text="statusLabel"></span>
            </div>
            <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center transition-colors"
                 :class="iconBgClasses">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-200 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ __('Selected Period') }}</span>
                <span class="font-semibold text-slate-900" x-text="profitMarginFormatted"></span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ __('app.Yearly Average') }}</span>
                <span class="font-semibold text-slate-900">{{ number_format($yearlyProfitMargin, 1) }}%</span>
            </div>
        </div>
    </div>
</div>
