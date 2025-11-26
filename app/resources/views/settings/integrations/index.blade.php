<x-app-layout>
    <x-slot name="pageTitle">{{ __('Settings') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-slate-900">{{ __('Integrations') }}</h1>
                    <p class="text-slate-500 mt-1">{{ __('Connect your favorite tools and services') }}</p>
                </div>

                <!-- Integrations Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- SmartBill -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">SmartBill</h3>
                                    <p class="text-sm text-slate-500">{{ __('Invoice import and sync') }}</p>
                                </div>
                            </div>
                            @if(config('services.smartbill.token'))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('Connected') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                    {{ __('Not configured') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-600 mb-4">{{ __('Import invoices from SmartBill and automatically download PDF documents.') }}</p>
                        <a href="{{ route('settings.smartbill.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700">
                            {{ __('Configure') }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <!-- ClickUp -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">ClickUp</h3>
                                    <p class="text-sm text-slate-500">{{ __('Task import') }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                {{ __('Not configured') }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 mb-4">{{ __('Import tasks and time entries from ClickUp workspaces.') }}</p>
                        <a href="{{ route('settings.clickup.index') }}" class="inline-flex items-center text-sm font-medium text-purple-600 hover:text-purple-700">
                            {{ __('Configure') }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                </div>

                <!-- Future Integrations -->
                <div class="mt-8">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Coming Soon') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-slate-50 rounded-lg border border-slate-200 border-dashed p-4 text-center">
                            <div class="w-10 h-10 bg-slate-200 rounded-lg mx-auto mb-2 flex items-center justify-center">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-slate-600">Slack</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg border border-slate-200 border-dashed p-4 text-center">
                            <div class="w-10 h-10 bg-slate-200 rounded-lg mx-auto mb-2 flex items-center justify-center">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-slate-600">WhatsApp</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg border border-slate-200 border-dashed p-4 text-center">
                            <div class="w-10 h-10 bg-slate-200 rounded-lg mx-auto mb-2 flex items-center justify-center">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-slate-600">Stripe</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
