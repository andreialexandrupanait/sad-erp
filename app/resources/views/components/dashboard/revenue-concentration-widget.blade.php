{{-- Component class handles all logic - see App\View\Components\Dashboard\RevenueConcentrationWidget --}}
<div
    x-data="{
        concentration: {{ $revenueConcentration }},
        concentrationFormatted: '{{ number_format($revenueConcentration, 1) }}%',
        topThreeRevenue: {{ $topThreeClientsRevenue }},
        topThreeRevenueFormatted: '{{ number_format($topThreeClientsRevenue, 0) }}',
        totalRevenue: {{ $yearlyRevenue }},
        totalRevenueFormatted: '{{ number_format($yearlyRevenue, 0) }}',
        riskLevel: '{{ $riskLevel }}',
        riskLabel: '{{ $riskLabel }}',
        loading: false,

        // Thresholds from config
        thresholds: {
            high: {{ config('dashboard.revenue_concentration.thresholds.high_risk', 70) }},
            medium: {{ config('dashboard.revenue_concentration.thresholds.medium_risk', 50) }}
        },

        get badgeClasses() {
            const colors = {
                high: 'bg-red-100 text-red-800',
                medium: 'bg-yellow-100 text-yellow-800',
                low: 'bg-green-100 text-green-800'
            };
            return colors[this.riskLevel] || colors.low;
        },

        get iconBgClasses() {
            const colors = {
                high: 'bg-red-100 text-red-600',
                medium: 'bg-yellow-100 text-yellow-600',
                low: 'bg-green-100 text-green-600'
            };
            return colors[this.riskLevel] || colors.low;
        },

        determineRiskLevel(concentration) {
            if (concentration >= this.thresholds.high) return 'high';
            if (concentration >= this.thresholds.medium) return 'medium';
            return 'low';
        },

        getRiskLabel(level) {
            const labels = {
                high: '{{ __('app.High Risk') }}',
                medium: '{{ __('app.Medium Risk') }}',
                low: '{{ __('app.Low Risk') }}'
            };
            return labels[level] || labels.low;
        },

        async fetchData(event) {
            this.loading = true;
            try {
                const params = new URLSearchParams({ period: event.detail.period });
                if (event.detail.from) params.append('from', event.detail.from);
                if (event.detail.to) params.append('to', event.detail.to);

                const response = await fetch('{{ route('widgets.revenue-concentration') }}?' + params.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.concentration = data.concentration;
                    this.concentrationFormatted = data.concentration.toFixed(1) + '%';
                    this.topThreeRevenue = data.top_three_revenue;
                    this.topThreeRevenueFormatted = new Intl.NumberFormat('ro-RO').format(data.top_three_revenue);
                    this.totalRevenue = data.total_revenue;
                    this.totalRevenueFormatted = new Intl.NumberFormat('ro-RO').format(data.total_revenue);
                    this.riskLevel = data.risk_level;
                    this.riskLabel = data.risk_label;
                }
            } catch (error) {
                console.error('Failed to fetch revenue concentration:', error);
            } finally {
                this.loading = false;
            }
        }
    }"
    @period-changed-revenue-concentration.window="fetchData($event)"
    class="bg-white border border-slate-200 rounded-xl shadow-sm"
>
    <div class="flex items-center justify-between px-4 md:px-6 py-3 md:py-4 border-b border-slate-200 bg-slate-100">
        <h3 class="text-base font-semibold text-slate-900">{{ __('app.Revenue Concentration') }}</h3>
        <a href="{{ route('clients.index') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium transition-colors">{{ __('View all') }} &rarr;</a>
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
            <x-widgets.period-selector selected="current_year" widget-id="revenue-concentration" />
        </div>

        <div class="flex items-baseline justify-between gap-2 mb-4">
            <div class="flex items-baseline gap-2">
                <p class="text-2xl md:text-3xl font-bold text-slate-900" x-text="concentrationFormatted"></p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold transition-colors"
                      :class="badgeClasses"
                      x-text="riskLabel"></span>
            </div>
            <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center transition-colors"
                 :class="iconBgClasses">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-200 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ __('app.Top 3 Clients') }}</span>
                <span class="font-semibold text-slate-900" x-text="topThreeRevenueFormatted"></span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ __('app.Total Revenue') }}</span>
                <span class="font-semibold text-slate-900" x-text="totalRevenueFormatted"></span>
            </div>
        </div>
    </div>
</div>
