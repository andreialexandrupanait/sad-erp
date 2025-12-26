<x-app-layout>
    <x-slot name="pageTitle">{{ $contract->contract_number }}</x-slot>

    <x-slot name="headerActions">
        <div class="flex items-center gap-2">
            {{-- Edit Content (Draft or Active contracts) --}}
            @if(in_array($contract->status, ['draft', 'active']))
                <x-ui.button variant="primary" onclick="window.location.href='{{ route('contracts.builder', $contract) }}'">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('Edit Content') }}
                </x-ui.button>
            @endif

            {{-- Preview PDF button for draft contracts with content --}}
            @if($contract->isDraft() && $contract->content && !$contract->is_finalized)
                <x-ui.button variant="outline" onclick="window.open('{{ route('contracts.preview', $contract) }}', '_blank')">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{ __('Preview PDF') }}
                </x-ui.button>

                <form action="{{ route('contracts.finalize-and-download', $contract) }}" method="POST" class="inline"
                      onsubmit="return confirm('{{ __('Are you sure you want to finalize this contract? Once finalized, the contract cannot be edited.') }}')">
                    @csrf
                    <x-ui.button variant="default" type="submit">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('Finalize & Download PDF') }}
                    </x-ui.button>
                </form>
            @endif

            @if($contract->pdf_path)
                <x-ui.button variant="outline" onclick="window.location.href='{{ route('contracts.download', $contract) }}'">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('Download PDF') }}
                </x-ui.button>
            @endif

            @if($contract->isActive())
                <x-ui.button variant="default" onclick="window.location.href='{{ route('contracts.add-annex', $contract) }}'">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Add Annex') }}
                </x-ui.button>

                <form action="{{ route('contracts.terminate', $contract) }}" method="POST" class="inline"
                      onsubmit="return confirm('{{ __('Are you sure you want to terminate this contract?') }}')">
                    @csrf
                    <x-ui.button variant="destructive-outline" type="submit">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        {{ __('Terminate') }}
                    </x-ui.button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="p-6 space-y-6">
        {{-- Messages --}}
        @if (session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="destructive">{{ session('error') }}</x-ui.alert>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Contract Details --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold">{{ $contract->title }}</h2>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @switch($contract->status)
                                    @case('draft') bg-slate-100 text-slate-700 @break
                                    @case('active') bg-green-100 text-green-700 @break
                                    @case('completed') bg-blue-100 text-blue-700 @break
                                    @case('terminated') bg-red-100 text-red-700 @break
                                    @case('expired') bg-yellow-100 text-yellow-700 @break
                                @endswitch">
                                {{ $contract->status_label }}
                            </span>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        @if($contract->content)
                            <div class="contract-content">
                                {!! $contract->rendered_content !!}
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-4 text-slate-500">{{ __('Contract content not yet generated.') }}</p>
                                <p class="mt-1 text-sm text-slate-400">{{ __('Use the contract builder to create the content.') }}</p>
                                @if(in_array($contract->status, ['draft', 'active']))
                                    <a href="{{ route('contracts.builder', $contract) }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ __('Open Contract Builder') }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Original Offer --}}
                @if($contract->offer)
                    <x-ui.card>
                        <x-ui.card-header>
                            <h3 class="font-semibold">{{ __('Original Offer') }}</h3>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <div class="flex items-center justify-between">
                                <div>
                                    <a href="{{ route('offers.show', $contract->offer) }}" class="font-medium text-blue-600 hover:text-blue-800">
                                        {{ $contract->offer->offer_number }}
                                    </a>
                                    <p class="text-sm text-slate-500">{{ $contract->offer->title }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium">{{ number_format($contract->offer->total, 2) }} {{ $contract->offer->currency }}</div>
                                    <div class="text-sm text-slate-500">{{ __('Accepted') }} {{ $contract->offer->accepted_at->format('d.m.Y') }}</div>
                                </div>
                            </div>

                            {{-- Offer Items Summary --}}
                            <div class="mt-4 pt-4 border-t">
                                <h4 class="text-sm font-medium text-slate-700 mb-2">{{ __('Items') }}</h4>
                                <ul class="space-y-1 text-sm">
                                    @foreach($contract->offer->items as $item)
                                        <li class="flex justify-between">
                                            <span class="text-slate-600">{{ $item->title }}</span>
                                            <span class="font-medium">{{ number_format($item->total_price, 2) }} {{ $contract->offer->currency }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                @endif

                {{-- Annexes --}}
                @if($contract->annexes->count() > 0)
                    <x-ui.card>
                        <x-ui.card-header>
                            <h3 class="font-semibold">{{ __('Annexes') }}</h3>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <div class="space-y-4">
                                @foreach($contract->annexes as $annex)
                                    <div class="flex items-center justify-between p-4 border rounded-lg">
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $annex->annex_code }}</div>
                                            <div class="text-sm text-slate-500">{{ $annex->title }}</div>
                                            <div class="text-xs text-slate-400">{{ __('Effective') }}: {{ $annex->effective_date->format('d.m.Y') }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-medium text-slate-900">+{{ number_format($annex->additional_value, 2) }} {{ $annex->currency }}</div>
                                            @if($annex->pdf_path)
                                                <a href="{{ route('contracts.annex.download', [$contract, $annex]) }}"
                                                   class="text-sm text-blue-600 hover:text-blue-800">
                                                    {{ __('Download PDF') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Client Info --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="font-semibold">{{ __('Client') }}</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div class="space-y-3">
                            @if($contract->client)
                                <div>
                                    <a href="{{ route('clients.show', $contract->client) }}" class="font-medium text-slate-900 hover:text-blue-600">
                                        {{ $contract->client->name }}
                                    </a>
                                    @if($contract->client->company_name)
                                        <div class="text-sm text-slate-500">{{ $contract->client->company_name }}</div>
                                    @endif
                                </div>
                                @if($contract->client->email)
                                    <div class="text-sm">
                                        <span class="text-slate-500">{{ __('Email:') }}</span>
                                        <a href="mailto:{{ $contract->client->email }}" class="text-blue-600 hover:underline">{{ $contract->client->email }}</a>
                                    </div>
                                @endif
                            @elseif($contract->temp_client_name || $contract->offer?->temp_client_name)
                                {{-- Temp client from offer --}}
                                <div>
                                    <span class="font-medium text-slate-900">
                                        {{ $contract->temp_client_name ?? $contract->offer?->temp_client_name }}
                                    </span>
                                    @if($contract->temp_client_company ?? $contract->offer?->temp_client_company)
                                        <div class="text-sm text-slate-500">{{ $contract->temp_client_company ?? $contract->offer?->temp_client_company }}</div>
                                    @endif
                                </div>
                                @if($contract->temp_client_email ?? $contract->offer?->temp_client_email)
                                    <div class="text-sm">
                                        <span class="text-slate-500">{{ __('Email:') }}</span>
                                        <a href="mailto:{{ $contract->temp_client_email ?? $contract->offer?->temp_client_email }}" class="text-blue-600 hover:underline">
                                            {{ $contract->temp_client_email ?? $contract->offer?->temp_client_email }}
                                        </a>
                                    </div>
                                @endif
                                <div class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded">
                                    {{ __('Temporary client - not yet in client database') }}
                                </div>
                            @else
                                <p class="text-slate-500 italic">{{ __('No client assigned') }}</p>
                            @endif
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Contract Info --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="font-semibold">{{ __('Details') }}</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500">{{ __('Contract Value') }}</dt>
                                <dd class="font-medium text-slate-900">{{ number_format($contract->total_value, 2) }} {{ $contract->currency }}</dd>
                            </div>
                            @if($contract->annexes->count() > 0)
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">{{ __('With Annexes') }}</dt>
                                    <dd class="font-medium text-slate-900">{{ number_format($contract->total_value_with_annexes, 2) }} {{ $contract->currency }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-slate-500">{{ __('Start Date') }}</dt>
                                <dd class="text-slate-900">{{ $contract->start_date->format('d.m.Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">{{ __('End Date') }}</dt>
                                <dd class="text-slate-900">
                                    @if($contract->end_date)
                                        {{ $contract->end_date->format('d.m.Y') }}
                                        @if($contract->isActive() && $contract->days_until_expiry !== null)
                                            <span class="text-xs
                                                @if($contract->expiry_urgency === 'urgent') text-red-600
                                                @elseif($contract->expiry_urgency === 'warning') text-yellow-600
                                                @else text-slate-500
                                                @endif">
                                                ({{ trans_choice(':count day|:count days', $contract->days_until_expiry, ['count' => $contract->days_until_expiry]) }})
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-slate-500">{{ __('Indefinite') }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">{{ __('Auto Renew') }}</dt>
                                <dd class="text-slate-900">{{ $contract->auto_renew ? __('Yes') : __('No') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">{{ __('Created') }}</dt>
                                <dd class="text-slate-900">{{ $contract->created_at->format('d.m.Y H:i') }}</dd>
                            </div>
                        </dl>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Auto-Renew Management --}}
                @if($contract->end_date && $contract->isActive())
                    <x-ui.card x-data="autoRenewManager({{ $contract->id }}, {{ $contract->auto_renew ? 'true' : 'false' }})">
                        <x-ui.card-header>
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-slate-900">{{ __('Auto-Renewal') }}</h3>
                                <span class="text-xs px-2 py-1 rounded-full"
                                      :class="autoRenew ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'"
                                      x-text="autoRenew ? '{{ __('Enabled') }}' : '{{ __('Disabled') }}'">
                                </span>
                            </div>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <div class="space-y-4">
                                {{-- Toggle Switch --}}
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-slate-700">{{ __('Auto-renew contract') }}</p>
                                        <p class="text-xs text-slate-500">{{ __('Contract will automatically renew when it expires') }}</p>
                                    </div>
                                    <button @click="toggleAutoRenew()"
                                            :disabled="updating"
                                            type="button"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                            :class="autoRenew ? 'bg-blue-600' : 'bg-slate-200'"
                                            role="switch"
                                            :aria-checked="autoRenew">
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                              :class="autoRenew ? 'translate-x-5' : 'translate-x-0'">
                                        </span>
                                    </button>
                                </div>

                                {{-- Expiry Info --}}
                                <div class="pt-3 border-t border-slate-200">
                                    <p class="text-sm text-slate-600">
                                        <span class="font-medium">{{ __('Expires:') }}</span>
                                        {{ $contract->end_date->format('d.m.Y') }}
                                        @if($contract->days_until_expiry !== null)
                                            <span class="text-xs ml-1
                                                @if($contract->expiry_urgency === 'urgent') text-red-600
                                                @elseif($contract->expiry_urgency === 'warning') text-yellow-600
                                                @else text-slate-500
                                                @endif">
                                                ({{ trans_choice(':count day left|:count days left', $contract->days_until_expiry, ['count' => $contract->days_until_expiry]) }})
                                            </span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500 mt-1" x-show="autoRenew">
                                        {{ __('Next renewal will create a new contract starting :date', ['date' => $contract->end_date->addDay()->format('d.m.Y')]) }}
                                    </p>
                                </div>

                                {{-- Renewal History (if any) --}}
                                @if($contract->parentContract || $contract->renewals->count() > 0)
                                    <div class="pt-3 border-t border-slate-200">
                                        <p class="text-xs font-medium text-slate-600 mb-2">{{ __('Renewal Chain') }}</p>
                                        <div class="space-y-1 text-xs">
                                            @if($contract->parentContract)
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                                                    </svg>
                                                    <a href="{{ route('contracts.show', $contract->parentContract) }}" class="text-blue-600 hover:text-blue-800">
                                                        {{ $contract->parentContract->contract_number }}
                                                    </a>
                                                    <span class="text-slate-400">{{ __('(previous)') }}</span>
                                                </div>
                                            @endif
                                            @foreach($contract->renewals as $renewal)
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                                    </svg>
                                                    <a href="{{ route('contracts.show', $renewal) }}" class="text-blue-600 hover:text-blue-800">
                                                        {{ $renewal->contract_number }}
                                                    </a>
                                                    <span class="text-slate-400">{{ __('(renewal)') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Update feedback --}}
                                <div x-show="message" x-transition class="text-xs" :class="messageType === 'success' ? 'text-green-600' : 'text-red-600'" x-text="message"></div>
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                @endif

                {{-- Version History --}}
                <x-ui.card x-data="versionHistory({{ $contract->id }})">
                    <x-ui.card-header>
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-slate-900">{{ __('Version History') }}</h3>
                            <button @click="loadVersions()" class="text-xs text-blue-600 hover:text-blue-800">
                                <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div x-show="!loaded" class="text-center py-4">
                            <button @click="loadVersions()" class="text-sm text-blue-600 hover:text-blue-800">
                                {{ __('Load versions') }}
                            </button>
                        </div>
                        <div x-show="loaded">
                            <template x-if="versions.length === 0">
                                <p class="text-sm text-slate-500 text-center py-4">{{ __('No version history yet.') }}</p>
                            </template>
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                <template x-for="version in versions" :key="version.id">
                                    <div class="flex items-start gap-3 text-sm p-2 rounded" :class="{ 'bg-blue-50': version.is_current }">
                                        <div class="flex-shrink-0 w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-xs font-medium text-slate-600" x-text="'v' + version.version_number"></div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-slate-900" x-text="version.author"></span>
                                                <span x-show="version.is_current" class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">{{ __('Current') }}</span>
                                            </div>
                                            <div class="text-xs text-slate-500" x-text="version.created_at_human"></div>
                                            <div x-show="version.reason" class="text-xs text-slate-600 mt-1" x-text="version.reason"></div>
                                            <button x-show="!version.is_current"
                                                    @click="restoreVersion(version.version_number)"
                                                    class="text-xs text-blue-600 hover:text-blue-800 mt-1">
                                                {{ __('Restore') }}
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Activity Log --}}
                <x-ui.card x-data="activityLog({{ $contract->id }})" x-init="loadActivities()">
                    <x-ui.card-header>
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-slate-900">{{ __('Activity') }}</h3>
                            <button @click="loadActivities()" class="text-xs text-blue-600 hover:text-blue-800">
                                <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div x-show="loading" class="text-center py-4">
                            <x-ui.spinner size="sm" />
                        </div>
                        <div x-show="loaded && !loading">
                            <template x-if="activities.length === 0">
                                <p class="text-sm text-slate-500 text-center py-4">{{ __('No activity recorded.') }}</p>
                            </template>
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                <template x-for="activity in activities" :key="activity.id">
                                    <div class="flex items-start gap-3 text-sm">
                                        <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center"
                                             :class="{
                                                 'bg-green-100 text-green-600': activity.action_color === 'green',
                                                 'bg-blue-100 text-blue-600': activity.action_color === 'blue',
                                                 'bg-yellow-100 text-yellow-600': activity.action_color === 'yellow',
                                                 'bg-red-100 text-red-600': activity.action_color === 'red',
                                                 'bg-slate-100 text-slate-600': !activity.action_color
                                             }">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-slate-900">
                                                <span class="font-medium" x-text="activity.performer"></span>
                                                <span x-text="activity.action_label"></span>
                                            </div>
                                            <div class="text-xs text-slate-500" x-text="activity.created_at_human"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Quick Actions --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="font-semibold text-slate-900">{{ __('Actions') }}</h3>
                    </x-ui.card-header>
                    <x-ui.card-content class="space-y-2">
                        @if($contract->client_id)
                            <x-ui.button variant="outline" class="w-full justify-center" onclick="window.location.href='{{ route('offers.create', ['client_id' => $contract->client_id]) }}'">
                                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('New Offer for Client') }}
                            </x-ui.button>
                        @else
                            <x-ui.button variant="outline" class="w-full justify-center" onclick="window.location.href='{{ route('offers.create') }}'">
                                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('New Offer') }}
                            </x-ui.button>
                        @endif
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function versionHistory(contractId) {
        return {
            versions: [],
            loaded: false,
            loading: false,

            async loadVersions() {
                if (this.loading) return;
                this.loading = true;
                try {
                    const response = await fetch(`/contracts/${contractId}/versions`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    this.versions = data.versions || [];
                    this.loaded = true;
                } catch (error) {
                    console.error('Failed to load versions:', error);
                } finally {
                    this.loading = false;
                }
            },

            async restoreVersion(versionNumber) {
                if (!confirm('{{ __("Are you sure you want to restore this version?") }}')) return;

                try {
                    const response = await fetch(`/contracts/${contractId}/versions/${versionNumber}/restore`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        const data = await response.json();
                        alert(data.message || '{{ __("Failed to restore version") }}');
                    }
                } catch (error) {
                    console.error('Failed to restore version:', error);
                    alert('{{ __("Failed to restore version") }}');
                }
            }
        };
    }

    function activityLog(contractId) {
        return {
            activities: [],
            loaded: false,
            loading: false,

            async loadActivities() {
                if (this.loading) return;
                this.loading = true;
                try {
                    const response = await fetch(`/contracts/${contractId}/activities`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    this.activities = data.activities || [];
                    this.loaded = true;
                } catch (error) {
                    console.error('Failed to load activities:', error);
                } finally {
                    this.loading = false;
                }
            }
        };
    }

    function autoRenewManager(contractId, initialValue) {
        return {
            autoRenew: initialValue,
            updating: false,
            message: '',
            messageType: 'success',

            async toggleAutoRenew() {
                if (this.updating) return;

                this.updating = true;
                this.message = '';

                const newValue = !this.autoRenew;

                try {
                    const response = await fetch(`/contracts/${contractId}/toggle-auto-renew`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ auto_renew: newValue })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.autoRenew = newValue;
                        this.message = data.message || '{{ __("Auto-renewal updated successfully") }}';
                        this.messageType = 'success';
                        setTimeout(() => this.message = '', 3000);
                    } else {
                        throw new Error(data.message || '{{ __("Failed to update auto-renewal") }}');
                    }
                } catch (error) {
                    console.error('Failed to toggle auto-renew:', error);
                    this.message = error.message;
                    this.messageType = 'error';
                    setTimeout(() => this.message = '', 5000);
                } finally {
                    this.updating = false;
                }
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
