<x-app-layout>
    <x-slot name="pageTitle">{{ __('Istoric Financiar') }}</x-slot>

    <x-slot name="headerActions">
        <div class="flex items-center gap-3 no-print">
            <x-ui.button variant="outline" onclick="window.location.href='{{ route('financial.history.export') }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('Export CSV') }}
            </x-ui.button>
            <x-ui.button variant="outline" onclick="window.print()">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                {{ __('Print / PDF') }}
            </x-ui.button>
            <x-ui.button variant="default" onclick="window.location.href='{{ route('financial.dashboard') }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                {{ __('Dashboard') }}
            </x-ui.button>
        </div>
    </x-slot>

    <div class="p-4 md:p-6 space-y-4 md:space-y-6">

        {{-- Section 1: KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {{-- Total Revenue --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500 uppercase">{{ __('Venituri Total') }}</span>
                    <div class="p-1.5 bg-green-50 rounded-lg">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xl font-bold text-green-600">{{ number_format($totals['revenue_ron'], 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500">RON</p>
                @if($totals['revenue_eur'] > 0)
                    <p class="text-xs text-slate-400 mt-1">+ {{ number_format($totals['revenue_eur'], 0, ',', '.') }} EUR</p>
                @endif
            </div>

            {{-- Total Expenses --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500 uppercase">{{ __('Cheltuieli Total') }}</span>
                    <div class="p-1.5 bg-red-50 rounded-lg">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xl font-bold text-red-600">{{ number_format($totals['expense_ron'], 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500">RON</p>
                @if($totals['expense_eur'] > 0)
                    <p class="text-xs text-slate-400 mt-1">+ {{ number_format($totals['expense_eur'], 0, ',', '.') }} EUR</p>
                @endif
            </div>

            {{-- Net Profit --}}
            <div class="bg-gradient-to-br {{ $totals['profit_ron'] >= 0 ? 'from-emerald-500 to-green-600' : 'from-red-500 to-rose-600' }} rounded-xl shadow-sm p-4 text-white">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-white/80 uppercase">{{ __('Profit Net') }}</span>
                    <div class="p-1.5 bg-white/20 rounded-lg">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xl font-bold">{{ number_format($totals['profit_ron'], 0, ',', '.') }}</p>
                <p class="text-xs text-white/80">RON</p>
            </div>

            {{-- Profit Margin --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500 uppercase">{{ __('Marjă Profit') }}</span>
                    <div class="p-1.5 bg-purple-50 rounded-lg">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xl font-bold {{ $totals['margin_percent'] >= 20 ? 'text-green-600' : ($totals['margin_percent'] >= 10 ? 'text-yellow-600' : 'text-red-600') }}">{{ $totals['margin_percent'] }}%</p>
                <p class="text-xs text-slate-500">{{ __('din venituri') }}</p>
            </div>

            {{-- Clients Count --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500 uppercase">{{ __('Clienți') }}</span>
                    <div class="p-1.5 bg-blue-50 rounded-lg">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xl font-bold text-slate-900">{{ $totals['client_count'] }}</p>
                <p class="text-xs text-slate-500">{{ __('unici') }}</p>
            </div>

            {{-- Invoices Count --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500 uppercase">{{ __('Facturi') }}</span>
                    <div class="p-1.5 bg-indigo-50 rounded-lg">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xl font-bold text-slate-900">{{ $totals['invoice_count'] }}</p>
                <p class="text-xs text-slate-500">{{ __('total') }}</p>
            </div>
        </div>

        {{-- Section 2: Smart Insights Panel --}}
        <div x-data="{ open: true }" class="bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 rounded-xl border border-indigo-100 overflow-hidden">
            <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-100 rounded-lg">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <span class="font-semibold text-slate-900">{{ __('Analiză Inteligentă') }}</span>
                </div>
                <svg :class="{ 'rotate-180': open }" class="w-5 h-5 text-slate-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-collapse class="px-4 pb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Best Year --}}
                    <div class="bg-white/60 backdrop-blur rounded-lg p-3 border border-white/50">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            <span class="text-xs font-medium text-slate-600">{{ __('Cel mai profitabil an') }}</span>
                        </div>
                        <p class="text-lg font-bold text-slate-900">{{ $analytics['best_year'] }}</p>
                        <p class="text-sm text-green-600">+{{ number_format($analytics['best_year_profit'], 0, ',', '.') }} RON</p>
                    </div>

                    {{-- Top Client --}}
                    @if($analytics['top_clients']->isNotEmpty())
                    <div class="bg-white/60 backdrop-blur rounded-lg p-3 border border-white/50">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-xs font-medium text-slate-600">{{ __('Client principal') }}</span>
                        </div>
                        <p class="text-lg font-bold text-slate-900 truncate">{{ $analytics['top_clients']->first()?->client?->name ?? '-' }}</p>
                        <p class="text-sm text-blue-600">{{ $analytics['top_client_percentage'] }}% {{ __('din venituri') }}</p>
                    </div>
                    @endif

                    {{-- Client Dependency Risk --}}
                    @if($analytics['client_dependency_risk'])
                    <div class="bg-white/60 backdrop-blur rounded-lg p-3 border border-amber-200">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span class="text-xs font-medium text-amber-700">{{ __('Risc de Dependență') }}</span>
                        </div>
                        <p class="text-sm text-amber-800">{{ __('Un singur client reprezintă peste 30% din venituri') }}</p>
                    </div>
                    @endif

                    {{-- Expense Spikes --}}
                    @if($analytics['expense_spikes']->isNotEmpty())
                    <div class="bg-white/60 backdrop-blur rounded-lg p-3 border border-white/50">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            </svg>
                            <span class="text-xs font-medium text-slate-600">{{ __('Vârf de cheltuieli') }}</span>
                        </div>
                        @php $spike = $analytics['expense_spikes']->first(); @endphp
                        <p class="text-lg font-bold text-slate-900">{{ \Carbon\Carbon::create()->month($spike['month'])->translatedFormat('F') }} {{ $spike['year'] }}</p>
                        <p class="text-sm text-red-600">{{ $spike['multiplier'] }}x {{ __('peste medie') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section 3: Multi-Year Comparison Chart --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Evoluție Anuală') }}</h3>
                <div class="flex items-center gap-4 text-sm">
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 bg-green-500 rounded"></span>
                        {{ __('Venituri') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 bg-red-500 rounded"></span>
                        {{ __('Cheltuieli') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 bg-purple-500 rounded"></span>
                        {{ __('Profit') }}
                    </span>
                </div>
            </div>
            <div style="height: 300px;">
                <canvas id="multiYearChart"></canvas>
            </div>
        </div>

        {{-- Section 4: Yearly Summary Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-100">
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Rezumat pe Ani') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('An') }}</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Venituri RON') }}</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Cheltuieli RON') }}</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Profit RON') }}</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Marjă %') }}</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Creștere YoY') }}</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Clienți') }}</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Facturi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($availableYears as $year)
                            @php $data = $yearlySummary[$year]; @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900">{{ $year }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-700 font-semibold">{{ number_format($data['revenue_ron'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-700 font-semibold">{{ number_format($data['expense_ron'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $data['profit_ron'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ number_format($data['profit_ron'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $data['margin_percent'] >= 20 ? 'bg-green-100 text-green-800' : ($data['margin_percent'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $data['margin_percent'] }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    @if($data['yoy_growth'] !== null)
                                        <span class="inline-flex items-center gap-1 {{ $data['yoy_growth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            @if($data['yoy_growth'] >= 0)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                                </svg>
                                            @endif
                                            {{ $data['yoy_growth'] > 0 ? '+' : '' }}{{ $data['yoy_growth'] }}%
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-slate-600">{{ $data['client_count'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-slate-600">{{ $data['invoice_count'] }}</td>
                            </tr>
                        @endforeach
                        {{-- Totals Row --}}
                        <tr class="bg-slate-100 font-bold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900">TOTAL</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-700">{{ number_format($totals['revenue_ron'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-700">{{ number_format($totals['expense_ron'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $totals['profit_ron'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">{{ number_format($totals['profit_ron'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $totals['margin_percent'] >= 20 ? 'bg-green-100 text-green-800' : ($totals['margin_percent'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $totals['margin_percent'] }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-slate-400">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-slate-700">{{ $totals['client_count'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-slate-700">{{ $totals['invoice_count'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 5: Detailed Breakdowns --}}
        <div x-data="{ activeTab: 'clients' }" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="border-b border-slate-200">
                <nav class="flex -mb-px">
                    <button @click="activeTab = 'clients'" :class="activeTab === 'clients' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors">
                        {{ __('Top Clienți') }}
                    </button>
                    <button @click="activeTab = 'categories'" :class="activeTab === 'categories' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors">
                        {{ __('Categorii Cheltuieli') }}
                    </button>
                </nav>
            </div>

            {{-- Top Clients Tab --}}
            <div x-show="activeTab === 'clients'" class="p-4 md:p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Top Clients List --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 mb-4">{{ __('Top 10 Clienți după Venituri (toate timpurile)') }}</h4>
                        <div class="space-y-3">
                            @foreach($analytics['top_clients'] as $index => $clientData)
                                @php
                                    $percentage = $totals['revenue_ron'] > 0 ? round(($clientData->total / $totals['revenue_ron']) * 100, 1) : 0;
                                @endphp
                                <div class="flex items-center gap-3">
                                    <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-slate-100 rounded-full text-xs font-medium text-slate-600">{{ $index + 1 }}</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-sm font-medium text-slate-900 truncate">{{ $clientData->client?->name ?? __('Necunoscut') }}</span>
                                            <span class="text-sm font-semibold text-green-600">{{ number_format($clientData->total, 0, ',', '.') }} RON</span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs text-slate-500">{{ $percentage }}% {{ __('din total') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Client Distribution Chart --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 mb-4">{{ __('Distribuție Venituri') }}</h4>
                        <div style="height: 300px;">
                            <canvas id="clientDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Expense Categories Tab --}}
            <div x-show="activeTab === 'categories'" class="p-4 md:p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Categories List --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 mb-4">{{ __('Top Categorii Cheltuieli (toate timpurile)') }}</h4>
                        <div class="space-y-3">
                            @foreach($analytics['expense_by_category'] as $index => $categoryData)
                                @php
                                    $percentage = $totals['expense_ron'] > 0 ? round(($categoryData->total / $totals['expense_ron']) * 100, 1) : 0;
                                @endphp
                                <div class="flex items-center gap-3">
                                    <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-slate-100 rounded-full text-xs font-medium text-slate-600">{{ $index + 1 }}</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-sm font-medium text-slate-900 truncate">{{ $categoryData->category?->name ?? __('Necategorizat') }}</span>
                                            <span class="text-sm font-semibold text-red-600">{{ number_format($categoryData->total, 0, ',', '.') }} RON</span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-2">
                                            <div class="bg-red-500 h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs text-slate-500">{{ $percentage }}% {{ __('din total') }} ({{ $categoryData->count }} {{ __('tranzacții') }})</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Category Distribution Chart --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-700 mb-4">{{ __('Distribuție Cheltuieli') }}</h4>
                        <div style="height: 300px;">
                            <canvas id="categoryDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Multi-Year Comparison Chart
            const multiYearCtx = document.getElementById('multiYearChart').getContext('2d');
            new Chart(multiYearCtx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [
                        {
                            label: '{{ __("Venituri") }}',
                            data: @json($chartData['revenues']),
                            backgroundColor: 'rgba(34, 197, 94, 0.8)',
                            borderColor: 'rgb(34, 197, 94)',
                            borderWidth: 1,
                            borderRadius: 4,
                            order: 2
                        },
                        {
                            label: '{{ __("Cheltuieli") }}',
                            data: @json($chartData['expenses']),
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1,
                            borderRadius: 4,
                            order: 3
                        },
                        {
                            label: '{{ __("Profit") }}',
                            data: @json($chartData['profits']),
                            type: 'line',
                            backgroundColor: 'rgba(139, 92, 246, 0.2)',
                            borderColor: 'rgb(139, 92, 246)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgb(139, 92, 246)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            order: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(148, 163, 184, 0.3)',
                            borderWidth: 1,
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + new Intl.NumberFormat('ro-RO').format(context.parsed.y) + ' RON';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('ro-RO', { notation: 'compact' }).format(value);
                                }
                            },
                            grid: {
                                color: 'rgba(226, 232, 240, 0.5)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Client Distribution Doughnut Chart
            const clientCtx = document.getElementById('clientDistributionChart').getContext('2d');
            const clientLabels = @json($analytics['top_clients']->take(5)->map(fn($c) => $c->client?->name ?? 'Necunoscut'));
            const clientValues = @json($analytics['top_clients']->take(5)->pluck('total'));

            new Chart(clientCtx, {
                type: 'doughnut',
                data: {
                    labels: clientLabels,
                    datasets: [{
                        data: clientValues,
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(249, 115, 22, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + new Intl.NumberFormat('ro-RO').format(context.parsed) + ' RON (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });

            // Category Distribution Doughnut Chart
            const categoryCtx = document.getElementById('categoryDistributionChart').getContext('2d');
            const categoryLabels = @json($analytics['expense_by_category']->take(5)->map(fn($c) => $c->category?->name ?? 'Necategorizat'));
            const categoryValues = @json($analytics['expense_by_category']->take(5)->pluck('total'));

            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryValues,
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(249, 115, 22, 0.8)',
                            'rgba(234, 179, 8, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(236, 72, 153, 0.8)'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + new Intl.NumberFormat('ro-RO').format(context.parsed) + ' RON (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush

    @push('styles')
    <style>
        @media print {
            /* Hide UI elements */
            header, nav, .no-print,
            button, .sidebar,
            .pagination, .filters,
            [onclick*="print"],
            [onclick*="export"] {
                display: none !important;
            }

            /* Show only content */
            body {
                background: white !important;
                margin: 0;
                padding: 20px;
            }

            /* Optimize for print */
            .container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Preserve chart quality */
            canvas {
                max-width: 100% !important;
                page-break-inside: avoid;
            }

            /* Tables */
            table {
                page-break-inside: avoid;
                width: 100%;
            }

            /* KPI Cards */
            .grid {
                display: block !important;
            }

            /* Page breaks */
            h2, h3 {
                page-break-after: avoid;
            }

            /* Print colors */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Keep gradient backgrounds readable */
            .bg-gradient-to-br {
                background: #f0fdf4 !important;
            }
        }
    </style>
    @endpush
</x-app-layout>
