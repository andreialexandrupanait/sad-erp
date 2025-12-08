<x-app-layout>
    <x-slot name="pageTitle">{{ __('Financial Dashboard') }}</x-slot>

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
                {{ __('Add Revenue') }}
            </x-ui.button>
            <x-ui.button variant="default" onclick="window.location.href='{{ route('financial.expenses.create') }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Add Expense') }}
            </x-ui.button>
            <x-ui.button variant="outline" onclick="window.location.href='{{ route('financial.export', $year) }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                {{ __('Export CSV') }}
            </x-ui.button>
        </div>
    </x-slot>

    <div class="p-6 space-y-6">
        @php
            // Calculate budget status for alerts
            $alerts = [];

            // Expense budget alerts (yearly)
            if ($budgetThresholds['expense_budget_ron'] && $yearlyExpenseRON > $budgetThresholds['expense_budget_ron']) {
                $alerts[] = ['type' => 'danger', 'message' => __('Expense budget RON exceeded! :current / :budget', ['current' => number_format($yearlyExpenseRON, 0), 'budget' => number_format($budgetThresholds['expense_budget_ron'], 0)])];
            } elseif ($budgetThresholds['expense_budget_ron'] && $yearlyExpenseRON >= $budgetThresholds['expense_budget_ron'] * 0.8) {
                $alerts[] = ['type' => 'warning', 'message' => __('Approaching expense budget RON: :percent%', ['percent' => round(($yearlyExpenseRON / $budgetThresholds['expense_budget_ron']) * 100)])];
            }

            // Revenue target alerts (yearly)
            if ($budgetThresholds['revenue_target_ron'] && $yearlyRevenueRON < $budgetThresholds['revenue_target_ron'] * 0.5) {
                $alerts[] = ['type' => 'warning', 'message' => __('Revenue target RON at risk: :percent% achieved', ['percent' => round(($yearlyRevenueRON / $budgetThresholds['revenue_target_ron']) * 100)])];
            }

            // Profit margin alert
            if ($budgetThresholds['profit_margin_min'] && $profitMargin < $budgetThresholds['profit_margin_min']) {
                $alerts[] = ['type' => 'danger', 'message' => __('Profit margin below threshold: :current% (min: :min%)', ['current' => $profitMargin, 'min' => $budgetThresholds['profit_margin_min']])];
            }

            // Calculate progress percentages
            $expenseProgress = $budgetThresholds['expense_budget_ron'] ? min(100, ($yearlyExpenseRON / $budgetThresholds['expense_budget_ron']) * 100) : null;
            $revenueProgress = $budgetThresholds['revenue_target_ron'] ? min(100, ($yearlyRevenueRON / $budgetThresholds['revenue_target_ron']) * 100) : null;
        @endphp

        <!-- Budget Alert Banner -->
        @if(count($alerts) > 0)
        <div class="space-y-2">
            @foreach($alerts as $alert)
                <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-lg {{ $alert['type'] === 'danger' ? 'bg-red-50 border border-red-200' : 'bg-amber-50 border border-amber-200' }}">
                    <div class="flex items-center gap-3">
                        @if($alert['type'] === 'danger')
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        @endif
                        <span class="text-sm font-medium {{ $alert['type'] === 'danger' ? 'text-red-800' : 'text-amber-800' }}">{{ $alert['message'] }}</span>
                    </div>
                    <a href="{{ route('settings.application') }}#expense_budget_ron" class="text-xs {{ $alert['type'] === 'danger' ? 'text-red-600 hover:text-red-800' : 'text-amber-600 hover:text-amber-800' }} underline">{{ __('Edit thresholds') }}</a>
                </div>
            @endforeach
        </div>
        @elseif(!$budgetThresholds['expense_budget_ron'] && !$budgetThresholds['revenue_target_ron'] && !$budgetThresholds['profit_margin_min'])
        <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-lg bg-blue-50 border border-blue-200">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm text-blue-800">{{ __('Set budget thresholds to see visual alerts when you approach or exceed your limits.') }}</span>
            </div>
            <a href="{{ route('settings.application') }}#expense_budget_ron" class="text-xs text-blue-600 hover:text-blue-800 underline whitespace-nowrap">{{ __('Configure in Settings') }}</a>
        </div>
        @endif

        <!-- Widgets Container - 3 Widgets -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Widget 1: Venituri (RON + EUR) -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5 {{ $budgetThresholds['revenue_target_ron'] && $revenueProgress < 50 ? 'ring-2 ring-amber-300' : '' }}">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-700 uppercase">{{ __('Revenues') }}</h3>
                    <div class="flex items-center gap-2">
                        @if($budgetThresholds['revenue_target_ron'])
                            @if($revenueProgress >= 100)
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">{{ __('Target met') }}</span>
                            @elseif($revenueProgress >= 75)
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">{{ round($revenueProgress) }}%</span>
                            @endif
                        @endif
                        <div class="p-1.5 bg-green-50 rounded-lg">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="space-y-2.5">
                    <div>
                        <p class="text-xs text-slate-500 mb-0.5">RON</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($yearlyRevenueRON, 2) }} <span class="text-lg">RON</span></p>
                    </div>
                    @if($budgetThresholds['revenue_target_ron'])
                        <div class="pt-2">
                            <div class="flex justify-between text-xs text-slate-500 mb-1">
                                <span>{{ __('Target') }}: {{ number_format($budgetThresholds['revenue_target_ron'], 0) }} RON</span>
                                <span>{{ round($revenueProgress) }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full transition-all {{ $revenueProgress >= 100 ? 'bg-green-500' : ($revenueProgress >= 75 ? 'bg-blue-500' : 'bg-amber-500') }}" style="width: {{ $revenueProgress }}%"></div>
                            </div>
                        </div>
                    @endif
                    <div class="pt-2.5 border-t border-slate-100">
                        <p class="text-xs text-slate-500 mb-0.5">EUR</p>
                        <p class="text-base font-semibold text-slate-700">{{ number_format($yearlyRevenueEUR, 2) }} EUR</p>
                    </div>
                </div>
            </div>

            <!-- Widget 2: Cheltuieli (RON + EUR) -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5 {{ $budgetThresholds['expense_budget_ron'] && $expenseProgress >= 100 ? 'ring-2 ring-red-300' : ($budgetThresholds['expense_budget_ron'] && $expenseProgress >= 80 ? 'ring-2 ring-amber-300' : '') }}">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-700 uppercase">{{ __('Expenses') }}</h3>
                    <div class="flex items-center gap-2">
                        @if($budgetThresholds['expense_budget_ron'])
                            @if($expenseProgress >= 100)
                                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">{{ __('Over budget') }}</span>
                            @elseif($expenseProgress >= 80)
                                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">{{ round($expenseProgress) }}%</span>
                            @else
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">{{ round($expenseProgress) }}%</span>
                            @endif
                        @endif
                        <div class="p-1.5 bg-red-50 rounded-lg">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="space-y-2.5">
                    <div>
                        <p class="text-xs text-slate-500 mb-0.5">RON</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($yearlyExpenseRON, 2) }} <span class="text-lg">RON</span></p>
                    </div>
                    @if($budgetThresholds['expense_budget_ron'])
                        <div class="pt-2">
                            <div class="flex justify-between text-xs text-slate-500 mb-1">
                                <span>{{ __('Budget') }}: {{ number_format($budgetThresholds['expense_budget_ron'], 0) }} RON</span>
                                <span>{{ round($expenseProgress) }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full transition-all {{ $expenseProgress >= 100 ? 'bg-red-500' : ($expenseProgress >= 80 ? 'bg-amber-500' : 'bg-green-500') }}" style="width: {{ min(100, $expenseProgress) }}%"></div>
                            </div>
                        </div>
                    @endif
                    <div class="pt-2.5 border-t border-slate-100">
                        <p class="text-xs text-slate-500 mb-0.5">EUR</p>
                        <p class="text-base font-semibold text-slate-700">{{ number_format($yearlyExpenseEUR, 2) }} EUR</p>
                    </div>
                </div>
            </div>

            <!-- Widget 3: Profit Net (RON + EUR) -->
            <div class="bg-gradient-to-br {{ $yearlyProfitRON >= 0 ? 'from-emerald-500 to-green-600' : 'from-red-500 to-rose-600' }} rounded-lg shadow-sm border border-slate-200 p-5 {{ $budgetThresholds['profit_margin_min'] && $profitMargin < $budgetThresholds['profit_margin_min'] ? 'ring-2 ring-red-300' : '' }}">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-white uppercase">{{ __('Net Profit') }}</h3>
                    <div class="flex items-center gap-2">
                        @if($budgetThresholds['profit_margin_min'])
                            <span class="text-xs bg-white/20 text-white px-2 py-0.5 rounded-full font-medium">
                                {{ __('Margin') }}: {{ $profitMargin }}%
                                @if($profitMargin >= $budgetThresholds['profit_margin_min'])
                                    <svg class="w-3 h-3 inline ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>
                        @endif
                        <div class="p-1.5 bg-white/20 rounded-lg">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
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
                <h3 class="text-base font-semibold text-slate-900 mb-4">{{ __('Revenues (monthly)') }}</h3>
                <div style="height: 320px;">
                    {!! $revenueChart->container() !!}
                </div>
            </div>

            <!-- Expense Chart (RON Only) -->
            <div class="bg-white rounded-lg shadow border border-slate-200 p-6">
                <h3 class="text-base font-semibold text-slate-900 mb-4">{{ __('Expenses (monthly)') }}</h3>
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
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Monthly Summary (RON)') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Month') }}</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Revenues RON') }}</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Expenses RON') }}</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Profit RON') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($monthlyBreakdown as $data)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">{{ $data['month_name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-700 font-semibold">{{ number_format($data['revenue'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-700 font-semibold">{{ number_format($data['expense'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $data['profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($data['profit'], 2) }}</td>
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
