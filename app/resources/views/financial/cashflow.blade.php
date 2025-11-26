<x-app-layout>
    <x-slot name="pageTitle">{{ __('Cashflow') }} {{ $year }}</x-slot>

    <x-slot name="headerActions">
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2">
                <label class="text-sm font-medium text-slate-700">{{ __('An') }}:</label>
                <x-ui.select name="year" onchange="this.form.submit()">
                    @foreach($availableYears as $availableYear)
                        <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                    @endforeach
                </x-ui.select>
            </form>

            <x-ui.button variant="outline" onclick="window.print()">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                {{ __('Print') }}
            </x-ui.button>

            <x-ui.button variant="default" onclick="window.location.href='{{ route('financial.dashboard') }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                {{ __('Dashboard') }}
            </x-ui.button>
        </div>
    </x-slot>

    <div class="p-6 space-y-6">

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {{-- Total Inflows --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500 uppercase">{{ __('Total Intrări') }}</span>
                    <div class="p-1.5 bg-green-50 rounded-lg">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xl font-bold text-green-600">{{ number_format($totals['revenue_ron'], 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500">RON</p>
                @if($totals['revenue_eur'] > 0)
                    <p class="text-xs text-slate-400 mt-1">+ {{ number_format($totals['revenue_eur'], 0, ',', '.') }} EUR</p>
                @endif
            </div>

            {{-- Total Outflows --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500 uppercase">{{ __('Total Ieșiri') }}</span>
                    <div class="p-1.5 bg-red-50 rounded-lg">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xl font-bold text-red-600">{{ number_format($totals['expense_ron'], 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500">RON</p>
                @if($totals['expense_eur'] > 0)
                    <p class="text-xs text-slate-400 mt-1">+ {{ number_format($totals['expense_eur'], 0, ',', '.') }} EUR</p>
                @endif
            </div>

            {{-- Net Cashflow --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500 uppercase">{{ __('Cashflow Net') }}</span>
                    <div class="p-1.5 {{ $totals['net_ron'] >= 0 ? 'bg-blue-50' : 'bg-orange-50' }} rounded-lg">
                        <svg class="w-4 h-4 {{ $totals['net_ron'] >= 0 ? 'text-blue-600' : 'text-orange-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xl font-bold {{ $totals['net_ron'] >= 0 ? 'text-blue-600' : 'text-orange-600' }}">
                    {{ $totals['net_ron'] >= 0 ? '+' : '' }}{{ number_format($totals['net_ron'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-500">RON</p>
                @if($totals['net_eur'] != 0)
                    <p class="text-xs text-slate-400 mt-1">{{ $totals['net_eur'] >= 0 ? '+' : '' }}{{ number_format($totals['net_eur'], 0, ',', '.') }} EUR</p>
                @endif
            </div>

            {{-- Avg Monthly Inflow --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500 uppercase">{{ __('Medie/Lună Intrări') }}</span>
                </div>
                <p class="text-xl font-bold text-slate-700">{{ number_format($totals['revenue_ron'] / 12, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500">RON</p>
            </div>

            {{-- Avg Monthly Outflow --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500 uppercase">{{ __('Medie/Lună Ieșiri') }}</span>
                </div>
                <p class="text-xl font-bold text-slate-700">{{ number_format($totals['expense_ron'] / 12, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500">RON</p>
            </div>

            {{-- Profit Margin --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500 uppercase">{{ __('Marjă Profit') }}</span>
                </div>
                @php
                    $margin = $totals['revenue_ron'] > 0 ? ($totals['net_ron'] / $totals['revenue_ron']) * 100 : 0;
                @endphp
                <p class="text-xl font-bold {{ $margin >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($margin, 1) }}%</p>
                <p class="text-xs text-slate-500">{{ $margin >= 20 ? __('Sănătos') : ($margin >= 0 ? __('Moderat') : __('Atenție')) }}</p>
            </div>
        </div>

        {{-- Cashflow Chart --}}
        <x-ui.card>
            <x-ui.card-header>
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Evoluție Cashflow') }} {{ $year }}</h3>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="h-80">
                    <canvas id="cashflowChart"></canvas>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Monthly Breakdown Table --}}
        <x-ui.card>
            <x-ui.card-header>
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Detalii Lunare') }}</h3>
            </x-ui.card-header>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Luna') }}</th>
                            <th class="px-4 py-3 text-right font-semibold text-green-700">{{ __('Intrări RON') }}</th>
                            <th class="px-4 py-3 text-right font-semibold text-red-700">{{ __('Ieșiri RON') }}</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-700">{{ __('Net RON') }}</th>
                            <th class="px-4 py-3 text-right font-semibold text-blue-700">{{ __('Sold Cumulat') }}</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-500">{{ __('Tranzacții') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($cashflowData as $row)
                            <tr class="{{ $row['is_current_month'] ? 'bg-blue-50' : ($row['is_future'] ? 'opacity-50' : 'hover:bg-slate-50') }}">
                                <td class="px-4 py-3">
                                    <a href="{{ route('financial.revenues.index', ['year' => $year, 'month' => $row['month']]) }}" class="font-medium text-slate-900 hover:text-blue-600">
                                        {{ $row['month_name'] }}
                                        @if($row['is_current_month'])
                                            <span class="ml-2 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ __('Luna curentă') }}</span>
                                        @endif
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($row['revenue_ron'] > 0)
                                        <span class="text-green-600 font-medium">+{{ number_format($row['revenue_ron'], 0, ',', '.') }}</span>
                                        @if($row['revenue_eur'] > 0)
                                            <span class="text-xs text-slate-400 block">+{{ number_format($row['revenue_eur'], 0, ',', '.') }} EUR</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($row['expense_ron'] > 0)
                                        <span class="text-red-600 font-medium">-{{ number_format($row['expense_ron'], 0, ',', '.') }}</span>
                                        @if($row['expense_eur'] > 0)
                                            <span class="text-xs text-slate-400 block">-{{ number_format($row['expense_eur'], 0, ',', '.') }} EUR</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-semibold {{ $row['net_ron'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $row['net_ron'] >= 0 ? '+' : '' }}{{ number_format($row['net_ron'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-semibold {{ $row['balance_ron'] >= 0 ? 'text-blue-600' : 'text-orange-600' }}">
                                        {{ number_format($row['balance_ron'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-slate-500">
                                        {{ $row['revenue_count'] }} / {{ $row['expense_count'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-100 border-t-2 border-slate-300">
                        <tr class="font-bold">
                            <td class="px-4 py-3 text-slate-900">{{ __('TOTAL') }} {{ $year }}</td>
                            <td class="px-4 py-3 text-right text-green-700">+{{ number_format($totals['revenue_ron'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-red-700">-{{ number_format($totals['expense_ron'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right {{ $totals['net_ron'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                {{ $totals['net_ron'] >= 0 ? '+' : '' }}{{ number_format($totals['net_ron'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-blue-700">-</td>
                            <td class="px-4 py-3 text-center text-slate-500">-</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-ui.card>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('cashflowChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [
                        {
                            label: '{{ __("Intrări") }}',
                            data: @json($chartData['revenues']),
                            backgroundColor: 'rgba(34, 197, 94, 0.7)',
                            borderColor: 'rgb(34, 197, 94)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: '{{ __("Ieșiri") }}',
                            data: @json($chartData['expenses']),
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: '{{ __("Sold Cumulat") }}',
                            data: @json($chartData['balance']),
                            type: 'line',
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y1',
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
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += new Intl.NumberFormat('ro-RO').format(context.parsed.y) + ' RON';
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: '{{ __("Intrări / Ieșiri (RON)") }}'
                            },
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('ro-RO', { notation: 'compact' }).format(value);
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: '{{ __("Sold Cumulat (RON)") }}'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('ro-RO', { notation: 'compact' }).format(value);
                                }
                            }
                        },
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
