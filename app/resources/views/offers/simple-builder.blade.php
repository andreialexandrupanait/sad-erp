<x-app-layout>
    <x-slot name="pageTitle">{{ isset($offer) && $offer ? __('Edit Offer') : __('New Offer') }}</x-slot>

    @php
        $defaultValidUntil = now()->addDays($organization->settings['offer_validity_days'] ?? 30)->format('Y-m-d');
        $defaultCurrency = $organization->settings['default_currency'] ?? 'RON';
        $defaultVatPercent = $organization->settings['default_vat_percent'] ?? 19;
        $companyName = $organization->name ?? 'Company';
    @endphp

    <div class="h-[calc(100vh-4rem)] flex" x-data="simpleOfferBuilder()" x-init="initCustomerSync()" x-cloak>
        {{-- Customer Update Notification --}}
        <div x-show="showCustomerUpdateNotification"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 bg-purple-600 text-white rounded-lg shadow-lg px-6 py-4 flex items-center gap-4 max-w-lg">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-medium">{{ __('Customer Updated Selection') }}</p>
                <p class="text-sm text-purple-100">{{ __('The customer has changed their service selections.') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="applyCustomerUpdate()" class="bg-white text-purple-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-purple-50 transition-colors">
                    {{ __('Apply') }}
                </button>
                <button @click="dismissCustomerUpdate()" class="text-purple-200 hover:text-white p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Left Sidebar --}}
        @include('offers.partials.simple-sidebar')

        {{-- Main Canvas --}}
        <div class="flex-1 bg-slate-100 overflow-y-auto p-8">
            <div class="max-w-4xl mx-auto">
                {{-- Document Container --}}
                <div class="bg-white shadow-lg rounded-xl">
                    {{-- Header Block (Always First) --}}
                    @include('components.offer-simple.blocks.header')

                    {{-- Dynamic Blocks --}}
                    <div>
                        <template x-for="(block, blockIndex) in blocks" :key="block.id">
                            <div x-show="block.visible" class="relative group"
                                 :class="{
                                     '': block.type === 'brands',
                                     'py-4': block.type !== 'brands',
                                     'border-b border-slate-200': block.type !== 'brands' && blocks[blockIndex + 1]?.type !== 'brands'
                                 }">
                                {{-- Block Toolbar --}}
                                @include('offers.partials.block-toolbar')

                                {{-- Block Content --}}
                                <template x-if="block.type === 'text'">
                                    @include('components.offer-simple.blocks.text')
                                </template>
                                <template x-if="block.type === 'services'">
                                    @include('components.offer-simple.blocks.services')
                                </template>
                                <template x-if="block.type === 'summary'">
                                    @include('components.offer-simple.blocks.summary')
                                </template>
                                <template x-if="block.type === 'specifications'">
                                    @include('components.offer-simple.blocks.specifications')
                                </template>
                                <template x-if="block.type === 'brands'">
                                    @include('components.offer-simple.blocks.brands')
                                </template>
                                <template x-if="block.type === 'acceptance'">
                                    @include('components.offer-simple.blocks.acceptance')
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alpine.js Component --}}
    @include('offers.partials.simple-builder-script', [
        'defaultCurrency' => $defaultCurrency,
        'defaultValidUntil' => $defaultValidUntil,
        'defaultVatPercent' => $defaultVatPercent,
        'companyName' => $companyName,
    ])
</x-app-layout>
