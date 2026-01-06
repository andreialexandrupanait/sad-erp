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
                    <div class="bg-white rounded-lg border border-slate-200 p-6">
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
                            @php
                                $smartbillSettings = auth()->user()->organization->settings['smartbill'] ?? [];
                                $smartbillConfigured = !empty($smartbillSettings['username']) && !empty($smartbillSettings['token']) && !empty($smartbillSettings['cif']);
                            @endphp
                            @if($smartbillConfigured)
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
                    <div class="bg-white rounded-lg border border-slate-200 p-6">
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

                    <!-- Slack -->
                    <div class="bg-white rounded-lg border border-slate-200 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-[#4A154B]/10 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-[#4A154B]" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Slack</h3>
                                    <p class="text-sm text-slate-500">{{ __('Notifications & Alerts') }}</p>
                                </div>
                            </div>
                            @if(\App\Models\ApplicationSetting::get('slack_enabled') && \App\Models\ApplicationSetting::get('slack_webhook_url'))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('Connected') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                    {{ __('Not configured') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-600 mb-4">{{ __('Send notifications to Slack channels for domain expiry, subscription renewals, and system alerts.') }}</p>
                        <a href="{{ route('settings.slack.index') }}" class="inline-flex items-center text-sm font-medium text-[#4A154B] hover:text-[#611f69]">
                            {{ __('Configure') }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>


                    <!-- Email Notifications -->
                    <div class="bg-white rounded-lg border border-slate-200 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Email</h3>
                                    <p class="text-sm text-slate-500">{{ __('Email Notifications') }}</p>
                                </div>
                            </div>
                            @if(\App\Models\ApplicationSetting::get('notifications_enabled'))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('Enabled') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                    {{ __('Disabled') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-600 mb-4">{{ __('Send notifications via email for domain expiry, subscription renewals, and system alerts.') }}</p>
                        <a href="{{ route('settings.notifications') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700">
                            {{ __('Configure') }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <!-- Anthropic (Claude AI) -->
                    <div class="bg-white rounded-lg border border-slate-200 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Claude AI</h3>
                                    <p class="text-sm text-slate-500">{{ __('AI Design Generation') }}</p>
                                </div>
                            </div>
                            @if(\App\Models\ApplicationSetting::get('anthropic_enabled') && \App\Models\ApplicationSetting::get('anthropic_api_key'))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('Connected') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                    {{ __('Not configured') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-600 mb-4">{{ __('Generate landing page designs and analyze copy using Claude AI from Anthropic.') }}</p>
                        <a href="{{ route('settings.anthropic.index') }}" class="inline-flex items-center text-sm font-medium text-amber-600 hover:text-amber-700">
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
