<x-app-layout>
    <x-slot name="pageTitle">{{ $annex->annex_code }}</x-slot>

    <x-slot name="headerActions">
        <div class="flex items-center gap-2">
            {{-- Back to Contract --}}
            <x-ui.button variant="outline" onclick="window.location.href='{{ route('contracts.show', $contract) }}'">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Back to Contract') }}
            </x-ui.button>

            {{-- Edit --}}
            <x-ui.button variant="primary" onclick="window.location.href='{{ route('contracts.annex.edit', [$contract, $annex]) }}'">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                {{ __('Edit Content') }}
            </x-ui.button>

            {{-- Download PDF --}}
            @if($annex->pdf_path)
                <x-ui.button variant="outline" onclick="window.location.href='{{ route('contracts.annex.download', [$contract, $annex]) }}'">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('Download PDF') }}
                </x-ui.button>
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
                {{-- Annex Content --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold">{{ $annex->title ?: __('Annex Content') }}</h2>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-700">
                                {{ $annex->annex_code }}
                            </span>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        @if($annex->content)
                            <div class="contract-content">
                                {!! $annex->content !!}
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-4 text-slate-500">{{ __('Annex content not yet generated.') }}</p>
                                <p class="mt-1 text-sm text-slate-400">{{ __('Use the editor to create the content.') }}</p>
                                <a href="{{ route('contracts.annex.edit', [$contract, $annex]) }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    {{ __('Edit Annex') }}
                                </a>
                            </div>
                        @endif
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Services from Offer --}}
                @if($annex->offer && $annex->offer->items->count() > 0)
                    @php
                        $selectedItems = $annex->offer->items->where('is_selected', true);
                        $selectedTotal = $selectedItems->sum('total_price');
                    @endphp
                    <x-ui.card>
                        <x-ui.card-header>
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold">{{ __('Annex Services') }}</h3>
                                <a href="{{ route('offers.show', $annex->offer) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                    {{ $annex->offer->offer_number }}
                                </a>
                            </div>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            @if($selectedItems->count() > 0)
                                <ul class="space-y-2 text-sm">
                                    @foreach($selectedItems as $item)
                                        <li class="flex justify-between items-start">
                                            <div>
                                                <span class="text-slate-900">{{ $item->title }}</span>
                                                @if($item->quantity > 1)
                                                    <span class="text-slate-500">(x{{ $item->quantity }})</span>
                                                @endif
                                            </div>
                                            <span class="font-medium text-slate-900">{{ number_format($item->total_price, 2) }} {{ $annex->offer->currency }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-4 pt-3 border-t flex justify-between items-center">
                                    <span class="font-medium text-slate-700">{{ __('Total') }}</span>
                                    <span class="font-semibold text-slate-900">{{ number_format($selectedTotal, 2) }} {{ $annex->offer->currency }}</span>
                                </div>
                            @else
                                <p class="text-sm text-slate-500">{{ __('No services in this annex.') }}</p>
                            @endif
                        </x-ui.card-content>
                    </x-ui.card>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Document Files --}}
                <x-document-section :documentable="$annex" type="annex" />

                {{-- Client Card --}}
                @if($contract->client || $contract->temp_client_name)
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="font-semibold">{{ __('Client') }}</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        @if($contract->client)
                            <div class="space-y-4">
                                {{-- Client Name and Avatar --}}
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-sm font-medium text-blue-600">{{ strtoupper(substr($contract->client->display_name, 0, 2)) }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('clients.show', $contract->client) }}" class="text-sm font-medium text-slate-900 hover:text-blue-600">
                                            {{ $contract->client->display_name }}
                                        </a>
                                        @if($contract->client->company_name && $contract->client->company_name !== $contract->client->name)
                                            <p class="text-xs text-slate-500">{{ $contract->client->company_name }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Client Details --}}
                                <dl class="space-y-2 text-sm border-t pt-3">
                                    @if($contract->client->contact_person)
                                    <div class="flex justify-between">
                                        <dt class="text-slate-500">{{ __('Contact') }}</dt>
                                        <dd class="text-slate-900 text-right">{{ $contract->client->contact_person }}</dd>
                                    </div>
                                    @endif
                                    @if($contract->client->phone)
                                    <div class="flex justify-between">
                                        <dt class="text-slate-500">{{ __('Phone') }}</dt>
                                        <dd class="text-slate-900 text-right">{{ $contract->client->phone }}</dd>
                                    </div>
                                    @endif
                                    @if($contract->client->email)
                                    <div class="flex justify-between">
                                        <dt class="text-slate-500">{{ __('Email') }}</dt>
                                        <dd class="text-slate-900 text-right truncate max-w-[150px]" title="{{ $contract->client->email }}">{{ $contract->client->email }}</dd>
                                    </div>
                                    @endif
                                    @if($contract->client->address)
                                    <div class="flex justify-between">
                                        <dt class="text-slate-500">{{ __('Address') }}</dt>
                                        <dd class="text-slate-900 text-right text-xs max-w-[180px]">{{ $contract->client->address }}</dd>
                                    </div>
                                    @endif
                                    @if($contract->client->tax_id)
                                    <div class="flex justify-between">
                                        <dt class="text-slate-500">{{ __('CUI') }}</dt>
                                        <dd class="text-slate-900 font-mono text-right">{{ $contract->client->tax_id }}</dd>
                                    </div>
                                    @endif
                                    @if($contract->client->registration_number)
                                    <div class="flex justify-between">
                                        <dt class="text-slate-500">{{ __('Reg. Com.') }}</dt>
                                        <dd class="text-slate-900 font-mono text-xs text-right">{{ $contract->client->registration_number }}</dd>
                                    </div>
                                    @endif
                                    @if($contract->client->bank_account)
                                    <div class="flex justify-between">
                                        <dt class="text-slate-500">{{ __('IBAN') }}</dt>
                                        <dd class="text-slate-900 font-mono text-xs text-right truncate max-w-[150px]" title="{{ $contract->client->bank_account }}">{{ $contract->client->bank_account }}</dd>
                                    </div>
                                    @endif
                                </dl>
                            </div>
                        @else
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-slate-900">{{ $contract->temp_client_name }}</p>
                                @if($contract->temp_client_company)
                                    <p class="text-xs text-slate-500">{{ $contract->temp_client_company }}</p>
                                @endif
                                @if($contract->temp_client_email)
                                    <p class="text-xs text-slate-500">{{ $contract->temp_client_email }}</p>
                                @endif
                            </div>
                        @endif
                    </x-ui.card-content>
                </x-ui.card>
                @endif

                {{-- Annex Details Card --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="font-semibold">{{ __('Annex Details') }}</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500">{{ __('Annex Number') }}</dt>
                                <dd class="font-medium text-slate-900">#{{ $annex->annex_number }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">{{ __('Effective Date') }}</dt>
                                <dd class="font-medium text-slate-900">{{ $annex->effective_date?->format('d.m.Y') ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">{{ __('Additional Value') }}</dt>
                                <dd class="font-medium text-green-600">+{{ number_format($annex->additional_value, 2) }} {{ $annex->currency }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">{{ __('Created') }}</dt>
                                <dd class="text-slate-900">{{ $annex->created_at->format('d.m.Y H:i') }}</dd>
                            </div>
                        </dl>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Parent Contract Card --}}
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="font-semibold">{{ __('Parent Contract') }}</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <a href="{{ route('contracts.show', $contract) }}" class="block group">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-slate-900 group-hover:text-blue-600">{{ $contract->formatted_number }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
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
                            <p class="text-sm text-slate-500 mt-1">{{ $contract->title }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ number_format($contract->total_value, 2) }} {{ $contract->currency }}</p>
                        </a>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-app-layout>