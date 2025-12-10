<x-app-layout>
    <x-slot name="pageTitle">{{ __('Add Annex to') }} {{ $contract->contract_number }}</x-slot>

    <div class="p-6 max-w-2xl">
        <x-ui.card>
            <x-ui.card-header>
                <h2 class="text-lg font-semibold">{{ __('Select Offer for Annex') }}</h2>
            </x-ui.card-header>
            <x-ui.card-content>
                @if($availableOffers->count() > 0)
                    <form action="{{ route('contracts.add-annex.store', $contract) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            @foreach($availableOffers as $offer)
                                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
                                    <input type="radio" name="offer_id" value="{{ $offer->id }}" required
                                           class="mr-4 text-blue-600 focus:ring-blue-500">
                                    <div class="flex-1">
                                        <div class="font-medium text-slate-900">{{ $offer->offer_number }}</div>
                                        <div class="text-sm text-slate-500">{{ $offer->title }}</div>
                                        <div class="text-xs text-slate-400">{{ __('Accepted') }}: {{ $offer->accepted_at->format('d.m.Y') }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium text-slate-900">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-6 flex gap-3">
                            <x-ui.button variant="default" type="submit">
                                {{ __('Add Annex') }}
                            </x-ui.button>
                            <x-ui.button variant="outline" type="button" onclick="window.history.back()">
                                {{ __('Cancel') }}
                            </x-ui.button>
                        </div>
                    </form>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No available offers') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Create a new offer for this client first, then add it as an annex after it\'s accepted.') }}</p>
                        <div class="mt-6">
                            <x-ui.button variant="default" onclick="window.location.href='{{ route('offers.create', ['client_id' => $contract->client_id]) }}'">
                                {{ __('Create Offer') }}
                            </x-ui.button>
                        </div>
                    </div>
                @endif
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-app-layout>
