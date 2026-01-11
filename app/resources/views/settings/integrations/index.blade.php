<x-app-layout>
    <x-slot name="pageTitle">{{ __('Settings') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-4 md:p-6">
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

                    <!-- Cloudflare R2 -->
                    <div class="bg-white rounded-lg border border-slate-200 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Cloudflare R2</h3>
                                    <p class="text-sm text-slate-500">{{ __('Cloud Storage') }}</p>
                                </div>
                            </div>
                            @if(\App\Models\ApplicationSetting::get('r2_enabled') && \App\Models\ApplicationSetting::get('r2_bucket'))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('Connected') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                    {{ __('Not configured') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-600 mb-4">{{ __('Store files in Cloudflare R2 with zero egress fees.') }}</p>
                        <a href="{{ route('settings.r2.index') }}" class="inline-flex items-center text-sm font-medium text-orange-600 hover:text-orange-700">
                            {{ __('Configure') }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
