<x-app-layout>
    <x-slot name="pageTitle">{{ __('Settings') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-slate-900">{{ __('Nomenclature') }}</h1>
                    <p class="text-slate-500 mt-1">{{ __('Manage lookup tables, statuses, and categories used throughout the application') }}</p>
                </div>

                <!-- Statuses Section -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('Statuses') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Client Statuses -->
                        <a href="{{ route('settings.client-statuses') }}" class="bg-white rounded-lg border border-slate-200 p-4 hover:shadow-md hover:border-slate-300 transition-all group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-slate-900 group-hover:text-blue-600 transition-colors">{{ __('Client Statuses') }}</h3>
                                    <p class="text-sm text-slate-500 mt-1">{{ $counts['client_statuses'] ?? 0 }} {{ __('items') }}</p>
                                </div>
                                <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>

                        <!-- Domain Statuses -->
                        <a href="{{ route('settings.domain-statuses') }}" class="bg-white rounded-lg border border-slate-200 p-4 hover:shadow-md hover:border-slate-300 transition-all group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-slate-900 group-hover:text-blue-600 transition-colors">{{ __('Domain Statuses') }}</h3>
                                    <p class="text-sm text-slate-500 mt-1">{{ $counts['domain_statuses'] ?? 0 }} {{ __('items') }}</p>
                                </div>
                                <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>

                        <!-- Subscription Statuses -->
                        <a href="{{ route('settings.subscription-statuses') }}" class="bg-white rounded-lg border border-slate-200 p-4 hover:shadow-md hover:border-slate-300 transition-all group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-slate-900 group-hover:text-blue-600 transition-colors">{{ __('Subscription Statuses') }}</h3>
                                    <p class="text-sm text-slate-500 mt-1">{{ $counts['subscription_statuses'] ?? 0 }} {{ __('items') }}</p>
                                </div>
                                <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Categories Section -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        {{ __('Categories') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Platform Categories -->
                        <a href="{{ route('settings.access-platforms') }}" class="bg-white rounded-lg border border-slate-200 p-4 hover:shadow-md hover:border-slate-300 transition-all group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-slate-900 group-hover:text-emerald-600 transition-colors">{{ __('Platform Categories') }}</h3>
                                    <p class="text-sm text-slate-500 mt-1">{{ $counts['access_platforms'] ?? 0 }} {{ __('items') }}</p>
                                </div>
                                <svg class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>

                        <!-- Expense Categories -->
                        <a href="{{ route('settings.expense-categories') }}" class="bg-white rounded-lg border border-slate-200 p-4 hover:shadow-md hover:border-slate-300 transition-all group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-slate-900 group-hover:text-emerald-600 transition-colors">{{ __('Expense Categories') }}</h3>
                                    <p class="text-sm text-slate-500 mt-1">{{ $counts['expense_categories'] ?? 0 }} {{ __('items') }}</p>
                                </div>
                                <svg class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Financial Section -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('Financial') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Payment Methods -->
                        <a href="{{ route('settings.payment-methods') }}" class="bg-white rounded-lg border border-slate-200 p-4 hover:shadow-md hover:border-slate-300 transition-all group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-slate-900 group-hover:text-purple-600 transition-colors">{{ __('Payment Methods') }}</h3>
                                    <p class="text-sm text-slate-500 mt-1">{{ $counts['payment_methods'] ?? 0 }} {{ __('items') }}</p>
                                </div>
                                <svg class="w-5 h-5 text-slate-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>

                        <!-- Billing Cycles -->
                        <a href="{{ route('settings.billing-cycles') }}" class="bg-white rounded-lg border border-slate-200 p-4 hover:shadow-md hover:border-slate-300 transition-all group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-slate-900 group-hover:text-purple-600 transition-colors">{{ __('Billing Cycles') }}</h3>
                                    <p class="text-sm text-slate-500 mt-1">{{ $counts['billing_cycles'] ?? 0 }} {{ __('items') }}</p>
                                </div>
                                <svg class="w-5 h-5 text-slate-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>

                        <!-- Currencies -->
                        <a href="{{ route('settings.currencies') }}" class="bg-white rounded-lg border border-slate-200 p-4 hover:shadow-md hover:border-slate-300 transition-all group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-slate-900 group-hover:text-purple-600 transition-colors">{{ __('Currencies') }}</h3>
                                    <p class="text-sm text-slate-500 mt-1">{{ $counts['currencies'] ?? 0 }} {{ __('items') }}</p>
                                </div>
                                <svg class="w-5 h-5 text-slate-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Domains Section -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                        {{ __('Domains') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Domain Registrars -->
                        <a href="{{ route('settings.domain-registrars') }}" class="bg-white rounded-lg border border-slate-200 p-4 hover:shadow-md hover:border-slate-300 transition-all group">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-slate-900 group-hover:text-amber-600 transition-colors">{{ __('Domain Registrars') }}</h3>
                                    <p class="text-sm text-slate-500 mt-1">{{ $counts['domain_registrars'] ?? 0 }} {{ __('items') }}</p>
                                </div>
                                <svg class="w-5 h-5 text-slate-400 group-hover:text-amber-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
