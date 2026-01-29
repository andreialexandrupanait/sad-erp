@pushOnce('styles')
<style>
    /* Contract content display styles */
    .contract-content {
        font-size: 14px !important;
        line-height: 1.5 !important;
        color: #1e293b;
    }
    .contract-content h1 {
        font-size: 1.875rem !important;
        font-weight: 700 !important;
        line-height: 1.3 !important;
        margin-top: 1.5rem !important;
        margin-bottom: 0.25rem !important;
        color: #1e293b;
    }
    .contract-content h2 {
        font-size: 1.5rem !important;
        font-weight: 600 !important;
        line-height: 1.35 !important;
        margin-top: 1.25rem !important;
        margin-bottom: 0.2rem !important;
        color: #1e293b;
    }
    .contract-content h3 {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        line-height: 1.4 !important;
        margin-top: 1rem !important;
        margin-bottom: 0.15rem !important;
        color: #334155;
    }
    .contract-content p {
        margin-bottom: 0.5rem !important;
        line-height: 1.5 !important;
    }
    .contract-content ul,
    .contract-content ol {
        margin-bottom: 0.75rem !important;
        padding-left: 1.5rem !important;
    }
    .contract-content li {
        margin-bottom: 0.1rem !important;
        line-height: 1.4 !important;
    }
    .contract-content li:last-child {
        margin-bottom: 0.5rem !important;
    }
    .contract-content blockquote {
        border-left: 4px solid #3b82f6 !important;
        padding-left: 1rem !important;
        margin: 0.75rem 0 !important;
        color: #475569;
        font-style: italic;
    }
    .contract-content hr {
        margin: 1rem 0 !important;
        border-color: #e2e8f0;
    }
    .contract-content > *:first-child {
        margin-top: 0 !important;
    }
    .contract-content p:empty::before {
        content: '\00a0';
    }
    .contract-content .ql-align-center { text-align: center; }
    .contract-content .ql-align-right { text-align: right; }
    .contract-content .ql-align-justify { text-align: justify; }
    .contract-content .ql-indent-1 { padding-left: 3em; }
    .contract-content .ql-indent-2 { padding-left: 6em; }
    .contract-content .ql-indent-3 { padding-left: 9em; }
    .contract-content .ql-size-small { font-size: 0.75em; }
    .contract-content .ql-size-large { font-size: 1.5em; }
    .contract-content .ql-size-huge { font-size: 2.5em; }
</style>
@endPushOnce

