@php app()->setLocale($offer->language ?? 'ro'); @endphp
<!DOCTYPE html>
<html lang="{{ $offer->language ?? 'ro' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $offer->offer_number }} - {{ $offer->title }}</title>
    {{-- Load Alpine.js directly since this page doesn't use Livewire --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen">
    @php
        $organization = $offer->organization;
        $client = $offer->client;
        $blocks = $offer->blocks ?? [];
        $items = $offer->items;
        $headerData = $offer->header_data ?? [];

        // Offer is read-only when not in 'sent' or 'viewed' status
        $isReadOnly = !in_array($offer->status, ['sent', 'viewed']);

        // Separate custom services (main list) and card services (extras)
        $customItems = $items->filter(fn($item) => $item->type === 'custom' || $item->type === null);
        $cardItems = $items->filter(fn($item) => $item->type === 'card');

        // Get initially deselected items (from admin's selection)
        $initiallyDeselectedIds = $customItems->filter(fn($item) => $item->is_selected === false)->pluck('id')->toArray();

        // Get initially selected card items (cards chosen by client)
        $initiallySelectedCardIds = $cardItems->filter(fn($item) => $item->is_selected === true)->pluck('id')->toArray();

        // Calculate FULL subtotal (all custom items) - we'll subtract deselected in JS
        $fullSubtotal = $customItems->sum('total_price');
        // Calculate initial subtotal (only selected custom items)
        $subtotal = $customItems->filter(fn($item) => $item->is_selected !== false)->sum('total_price');
        $discountAmount = $subtotal * ($offer->discount_percent ?? 0) / 100;

        // VAT is disabled - will be enabled from organization settings when needed
        $grandTotal = $subtotal - $discountAmount;

        // Get services block for card heading
        $servicesBlock = collect($blocks)->firstWhere('type', 'services');
        $cardsHeading = $servicesBlock['data']['cardsHeading'] ?? __('Servicii extra disponibile');

        // Bank accounts (stored in organization settings)
        $bankAccounts = collect($organization->settings['bank_accounts'] ?? []);
    @endphp

    <script>
    window.publicOffer = function() {
        return {
            showRejectModal: false,
            isAccepting: false,
            selectedCards: {!! json_encode($initiallySelectedCardIds) !!},
            selectedOptionalServices: [],
            optionalServicesPrices: {},
            // Initialize with items that admin has deselected
            deselectedServices: {!! json_encode($initiallyDeselectedIds) !!},
            deselectedServicesPrices: {},

            // Base values (ALL custom services - we subtract deselected dynamically)
            baseSubtotal: {{ $fullSubtotal }},
            discountPercent: {{ $offer->discount_percent ?? 0 }},
            currency: '{{ $offer->currency }}',

            // Service prices map (for deselection)
            servicePrices: {
                @foreach($customItems as $item)
                {{ $item->id }}: {{ $item->total_price }},
                @endforeach
            },

            // Card prices map
            cardPrices: {
                @foreach($cardItems as $item)
                {{ $item->id }}: {{ $item->total_price }},
                @endforeach
            },

            // Computed totals
            get deselectedServicesTotal() {
                return this.deselectedServices.reduce((sum, id) => sum + (this.servicePrices[id] || 0), 0);
            },

            get selectedCardsTotal() {
                return this.selectedCards.reduce((sum, id) => sum + (this.cardPrices[id] || 0), 0);
            },

            get selectedOptionalServicesTotal() {
                return this.selectedOptionalServices.reduce((sum, key) => sum + (this.optionalServicesPrices[key] || 0), 0);
            },

            get currentSubtotal() {
                // Base minus deselected, plus extras selected
                return this.baseSubtotal - this.deselectedServicesTotal + this.selectedCardsTotal + this.selectedOptionalServicesTotal;
            },

            get currentDiscount() {
                return this.currentSubtotal * (this.discountPercent / 100);
            },

            get currentGrandTotal() {
                return this.currentSubtotal - this.currentDiscount;
            },

            // Toggle main service selection (deselect/reselect)
            toggleService(serviceId, price) {
                const index = this.deselectedServices.indexOf(serviceId);
                if (index > -1) {
                    // Re-select (remove from deselected)
                    this.deselectedServices.splice(index, 1);
                } else {
                    // Deselect (add to deselected)
                    this.deselectedServices.push(serviceId);
                }
                // Sync with backend
                this.syncSelectionsToBackend();
            },

            // Toggle card selection
            toggleCard(cardId, price) {
                const index = this.selectedCards.indexOf(cardId);
                if (index > -1) {
                    this.selectedCards.splice(index, 1);
                } else {
                    this.selectedCards.push(cardId);
                }
                // Sync with backend
                this.syncSelectionsToBackend();
            },

            // Toggle optional service selection
            toggleOptionalService(key, price, title, description, quantity, unit, unitPrice, currency) {
                const index = this.selectedOptionalServices.indexOf(key);
                if (index > -1) {
                    // Remove from selection
                    this.selectedOptionalServices.splice(index, 1);
                    delete this.optionalServicesPrices[key];
                } else {
                    // Add to selection
                    this.selectedOptionalServices.push(key);
                    this.optionalServicesPrices[key] = price;
                }
                // Sync with backend
                this.syncSelectionsToBackend();
            },

            // Debounce timer for syncing
            syncTimer: null,
            isSyncing: false,

            // Sync customer selections to backend (debounced)
            syncSelectionsToBackend() {
                // Debounce - wait 500ms after last change before syncing
                if (this.syncTimer) {
                    clearTimeout(this.syncTimer);
                }
                this.syncTimer = setTimeout(() => {
                    this.doSyncSelections();
                }, 500);
            },

            async doSyncSelections() {
                if (this.isSyncing) return;
                this.isSyncing = true;

                try {
                    const response = await fetch('{{ route('offers.public.selections', $offer->public_token) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            deselected_services: this.deselectedServices,
                            selected_cards: this.selectedCards,
                            selected_optional_services: this.selectedOptionalServices
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        // Update our local timestamp to avoid triggering notification for our own change
                        this.lastUpdatedAt = data.updated_at;
                    }
                } catch (error) {
                    // Silently ignore sync errors - non-critical
                } finally {
                    this.isSyncing = false;
                }
            },

            // Format currency - use browser locale for international support
            formatCurrency(value) {
                const locale = document.documentElement.lang || navigator.language || 'en';
                return new Intl.NumberFormat(locale, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(value) + ' ' + this.currency;
            },

            init() {
                // Component initialized - selections sync to backend on user interaction
            }
        };
    }
    </script>

    <div class="max-w-4xl mx-auto py-8 px-4" x-data="publicOffer()">
        {{-- Messages --}}
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 mb-6">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6">
                {{ session('error') }}
            </div>
        @endif

        {{-- Document Container --}}
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">

            {{-- ===== HEADER BLOCK (Shared Component) ===== --}}
            @include('components.offer-simple.blocks.header', [
                'mode' => 'public',
                'organization' => $organization,
                'bankAccounts' => $bankAccounts,
                'offer' => $offer,
                'headerData' => $headerData,
                'client' => $client,
            ])

            {{-- ===== DYNAMIC BLOCKS ===== --}}
            @foreach($blocks as $block)
                @if($block['visible'] ?? true)
                    @switch($block['type'])
                        @case('services')
                            {{-- ===== SERVICES BLOCK ===== --}}
                            <div class="px-6 py-6 border-b border-slate-200">
                                {{-- Main Block Heading --}}
                                <div class="mb-6">
                                    <h2 class="text-xl font-bold text-slate-800">
                                        {{ $block['data']['heading'] ?? __('Oferta include următoarele servicii:') }}
                                        <div class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
                                    </h2>
                                </div>

                                {{-- SECTION 1: Main Services List (custom type) - Interactive --}}
                                @if($customItems->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($customItems as $item)
                                            <div class="rounded-xl border shadow-md transition-all duration-200 {{ $isReadOnly ? '' : 'cursor-pointer' }}"
                                                 :class="deselectedServices.includes({{ $item->id }}) ? 'bg-slate-50 border-slate-200 opacity-60' : 'bg-white border-green-200'"
                                                 {!! !$isReadOnly ? '@click="toggleService(' . $item->id . ', ' . $item->total_price . ')"' : '' !!}>
                                                <div class="flex items-start gap-4 p-5">
                                                    {{-- Interactive Checkbox (only in edit mode) --}}
                                                    @if(!$isReadOnly)
                                                    <div class="pt-0.5">
                                                        <div class="w-6 h-6 rounded-md border-2 flex items-center justify-center transition-all"
                                                             :class="deselectedServices.includes({{ $item->id }}) ? 'border-slate-300 bg-white' : 'border-green-500 bg-green-500'">
                                                            <svg x-show="!deselectedServices.includes({{ $item->id }})" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    {{-- Service Info --}}
                                                    <div class="flex-1 min-w-0">
                                                        <h4 class="font-semibold text-lg transition-all"
                                                            :class="deselectedServices.includes({{ $item->id }}) ? 'text-slate-400 line-through' : 'text-slate-900'">
                                                            {{ $item->title }}
                                                        </h4>
                                                        @if($item->description)
                                                            <p class="text-sm mt-2 leading-relaxed whitespace-pre-line transition-all"
                                                               :class="deselectedServices.includes({{ $item->id }}) ? 'text-slate-300' : 'text-slate-500'">
                                                                {{ $item->description }}
                                                            </p>
                                                        @endif

                                                        {{-- Price display --}}
                                                        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-100">
                                                            <span class="text-sm text-slate-400">{{ __('Cost') }}:</span>
                                                            <span class="text-sm font-semibold"
                                                                  :class="deselectedServices.includes({{ $item->id }}) ? 'text-slate-400' : 'text-slate-700'">
                                                                {{ number_format($item->unit_price, 2) }} {{ $offer->currency }}
                                                            </span>
                                                            @if($item->unit !== 'buc' && $item->unit !== 'proiect')
                                                                <span class="text-sm text-slate-400">/ {{ $item->unit }}</span>
                                                            @endif
                                                        </div>

                                                        {{-- Quantity --}}
                                                        @if($item->quantity > 1 || ($item->unit !== 'proiect' && $item->unit !== 'buc'))
                                                            <div class="flex items-center gap-4 mt-2">
                                                                <span class="text-sm font-medium text-slate-600">{{ __('Quantity') }}:</span>
                                                                <span class="text-sm text-slate-700">{{ number_format($item->quantity, $item->quantity == floor($item->quantity) ? 0 : 2) }} {{ $item->unit }}</span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- Total Column --}}
                                                    <div class="w-32 text-right pt-1">
                                                        <span class="text-lg font-bold transition-all"
                                                              :class="deselectedServices.includes({{ $item->id }}) ? 'text-slate-400 line-through' : 'text-green-600'">
                                                            {{ number_format($item->total_price, 2) }} {{ $offer->currency }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8 text-slate-500">
                                        <p>{{ __('No services included in this offer.') }}</p>
                                    </div>
                                @endif

                                {{-- SECTION 2: Extra Services Cards (card type) - Interactive --}}
                                @if($cardItems->count() > 0)
                                    <div class="mt-8">
                                        {{-- Section Title --}}
                                        <div class="mb-4">
                                            <h3 class="text-lg font-semibold text-slate-700">
                                                {{ $block['data']['cardsHeading'] ?? __('Servicii extra disponibile') }}
                                            </h3>
                                        </div>

                                        {{-- Service Cards Grid --}}
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @foreach($cardItems as $index => $item)
                                                <div class="rounded-xl p-5 transition-all border flex flex-col bg-slate-900 border-slate-700"
                                                     :class="selectedCards.includes({{ $item->id }}) ? 'border-green-500 bg-slate-800 shadow-lg' : 'hover:border-slate-600 hover:shadow-lg'"
                                                     data-card-id="{{ $item->id }}"
                                                     data-card-price="{{ $item->total_price }}">

                                                    {{-- Top Content Area --}}
                                                    <div class="flex-1">
                                                        {{-- Card Header with Icon --}}
                                                        <div class="flex items-start gap-4 mb-4">
                                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                                                                 :class="selectedCards.includes({{ $item->id }}) ? 'bg-green-500' : 'bg-slate-700'">
                                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                </svg>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <h4 class="font-semibold text-white text-base">{{ $item->title }}</h4>
                                                            </div>
                                                        </div>

                                                        {{-- Description --}}
                                                        @if($item->description)
                                                            <p class="text-sm text-slate-400 mb-3 whitespace-pre-line">{{ $item->description }}</p>
                                                        @endif
                                                    </div>

                                                    {{-- Bottom Area --}}
                                                    <div class="mt-auto">
                                                        {{-- Price Display --}}
                                                        <div class="flex items-baseline justify-between mb-3 pt-2 border-t"
                                                             :class="selectedCards.includes({{ $item->id }}) ? 'border-green-500/30' : 'border-slate-700'">
                                                            <span class="text-xs text-slate-500">{{ __('Price') }}</span>
                                                            <div class="flex items-baseline gap-1">
                                                                <span class="text-xl font-bold"
                                                                      :class="selectedCards.includes({{ $item->id }}) ? 'text-green-400' : 'text-white'">{{ number_format($item->unit_price, 0) }}</span>
                                                                <span class="text-xs text-slate-500">{{ $offer->currency }}</span>
                                                            </div>
                                                        </div>

                                                        {{-- Add/Remove Button (only in edit mode) --}}
                                                        @if(!$isReadOnly)
                                                        <button type="button"
                                                                @click="toggleCard({{ $item->id }}, {{ $item->total_price }})"
                                                                class="w-full py-2.5 rounded-lg font-medium text-sm transition-all flex items-center justify-center gap-2"
                                                                :class="selectedCards.includes({{ $item->id }})
                                                                    ? 'bg-red-500 text-white hover:bg-red-600'
                                                                    : 'bg-green-500 text-white hover:bg-green-600'">
                                                            <svg x-show="!selectedCards.includes({{ $item->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                            <svg x-show="selectedCards.includes({{ $item->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                            <span x-text="selectedCards.includes({{ $item->id }}) ? '{{ __('Remove') }}' : '{{ __('Add to offer') }}'"></span>
                                                        </button>
                                                        @else
                                                        {{-- Read-only: Show selection status --}}
                                                        <div class="w-full py-2.5 rounded-lg font-medium text-sm flex items-center justify-center gap-2"
                                                             :class="selectedCards.includes({{ $item->id }}) ? 'bg-green-500/20 text-green-400' : 'bg-slate-700 text-slate-400'">
                                                            <svg x-show="selectedCards.includes({{ $item->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            <span x-text="selectedCards.includes({{ $item->id }}) ? '{{ __('Selected') }}' : '{{ __('Not selected') }}'"></span>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @break

                        @case('specifications')
                            {{-- ===== SPECIFICATIONS BLOCK ===== --}}
                            <div class="px-6 py-6 border-b border-slate-200">
                                <div class="mb-6">
                                    <h2 class="text-xl font-bold text-slate-800">
                                        {{ $block['data']['heading'] ?? __('Precizări') }}
                                        <div class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
                                    </h2>
                                </div>

                                @if(!empty($block['data']['sections']))
                                    <div class="space-y-6">
                                        @foreach($block['data']['sections'] as $section)
                                            <div class="bg-slate-50 rounded-lg p-4">
                                                @if(!empty($section['title']))
                                                    <h3 class="text-lg font-semibold text-slate-800 mb-3">{{ $section['title'] }}</h3>
                                                @endif

                                                @if(($section['type'] ?? 'paragraph') === 'paragraph')
                                                    @php
                                                        // Convert URLs to clickable links
                                                        $content = e($section['content'] ?? '');
                                                        $content = preg_replace(
                                                            '/(https?:\/\/[^\s<]+)/i',
                                                            '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 hover:underline">$1</a>',
                                                            $content
                                                        );
                                                    @endphp
                                                    <p class="text-sm text-slate-600 whitespace-pre-line">{!! $content !!}</p>
                                                @else
                                                    <ul class="space-y-2">
                                                        @foreach($section['items'] ?? [] as $sectionItem)
                                                            @if(!empty($sectionItem))
                                                                <li class="flex items-start gap-2 text-sm text-slate-600">
                                                                    <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                                    </svg>
                                                                    <span>{{ $sectionItem }}</span>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @break

                        @case('summary')
                            {{-- ===== SUMMARY BLOCK ===== --}}
                            @php
                                $optionalServicesBlock = collect($blocks)->firstWhere('type', 'optional_services');
                                $optionalServices = $optionalServicesBlock['data']['services'] ?? [];
                            @endphp
                            <div class="px-6 py-6 border-b border-slate-200">
                                <div class="mb-6">
                                    <h2 class="text-xl font-bold text-slate-800">
                                        {{ $block['data']['heading'] ?? __('Sumar servicii selectate') }}
                                        <div class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
                                    </h2>
                                </div>

                                @if($customItems->count() > 0 || $cardItems->count() > 0)
                                    {{-- Services Table with Totals --}}
                                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                                        {{-- Table Header --}}
                                        <div class="grid grid-cols-12 gap-2 px-4 py-3 bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
                                            <div class="col-span-5">{{ __('Serviciu') }}</div>
                                            <div class="col-span-2 text-center">{{ __('Cant.') }}</div>
                                            <div class="col-span-2 text-right">{{ __('Preț') }}</div>
                                            <div class="col-span-1 text-center">{{ __('Disc.') }}</div>
                                            <div class="col-span-2 text-right">{{ __('Total') }}</div>
                                        </div>

                                        {{-- Services Rows --}}
                                        <div class="divide-y divide-slate-100">
                                            {{-- Main Services (custom type) --}}
                                            @foreach($customItems as $item)
                                                <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center hover:bg-slate-50 transition-all"
                                                     x-show="!deselectedServices.includes({{ $item->id }})"
                                                     x-transition>
                                                    <div class="col-span-5">
                                                        <span class="font-medium text-slate-800">{{ $item->title }}</span>
                                                    </div>
                                                    <div class="col-span-2 text-center">
                                                        <span class="text-sm text-slate-600">{{ number_format($item->quantity, $item->quantity == floor($item->quantity) ? 0 : 2) }} {{ $item->unit }}</span>
                                                    </div>
                                                    <div class="col-span-2 text-right">
                                                        <span class="text-sm text-slate-600">{{ number_format($item->unit_price, 2) }} {{ $offer->currency }}</span>
                                                    </div>
                                                    <div class="col-span-1 text-center">
                                                        @if($item->discount_percent > 0)
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                                                -{{ number_format($item->discount_percent, 0) }}%
                                                            </span>
                                                        @else
                                                            <span class="text-sm text-slate-300">—</span>
                                                        @endif
                                                    </div>
                                                    <div class="col-span-2 text-right">
                                                        <span class="font-semibold text-slate-800">{{ number_format($item->total_price, 2) }} {{ $offer->currency }}</span>
                                                    </div>
                                                </div>
                                            @endforeach

                                            {{-- Card Services (Extra) --}}
                                            @foreach($cardItems as $item)
                                                <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center bg-green-50 hover:bg-green-100 transition-all"
                                                     x-show="selectedCards.includes({{ $item->id }})"
                                                     x-transition>
                                                    <div class="col-span-5">
                                                        <span class="font-medium text-slate-800">{{ $item->title }}</span>
                                                        <span class="text-xs text-green-600 font-medium ml-2">({{ __('Extra') }})</span>
                                                    </div>
                                                    <div class="col-span-2 text-center">
                                                        <span class="text-sm text-slate-600">{{ number_format($item->quantity, $item->quantity == floor($item->quantity) ? 0 : 2) }} {{ $item->unit }}</span>
                                                    </div>
                                                    <div class="col-span-2 text-right">
                                                        <span class="text-sm text-slate-600">{{ number_format($item->unit_price, 2) }} {{ $offer->currency }}</span>
                                                    </div>
                                                    <div class="col-span-1 text-center">
                                                        <span class="text-sm text-slate-300">—</span>
                                                    </div>
                                                    <div class="col-span-2 text-right">
                                                        <span class="font-semibold text-green-700">{{ number_format($item->total_price, 2) }} {{ $offer->currency }}</span>
                                                    </div>
                                                </div>
                                            @endforeach

                                            {{-- Optional Services --}}
                                            @foreach($optionalServices as $optService)
                                                @php
                                                    $optKey = $optService['_key'] ?? uniqid();
                                                    $optTitle = $optService['title'] ?? '';
                                                    $optQuantity = $optService['quantity'] ?? 1;
                                                    $optUnit = $optService['unit'] ?? 'ora';
                                                    $optUnitPrice = $optService['unit_price'] ?? 0;
                                                    $optCurrency = $optService['currency'] ?? $offer->currency;
                                                    $optTotal = $optQuantity * $optUnitPrice;
                                                @endphp
                                                <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center bg-green-50 hover:bg-green-100 transition-all"
                                                     x-show="selectedOptionalServices.includes('{{ $optKey }}')"
                                                     x-transition>
                                                    <div class="col-span-5">
                                                        <span class="font-medium text-slate-800">{{ $optTitle }}</span>
                                                        <span class="text-xs text-green-600 font-medium ml-2">({{ __('Optional') }})</span>
                                                    </div>
                                                    <div class="col-span-2 text-center">
                                                        <span class="text-sm text-slate-600">{{ number_format($optQuantity, $optQuantity == floor($optQuantity) ? 0 : 2) }} {{ $optUnit }}</span>
                                                    </div>
                                                    <div class="col-span-2 text-right">
                                                        <span class="text-sm text-slate-600">{{ number_format($optUnitPrice, 2) }} {{ $optCurrency }}</span>
                                                    </div>
                                                    <div class="col-span-1 text-center">
                                                        <span class="text-sm text-slate-300">—</span>
                                                    </div>
                                                    <div class="col-span-2 text-right">
                                                        <span class="font-semibold text-green-700">{{ number_format($optTotal, 2) }} {{ $optCurrency }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Totals Section (inside table) --}}
                                        <div class="border-t-2 border-slate-200 bg-gradient-to-br from-slate-50 to-slate-100">
                                            @if($block['data']['showSubtotal'] ?? true)
                                                <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center">
                                                    <div class="col-span-10 text-right">
                                                        <span class="text-sm font-medium text-slate-600">{{ __('Subtotal') }}</span>
                                                    </div>
                                                    <div class="col-span-2 text-right">
                                                        <span class="font-semibold text-slate-800" x-text="formatCurrency(currentSubtotal)"></span>
                                                    </div>
                                                </div>
                                            @endif

                                            @if(($block['data']['showDiscount'] ?? true) && $offer->discount_percent > 0)
                                                <div class="grid grid-cols-12 gap-2 px-4 py-2 items-center border-t border-dashed border-slate-200">
                                                    <div class="col-span-10 text-right">
                                                        <span class="text-sm text-slate-600">
                                                            {{ __('Discount') }}
                                                            <span class="text-xs text-green-600 ml-1">(-{{ number_format($offer->discount_percent, 0) }}%)</span>
                                                        </span>
                                                    </div>
                                                    <div class="col-span-2 text-right">
                                                        <span class="font-medium text-green-600">-<span x-text="formatCurrency(currentDiscount)"></span></span>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($block['data']['showGrandTotal'] ?? true)
                                                <div class="grid grid-cols-12 gap-2 px-4 py-4 items-center border-t-2 border-slate-300 bg-slate-100">
                                                    <div class="col-span-10 text-right">
                                                        <span class="text-base font-bold text-slate-800">{{ __('Total de plată') }}</span>
                                                    </div>
                                                    <div class="col-span-2 text-right">
                                                        <span class="text-xl font-bold text-green-600" x-text="formatCurrency(currentGrandTotal)"></span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @break

                        @case('brands')
                            {{-- ===== BRANDS BLOCK ===== --}}
                            @if(!empty($block['data']['image']) || !empty($block['data']['heading']))
                                <div class="px-6 py-6 border-b border-slate-200">
                                    @if(!empty($block['data']['heading']))
                                        <div class="mb-4 text-center">
                                            <h3 class="text-lg font-semibold text-slate-700">{{ $block['data']['heading'] }}</h3>
                                        </div>
                                    @endif
                                    @if(!empty($block['data']['image']))
                                        <div class="flex justify-center">
                                            <img src="{{ $block['data']['image'] }}" alt="Brands" class="max-w-full h-auto rounded-lg">
                                        </div>
                                    @endif
                                </div>
                            @endif
                            @break

                        @case('acceptance')
                            {{-- ===== ACCEPTANCE BLOCK ===== --}}
                            <div class="px-6 py-6">
                                @if(!empty($block['data']['paragraph']))
                                    <div class="mb-6 text-sm text-slate-600 leading-relaxed">
                                        {{ $block['data']['paragraph'] }}
                                    </div>
                                @endif

                                {{-- Action Buttons --}}
                                @if($offer->canBeAccepted())
                                    <div class="flex flex-col sm:flex-row gap-4" role="group" aria-label="{{ __('Offer response actions') }}">
                                        <button type="button" @click="showRejectModal = true"
                                                aria-label="{{ __('Decline this offer') }}"
                                                class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-slate-300 rounded-lg text-base font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-colors">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            {{ $block['data']['rejectButtonText'] ?? __('Decline') }}
                                        </button>

                                        <form action="{{ route('offers.public.accept', $offer->public_token) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit"
                                                    aria-label="{{ __('Accept this offer') }}"
                                                    class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ $block['data']['acceptButtonText'] ?? __('Accept Offer') }}
                                            </button>
                                        </form>
                                    </div>
                                @elseif($offer->isAccepted())
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                                        <svg class="w-12 h-12 text-green-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-green-800">{{ __('Offer Accepted') }}</h3>
                                        <p class="text-green-700 mt-1">{{ __('Thank you! This offer was accepted on') }} {{ $offer->accepted_at->format('d.m.Y') }}.</p>
                                    </div>
                                @elseif($offer->isRejected())
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                                        <svg class="w-12 h-12 text-red-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-red-800">{{ __('Offer Declined') }}</h3>
                                        <p class="text-red-700 mt-1">{{ __('This offer was declined on') }} {{ $offer->rejected_at->format('d.m.Y') }}.</p>
                                    </div>
                                @elseif($offer->isExpired())
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                                        <svg class="w-12 h-12 text-yellow-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-yellow-800">{{ __('Offer Expired') }}</h3>
                                        <p class="text-yellow-700 mt-1">{{ __('This offer has expired. Please contact us for a new offer.') }}</p>
                                    </div>
                                @endif
                            </div>
                            @break

                        @case('text')
                            {{-- ===== TEXT BLOCK ===== --}}
                            <div class="px-6 py-6 border-b border-slate-200">
                                @if(!empty($block['data']['heading']))
                                    <h2 class="text-xl font-bold text-slate-800 mb-4">
                                        {{ $block['data']['heading'] }}
                                        <div class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
                                    </h2>
                                @endif
                                @if(!empty($block['data']['content']))
                                    <div class="prose prose-sm max-w-none text-slate-600">
                                        {!! \Illuminate\Support\Str::of($block['data']['content'])->stripTags('<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><pre><code>') !!}
                                    </div>
                                @endif
                            </div>
                            @break

                        @case('optional_services')
                            {{-- ===== OPTIONAL SERVICES BLOCK ===== --}}
                            @if(!empty($block['data']['services']) && count($block['data']['services']) > 0)
                                <div class="px-6 py-6 border-b border-slate-200">
                                    <div class="mb-6">
                                        <h2 class="text-xl font-bold text-slate-800">
                                            {{ __('Optional Services') }}
                                            <div class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
                                        </h2>
                                        <p class="text-sm text-slate-600 mt-3">{{ __('Select additional services to add to your offer:') }}</p>
                                    </div>

                                    {{-- Optional Services Cards Grid --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($block['data']['services'] as $optService)
                                            @php
                                                $optKey = $optService['_key'] ?? uniqid();
                                                $optTitle = $optService['title'] ?? '';
                                                $optDescription = $optService['description'] ?? '';
                                                $optQuantity = $optService['quantity'] ?? 1;
                                                $optUnit = $optService['unit'] ?? 'ora';
                                                $optUnitPrice = $optService['unit_price'] ?? 0;
                                                $optCurrency = $optService['currency'] ?? $offer->currency;
                                                $optTotal = $optQuantity * $optUnitPrice;
                                            @endphp

                                            <div class="rounded-xl p-5 transition-all border flex flex-col"
                                                 :class="selectedOptionalServices.includes('{{ $optKey }}') ? 'border-green-500 bg-green-50 shadow-lg' : 'bg-white border-slate-200 hover:border-slate-300 hover:shadow-md'"
                                                 data-optional-key="{{ $optKey }}"
                                                 data-optional-price="{{ $optTotal }}">

                                                {{-- Top Content Area --}}
                                                <div class="flex-1">
                                                    {{-- Card Header with Checkbox --}}
                                                    <div class="flex items-start gap-3 mb-3">
                                                        @if(!$isReadOnly)
                                                        <div class="pt-0.5">
                                                            <input type="checkbox"
                                                                   id="service-{{ $optKey }}"
                                                                   :checked="selectedOptionalServices.includes('{{ $optKey }}')"
                                                                   @change="toggleOptionalService('{{ $optKey }}', {{ $optTotal }}, '{{ addslashes($optTitle) }}', '{{ addslashes($optDescription) }}', {{ $optQuantity }}, '{{ $optUnit }}', {{ $optUnitPrice }}, '{{ $optCurrency }}')"
                                                                   aria-describedby="service-desc-{{ $optKey }}"
                                                                   class="w-5 h-5 rounded border-slate-300 text-green-600 focus:ring-green-500 cursor-pointer">
                                                        </div>
                                                        @endif
                                                        <div class="flex-1 min-w-0">
                                                            <label for="service-{{ $optKey }}" class="font-semibold text-slate-900 text-base leading-tight {{ $isReadOnly ? '' : 'cursor-pointer' }}">{{ $optTitle }}</label>
                                                        </div>
                                                    </div>

                                                    {{-- Description --}}
                                                    @if($optDescription)
                                                        <p id="service-desc-{{ $optKey }}" class="text-sm text-slate-600 mb-3 leading-relaxed">{{ $optDescription }}</p>
                                                    @endif
                                                </div>

                                                {{-- Bottom Area: Price and Quantity --}}
                                                <div class="mt-auto pt-3 border-t border-slate-200">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <span class="text-xs text-slate-500">{{ __('Quantity') }}</span>
                                                        <span class="text-sm font-medium text-slate-700">{{ number_format($optQuantity, $optQuantity == floor($optQuantity) ? 0 : 2) }} {{ $optUnit }}</span>
                                                    </div>
                                                    <div class="flex items-baseline justify-between">
                                                        <span class="text-xs text-slate-500">{{ __('Total') }}</span>
                                                        <span class="text-lg font-bold text-green-600">{{ number_format($optTotal, 2) }} {{ $optCurrency }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @break
                    @endswitch
                @endif
            @endforeach

            {{-- Fallback if no blocks with acceptance --}}
            @if(empty($blocks) || !collect($blocks)->contains('type', 'acceptance'))
                {{-- Actions --}}
                <div class="px-6 py-6">
                    @if($offer->canBeAccepted())
                        <h3 class="font-semibold text-slate-900 mb-4">{{ __('Your Response') }}</h3>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="button" @click="showRejectModal = true"
                                    class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-slate-300 rounded-lg text-base font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                {{ __('Decline Offer') }}
                            </button>

                            <form action="{{ route('offers.public.accept', $offer->public_token) }}" method="POST" class="flex-1" @submit="isAccepting = true">
                                @csrf
                                <button type="submit"
                                        :disabled="isAccepting"
                                        :class="isAccepting ? 'bg-green-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'"
                                        class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                    <svg x-show="!isAccepting" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <svg x-show="isAccepting" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-show="!isAccepting">{{ __('Accept Offer') }}</span>
                                    <span x-show="isAccepting">{{ __('Processing...') }}</span>
                                </button>
                            </form>
                        </div>
                    @elseif($offer->isAccepted())
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                            <svg class="w-12 h-12 text-green-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-green-800">{{ __('Offer Accepted') }}</h3>
                            <p class="text-green-700 mt-1">{{ __('Thank you! This offer was accepted on') }} {{ $offer->accepted_at->format('d.m.Y') }}.</p>
                        </div>
                    @elseif($offer->isRejected())
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                            <svg class="w-12 h-12 text-red-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-red-800">{{ __('Offer Declined') }}</h3>
                            <p class="text-red-700 mt-1">{{ __('This offer was declined on') }} {{ $offer->rejected_at->format('d.m.Y') }}.</p>
                        </div>
                    @elseif($offer->isExpired())
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                            <svg class="w-12 h-12 text-yellow-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-yellow-800">{{ __('Offer Expired') }}</h3>
                            <p class="text-yellow-700 mt-1">{{ __('This offer has expired. Please contact us for a new offer.') }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="text-center text-sm text-slate-500 mt-8">
            @if($organization?->email)
                <p>{{ __('Questions? Contact us at') }} <a href="mailto:{{ $organization->email }}" class="text-blue-600 hover:underline">{{ $organization->email }}</a></p>
            @endif
            <p class="mt-2">&copy; {{ date('Y') }} {{ $organization?->name ?? config('app.name') }}</p>
        </div>

        {{-- Reject Modal --}}
        <div x-show="showRejectModal" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="showRejectModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showRejectModal = false"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Decline Offer') }}</h3>
                    <form action="{{ route('offers.public.reject', $offer->public_token) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Reason (optional)') }}</label>
                            <textarea name="reason" rows="3"
                                      class="w-full border-slate-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                      placeholder="{{ __('Please let us know why you are declining...') }}"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showRejectModal = false"
                                    class="flex-1 px-4 py-2 border border-slate-300 rounded-md text-slate-700 hover:bg-slate-50">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                {{ __('Decline') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


</body>
</html>
