<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ __('Domain Name') }}: {{ $domain->domain_name }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">{{ __('View domain details and information') }}</p>
            </div>
            <div class="flex gap-2">
                <x-ui.button variant="default" onclick="window.location.href='{{ route('domains.edit', $domain) }}'">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('Edit') }}
                </x-ui.button>
                <x-ui.button variant="outline" onclick="window.location.href='{{ route('domains.index') }}'">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('Back to Domains') }}
                </x-ui.button>
            </div>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8 space-y-6">
        <!-- Success Messages -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- Domain Information -->
        <x-ui.card>
            <x-ui.card-header>
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Domain Information') }}</h3>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Domain Name -->
                    <div>
                        <div class="text-sm font-medium text-slate-500">{{ __('Domain Name') }}</div>
                        <div class="mt-1 text-lg font-mono font-semibold text-slate-900">{{ $domain->domain_name }}</div>
                    </div>

                    <!-- Expiry Status -->
                    <div>
                        <div class="text-sm font-medium text-slate-500">{{ __('Expiry Status') }}</div>
                        <div class="mt-2">
                            @if ($domain->expiry_status === 'Expired')
                                <x-ui.badge variant="destructive" class="text-base">
                                    {{ $domain->expiry_text }}
                                </x-ui.badge>
                            @elseif ($domain->expiry_status === 'Expiring')
                                <x-ui.badge variant="warning" class="text-base">
                                    {{ $domain->expiry_text }}
                                </x-ui.badge>
                            @else
                                <x-ui.badge variant="success" class="text-base">
                                    {{ $domain->expiry_text }}
                                </x-ui.badge>
                            @endif
                        </div>
                    </div>

                    <!-- Client -->
                    @if ($domain->client)
                        <div>
                            <div class="text-sm font-medium text-slate-500">{{ __('Client') }}</div>
                            <div class="mt-1">
                                <a href="{{ route('clients.show', $domain->client) }}" class="text-slate-900 hover:text-slate-600 font-medium transition-colors">
                                    {{ $domain->client->display_name }}
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Registrar -->
                    @if ($domain->registrar)
                        <div>
                            <div class="text-sm font-medium text-slate-500">{{ __('Registrar') }}</div>
                            <div class="mt-1 text-slate-900">{{ $domain->registrar }}</div>
                        </div>
                    @endif

                    <!-- Registration Date -->
                    @if ($domain->registration_date)
                        <div>
                            <div class="text-sm font-medium text-slate-500">{{ __('Registration Date') }}</div>
                            <div class="mt-1 text-slate-900">{{ $domain->registration_date->format('M d, Y') }}</div>
                        </div>
                    @endif

                    <!-- Expiry Date -->
                    <div>
                        <div class="text-sm font-medium text-slate-500">{{ __('Expiry Date') }}</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">{{ $domain->expiry_date->format('M d, Y') }}</div>
                    </div>

                    <!-- Annual Cost -->
                    @if ($domain->annual_cost)
                        <div>
                            <div class="text-sm font-medium text-slate-500">{{ __('Annual Cost ($)') }}</div>
                            <div class="mt-1 text-slate-900 font-medium">${{ number_format($domain->annual_cost, 2) }}</div>
                        </div>
                    @endif

                    <!-- Auto-Renew -->
                    <div>
                        <div class="text-sm font-medium text-slate-500">{{ __('Auto-renew enabled') }}</div>
                        <div class="mt-2">
                            @if ($domain->auto_renew)
                                <x-ui.badge variant="info">{{ __('Enabled') }}</x-ui.badge>
                            @else
                                <x-ui.badge variant="secondary">{{ __('Disabled') }}</x-ui.badge>
                            @endif
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <div class="text-sm font-medium text-slate-500">{{ __('Status') }}</div>
                        <div class="mt-1 text-slate-900">{{ $domain->status }}</div>
                    </div>

                    <!-- Notes -->
                    @if ($domain->notes)
                        <div class="md:col-span-2">
                            <div class="text-sm font-medium text-slate-500">{{ __('Notes') }}</div>
                            <div class="mt-1 text-slate-900 whitespace-pre-line">{{ $domain->notes }}</div>
                        </div>
                    @endif

                    <!-- Created At -->
                    <div>
                        <div class="text-sm font-medium text-slate-500">{{ __('Created') }}</div>
                        <div class="mt-1 text-sm text-slate-700">{{ $domain->created_at->format('M d, Y H:i') }}</div>
                    </div>

                    <!-- Updated At -->
                    <div>
                        <div class="text-sm font-medium text-slate-500">{{ __('Last Updated') }}</div>
                        <div class="mt-1 text-sm text-slate-700">{{ $domain->updated_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-app-layout>
