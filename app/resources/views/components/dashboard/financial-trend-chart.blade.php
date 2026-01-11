@props(['yearlyRevenueTrend', 'yearlyExpenseTrend', 'yearlyProfitTrend'])

<div
    x-data="{
        chart: null,
        loading: false,
        labels: {!! json_encode(array_column($yearlyRevenueTrend, 'month')) !!},
        revenueData: {!! json_encode(array_column($yearlyRevenueTrend, 'amount')) !!},
        expenseData: {!! json_encode(array_column($yearlyExpenseTrend, 'amount')) !!},
        profitData: {!! json_encode(array_column($yearlyProfitTrend, 'amount')) !!},

        initChart() {
            const ctx = this.$refs.chartCanvas;
            if (!ctx || this.chart) return;

            this.chart = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: this.labels,
                    datasets: [
                        {
                            label: '{{ __('app.Revenue') }}',
                            data: this.revenueData,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(34, 197, 94)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                        },
                        {
                            label: '{{ __('app.Expenses') }}',
                            data: this.expenseData,
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(239, 68, 68)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                        },
                        {
                            label: '{{ __('app.Net Profit') }}',
                            data: this.profitData,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(148, 163, 184, 0.3)',
                            borderWidth: 1,
                            padding: 12,
                            bodySpacing: 8,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' +
                                        new Intl.NumberFormat('ro-RO', {
                                            style: 'decimal',
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }).format(context.parsed.y) + ' RON';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: 'rgb(100, 116, 139)',
                                font: { size: 11 },
                                callback: function(value) {
                                    return new Intl.NumberFormat('ro-RO', {
                                        notation: 'compact',
                                        compactDisplay: 'short'
                                    }).format(value);
                                }
                            },
                            grid: { color: 'rgba(226, 232, 240, 0.5)', drawBorder: false }
                        },
                        x: {
                            ticks: { color: 'rgb(71, 85, 105)', font: { size: 11, weight: '500' } },
                            grid: { display: false }
                        }
                    }
                }
            });
        },

        updateChart(labels, revenue, expenses, profit) {
            if (!this.chart) return;
            this.chart.data.labels = labels;
            this.chart.data.datasets[0].data = revenue;
            this.chart.data.datasets[1].data = expenses;
            this.chart.data.datasets[2].data = profit;
            this.chart.update();
        },

        async fetchData(event) {
            this.loading = true;
            try {
                const params = new URLSearchParams({ period: event.detail.period });
                if (event.detail.from) params.append('from', event.detail.from);
                if (event.detail.to) params.append('to', event.detail.to);

                const response = await fetch('{{ route('widgets.financial-trend') }}?' + params.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const labels = data.labels || data.revenue_trend.map(function(r) { return r.month; });
                    const revenue = data.revenue_trend.map(function(r) { return r.amount; });
                    const expenses = data.expense_trend.map(function(r) { return r.amount; });
                    const profit = data.profit_trend.map(function(r) { return r.amount; });
                    this.updateChart(labels, revenue, expenses, profit);
                }
            } catch (error) {
                console.error('Failed to fetch trend data:', error);
            } finally {
                this.loading = false;
            }
        }
    }"
    x-init="$nextTick(() => initChart())"
    @period-changed-trend.window="fetchData($event)"
    class="bg-white border border-slate-200 rounded-xl shadow-sm"
>
    <div class="flex items-center justify-between px-4 md:px-6 py-3 md:py-4 border-b border-slate-200 bg-slate-100">
        <h3 class="text-base font-semibold text-slate-900">{{ __('app.Monthly Trend') }}</h3>
        <a href="{{ route('financial.dashboard') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium transition-colors">{{ __('app.View all') }} &rarr;</a>
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
            <x-widgets.period-selector selected="current_year" widget-id="trend" />
        </div>

        <div class="flex items-center justify-center gap-4 text-xs mb-4">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                <span class="text-slate-600">{{ __('app.Revenue') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                <span class="text-slate-600">{{ __('app.Expenses') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                <span class="text-slate-600">{{ __('app.Net Profit') }}</span>
            </div>
        </div>
        <div style="height: 280px;">
            <canvas x-ref="chartCanvas"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
