@props(['yearlyRevenueTrend', 'yearlyExpenseTrend', 'yearlyProfitTrend'])

<div class="bg-white border border-slate-200 rounded-xl shadow-sm">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-100">
        <h3 class="text-base font-semibold text-slate-900">{{ __('app.Monthly Trend') }}</h3>
        <a href="{{ route('financial.dashboard') }}" class="text-sm text-slate-600 hover:text-slate-900 font-medium transition-colors">{{ __('app.View all') }} →</a>
    </div>
    <div class="p-6">
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
        <div style="height: 320px;">
            <canvas id="financialTrendChart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('financialTrendChart');

    if (!ctx) return;

    const chart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($yearlyRevenueTrend, 'month')) !!},
            datasets: [
                {
                    label: '{{ __("app.Revenue") }}',
                    data: {!! json_encode(array_column($yearlyRevenueTrend, 'amount')) !!},
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
                    label: '{{ __("app.Expenses") }}',
                    data: {!! json_encode(array_column($yearlyExpenseTrend, 'amount')) !!},
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
                    label: '{{ __("app.Net Profit") }}',
                    data: {!! json_encode(array_column($yearlyProfitTrend, 'amount')) !!},
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
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false
                },
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
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return new Intl.NumberFormat('ro-RO', {
                                notation: 'compact',
                                compactDisplay: 'short'
                            }).format(value);
                        }
                    },
                    grid: {
                        color: 'rgba(226, 232, 240, 0.5)',
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        color: 'rgb(71, 85, 105)',
                        font: {
                            size: 11,
                            weight: '500'
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
@endpush
