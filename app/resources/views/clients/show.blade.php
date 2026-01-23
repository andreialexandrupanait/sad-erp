<x-app-layout>
    <x-slot name="pageTitle">{{ $client->name }}@if($client->company_name) - {{ $client->company_name }}@endif</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('clients.edit', $client) }}'">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('Edit Client') }}
        </x-ui.button>
    </x-slot>

    <div class="p-4 md:p-6 space-y-4 md:space-y-6">

        <!-- Client Info Card -->
        <x-ui.card x-data="{ statusDropdownOpen: false, savingStatus: false }" class="overflow-hidden">
                <!-- Header: Client Name + Status + Total Revenue -->
                <div class="flex flex-col md:flex-row md:items-center justify-between px-4 md:px-6 py-3 bg-slate-50 border-b border-slate-200 gap-2 md:gap-0">
                    <div class="flex flex-wrap items-center gap-2 md:gap-3">
                        <h2 class="text-base md:text-lg font-bold text-slate-900">{{ $client->name }}</h2>
                        <!-- Status Dropdown -->
                        <div class="relative">
                            <button type="button"
                                    @click.stop="statusDropdownOpen = !statusDropdownOpen"
                                    class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md cursor-pointer transition-all hover:opacity-80"
                                    style="background-color: {{ $client->status?->color_class ?? '#64748b' }}; color: white;">
                                <span>{{ $client->status?->name ?? __('No Status') }}</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="statusDropdownOpen"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 @click.outside="statusDropdownOpen = false"
                                 class="absolute z-50 mt-1 left-0 w-44 bg-white rounded-lg shadow-lg border border-slate-200 py-1"
                                 x-cloak>
                                @foreach($clientStatuses as $status)
                                    <button type="button"
                                            @click="savingStatus = true; fetch('{{ route('clients.update-status', $client) }}', {
                                                method: 'PATCH',
                                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                                body: JSON.stringify({ status_id: {{ $status->id }} })
                                            }).then(() => window.location.reload()).catch(() => savingStatus = false)"
                                            class="w-full px-3 py-1.5 text-left text-sm hover:bg-slate-100 flex items-center gap-2 transition-colors">
                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $status->color_class }}"></span>
                                        <span>{{ $status->name }}</span>
                                        @if($client->status_id === $status->id)
                                            <svg class="w-4 h-4 ml-auto text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <template x-if="savingStatus">
                            <svg class="w-4 h-4 animate-spin text-slate-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                    </div>
                    <div class="text-left md:text-right">
                        <p class="text-xs text-slate-500">{{ __('Total Revenue') }}</p>
                        <p class="text-lg md:text-xl font-bold text-green-600">{{ number_format($stats['total_revenue'], 2) }} RON</p>
                    </div>
                </div>

                <!-- Contact & Company Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-200">
                    <!-- Contact Information -->
                    <div class="px-4 md:px-6 py-4">
                        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ __('Contact Information') }}
                        </h3>
                        <dl class="space-y-1.5">
                            @if($client->contact_person)
                                <div class="flex items-center gap-3">
                                    <dt class="text-sm text-slate-500 w-28 flex-shrink-0">{{ __('Contact') }}</dt>
                                    <dd class="text-sm font-medium text-slate-900">{{ $client->contact_person }}</dd>
                                </div>
                            @endif
                            @if($client->email)
                                <div class="flex items-center gap-3">
                                    <dt class="text-sm text-slate-500 w-28 flex-shrink-0">{{ __('Email') }}</dt>
                                    <dd class="text-sm text-slate-900">
                                        <a href="mailto:{{ $client->email }}" class="text-blue-600 hover:text-blue-800 hover:underline">{{ $client->email }}</a>
                                    </dd>
                                </div>
                            @endif
                            @if($client->phone)
                                <div class="flex items-center gap-3">
                                    <dt class="text-sm text-slate-500 w-28 flex-shrink-0">{{ __('Phone') }}</dt>
                                    <dd class="text-sm text-slate-900">
                                        <a href="tel:{{ $client->phone }}" class="text-blue-600 hover:text-blue-800 hover:underline">{{ $client->phone }}</a>
                                    </dd>
                                </div>
                            @endif
                            @if(!$client->contact_person && !$client->email && !$client->phone)
                                <p class="text-sm text-slate-400 italic">{{ __('No contact information') }}</p>
                            @endif
                        </dl>
                    </div>

                    <!-- Company Information -->
                    <div class="px-4 md:px-6 py-4">
                        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            {{ __('Company Information') }}
                        </h3>
                        <dl class="space-y-1.5">
                            @if($client->company_name)
                                <div class="flex items-center gap-3">
                                    <dt class="text-sm text-slate-500 w-28 flex-shrink-0">{{ __('Company') }}</dt>
                                    <dd class="text-sm font-medium text-slate-900">{{ $client->company_name }}</dd>
                                </div>
                            @endif
                            @if($client->tax_id)
                                <div class="flex items-center gap-3">
                                    <dt class="text-sm text-slate-500 w-28 flex-shrink-0">{{ __('CUI') }}</dt>
                                    <dd class="text-sm text-slate-900 font-mono">{{ $client->tax_id }}</dd>
                                </div>
                            @endif
                            @if($client->registration_number)
                                <div class="flex items-center gap-3">
                                    <dt class="text-sm text-slate-500 w-28 flex-shrink-0">{{ __('Reg. No.') }}</dt>
                                    <dd class="text-sm text-slate-900 font-mono">{{ $client->registration_number }}</dd>
                                </div>
                            @endif
                            <div class="flex items-center gap-3">
                                <dt class="text-sm text-slate-500 w-28 flex-shrink-0">{{ __('VAT Payer') }}</dt>
                                <dd class="text-sm">
                                    @if($client->vat_payer)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">{{ __('Yes') }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">{{ __('No') }}</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Address -->
                @if($client->address)
                    <div class="px-4 md:px-6 py-3 border-t border-slate-200 bg-slate-50/50">
                        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ __('Address') }}
                        </h3>
                        <p class="text-sm text-slate-700">{{ $client->address }}</p>
                    </div>
                @endif

                <!-- Internal Notes -->
                @if($client->notes)
                    <div class="px-4 md:px-6 py-3 border-t border-slate-200">
                        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ __('Internal Notes') }}
                        </h3>
                        <div class="prose prose-sm max-w-none text-slate-700 prose-a:text-blue-600 prose-a:no-underline hover:prose-a:underline">
                            {!! strip_tags($client->notes, '<p><br><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><code><pre>') !!}
                        </div>
                    </div>
                @endif
        </x-ui.card>

        <!-- Tabs -->
        <x-ui.card>
            <div class="border-b border-slate-200 overflow-x-auto">
                <nav class="-mb-px flex space-x-4 md:space-x-8 px-4 md:px-6" aria-label="Tabs">
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'overview']) }}"
                        class="border-transparent {{ $activeTab === 'overview' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __('Overview') }}
                    </a>
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'revenues']) }}"
                        class="border-transparent {{ $activeTab === 'revenues' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __('Revenues') }}
                    </a>
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'domains']) }}"
                        class="border-transparent {{ $activeTab === 'domains' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __('Domains') }}
                    </a>
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'credentials']) }}"
                        class="border-transparent {{ $activeTab === 'credentials' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __('Access Credentials') }}
                    </a>
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'notes']) }}"
                        class="border-transparent {{ $activeTab === 'notes' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __('Notes') }}
                        @if($client->clientNotes && $client->clientNotes->count() > 0)
                            <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-slate-200 text-slate-700">
                                {{ $client->clientNotes->count() }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'contracts']) }}"
                        class="border-transparent {{ $activeTab === 'contracts' ? 'border-slate-900 text-slate-900' : 'text-slate-500 hover:text-slate-700 hover:border-slate-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ __("Contracts") }}
                        @if($client->contracts && $client->contracts->count() > 0)
                            <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-indigo-200 text-indigo-700">
                                {{ $client->contracts->count() }}
                            </span>
                        @endif
                    </a>
                </nav>
            </div>

            <x-ui.card-content>
                @if($activeTab === 'overview')
                    <div class="space-y-6">
                        <!-- Quick Stats Grid - All 6 widgets on one line -->
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'revenues']) }}" class="bg-green-50 hover:bg-green-100 p-4 rounded-lg border border-green-200 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-green-100 rounded-lg group-hover:bg-green-200 transition-colors">
                                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-green-600 font-medium">{{ __('Invoices') }}</p>
                                        <p class="text-lg font-bold text-green-700">{{ $stats['invoices_count'] }}</p>
                                    </div>
                                </div>
                            </a>

                            <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'domains']) }}" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg border border-blue-200 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors">
                                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-600 font-medium">{{ __('Domains') }}</p>
                                        <p class="text-lg font-bold text-blue-700">{{ $stats['active_domains'] }}</p>
                                    </div>
                                </div>
                            </a>

                            <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'credentials']) }}" class="bg-purple-50 hover:bg-purple-100 p-4 rounded-lg border border-purple-200 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-purple-100 rounded-lg group-hover:bg-purple-200 transition-colors">
                                        <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-purple-600 font-medium">{{ __('Credentials') }}</p>
                                        <p class="text-lg font-bold text-purple-700">{{ $stats['credentials_count'] }}</p>
                                    </div>
                                </div>
                            </a>

                            <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'notes']) }}" class="bg-amber-50 hover:bg-amber-100 p-4 rounded-lg border border-amber-200 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-amber-100 rounded-lg group-hover:bg-amber-200 transition-colors">
                                        <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-amber-600 font-medium">{{ __('Notes') }}</p>
                                        <p class="text-lg font-bold text-amber-700">{{ $client->clientNotes ? $client->clientNotes->count() : 0 }}</p>
                                    </div>
                                </div>
                            </a>

                            <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'contracts']) }}" class="bg-indigo-50 hover:bg-indigo-100 p-4 rounded-lg border border-indigo-200 transition-colors group">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-indigo-100 rounded-lg group-hover:bg-indigo-200 transition-colors">
                                        <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-indigo-600 font-medium">{{ __('Contracts') }}</p>
                                        <p class="text-lg font-bold text-indigo-700">{{ $stats['contracts_count'] }}</p>
                                    </div>
                                </div>
                            </a>

                            <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-200">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-emerald-100 rounded-lg">
                                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-emerald-600 font-medium">{{ __('Active Contracts') }}</p>
                                        <p class="text-lg font-bold text-emerald-700">{{ $stats['active_contracts_count'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Invoices -->
                        @if($client->revenues->isNotEmpty())
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-semibold text-slate-900">{{ __('Recent Invoices') }}</h3>
                                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'revenues']) }}" class="text-xs text-blue-600 hover:text-blue-800">{{ __('View all') }} →</a>
                                </div>
                                <div class="space-y-2">
                                    @foreach($client->revenues->take(5) as $revenue)
                                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                            <div class="flex items-center gap-3">
                                                <div class="text-sm font-medium text-slate-900">{{ $revenue->document_name }}</div>
                                                <span class="text-xs text-slate-500">{{ $revenue->occurred_at->format('d M Y') }}</span>
                                            </div>
                                            <div class="text-sm font-bold text-green-600">{{ number_format($revenue->amount, 2) }} {{ $revenue->currency }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Recent Notes -->
                        @if($client->clientNotes && $client->clientNotes->isNotEmpty())
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-semibold text-slate-900">{{ __('Recent Notes') }}</h3>
                                    <a href="{{ route('clients.show', ['client' => $client, 'tab' => 'notes']) }}" class="text-xs text-blue-600 hover:text-blue-800">{{ __('View all') }} →</a>
                                </div>
                                <div class="space-y-2">
                                    @foreach($client->clientNotes->take(3) as $note)
                                        <div class="p-3 bg-slate-50 rounded-lg">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-xs font-medium text-slate-900">{{ $note->created_at->format('d M Y') }}</span>
                                                <span class="text-xs text-slate-400">{{ __('by') }} {{ $note->user->name ?? 'Unknown' }}</span>
                                            </div>
                                            <p class="text-sm text-slate-600 line-clamp-2">{{ Str::limit(strip_tags($note->content), 150) }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @elseif($activeTab === 'revenues')
                    @if($client->revenues->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No revenues yet') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Get started by adding a revenue entry') }}</p>
                        </div>
                    @else
                        <!-- Invoice Summary -->
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center justify-between">
                            <p class="text-sm text-green-900">
                                <span class="font-semibold">{{ $stats['invoices_count'] }}</span> {{ $stats['invoices_count'] === 1 ? __('invoice') : __('invoices') }} •
                                <span class="font-semibold">{{ number_format($client->total_revenue, 2) }} RON</span> {{ __('total revenue') }}
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full caption-bottom text-sm">
                                <thead class="bg-slate-100">
                                    <tr class="border-b border-slate-200">
                                        <x-ui.sortable-header column="document_name" label="{{ __('Invoice') }}" />
                                        <x-ui.sortable-header column="amount" label="{{ __('Amount') }}" class="text-right" />
                                        <x-ui.table-head>{{ __('Currency') }}</x-ui.table-head>
                                        <x-ui.sortable-header column="occurred_at" label="{{ __('Date') }}" />
                                        <x-ui.table-head class="text-right">{{ __('Actions') }}</x-ui.table-head>
                                    </tr>
                                </thead>
                                <tbody class="[&_tr:last-child]:border-0">
                                    @foreach($client->revenues as $revenue)
                                        <x-ui.table-row>
                                            <x-ui.table-cell>
                                                <div class="text-sm font-medium text-slate-900">{{ $revenue->document_name }}</div>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell class="text-right">
                                                <div class="text-sm font-bold text-green-600">{{ number_format($revenue->amount, 2) }}</div>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">{{ $revenue->currency }}</span>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>
                                                <div class="text-sm text-slate-900">{{ $revenue->occurred_at->format('d M Y') }}</div>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell class="text-right">
                                                <div class="flex items-center gap-1 justify-end">
                                                    @if($revenue->files->isNotEmpty())
                                                        @php
                                                            $file = $revenue->files->first();
                                                            $shareUrl = URL::signedRoute('share.invoice', ['file' => $file->id], now()->addDays(30));
                                                        @endphp
                                                        <!-- View/Preview -->
                                                        <a href="{{ route('financial.files.show', $file) }}" target="_blank" class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors" title="{{ __('View') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                            </svg>
                                                        </a>
                                                        <!-- Download -->
                                                        <a href="{{ route('financial.files.download', $file) }}" class="p-1.5 text-slate-500 hover:text-green-600 hover:bg-green-50 rounded transition-colors" title="{{ __('Download') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                            </svg>
                                                        </a>
                                                        <!-- Print -->
                                                        <a href="{{ route('financial.files.show', $file) }}" target="_blank" onclick="setTimeout(() => window.open('{{ route('financial.files.show', $file) }}').print(), 500); return false;" class="p-1.5 text-slate-500 hover:text-purple-600 hover:bg-purple-50 rounded transition-colors" title="{{ __('Print') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                                            </svg>
                                                        </a>
                                                        <!-- Copy Share Link -->
                                                        <button onclick="copyToClipboard('{{ $shareUrl }}', 'Share link')" class="p-1.5 text-slate-500 hover:text-orange-600 hover:bg-orange-50 rounded transition-colors" title="{{ __('Copy share link') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <span class="text-slate-400 text-xs">{{ __('No file') }}</span>
                                                    @endif
                                                </div>
                                            </x-ui.table-cell>
                                        </x-ui.table-row>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @elseif($activeTab === 'domains')
                    @if($client->domains->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No domains yet') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('No domains are associated with this client') }}</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full caption-bottom text-sm">
                                <thead class="bg-slate-100">
                                    <tr class="border-b border-slate-200">
                                        <x-ui.table-head>{{ __('Domain Name') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Registrar') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Status') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Expiry Date') }}</x-ui.table-head>
                                        <x-ui.table-head class="text-right">{{ __('Actions') }}</x-ui.table-head>
                                    </tr>
                                </thead>
                                <tbody class="[&_tr:last-child]:border-0">
                                    @foreach($client->domains as $domain)
                                        <x-ui.table-row>
                                            <x-ui.table-cell>
                                                <a href="{{ route('domains.show', $domain) }}" class="text-slate-900 hover:text-slate-600 font-medium transition-colors">
                                                    {{ $domain->domain_name }}
                                                </a>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>{{ $domain->registrar }}</x-ui.table-cell>
                                            <x-ui.table-cell>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">
                                                    {{ $domain->status }}
                                                </span>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>{{ $domain->expiry_date->format('d M Y') }}</x-ui.table-cell>
                                            <x-ui.table-cell class="text-right">
                                                <x-table-actions
                                                    :viewUrl="route('domains.show', $domain)"
                                                />
                                            </x-ui.table-cell>
                                        </x-ui.table-row>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @elseif($activeTab === 'credentials')
                    @if($client->accessCredentials->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No credentials yet') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('No access credentials are associated with this client') }}</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-100">
                                    <tr>
                                        <x-ui.table-head>{{ __('Platform') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('URL') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Username') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Password') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Notes') }}</x-ui.table-head>
                                        <x-ui.table-head class="text-right">{{ __('Actions') }}</x-ui.table-head>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    @foreach($client->accessCredentials as $credential)
                                        <x-ui.table-row x-data="{ showPassword{{ $credential->id }}: false }">
                                            <!-- Platform -->
                                            <x-ui.table-cell>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                                    {{ $credential->platform }}
                                                </span>
                                            </x-ui.table-cell>

                                            <!-- URL -->
                                            <x-ui.table-cell>
                                                @if($credential->url)
                                                    <a href="{{ $credential->url }}" target="_blank" rel="noopener noreferrer"
                                                       class="text-sm text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                        </svg>
                                                        <span class="truncate max-w-[150px]" title="{{ $credential->url }}">
                                                            {{ parse_url($credential->url, PHP_URL_HOST) ?: $credential->url }}
                                                        </span>
                                                    </a>
                                                @else
                                                    <span class="text-sm text-slate-400">-</span>
                                                @endif
                                            </x-ui.table-cell>

                                            <!-- Username -->
                                            <x-ui.table-cell>
                                                @if($credential->username)
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm text-slate-700">{{ $credential->username }}</span>
                                                        <button onclick="copyToClipboard('{{ $credential->username }}', 'Username')"
                                                                class="text-slate-400 hover:text-slate-600 transition-colors"
                                                                title="{{ __('Copy username') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-sm text-slate-400">-</span>
                                                @endif
                                            </x-ui.table-cell>

                                            <!-- Password -->
                                            <x-ui.table-cell>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-mono"
                                                          :class="showPassword{{ $credential->id }} ? 'text-slate-700' : 'text-slate-500'"
                                                          x-text="showPassword{{ $credential->id }} ? '{{ $credential->password }}' : '{{ $credential->masked_password }}'">
                                                    </span>
                                                    <button @click="showPassword{{ $credential->id }} = !showPassword{{ $credential->id }}"
                                                            class="text-slate-400 hover:text-slate-600 transition-colors"
                                                            :title="showPassword{{ $credential->id }} ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'">
                                                        <svg x-show="!showPassword{{ $credential->id }}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                        <svg x-show="showPassword{{ $credential->id }}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                                        </svg>
                                                    </button>
                                                    <button x-show="showPassword{{ $credential->id }}"
                                                            onclick="copyToClipboard('{{ $credential->password }}', 'Password')"
                                                            class="text-slate-400 hover:text-slate-600 transition-colors"
                                                            title="{{ __('Copy password') }}"
                                                            style="display: none;">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </x-ui.table-cell>

                                            <!-- Notes -->
                                            <x-ui.table-cell>
                                                @if($credential->notes)
                                                    <span class="text-sm text-slate-600 line-clamp-2" title="{{ $credential->notes }}">
                                                        {{ $credential->notes }}
                                                    </span>
                                                @else
                                                    <span class="text-sm text-slate-400">-</span>
                                                @endif
                                            </x-ui.table-cell>

                                            <!-- Actions -->
                                            <x-ui.table-cell class="text-right">
                                                <x-table-actions
                                                    :viewUrl="route('credentials.show', $credential)"
                                                />
                                            </x-ui.table-cell>
                                        </x-ui.table-row>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @elseif($activeTab === 'notes')
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-slate-900">{{ __('Client Notes') }}</h3>
                        <x-ui.button variant="default" size="sm" onclick="window.location.href='{{ route('notes.create', ['client' => $client->slug]) }}'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Add Note') }}
                        </x-ui.button>
                    </div>

                    @if(!$client->clientNotes || $client->clientNotes->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No notes yet') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Get started by creating a note for this client.') }}</p>
                            <div class="mt-6">
                                <x-ui.button variant="default" onclick="window.location.href='{{ route('notes.create', ['client' => $client->slug]) }}'">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('Create Note') }}
                                </x-ui.button>
                            </div>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($client->clientNotes->take(10) as $note)
                                <div class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <!-- Date & Author -->
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="text-sm font-medium text-slate-900">
                                                    {{ $note->created_at->format('d M Y, H:i') }}
                                                </span>
                                                <span class="text-xs text-slate-400">
                                                    {{ __('by') }} {{ $note->user?->name ?? 'Unknown' }}
                                                </span>
                                            </div>

                                            <!-- Content -->
                                            <div class="prose prose-sm max-w-none text-slate-700
                                                        prose-headings:text-slate-900 prose-headings:font-semibold
                                                        prose-p:my-2 prose-ul:my-2 prose-ol:my-2
                                                        prose-li:my-0.5 prose-a:text-blue-600 prose-a:no-underline hover:prose-a:underline
                                                        prose-strong:text-slate-900 prose-code:text-pink-600 prose-code:bg-slate-100 prose-code:px-1 prose-code:rounded">
                                                {!! Str::limit(strip_tags($note->content, '<p><br><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><code><pre>'), 1000) !!}
                                            </div>

                                            <!-- Tags -->
                                            @if(!empty($note->tags))
                                                <div class="mt-3 flex flex-wrap gap-1">
                                                    @foreach($note->tags as $tag)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ ucfirst($tag) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex items-center gap-1 ml-4">
                                            <a href="{{ route('notes.edit', $note) }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors" title="{{ __('Edit') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($client->clientNotes && $client->clientNotes->count() > 10)
                            <div class="mt-4 text-center">
                                <a href="{{ route('notes.index', ['client_id' => $client->id]) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                    {{ __('View all :count notes', ['count' => $client->clientNotes->count()]) }}
                                </a>
                            </div>
                        @endif
                    @endif
                @elseif($activeTab === 'contracts')
                    @if($client->contracts->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No contracts yet') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('No contracts are associated with this client') }}</p>
                        </div>
                    @else
                        <!-- Contracts Summary -->
                        <div class="mb-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg flex items-center justify-between">
                            <p class="text-sm text-indigo-900">
                                <span class="font-semibold">{{ $stats['contracts_count'] }}</span> {{ $stats['contracts_count'] === 1 ? __('contract') : __('contracts') }} ·
                                <span class="font-semibold">{{ $stats['active_contracts_count'] }}</span> {{ __('active') }}
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full caption-bottom text-sm">
                                <thead class="bg-slate-100">
                                    <tr class="border-b border-slate-200">
                                        <x-ui.table-head>{{ __('Contract Number') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Title') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Status') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Period') }}</x-ui.table-head>
                                        <x-ui.table-head class="text-right">{{ __('Value') }}</x-ui.table-head>
                                        <x-ui.table-head>{{ __('Annexes') }}</x-ui.table-head>
                                        <x-ui.table-head class="text-right">{{ __('Actions') }}</x-ui.table-head>
                                    </tr>
                                </thead>
                                <tbody class="[&_tr:last-child]:border-0">
                                    @foreach($client->contracts as $contract)
                                        <x-ui.table-row>
                                            <x-ui.table-cell>
                                                <a href="{{ route('contracts.show', $contract) }}" class="text-slate-900 hover:text-indigo-600 font-medium transition-colors">
                                                    {{ $contract->contract_number }}
                                                </a>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>
                                                <div class="text-sm text-slate-700 max-w-xs truncate" title="{{ $contract->title }}">
                                                    {{ $contract->title }}
                                                </div>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>
                                                @php
                                                    $statusColors = [
                                                        'draft' => 'bg-slate-100 text-slate-700',
                                                        'active' => 'bg-green-100 text-green-700',
                                                        'completed' => 'bg-blue-100 text-blue-700',
                                                        'terminated' => 'bg-red-100 text-red-700',
                                                        'expired' => 'bg-amber-100 text-amber-700',
                                                    ];
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$contract->status] ?? 'bg-slate-100 text-slate-700' }}">
                                                    {{ $contract->status_label ?? ucfirst($contract->status) }}
                                                </span>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>
                                                <div class="text-sm text-slate-600">
                                                    {{ $contract->start_date?->format('d M Y') ?? '-' }}
                                                    @if($contract->end_date)
                                                        <span class="text-slate-400">→</span>
                                                        {{ $contract->end_date->format('d M Y') }}
                                                    @endif
                                                </div>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell class="text-right">
                                                <div class="text-sm font-semibold text-slate-900">{{ number_format($contract->total_value, 2) }} {{ $contract->currency }}</div>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>
                                                @if($contract->annexes->count() > 0)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">
                                                        {{ $contract->annexes->count() }} {{ __('annexes') }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-400">-</span>
                                                @endif
                                            </x-ui.table-cell>
                                            <x-ui.table-cell class="text-right">
                                                <a href="{{ route('contracts.show', $contract) }}" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors inline-flex" title="{{ __('View') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                            </x-ui.table-cell>
                                        </x-ui.table-row>
                                        @if($contract->annexes->count() > 0)
                                            @foreach($contract->annexes as $annex)
                                                <x-ui.table-row class="bg-slate-50">
                                                    <x-ui.table-cell class="pl-8">
                                                        <span class="text-slate-500">↳</span>
                                                        <a href="{{ route('contracts.annex.show', [$contract, $annex]) }}" class="text-slate-700 hover:text-purple-600 font-medium transition-colors ml-1">
                                                            {{ $annex->annex_code }}
                                                        </a>
                                                    </x-ui.table-cell>
                                                    <x-ui.table-cell>
                                                        <div class="text-sm text-slate-600">{{ $annex->title }}</div>
                                                    </x-ui.table-cell>
                                                    <x-ui.table-cell>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">
                                                            {{ __('Annex') }}
                                                        </span>
                                                    </x-ui.table-cell>
                                                    <x-ui.table-cell>
                                                        <div class="text-sm text-slate-600">{{ $annex->effective_date?->format('d M Y') ?? '-' }}</div>
                                                    </x-ui.table-cell>
                                                    <x-ui.table-cell class="text-right">
                                                        <div class="text-sm font-semibold text-purple-700">+{{ number_format($annex->additional_value, 2) }} {{ $annex->currency }}</div>
                                                    </x-ui.table-cell>
                                                    <x-ui.table-cell></x-ui.table-cell>
                                                    <x-ui.table-cell class="text-right">
                                                        <a href="{{ route('contracts.annex.show', [$contract, $annex]) }}" class="p-1.5 text-slate-500 hover:text-purple-600 hover:bg-purple-50 rounded transition-colors inline-flex" title="{{ __('View') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                            </svg>
                                                        </a>
                                                    </x-ui.table-cell>
                                                </x-ui.table-row>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
            </x-ui.card-content>
        </x-ui.card>
    </div>

    @push('scripts')
    <script>
        function copyToClipboard(text, label) {
            if (!text) {
                console.warn('Nothing to copy');
                return;
            }

            navigator.clipboard.writeText(text).then(function() {
                // Show success toast
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: (label || 'Text') + ' copied to clipboard!'
                    }
                }));
            }).catch(function(err) {
                console.error('Failed to copy:', err);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        message: 'Failed to copy to clipboard'
                    }
                }));
            });
        }
    </script>
    @endpush
</x-app-layout>
