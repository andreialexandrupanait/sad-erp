<x-app-layout>
    <x-slot name="pageTitle">{{ __('Settings') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-slate-900">{{ __('Nomenclatoare') }}</h1>
                    <p class="text-slate-500 mt-1">{{ __('Gestionează tabelele de referință, statusurile și categoriile folosite în aplicație') }}</p>
                </div>

                <!-- Statusuri -->
                <div class="bg-white rounded-[10px] border border-slate-200 overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-slate-100 border-b border-slate-200 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Statusuri') }}</h3>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <a href="{{ route('settings.client-statuses') }}" class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors group">
                            <div>
                                <h3 class="font-medium text-slate-900 group-hover:text-blue-600 transition-colors">{{ __('Status clienți') }}</h3>
                                <p class="text-sm text-slate-500">{{ $counts['client_statuses'] ?? 0 }} {{ __('elemente') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('settings.domain-statuses') }}" class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors group">
                            <div>
                                <h3 class="font-medium text-slate-900 group-hover:text-blue-600 transition-colors">{{ __('Status domenii') }}</h3>
                                <p class="text-sm text-slate-500">{{ $counts['domain_statuses'] ?? 0 }} {{ __('elemente') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('settings.subscription-statuses') }}" class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors group">
                            <div>
                                <h3 class="font-medium text-slate-900 group-hover:text-blue-600 transition-colors">{{ __('Status abonamente') }}</h3>
                                <p class="text-sm text-slate-500">{{ $counts['subscription_statuses'] ?? 0 }} {{ __('elemente') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Financiar -->
                <div class="bg-white rounded-[10px] border border-slate-200 overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-slate-100 border-b border-slate-200 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Financiar') }}</h3>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <a href="{{ route('settings.expense-categories') }}" class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors group">
                            <div>
                                <h3 class="font-medium text-slate-900 group-hover:text-emerald-600 transition-colors">{{ __('Categorii cheltuieli') }}</h3>
                                <p class="text-sm text-slate-500">{{ $counts['expense_categories'] ?? 0 }} {{ __('elemente') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('settings.payment-methods') }}" class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors group">
                            <div>
                                <h3 class="font-medium text-slate-900 group-hover:text-emerald-600 transition-colors">{{ __('Metode de plată') }}</h3>
                                <p class="text-sm text-slate-500">{{ $counts['payment_methods'] ?? 0 }} {{ __('elemente') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('settings.billing-cycles') }}" class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors group">
                            <div>
                                <h3 class="font-medium text-slate-900 group-hover:text-emerald-600 transition-colors">{{ __('Cicluri de facturare') }}</h3>
                                <p class="text-sm text-slate-500">{{ $counts['billing_cycles'] ?? 0 }} {{ __('elemente') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('settings.currencies') }}" class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors group">
                            <div>
                                <h3 class="font-medium text-slate-900 group-hover:text-emerald-600 transition-colors">{{ __('Valute') }}</h3>
                                <p class="text-sm text-slate-500">{{ $counts['currencies'] ?? 0 }} {{ __('elemente') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Infrastructură -->
                <div class="bg-white rounded-[10px] border border-slate-200 overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-slate-100 border-b border-slate-200 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Infrastructură') }}</h3>
                    </div>
                    <div class="divide-y divide-slate-100">
                        <a href="{{ route('settings.domain-registrars') }}" class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors group">
                            <div>
                                <h3 class="font-medium text-slate-900 group-hover:text-purple-600 transition-colors">{{ __('Registratori de domenii') }}</h3>
                                <p class="text-sm text-slate-500">{{ $counts['domain_registrars'] ?? 0 }} {{ __('elemente') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('settings.access-platforms') }}" class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors group">
                            <div>
                                <h3 class="font-medium text-slate-900 group-hover:text-purple-600 transition-colors">{{ __('Categorii platforme') }}</h3>
                                <p class="text-sm text-slate-500">{{ $counts['access_platforms'] ?? 0 }} {{ __('elemente') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
