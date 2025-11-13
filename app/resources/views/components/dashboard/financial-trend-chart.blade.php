@props(['yearlyRevenueTrend', 'yearlyExpenseTrend', 'yearlyProfitTrend'])

<x-ui.card>
    <x-ui.card-header>
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900">{{ __('Tendință lunară') }}</h3>
            <div class="flex items-center gap-4 text-xs">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-slate-600">{{ __('Venituri') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <span class="text-slate-600">{{ __('Cheltuieli') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span class="text-slate-600">{{ __('Profit Net') }}</span>
                </div>
            </div>
        </div>
    </x-ui.card-header>
    <x-ui.card-content>
        <div style="height: 320px;">
            <canvas id="financialTrendChart"></canvas>
        </div>
    </x-ui.card-content>
</x-ui.card>

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
                    label: 'Venituri',
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
                    label: 'Cheltuieli',
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
                    label: 'Profit Net',
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
