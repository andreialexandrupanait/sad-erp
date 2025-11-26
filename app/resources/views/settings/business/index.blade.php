<x-app-layout>
    <x-slot name="pageTitle">{{ __('Settings') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-slate-900">{{ __('Business Settings') }}</h1>
                    <p class="text-slate-500 mt-1">{{ __('Manage your company information, invoicing, and services') }}</p>
                </div>

                <!-- Settings List -->
                <div class="bg-white rounded-xl border border-slate-200 divide-y divide-slate-200">

                    <!-- Company Information -->
                    <a href="{{ route('settings.business-info') }}" class="flex items-center justify-between p-5 hover:bg-slate-50 transition-colors group">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-medium text-slate-900 group-hover:text-emerald-600 transition-colors">{{ __('Company Information') }}</h3>
                                <p class="text-sm text-slate-500">{{ __('Business name, address, tax ID, and contact details') }}</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <!-- Invoice Settings -->
                    <a href="{{ route('settings.invoice-settings') }}" class="flex items-center justify-between p-5 hover:bg-slate-50 transition-colors group">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-medium text-slate-900 group-hover:text-blue-600 transition-colors">{{ __('Invoice Settings') }}</h3>
                                <p class="text-sm text-slate-500">{{ __('Invoice numbering, templates, and defaults') }}</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <!-- Services Catalog -->
                    <a href="{{ route('settings.services') }}" class="flex items-center justify-between p-5 hover:bg-slate-50 transition-colors group">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-medium text-slate-900 group-hover:text-purple-600 transition-colors">{{ __('Services Catalog') }}</h3>
                                <p class="text-sm text-slate-500">{{ __('Manage billable services and default hourly rates') }}</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-purple-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
