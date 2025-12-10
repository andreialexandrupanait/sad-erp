<x-app-layout>
    <x-slot name="pageTitle">{{ $contract->contract_number }}</x-slot>

    <x-slot name="headerActions">
        <div class="flex items-center gap-2">
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
                            <div class="prose prose-sm max-w-none">
                                {!! $contract->content !!}
                            </div>
                        @else
                            <p class="text-slate-500 italic">{{ __('Contract content not yet generated.') }}</p>
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

                {{-- Quick Actions --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="font-semibold text-slate-900">{{ __('Actions') }}</h3>
                    </x-ui.card-header>
                    <x-ui.card-content class="space-y-2">
                        <x-ui.button variant="outline" class="w-full justify-center" onclick="window.location.href='{{ route('offers.create', ['client_id' => $contract->client_id]) }}'">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('New Offer for Client') }}
                        </x-ui.button>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-app-layout>
