<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ $client->name }}
                </h2>
                @if($client->company_name)
                    <p class="text-sm text-slate-600 mt-1">{{ $client->company_name }}</p>
                @endif
            </div>
            <div class="flex gap-2">
                <x-ui.button variant="default" onclick="window.location.href='{{ route('clients.edit', $client) }}'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Client
                </x-ui.button>
            </div>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8 space-y-6">

        <!-- Client Info Card -->
        <x-ui.card>
            <x-ui.card-content>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Status -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600">Status</h3>
                        <div class="mt-2">
                            <x-client-status-badge :status="$client->status" />
                        </div>
                    </div>

                    <!-- Total Revenue -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600">Total Revenue</h3>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($stats['total_revenue'], 2) }} RON</p>
                    </div>

                    <!-- Active Domains -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600">Active Domains</h3>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stats['active_domains'] }}</p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200 pt-6">
                    <!-- Contact Info -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600 mb-3">Contact Information</h3>
                        <dl class="space-y-2">
                            @if($client->email)
                                <div class="flex items-start">
                                    <dt class="text-sm text-slate-600 w-32">Email:</dt>
                                    <dd class="text-sm text-slate-900">{{ $client->email }}</dd>
                                </div>
                            @endif
                            @if($client->phone)
                                <div class="flex items-start">
                                    <dt class="text-sm text-slate-600 w-32">Phone:</dt>
                                    <dd class="text-sm text-slate-900">{{ $client->phone }}</dd>
                                </div>
                            @endif
                            @if($client->contact_person)
                                <div class="flex items-start">
                                    <dt class="text-sm text-slate-600 w-32">Contact Person:</dt>
                                    <dd class="text-sm text-slate-900">{{ $client->contact_person }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Company Info -->
                    <div>
                        <h3 class="text-sm font-medium text-slate-600 mb-3">Company Information</h3>
                        <dl class="space-y-2">
                            @if($client->tax_id)
                                <div class="flex items-start">
                                    <dt class="text-sm text-slate-600 w-32">Tax ID:</dt>
                                    <dd class="text-sm text-slate-900">{{ $client->tax_id }}</dd>
                                </div>
                            @endif
                            @if($client->registration_number)
                                <div class="flex items-start">
                                    <dt class="text-sm text-slate-600 w-32">Reg. Number:</dt>
                                    <dd class="text-sm text-slate-900">{{ $client->registration_number }}</dd>
                                </div>
                            @endif
                            <div class="flex items-start">
                                <dt class="text-sm text-slate-600 w-32">VAT Payer:</dt>
                                <dd class="text-sm text-slate-900">{{ $client->vat_payer ? 'Yes' : 'No' }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if($client->address)
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-slate-600 mb-2">Address</h3>
                            <p class="text-sm text-slate-900">{{ $client->address }}</p>
                        </div>
                    @endif

                    @if($client->notes)
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-slate-600 mb-2">Notes</h3>
                            <p class="text-sm text-slate-900">{{ $client->notes }}</p>
                        </div>
                    @endif
                </div>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Tabs -->
        <x-ui.card>
            <div class="border-b border-slate-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'overview']) }}"
                        class="border-transparent {{ $activeTab === 'overview' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Overview
                    </a>
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'revenues']) }}"
                        class="border-transparent {{ $activeTab === 'revenues' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Revenues
                    </a>
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'domains']) }}"
                        class="border-transparent {{ $activeTab === 'domains' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Domains
                    </a>
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'credentials']) }}"
                        class="border-transparent {{ $activeTab === 'credentials' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Access Credentials
                    </a>
                </nav>
            </div>

            <x-ui.card-content>
                @if($activeTab === 'overview')
                    <div class="text-center py-12 text-slate-500">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">Client Overview</h3>
                        <p class="mt-1 text-sm text-slate-500">View client summary and quick stats</p>
                    </div>
                @elseif($activeTab === 'revenues')
                    <div class="text-center py-12 text-slate-500">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">Revenues</h3>
                        <p class="mt-1 text-sm text-slate-500">Total revenue: {{ number_format($stats['total_revenue'], 2) }} RON</p>
                    </div>
                @elseif($activeTab === 'domains')
                    <div class="text-center py-12 text-slate-500">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">Domains</h3>
                        <p class="mt-1 text-sm text-slate-500">Active domains: {{ $stats['active_domains'] }}</p>
                    </div>
                @elseif($activeTab === 'credentials')
                    <div class="text-center py-12 text-slate-500">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">Access Credentials</h3>
                        <p class="mt-1 text-sm text-slate-500">Total credentials: {{ $stats['credentials_count'] }}</p>
                    </div>
                @endif
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-app-layout>
