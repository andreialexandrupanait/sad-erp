<x-app-layout>
    <x-slot name="pageTitle">{{ __('Yearly Objectives') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-slate-900">{{ __('Yearly Objectives') }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ __('Set budget limits and targets to track your financial progress') }}</p>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                        <div class="font-semibold mb-2">{{ __('Please fix the following errors') }}:</div>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('settings.yearly-objectives.update') }}">
                    @csrf

                    <!-- Expense Budget -->
                    <div class="bg-white rounded-lg border border-slate-200 mb-6 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Expense Budget') }}</h3>
                                <p class="text-sm text-slate-500">{{ __('Set maximum expense limits for the year') }}</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="expense_budget_ron" class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Yearly Expense Budget (RON)') }}
                                    </label>
                                    <div class="relative">
                                        <input type="number"
                                               id="expense_budget_ron"
                                               name="expense_budget_ron"
                                               value="{{ $budgetThresholds['expense_budget_ron'] }}"
                                               step="100"
                                               min="0"
                                               placeholder="ex: 50000"
                                               class="w-full pl-12 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-slate-500 font-medium">RON</span>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ __('Alert when yearly expenses exceed this amount') }}</p>
                                </div>
                                <div>
                                    <label for="expense_budget_eur" class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Yearly Expense Budget (EUR)') }}
                                    </label>
                                    <div class="relative">
                                        <input type="number"
                                               id="expense_budget_eur"
                                               name="expense_budget_eur"
                                               value="{{ $budgetThresholds['expense_budget_eur'] }}"
                                               step="100"
                                               min="0"
                                               placeholder="ex: 10000"
                                               class="w-full pl-12 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-slate-500 font-medium">EUR</span>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ __('Alert when yearly expenses in EUR exceed this amount') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Target -->
                    <div class="bg-white rounded-lg border border-slate-200 mb-6 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Revenue Target') }}</h3>
                                <p class="text-sm text-slate-500">{{ __('Set revenue goals for the year') }}</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="revenue_target_ron" class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Yearly Revenue Target (RON)') }}
                                    </label>
                                    <div class="relative">
                                        <input type="number"
                                               id="revenue_target_ron"
                                               name="revenue_target_ron"
                                               value="{{ $budgetThresholds['revenue_target_ron'] }}"
                                               step="100"
                                               min="0"
                                               placeholder="ex: 200000"
                                               class="w-full pl-12 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-slate-500 font-medium">RON</span>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ __('Track progress towards your revenue goal') }}</p>
                                </div>
                                <div>
                                    <label for="revenue_target_eur" class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Yearly Revenue Target (EUR)') }}
                                    </label>
                                    <div class="relative">
                                        <input type="number"
                                               id="revenue_target_eur"
                                               name="revenue_target_eur"
                                               value="{{ $budgetThresholds['revenue_target_eur'] }}"
                                               step="100"
                                               min="0"
                                               placeholder="ex: 40000"
                                               class="w-full pl-12 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-slate-500 font-medium">EUR</span>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ __('Track progress towards your EUR revenue goal') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profit Margin -->
                    <div class="bg-white rounded-lg border border-slate-200 mb-6 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Profit Margin') }}</h3>
                                <p class="text-sm text-slate-500">{{ __('Set minimum acceptable profit margin') }}</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="profit_margin_min" class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Minimum Profit Margin (%)') }}
                                    </label>
                                    <div class="relative">
                                        <input type="number"
                                               id="profit_margin_min"
                                               name="profit_margin_min"
                                               value="{{ $budgetThresholds['profit_margin_min'] }}"
                                               step="0.1"
                                               min="0"
                                               max="100"
                                               placeholder="ex: 20"
                                               class="w-full pl-4 pr-12 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-slate-500 font-medium">%</span>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ __('Alert when profit margin falls below this percentage') }}</p>
                                </div>
                                <div class="flex items-end">
                                    <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 w-full">
                                        <p class="text-sm text-slate-600">
                                            <strong>{{ __('Tip:') }}</strong> {{ __('A healthy profit margin for service businesses is typically 15-25%. Set your minimum based on your business costs and goals.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-1">{{ __('How it works') }}</p>
                                <ul class="list-disc list-inside space-y-1 text-blue-700">
                                    <li>{{ __('These thresholds will be displayed on your Financial Dashboard') }}</li>
                                    <li>{{ __('Visual indicators will show your progress towards each goal') }}</li>
                                    <li>{{ __('Alerts appear when you exceed expense budgets or fall below profit margins') }}</li>
                                    <li>{{ __('Leave fields empty to disable specific tracking') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('financial.dashboard') }}"
                           class="px-6 py-2.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                            {{ __('Go to Dashboard') }}
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('Save Objectives') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