<x-app-layout>
    <x-slot name="pageTitle">{{ $contract->formatted_number }}</x-slot>

    <x-slot name="headerActions">
        {{-- Mobile: Dropdown menu --}}
        <div class="md:hidden" x-data="{ open: false }">
            <div class="flex items-center gap-2">
                <x-ui.button variant="outline" onclick="window.location.href='{{ route('contracts.index') }}'" class="p-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </x-ui.button>
                @if(in_array($contract->status, ['draft', 'active']))
                    <x-ui.button variant="primary" onclick="window.location.href='{{ route('contracts.edit', $contract) }}'" class="p-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </x-ui.button>
                @endif
                <div class="relative">
                    <button @click="open = !open" type="button" class="inline-flex items-center justify-center p-2 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50">
                        @if($contract->isActive())
                            <a href="{{ route('contracts.add-annex', $contract) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">{{ __('Add Annex') }}</a>
                        @endif
                        @if($contract->pdf_path)
                            <a href="{{ route('contracts.download', $contract) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">{{ __('Download PDF') }}</a>
                        @endif
                        @if($contract->isDraft() && $contract->content && !$contract->is_finalized)
                            <a href="{{ route('contracts.preview', $contract) }}" target="_blank" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">{{ __('Preview PDF') }}</a>
                            <button onclick="finalizeAndRedirect()" class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">{{ __('Finalize & Download') }}</button>
                        @endif
                        @if($contract->isActive())
                            <form action="{{ route('contracts.terminate', $contract) }}" method="POST" class="block"
                                  onsubmit="return confirm('{{ __('Are you sure you want to terminate this contract?') }}')">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">{{ __('Terminate') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Desktop: Full button row --}}
        <div class="hidden md:flex items-center gap-2">
            {{-- Navigation --}}
            <x-ui.button variant="outline" onclick="window.location.href='{{ route('contracts.index') }}'">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Back to Contracts') }}
            </x-ui.button>

            <div class="h-6 w-px bg-slate-200"></div>

            {{-- Primary Actions --}}
            {{-- Edit Content (Draft or Active contracts) --}}
            @if(in_array($contract->status, ['draft', 'active']))
                <x-ui.button variant="primary" onclick="window.location.href='{{ route('contracts.edit', $contract) }}'">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    {{ __('Edit Content') }}
                </x-ui.button>
            @endif

            {{-- Add Annex moved up for active contracts --}}
            @if($contract->isActive())
                <x-ui.button variant="default" onclick="window.location.href='{{ route('contracts.add-annex', $contract) }}'">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Add Annex') }}
                </x-ui.button>
            @endif

            <div class="h-6 w-px bg-slate-200"></div>

            {{-- PDF Actions --}}
            {{-- Preview PDF button for draft contracts with content --}}
            @if($contract->isDraft() && $contract->content && !$contract->is_finalized)
                <x-ui.button variant="outline" onclick="window.open('{{ route('contracts.preview', $contract) }}', '_blank')">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{ __('Preview PDF') }}
                </x-ui.button>

                <x-ui.button variant="default" type="button" onclick="finalizeAndRedirect()">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('Finalize & Download PDF') }}
                    </x-ui.button>

                    <script>
                    function finalizeAndRedirect() {
                        if (!confirm('{{ __('Are you sure you want to finalize this contract? Once finalized, the contract cannot be edited.') }}')) {
                            return;
                        }

                        // Create hidden iframe for download
                        const iframe = document.createElement('iframe');
                        iframe.name = 'download_frame_' + Date.now();
                        iframe.style.display = 'none';
                        document.body.appendChild(iframe);

                        // Create form targeting the iframe
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("contracts.finalize-and-download", $contract) }}';
                        form.target = iframe.name;

                        // Add CSRF token
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = '{{ csrf_token() }}';
                        form.appendChild(csrfInput);

                        document.body.appendChild(form);
                        form.submit();

                        // Redirect after delay to allow download to start
                        setTimeout(function() {
                            window.location.href = '{{ route("contracts.index") }}';
                        }, 1500);
                    }
                    </script>
            @endif

            @if($contract->pdf_path)
                <x-ui.button variant="outline" onclick="window.location.href='{{ route('contracts.download', $contract) }}'">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('Download PDF') }}
                </x-ui.button>
            @endif

            {{-- Destructive Actions --}}
            @if($contract->isActive())
                <div class="ml-2">
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
                </div>
            @endif
        </div>
    </x-slot>

    <div class="p-4 md:p-6 space-y-4 md:space-y-6">
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
                                    <a href="{{ route('contracts.edit', $contract) }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ __('Edit Contract') }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Contracted Services (from Original Offer) --}}
                @if($contract->offer)
                    @php
                        $selectedItems = $contract->offer->items->where('is_selected', true);
                        $selectedTotal = $selectedItems->sum('total_price');
                    @endphp
                    <x-ui.card>
                        <x-ui.card-header>
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold">{{ __('Contracted Services') }}</h3>
                                <a href="{{ route('offers.show', $contract->offer) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                    {{ $contract->offer->offer_number }}
                                </a>
                            </div>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            {{-- Selected Services --}}
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
                                            <span class="font-medium text-slate-900">{{ number_format($item->total_price, 2) }} {{ $contract->offer->currency }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-4 pt-3 border-t flex justify-between items-center">
                                    <span class="font-medium text-slate-700">{{ __('Total') }}</span>
                                    <span class="font-semibold text-slate-900">{{ number_format($selectedTotal, 2) }} {{ $contract->offer->currency }}</span>
                                </div>
                            @else
                                <p class="text-sm text-slate-500 italic">{{ __('No services selected') }}</p>
                            @endif

                            @if($contract->offer->accepted_at)
                                <div class="mt-3 pt-3 border-t text-xs text-slate-500">
                                    {{ __('Accepted') }}: {{ $contract->offer->accepted_at->format('d.m.Y H:i') }}
                                </div>
                            @endif
                        </x-ui.card-content>
                    </x-ui.card>
                @endif

                {{-- Annexes --}}
                @if($contract->annexes->count() > 0)
                    <x-ui.card>
                        <x-ui.card-header>
                            <h3 class="font-semibold">{{ __('Annexes') }}</h3>
                        </x-ui.card-header>
                        <x-ui.card-content class="p-0">
                            <table class="w-full text-sm">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium">{{ __('Code') }}</th>
                                        <th class="px-4 py-3 text-left font-medium">{{ __('Title') }}</th>
                                        <th class="px-4 py-3 text-right font-medium">{{ __('Value') }}</th>
                                        <th class="px-4 py-3 text-center font-medium">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($contract->annexes as $annex)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('contracts.annex.show', [$contract, $annex]) }}" class="font-medium text-blue-600 hover:text-blue-800">
                                                {{ $annex->annex_code }}
                                            </a>
                                            <div class="text-xs text-slate-400 mt-0.5">{{ $annex->effective_date->format('d.m.Y') }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ $annex->title }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="font-medium text-green-600">+{{ number_format($annex->additional_value, 2) }}</span>
                                            <span class="text-slate-400 text-xs">{{ $annex->currency }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="{{ route('contracts.annex.show', [$contract, $annex]) }}"
                                                   class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded" title="{{ __('View') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('contracts.annex.edit', [$contract, $annex]) }}"
                                                   class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded" title="{{ __('Edit') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                                @if($annex->pdf_path)
                                                    <a href="{{ route('contracts.annex.download', [$contract, $annex]) }}"
                                                       class="p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded" title="{{ __('Download PDF') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </x-ui.card-content>
                    </x-ui.card>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Client Info --}}
                <div x-data="{ editingClient: false }">
                <x-ui.card>
                    <x-ui.card-header class="flex items-center justify-between">
                        <h3 class="font-semibold">{{ __('Client') }}</h3>
                        @if(($contract->temp_client_name || $contract->offer?->temp_client_name) && $contract->isDraft())
                            <button @click="editingClient = true" type="button"
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                                    title="{{ __('Edit client details') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        @endif
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div>
                            @if($contract->client)
                                @php
                                    // Use temp overrides if they exist, otherwise fall back to client data
                                    $displayName = $contract->temp_client_name ?: $contract->offer?->temp_client_name ?: $contract->client->name;
                                    $displayCompany = $contract->temp_client_company ?: $contract->offer?->temp_client_company ?: ($contract->client->company_name ?? null);
                                    $displayContactPerson = $contract->client->contact_person;
                                    $displayEmail = $contract->temp_client_email ?: $contract->offer?->temp_client_email ?: $contract->client->email;
                                    $displayPhone = $contract->temp_client_phone ?: $contract->offer?->temp_client_phone ?: $contract->client->phone;
                                    $displayAddress = $contract->temp_client_address ?: $contract->offer?->temp_client_address ?: $contract->client->address;
                                    $displayTaxId = $contract->temp_client_tax_id ?: $contract->offer?->temp_client_tax_id ?: $contract->client->tax_id;
                                    $displayRegNumber = $contract->temp_client_registration_number ?: $contract->offer?->temp_client_registration_number ?: $contract->client->registration_number;
                                    $displayBankAccount = $contract->temp_client_bank_account ?: $contract->offer?->temp_client_bank_account ?: ($contract->client->bank_account ?? null);
                                    $displayBankName = $contract->client->bank_name ?? null;
                                @endphp
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-semibold">
                                        {{ strtoupper(substr($displayName, 0, 2)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('clients.show', $contract->client) }}" class="font-semibold text-slate-900 hover:text-blue-600">
                                            {{ $displayName }}
                                        </a>
                                        @if($displayCompany && $displayCompany !== $displayName)
                                            <p class="text-sm text-slate-500">{{ $displayCompany }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-4 space-y-2">
                                    @if($displayContactPerson)
                                        <div class="flex items-center gap-2 text-sm text-slate-600">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span>{{ $displayContactPerson }}</span>
                                        </div>
                                    @endif
                                    @if($displayEmail)
                                        <a href="mailto:{{ $displayEmail }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-blue-600">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $displayEmail }}
                                        </a>
                                    @endif
                                    @if($displayPhone)
                                        <a href="tel:{{ $displayPhone }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-blue-600">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            {{ $displayPhone }}
                                        </a>
                                    @endif
                                    @if($displayAddress)
                                        <div class="flex items-start gap-2 text-sm text-slate-600">
                                            <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span>{{ $displayAddress }}</span>
                                        </div>
                                    @endif
                                    @if($displayTaxId)
                                        <div class="flex items-center gap-2 text-sm text-slate-600">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span>{{ __('CUI:') }} {{ $displayTaxId }}</span>
                                        </div>
                                    @endif
                                    @if($displayRegNumber)
                                        <div class="flex items-center gap-2 text-sm text-slate-600">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            <span>{{ __('Reg. Com.:') }} {{ $displayRegNumber }}</span>
                                        </div>
                                    @endif
                                    @if($displayBankAccount)
                                        <div class="flex items-center gap-2 text-sm text-slate-600">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                            <span>{{ __('IBAN:') }} {{ $displayBankAccount }}@if($displayBankName) ({{ $displayBankName }})@endif</span>
                                        </div>
                                    @endif
                                </div>
                            @elseif($contract->temp_client_name || $contract->offer?->temp_client_name)
                                {{-- Temp client from contract or offer --}}
                                @php
                                    $tempName = $contract->temp_client_name ?? $contract->offer?->temp_client_name;
                                    $tempCompany = $contract->temp_client_company ?? $contract->offer?->temp_client_company;
                                    $tempEmail = $contract->temp_client_email ?? $contract->offer?->temp_client_email;
                                    $tempPhone = $contract->temp_client_phone ?? $contract->offer?->temp_client_phone;
                                    $tempAddress = $contract->temp_client_address ?? $contract->offer?->temp_client_address;
                                    $tempTaxId = $contract->temp_client_tax_id ?? $contract->offer?->temp_client_tax_id;
                                    $tempRegNumber = $contract->temp_client_registration_number ?? $contract->offer?->temp_client_registration_number;
                                    $tempBankAccount = $contract->temp_client_bank_account ?? $contract->offer?->temp_client_bank_account;
                                @endphp
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-slate-900">{{ $tempName }}</p>
                                        @if($tempCompany)
                                            <p class="text-sm text-slate-500">{{ $tempCompany }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-4 space-y-2">
                                    @if($tempEmail)
                                        <a href="mailto:{{ $tempEmail }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-blue-600">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $tempEmail }}
                                        </a>
                                    @endif
                                    @if($tempPhone)
                                        <a href="tel:{{ $tempPhone }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-blue-600">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            {{ $tempPhone }}
                                        </a>
                                    @endif
                                    @if($tempAddress)
                                        <div class="flex items-start gap-2 text-sm text-slate-600">
                                            <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span>{{ $tempAddress }}</span>
                                        </div>
                                    @endif
                                    @if($tempTaxId)
                                        <div class="flex items-center gap-2 text-sm text-slate-600">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span>{{ __('CUI:') }} {{ $tempTaxId }}</span>
                                        </div>
                                    @endif
                                    @if($tempRegNumber)
                                        <div class="flex items-center gap-2 text-sm text-slate-600">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            <span>{{ __('Reg. Com.:') }} {{ $tempRegNumber }}</span>
                                        </div>
                                    @endif
                                    @if($tempBankAccount)
                                        <div class="flex items-center gap-2 text-sm text-slate-600">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                            <span>{{ __('IBAN:') }} {{ $tempBankAccount }}</span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="text-slate-500 italic">{{ __('No client assigned') }}</p>
                            @endif
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Edit Temp Client Modal --}}
                @if(($contract->temp_client_name || $contract->offer?->temp_client_name) && $contract->isDraft())
                    @php
                        $tempName = $contract->temp_client_name ?? $contract->offer?->temp_client_name;
                        $tempCompany = $contract->temp_client_company ?? $contract->offer?->temp_client_company;
                        $tempEmail = $contract->temp_client_email ?? $contract->offer?->temp_client_email;
                        $tempPhone = $contract->temp_client_phone ?? $contract->offer?->temp_client_phone;
                        $tempAddress = $contract->temp_client_address ?? $contract->offer?->temp_client_address;
                        $tempTaxId = $contract->temp_client_tax_id ?? $contract->offer?->temp_client_tax_id;
                        $tempRegNumber = $contract->temp_client_registration_number ?? $contract->offer?->temp_client_registration_number;
                        $tempBankAccount = $contract->temp_client_bank_account ?? $contract->offer?->temp_client_bank_account;
                    @endphp
                    <div x-show="editingClient" x-cloak
                         class="fixed inset-0 z-50 overflow-y-auto"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                            <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" @click="editingClient = false"></div>

                            <div class="relative bg-white rounded-xl shadow-xl transform transition-all sm:max-w-lg sm:w-full mx-auto"
                                 x-transition:enter="ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave="ease-in duration-200"
                                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                 @click.stop>
                                <form action="{{ route('contracts.update-temp-client', $contract) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 rounded-t-xl">
                                        <h3 class="text-lg font-semibold text-slate-900">{{ __('Edit Client Details') }}</h3>
                                    </div>
                                    <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Name') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="temp_client_name" value="{{ $tempName }}" required
                                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Company') }}</label>
                                            <input type="text" name="temp_client_company" value="{{ $tempCompany }}"
                                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Email') }}</label>
                                                <input type="email" name="temp_client_email" value="{{ $tempEmail }}"
                                                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Phone') }}</label>
                                                <input type="text" name="temp_client_phone" value="{{ $tempPhone }}"
                                                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Address') }}</label>
                                            <textarea name="temp_client_address" rows="2"
                                                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $tempAddress }}</textarea>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('CUI') }}</label>
                                                <input type="text" name="temp_client_tax_id" value="{{ $tempTaxId }}"
                                                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Reg. Com.') }}</label>
                                                <input type="text" name="temp_client_registration_number" value="{{ $tempRegNumber }}"
                                                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Bank Account (IBAN)') }}</label>
                                            <input type="text" name="temp_client_bank_account" value="{{ $tempBankAccount }}"
                                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end gap-3">
                                        <button type="button" @click="editingClient = false"
                                                class="px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                                            {{ __('Cancel') }}
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                            {{ __('Save Changes') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
                </div>

                {{-- Document Files --}}
                <x-document-section :documentable="$contract" type="contract" />

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
                                @if($contract->hasMixedCurrencyAnnexes())
                                    <div class="flex justify-between items-start">
                                        <dt class="text-slate-500">{{ __('With Annexes') }}</dt>
                                        <dd class="text-right">
                                            <div class="font-medium text-slate-900">{{ number_format($contract->total_value_with_annexes, 2) }} {{ $contract->currency }}</div>
                                            @foreach($contract->getAnnexValuesByCurrency() as $currency => $value)
                                                @if($currency !== $contract->currency)
                                                    <div class="text-sm text-purple-600">+ {{ number_format($value, 2) }} {{ $currency }}</div>
                                                @endif
                                            @endforeach
                                            <div class="text-xs text-amber-600 mt-1">
                                                <svg class="w-3 h-3 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                                {{ __('Multi-monedă') }}
                                            </div>
                                        </dd>
                                    </div>
                                @else
                                    <div class="flex justify-between">
                                        <dt class="text-slate-500">{{ __('With Annexes') }}</dt>
                                        <dd class="font-medium text-slate-900">{{ number_format($contract->total_value_with_annexes, 2) }} {{ $contract->currency }}</dd>
                                    </div>
                                @endif
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
                                                        {{ $contract->parentContract->formatted_number }}
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
                                                        {{ $renewal->formatted_number }}
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
                <x-ui.card x-data="versionHistory({{ $contract->id }})" x-init="loadVersions()">
                    <x-ui.card-header>
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-slate-900">{{ __('Version History') }}</h3>
                            <button @click="loadVersions()" class="text-xs text-blue-600 hover:text-blue-800" title="{{ __('Refresh') }}">
                                <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div x-show="loading && !loaded" class="text-center py-4">
                            <x-ui.spinner size="sm" />
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
