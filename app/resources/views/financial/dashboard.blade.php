<x-app-layout>
    <x-slot name="pageTitle">Dashboard Financiar</x-slot>

    <x-slot name="headerActions">
        <div class="flex items-center gap-3">
            <form method="GET" id="filterForm" class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-700">{{ __('An') }}:</label>
                    <x-ui.select name="year" onchange="this.form.submit()">
                        @foreach($availableYears as $availableYear)
                            <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </form>

            <x-ui.button variant="default" onclick="window.location.href='{{ route('financial.revenues.create') }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Adaugă venit
            </x-ui.button>
            <x-ui.button variant="default" onclick="window.location.href='{{ route('financial.expenses.create') }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Adaugă cheltuială
            </x-ui.button>
            <x-ui.button variant="outline" onclick="window.location.href='{{ route('financial.export', $year) }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Exportă CSV
            </x-ui.button>
        </div>
    </x-slot>

    <div class="p-6 space-y-6">

        <!-- Widgets Container - 3 Widgets -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Widget 1: Venituri (RON + EUR) -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-700 uppercase">Venituri</h3>
                    <div class="p-1.5 bg-green-50 rounded-lg">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="space-y-2.5">
                    <div>
                        <p class="text-xs text-slate-500 mb-0.5">RON</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($yearlyRevenueRON, 2) }} <span class="text-lg">RON</span></p>
                    </div>
                    <div class="pt-2.5 border-t border-slate-100">
                        <p class="text-xs text-slate-500 mb-0.5">EUR</p>
                        <p class="text-base font-semibold text-slate-700">{{ number_format($yearlyRevenueEUR, 2) }} EUR</p>
                    </div>
                </div>
            </div>

            <!-- Widget 2: Cheltuieli (RON + EUR) -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-700 uppercase">Cheltuieli</h3>
                    <div class="p-1.5 bg-red-50 rounded-lg">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="space-y-2.5">
                    <div>
                        <p class="text-xs text-slate-500 mb-0.5">RON</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($yearlyExpenseRON, 2) }} <span class="text-lg">RON</span></p>
                    </div>
                    <div class="pt-2.5 border-t border-slate-100">
                        <p class="text-xs text-slate-500 mb-0.5">EUR</p>
                        <p class="text-base font-semibold text-slate-700">{{ number_format($yearlyExpenseEUR, 2) }} EUR</p>
                    </div>
                </div>
            </div>

            <!-- Widget 3: Profit Net (RON + EUR) -->
            <div class="bg-gradient-to-br {{ $yearlyProfitRON >= 0 ? 'from-emerald-500 to-green-600' : 'from-red-500 to-rose-600' }} rounded-lg shadow-sm border border-slate-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-white uppercase">Profit Net</h3>
                    <div class="p-1.5 bg-white/20 rounded-lg">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="space-y-2.5">
                    <div>
                        <p class="text-xs text-white/80 mb-0.5">RON</p>
                        <p class="text-2xl font-bold text-white">{{ number_format($yearlyProfitRON, 2) }} <span class="text-lg">RON</span></p>
                    </div>
                    <div class="pt-2.5 border-t border-white/20">
                        <p class="text-xs text-white/80 mb-0.5">EUR</p>
                        <p class="text-base font-semibold text-white">{{ number_format($yearlyProfitEUR, 2) }} EUR</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Revenue Chart (RON Only) -->
            <div class="bg-white rounded-lg shadow border border-slate-200 p-6">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Venituri (pe luni)</h3>
                <div style="height: 320px;">
                    {!! $revenueChart->container() !!}
                </div>
            </div>

            <!-- Expense Chart (RON Only) -->
            <div class="bg-white rounded-lg shadow border border-slate-200 p-6">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Cheltuieli (pe luni)</h3>
                <div style="height: 320px;">
                    {!! $expenseChart->container() !!}
                </div>
            </div>

            <!-- Expense Category Breakdown -->
            <x-dashboard.expense-category-chart :categoryData="$categoryBreakdown" :year="$year" />
        </div>

        <!-- Monthly Breakdown Table -->
        <x-ui.card>
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="text-lg font-semibold text-slate-900">Rezumat pe luni (RON)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Luna</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Venituri RON</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Cheltuieli RON</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Profit RON</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($monthlyBreakdown as $data)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">{{ $data['month_name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-700 font-semibold">{{ number_format($data['revenues_ron'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-700 font-semibold">{{ number_format($data['expenses_ron'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $data['profit_ron'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($data['profit_ron'], 2) }}</td>
                            </tr>
                        @endforeach
                        <!-- Totals Row -->
                        <tr class="bg-slate-100 font-bold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900">TOTAL</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-700">{{ number_format($yearlyRevenueRON, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-700">{{ number_format($yearlyExpenseRON, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $yearlyProfitRON >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($yearlyProfitRON, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    @push('scripts')
        <!-- Chart.js Library -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

        <!-- Render Charts -->
        {!! $revenueChart->script() !!}
        {!! $expenseChart->script() !!}

        <script>
        // Configure charts after they're created
        window.addEventListener('load', function() {
            const commonMaxValue = {{ $commonMaxValue ?? 0 }};
            const revenueChartId = "{{ $revenueChart->id }}";
            const expenseChartId = "{{ $expenseChart->id }}";

            // Access charts via their dynamic IDs from the window object
            const revenueChart = window[revenueChartId];
            const expenseChart = window[expenseChartId];

            [revenueChart, expenseChart].forEach(function(chart) {
                if (chart && chart.config && chart.config.type === 'bar') {
                    // Destroy and recreate with proper Chart.js v4 configuration
                    const ctx = chart.canvas.getContext('2d');
                    const labels = chart.data.labels;
                    const data = chart.data.datasets[0].data;
                    const backgroundColor = chart.data.datasets[0].backgroundColor;
                    const label = chart.data.datasets[0].label;

                    chart.destroy();

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: label,
                                data: data,
                                backgroundColor: backgroundColor
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    enabled: true,
                                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    borderColor: 'rgba(148, 163, 184, 0.3)',
                                    borderWidth: 1,
                                    padding: 12,
                                    displayColors: false,
                                    callbacks: {
                                        label: function(context) {
                                            return new Intl.NumberFormat('ro-RO', {
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
                                    max: commonMaxValue,
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
                }
            });
        });
        </script>
    @endpush
</x-app-layout>
