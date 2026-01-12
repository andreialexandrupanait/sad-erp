<x-app-layout>
    <x-slot name="pageTitle">{{ __('Add Annex to') }} {{ $contract->formatted_number }}</x-slot>

    <div class="p-4 md:p-6 max-w-2xl">
        {{-- Error Messages --}}
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="ml-2 text-sm text-red-700">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul class="text-sm text-red-700 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Warning if contract is not finalized --}}
        @if($contract->isActive() && !$contract->is_finalized)
            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex">
                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="ml-2 text-sm text-yellow-700">{{ __('This contract is not finalized yet. Finalize it first to add annexes.') }}</span>
                </div>
            </div>
        @endif

        <x-ui.card>
            <x-ui.card-header>
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                    <h2 class="text-lg font-semibold">{{ __('Add Annex to Contract') }}</h2>
                    <x-ui.button variant="outline" size="sm" class="w-full sm:w-auto" onclick="window.location.href='{{ route('offers.create', ['client_id' => $contract->client_id, 'parent_contract_id' => $contract->id]) }}'">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('Create New Offer') }}
                    </x-ui.button>
                </div>
            </x-ui.card-header>
            <x-ui.card-content>
                {{-- Contract Info --}}
                <div class="mb-6 p-4 bg-slate-50 rounded-lg">
                    <div class="text-sm text-slate-600">{{ __('Contract') }}</div>
                    <div class="font-semibold text-slate-900">{{ $contract->formatted_number }} - {{ $contract->title }}</div>
                    <div class="text-sm text-slate-500">{{ __('Client') }}: {{ $contract->client?->display_name ?? $contract->temp_client_name }}</div>
                    <div class="text-sm text-slate-500">{{ __('Current Value') }}: {{ number_format($contract->total_value, 2) }} {{ $contract->currency }}</div>
                    <div class="text-sm text-slate-500">
                        {{ __('Status') }}: 
                        <span class="font-medium {{ $contract->is_finalized ? 'text-green-600' : 'text-yellow-600' }}">
                            {{ $contract->is_finalized ? __('Finalized') : __('Not Finalized') }}
                        </span>
                    </div>
                </div>

                @if($availableOffers->count() > 0)
                    <form action="{{ route('contracts.add-annex.store', $contract) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <h3 class="text-sm font-medium text-slate-700 mb-2">{{ __('Select an accepted offer to add as annex:') }}</h3>
                        </div>
                        <div class="space-y-3">
                            @foreach($availableOffers as $offer)
                                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-slate-50 hover:border-blue-300 transition-colors {{ $loop->first ? 'border-blue-500 bg-blue-50' : '' }}">
                                    <input type="radio" name="offer_id" value="{{ $offer->id }}"
                                           {{ $loop->first ? 'checked' : '' }}
                                           class="mr-4 text-blue-600 focus:ring-blue-500">
                                    <div class="flex-1">
                                        <div class="font-medium text-slate-900">{{ $offer->offer_number }}</div>
                                        <div class="text-sm text-slate-500">{{ $offer->title }}</div>
                                        <div class="text-xs text-slate-400">{{ __('Accepted') }}: {{ $offer->accepted_at ? $offer->accepted_at->format('d.m.Y') : '-' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium text-slate-900">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        {{-- Annex Template Selection --}}
                        @if(isset($annexTemplates) && $annexTemplates->count() > 0)
                            <div class="mt-6">
                                <label for="template_id" class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('Annex Template') }}
                                    <span class="text-slate-400 font-normal">({{ __('optional') }})</span>
                                </label>
                                <select name="template_id" id="template_id"
                                        class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">{{ __('Use default template') }}</option>
                                    @foreach($annexTemplates as $template)
                                        <option value="{{ $template->id }}" {{ $template->is_default ? 'selected' : '' }}>
                                            {{ $template->name }}
                                            @if($template->is_default)
                                                ({{ __('Default') }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ __('Select a template for the annex document. If not selected, the default annex template will be used.') }}
                                </p>
                            </div>
                        @endif

                        <div class="mt-6 flex flex-col-reverse sm:flex-row gap-3">
                            <x-ui.button variant="destructive-outline" type="button" class="w-full sm:w-auto" onclick="window.history.back()">
                                {{ __('Cancel') }}
                            </x-ui.button>
                            @if($contract->canAcceptAnnex())
                                <x-ui.button variant="default" type="submit" class="w-full sm:w-auto">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ __('Add Annex') }}
                                </x-ui.button>
                            @else
                                <x-ui.button variant="default" type="button" disabled class="w-full sm:w-auto opacity-50 cursor-not-allowed">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ __('Add Annex') }}
                                </x-ui.button>
                            @endif
                        </div>
                    </form>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No accepted offers available') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Create a new offer for this client. Once it\'s accepted, you can add it as an annex.') }}</p>
                        <div class="mt-6 flex flex-col sm:flex-row justify-center gap-3">
                            <x-ui.button variant="default" class="w-full sm:w-auto" onclick="window.location.href='{{ route('offers.create', ['client_id' => $contract->client_id, 'parent_contract_id' => $contract->id]) }}'">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('Create New Offer') }}
                            </x-ui.button>
                            <x-ui.button variant="destructive-outline" class="w-full sm:w-auto" onclick="window.history.back()">
                                {{ __('Cancel') }}
                            </x-ui.button>
                        </div>
                    </div>
                @endif
            </x-ui.card-content>
        </x-ui.card>

        {{-- Help Text --}}
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h4 class="text-sm font-medium text-blue-800 mb-1">{{ __('What is a Contract Annex?') }}</h4>
            <p class="text-sm text-blue-700">{{ __('An annex (addendum) is a document that modifies or adds to an existing contract. It allows you to add new services or changes without creating a new contract.') }}</p>
        </div>
    </div>
</x-app-layout>
