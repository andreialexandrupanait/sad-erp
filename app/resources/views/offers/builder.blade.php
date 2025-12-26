<x-app-layout>
    <x-slot name="pageTitle">{{ isset($offer) && $offer ? __('Edit Offer') . ' - ' . $offer->offer_number : __('New Offer') }}</x-slot>

    @php
        $defaultValidUntil = now()->addDays($organization->settings['offer_validity_days'] ?? 30)->format('Y-m-d');
        $defaultCurrency = $organization->settings['default_currency'] ?? 'RON';
        $defaultTerms = $organization->settings['default_terms'] ?? '';
    @endphp

    <div class="h-[calc(100vh-4rem)] flex relative" x-data="offerBuilder()" :class="previewMode ? 'preview-mode' : ''">
        {{-- Main Canvas: Document Preview --}}
        <div class="flex-1 bg-slate-100 overflow-y-auto p-8" :class="previewMode ? 'relative' : ''">
            {{-- Preview Mode Edit Button (Top Right Corner) --}}
            <div x-show="previewMode" class="sticky top-0 right-0 z-50 flex justify-end mb-4">
                <button @click="previewMode = false"
                        class="bg-slate-900 text-white px-4 py-2 rounded-lg shadow-lg hover:bg-slate-800 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>{{ __('Exit Preview') }}</span>
                </button>
            </div>
            <div class="max-w-3xl mx-auto">
                {{-- Blocks Container - Separated blocks like Plutio --}}
                <div x-ref="blocksContainer" class="space-y-6">
                    <template x-for="(block, blockIndex) in blocks" :key="block.id">
                        <div x-show="block.visible"
                             class="relative group transition-all duration-200"
                             @mouseenter="hoveredBlockId = block.id"
                             @mouseleave="hoveredBlockId = null">

                            {{-- Block hover toolbar - ABOVE the block --}}
                            <div class="flex justify-center mb-2 opacity-0 group-hover:opacity-100 transition-all duration-200 z-20"
                                 x-show="!previewMode">
                                <div class="flex items-center gap-0.5 bg-slate-800 rounded-lg shadow-lg px-1 py-1">
                                    {{-- Drag handle --}}
                                    <div class="block-drag-handle w-7 h-7 rounded hover:bg-slate-700 flex items-center justify-center cursor-grab" title="{{ __('Drag to reorder') }}">
                                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                        </svg>
                                    </div>
                                    {{-- Up button --}}
                                    <button type="button" @click.stop="moveBlockUp(blockIndex)" :disabled="blockIndex === 0"
                                            class="w-7 h-7 rounded hover:bg-slate-700 disabled:opacity-30 flex items-center justify-center" title="{{ __('Move up') }}">
                                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    </button>
                                    {{-- Down button --}}
                                    <button type="button" @click.stop="moveBlockDown(blockIndex)" :disabled="blockIndex === blocks.length - 1"
                                            class="w-7 h-7 rounded hover:bg-slate-700 disabled:opacity-30 flex items-center justify-center" title="{{ __('Move down') }}">
                                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    {{-- Divider --}}
                                    <div class="w-px h-5 bg-slate-600 mx-0.5"></div>
                                    {{-- Settings/Style button --}}
                                    <button type="button" @click.stop="selectedBlockId = selectedBlockId === block.id ? null : block.id; activeTab = 'blocks'"
                                            class="w-7 h-7 rounded hover:bg-slate-700 flex items-center justify-center"
                                            :class="selectedBlockId === block.id ? 'bg-blue-600' : ''"
                                            title="{{ __('Block settings') }}">
                                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </button>
                                    {{-- Duplicate button --}}
                                    <button type="button" @click.stop="duplicateBlock(blockIndex)"
                                            class="w-7 h-7 rounded hover:bg-slate-700 flex items-center justify-center" title="{{ __('Duplicate') }}">
                                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                    {{-- Delete button --}}
                                    <button type="button" @click.stop="removeBlock(blockIndex)"
                                            class="w-7 h-7 rounded hover:bg-red-600 flex items-center justify-center" title="{{ __('Delete') }}">
                                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Block card with selection outline --}}
                            <div class="bg-white shadow-md hover:shadow-lg rounded-xl border border-slate-200/50 transition-all"
                                 :class="hoveredBlockId === block.id ? 'ring-2 ring-blue-400 ring-offset-2' : ''">

                                {{-- Block content --}}
                                <div class="transition-colors" :class="getBlockStyleClasses(block)">

                                {{-- HEADER BLOCK --}}
                                <template x-if="block.type === 'header'">
                                    @include('components.offer.blocks.header')
                                </template>

                                {{-- SERVICES BLOCK --}}
                                <template x-if="block.type === 'services'">
                                    @include('components.offer.blocks.services')
                                </template>

                                {{-- SUMMARY BLOCK --}}
                                <template x-if="block.type === 'summary'">
                                    @include('components.offer.blocks.summary')
                                </template>

                                {{-- BRANDS BLOCK --}}
                                <template x-if="block.type === 'brands'">
                                    @include('components.offer.blocks.brands')
                                </template>

                                {{-- ACCEPTANCE BLOCK --}}
                                <template x-if="block.type === 'acceptance'">
                                    @include('components.offer.blocks.acceptance')
                                </template>

                                {{-- SPACER BLOCK --}}
                                <template x-if="block.type === 'spacer'">
                                    @include('components.builder.blocks.spacer', ['isTemplate' => false])
                                </template>

                                {{-- OPTIONAL SERVICES BLOCK --}}
                                <template x-if="block.type === 'optional_services'">
                                    @include('components.offer.blocks.optional-services')
                                </template>

                                {{-- TERMS BLOCK --}}
                                <template x-if="block.type === 'terms'">
                                    @include('components.builder.blocks.terms', ['isTemplate' => false])
                                </template>

                                {{-- CONTENT/TEXT BLOCK --}}
                                <template x-if="block.type === 'content'">
                                    @include('components.builder.blocks.content', ['isTemplate' => false])
                                </template>

                                {{-- IMAGE BLOCK --}}
                                <template x-if="block.type === 'image'">
                                    @include('components.builder.blocks.image', ['isTemplate' => false])
                                </template>

                                {{-- DIVIDER BLOCK --}}
                                <template x-if="block.type === 'divider'">
                                    @include('components.builder.blocks.divider', ['isTemplate' => false])
                                </template>

                                {{-- SIGNATURE BLOCK --}}
                                <template x-if="block.type === 'signature'">
                                    @include('components.builder.blocks.signature', ['isTemplate' => false])
                                </template>

                                {{-- TABLE BLOCK --}}
                                <template x-if="block.type === 'table'">
                                    @include('components.builder.blocks.table', ['isTemplate' => false])
                                </template>

                                {{-- QUOTE BLOCK --}}
                                <template x-if="block.type === 'quote'">
                                    @include('components.builder.blocks.quote', ['isTemplate' => false])
                                </template>

                                {{-- COLUMNS BLOCK --}}
                                <template x-if="block.type === 'columns'">
                                    @include('components.builder.blocks.columns', ['isTemplate' => false])
                                </template>

                                {{-- PAGE BREAK BLOCK --}}
                                <template x-if="block.type === 'page_break'">
                                    @include('components.builder.blocks.page-break', ['isTemplate' => false])
                                </template>

                                {{-- PARAGRAPH BLOCK --}}
                                <template x-if="block.type === 'paragraph'">
                                    @include('components.builder.blocks.paragraph', ['isTemplate' => false])
                                </template>

                                {{-- LIST BLOCK --}}
                                <template x-if="block.type === 'list'">
                                    @include('components.builder.blocks.list', ['isTemplate' => false])
                                </template>

                                {{-- HIGHLIGHT BLOCK --}}
                                <template x-if="block.type === 'highlight'">
                                    @include('components.builder.blocks.highlight', ['isTemplate' => false])
                                </template>

                                {{-- NOTE BLOCK --}}
                                <template x-if="block.type === 'note'">
                                    @include('components.builder.blocks.note', ['isTemplate' => false])
                                </template>

                                </div>{{-- End block content --}}
                            </div>{{-- End block card --}}
                        </div>{{-- End block wrapper --}}
                    </template>
                </div>{{-- End blocks container --}}
            </div>{{-- End max-w-3xl wrapper --}}
        </div>{{-- End main canvas --}}

        {{-- Left Sidebar --}}
        <div x-show="!previewMode" class="w-[420px] bg-white border-r border-slate-200 flex flex-col overflow-hidden order-first">
            {{-- Tabs --}}
            <div class="flex border-b border-slate-200 bg-slate-100">
                <button type="button" @click="activeTab = 'settings'"
                        :class="activeTab === 'settings' ? 'border-b-2 border-slate-900 text-slate-900 bg-white' : 'text-slate-500 hover:text-slate-700'"
                        class="flex-1 px-3 py-3 text-sm font-medium transition-colors">
                    {{ __('Settings') }}
                </button>
                <button type="button" @click="activeTab = 'blocks'"
                        :class="activeTab === 'blocks' ? 'border-b-2 border-slate-900 text-slate-900 bg-white' : 'text-slate-500 hover:text-slate-700'"
                        class="flex-1 px-3 py-3 text-sm font-medium transition-colors">
                    {{ __('Blocks') }}
                </button>
            </div>

            {{-- Tab Content --}}
            <div class="flex-1 overflow-y-auto p-4">
                {{-- Settings Tab --}}
                <div x-show="activeTab === 'settings'" class="space-y-4">
                    {{-- Template Selection (only for new offers) --}}
                    @if(!$offer)
                    <div class="field-wrapper">
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Template') }}</label>
                        <select x-model="selectedTemplateId" @change="loadTemplate()" class="w-full h-10 border border-slate-300 rounded-md px-3 text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                            <option value="">{{ __('Select template') }}</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ $template->is_default ? 'selected' : '' }}>
                                    {{ $template->name }}{{ $template->is_default ? ' ★' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Client Selection --}}
                    <div class="field-wrapper">
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Client') }}</label>
                        <div class="flex gap-2">
                            <select x-model="offer.client_id" @change="onClientChange()" class="flex-1 h-10 border border-slate-300 rounded-md px-3 text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                <option value="">{{ __('Select or create new') }}</option>
                                <option value="new">+ {{ __('New Client') }}</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}"
                                            data-name="{{ $client->name }}"
                                            data-company="{{ $client->company_name }}"
                                            data-email="{{ $client->email }}"
                                            data-phone="{{ $client->phone }}"
                                            data-address="{{ $client->address }}"
                                            data-tax="{{ $client->tax_id }}"
                                            data-contact="{{ $client->contact_person }}">
                                        {{ $client->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- New Client Form (inline) --}}
                    <div x-show="offer.client_id === 'new'" x-transition class="p-3 bg-blue-50 rounded-lg border border-blue-200 space-y-3">
                        <p class="text-sm font-medium text-blue-800">{{ __('New Client Details') }}</p>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">{{ __('Company Name') }} *</label>
                            <input type="text" x-model="newClient.company_name" class="w-full h-9 border border-slate-300 rounded px-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">{{ __('Contact Person') }}</label>
                            <input type="text" x-model="newClient.contact_person" class="w-full h-9 border border-slate-300 rounded px-2 text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">{{ __('Email') }}</label>
                                <input type="email" x-model="newClient.email" class="w-full h-9 border border-slate-300 rounded px-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">{{ __('Phone') }}</label>
                                <input type="tel" x-model="newClient.phone" class="w-full h-9 border border-slate-300 rounded px-2 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">{{ __('Tax ID') }}</label>
                            <input type="text" x-model="newClient.tax_id" class="w-full h-9 border border-slate-300 rounded px-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">{{ __('Address') }}</label>
                            <input type="text" x-model="newClient.address" class="w-full h-9 border border-slate-300 rounded px-2 text-sm">
                        </div>
                    </div>

                    {{-- Selected Client Details (Preview) --}}
                    <div x-show="selectedClient.name && offer.client_id !== 'new'" x-transition class="p-3 bg-slate-50 rounded-lg text-sm space-y-1">
                        <p class="font-medium text-slate-900" x-text="selectedClient.company || selectedClient.name"></p>
                        <p x-show="selectedClient.contact" class="text-slate-600">
                            <span class="text-slate-500">{{ __('Contact') }}:</span> <span x-text="selectedClient.contact"></span>
                        </p>
                        <p x-show="selectedClient.email" class="text-slate-600" x-text="selectedClient.email"></p>
                        <p x-show="selectedClient.tax" class="text-slate-600">
                            <span class="text-slate-500">{{ __('Tax ID') }}:</span> <span x-text="selectedClient.tax"></span>
                        </p>
                    </div>

                    {{-- Offer Title --}}
                    <div class="field-wrapper">
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Offer Title') }} <span class="text-red-500">*</span></label>
                        <input type="text" x-model="offer.title" placeholder="{{ __('e.g. Web development services') }}"
                               class="w-full h-10 border border-slate-300 rounded-md px-3 text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                    </div>

                    {{-- Date, Currency & Discount --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="field-wrapper">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Valid Until') }} <span class="text-red-500">*</span></label>
                            <input type="date" x-model="offer.valid_until"
                                   class="w-full h-10 border border-slate-300 rounded-md px-3 text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                        </div>
                        <div class="field-wrapper">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Currency') }}</label>
                            <select x-model="offer.currency" class="w-full h-10 border border-slate-300 rounded-md px-3 text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                <option value="RON">RON</option>
                                <option value="EUR">EUR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                        <div class="field-wrapper">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Discount') }}</label>
                            <div class="flex items-center gap-1">
                                <input type="number" x-model="offer.discount_percent" step="0.1" min="0" max="100"
                                       class="w-full h-10 border border-slate-300 rounded-md px-3 text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                <span class="text-slate-500">%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Totals --}}
                    <div class="mt-4 pt-4 border-t border-slate-200 space-y-2">
                        <template x-for="(amount, currency) in subtotalsByCurrency" :key="'sidebar_'+currency">
                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">{{ __('Subtotal') }} <span x-text="currency"></span></span>
                                    <span class="font-medium" x-text="formatItemCurrency(amount, currency)"></span>
                                </div>
                                <div x-show="offer.discount_percent > 0" class="flex justify-between text-sm text-red-600">
                                    <span>{{ __('Discount') }} (<span x-text="offer.discount_percent"></span>%)</span>
                                    <span x-text="'-' + formatItemCurrency(amount * (offer.discount_percent / 100), currency)"></span>
                                </div>
                                <div class="flex justify-between text-lg font-bold pt-2 border-t border-slate-200 mt-2">
                                    <span>{{ __('Total') }} <span x-text="currency"></span></span>
                                    <span x-text="formatItemCurrency(amount * (1 - (offer.discount_percent / 100)), currency)"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Service Catalog --}}
                    <div class="mt-6 pt-4 border-t border-slate-200">
                        <p class="text-xs text-slate-500 font-medium flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            {{ __('Service Catalog') }}
                        </p>

                        {{-- Search --}}
                        <div class="relative mb-3">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" x-model="serviceSearch" placeholder="{{ __('Search services...') }}"
                                   class="w-full h-9 pl-10 pr-4 text-sm border border-slate-200 rounded-md focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                        </div>

                        {{-- Service List --}}
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @foreach($services as $service)
                                <div x-show="!serviceSearch || '{{ strtolower($service->name) }}'.includes(serviceSearch.toLowerCase())"
                                     @click="addServiceFromCatalog({{ $service->id }}, '{{ addslashes($service->name) }}', '{{ addslashes($service->description ?? '') }}', {{ $service->default_rate ?? 0 }}, '{{ $service->currency ?? 'RON' }}', '{{ $service->unit ?? 'ora' }}')"
                                     class="p-2 border border-slate-200 rounded-lg cursor-pointer hover:border-slate-400 hover:bg-slate-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-xs text-slate-900 truncate">{{ $service->name }}</p>
                                            <p class="text-xs text-slate-500">
                                                {{ number_format($service->default_rate, 2, ',', '.') }}
                                                <span class="{{ ($service->currency ?? 'RON') !== ($defaultCurrency ?? 'RON') ? 'text-amber-600 font-medium' : '' }}">{{ $service->currency ?? 'RON' }}</span>/{{ __($service->unit ?? 'hour') }}
                                            </p>
                                        </div>
                                        <svg class="w-4 h-4 text-green-600 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                </div>
                            @endforeach

                            @if($services->isEmpty())
                                <div class="text-center py-4 text-slate-500">
                                    <p class="text-xs">{{ __('No services defined yet.') }}</p>
                                    <a href="{{ route('settings.services') }}" class="text-blue-600 hover:underline text-xs">{{ __('Add services') }}</a>
                                </div>
                            @endif
                        </div>

                        {{-- Add Custom Item --}}
                        <div @click="addCustomItem()" class="mt-2 p-2 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-center gap-2 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span class="text-xs font-medium">{{ __('Custom Item') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Blocks Tab --}}
                <div x-show="activeTab === 'blocks'" class="space-y-3">
                    <p class="text-sm text-slate-500 mb-4">{{ __('Add and arrange offer sections') }}</p>

                    {{-- Active Blocks List --}}
                    <div class="space-y-2 mb-4">
                        <template x-for="(block, index) in blocks" :key="block.id">
                            <div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg border border-slate-200"
                                 :class="selectedBlockId === block.id ? 'ring-2 ring-blue-500' : ''"
                                 @click="selectedBlockId = block.id">
                                <div class="flex flex-col gap-0.5">
                                    <button type="button" @click.stop="moveBlockUp(index)" :disabled="index === 0"
                                            class="p-0.5 hover:bg-slate-200 rounded disabled:opacity-30">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    </button>
                                    <button type="button" @click.stop="moveBlockDown(index)" :disabled="index === blocks.length - 1"
                                            class="p-0.5 hover:bg-slate-200 rounded disabled:opacity-30">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="w-6 h-6 rounded flex items-center justify-center" :class="getBlockIcon(block.type).bg">
                                    <span x-html="getBlockIcon(block.type).icon" class="w-3 h-3"></span>
                                </div>
                                <span class="flex-1 text-sm font-medium truncate" x-text="getBlockLabel(block.type)"></span>
                                <label class="flex items-center gap-1 text-xs" @click.stop>
                                    <input type="checkbox" x-model="block.visible" class="rounded border-slate-300 w-3 h-3">
                                </label>
                                <button type="button" @click.stop="removeBlock(index)" x-show="!['header', 'services', 'summary'].includes(block.type)"
                                        class="p-1 text-slate-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Add Block Buttons --}}
                    @include('components.builder.sidebar.add-blocks', ['showOptionalServices' => true])

                    {{-- Block Style Customization Panel --}}
                    @include('components.builder.style-panel', ['showHeaderOptions' => true])
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="p-4 border-t border-slate-200 bg-slate-100 space-y-2">
                <button type="button" @click="previewMode = !previewMode"
                        :class="previewMode ? 'bg-slate-600' : 'bg-blue-600'"
                        class="w-full h-10 text-white rounded-md font-medium hover:opacity-90 flex items-center justify-center gap-2">
                    <svg x-show="!previewMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="previewMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span x-text="previewMode ? '{{ __('Edit Mode') }}' : '{{ __('Preview Offer') }}'"></span>
                </button>
                <button type="button" @click="saveOffer()" :disabled="isSaving"
                        class="w-full h-10 bg-slate-900 text-white rounded-md font-medium hover:bg-slate-800 disabled:opacity-50 flex items-center justify-center gap-2">
                    <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    <svg x-show="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isSaving ? '{{ __('Saving...') }}' : '{{ __('Save Offer') }}'"></span>
                </button>
                <div class="flex gap-2">
                    <button type="button" @click="showSaveTemplateModal = true"
                            class="flex-1 h-10 border border-green-300 text-green-700 rounded-md font-medium hover:bg-green-50 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                        </svg>
                        <span class="text-sm">{{ __('Save as Template') }}</span>
                    </button>
                    <button type="button" onclick="window.history.back()" class="flex-1 h-10 border border-slate-300 rounded-md font-medium hover:bg-slate-100">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>

            {{-- Save as Template Modal --}}
            <div x-show="showSaveTemplateModal" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                 @keydown.escape.window="showSaveTemplateModal = false">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4" @click.stop>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Save as Template') }}</h3>
                        <p class="text-sm text-slate-600 mb-4">{{ __('Save the current layout and services as a reusable template.') }}</p>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Template Name') }} <span class="text-red-500">*</span></label>
                                <input type="text" x-model="newTemplateName"
                                       class="w-full h-10 border border-slate-300 rounded-md px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       :placeholder="offer.title || '{{ __('My Template') }}'">
                            </div>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="newTemplateIsDefault" class="rounded border-slate-300 text-slate-900">
                                <span class="text-sm text-slate-700">{{ __('Set as default template') }}</span>
                            </label>

                            <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-600">
                                <p class="font-medium mb-1">{{ __('This will save:') }}</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>{{ __('All block configurations and texts') }}</li>
                                    <li>{{ __('Services with their prices') }}</li>
                                    <li>{{ __('Layout and styling options') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3 p-4 border-t border-slate-200 bg-slate-50 rounded-b-xl">
                        <button type="button" @click="showSaveTemplateModal = false"
                                class="flex-1 h-10 border border-slate-300 rounded-md font-medium hover:bg-white">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" @click="saveAsTemplate()" :disabled="isSavingTemplate || !newTemplateName.trim()"
                                class="flex-1 h-10 bg-green-600 text-white rounded-md font-medium hover:bg-green-700 disabled:opacity-50 flex items-center justify-center gap-2">
                            <svg x-show="isSavingTemplate" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isSavingTemplate ? '{{ __('Saving...') }}' : '{{ __('Save Template') }}'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    {{-- SortableJS for drag-and-drop --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    {{-- Shared Builder Utilities --}}
    <script src="{{ asset('js/builder/builder-core.js') }}"></script>
    <style>
        /* Block drag-and-drop styles */
        .sortable-ghost { opacity: 0.4; background-color: rgb(191 219 254); border-radius: 0.75rem; }
        .sortable-chosen { box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1); }
        .sortable-drag { opacity: 1; }
        .block-drag-handle { cursor: grab; }
        .block-drag-handle:active { cursor: grabbing; }
        /* Ensure blocks are visually distinct */
        [x-ref="blocksContainer"] > template + div {
            margin-top: 1.5rem;
        }
        /* Widget drag-and-drop styles */
        .widget-ghost { opacity: 0.4; background-color: rgb(219 234 254); border-radius: 0.5rem; }
        .widget-chosen { box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); }
        .widget-drag-handle { cursor: grab; }
        .widget-drag-handle:active { cursor: grabbing; }
        .widget-item { transition: all 0.15s ease; }
        .widgets-list { min-height: 20px; }
    </style>
    <script>
    function offerBuilder() {
        const existingOffer = @json($offer ?? null);
        const organization = @json($organization ?? null);
        const defaultValidUntil = '{{ $defaultValidUntil }}';
        const defaultCurrency = '{{ $defaultCurrency }}';
        const defaultTerms = @json($defaultTerms);
        const templates = @json($templates ?? []);
        const defaultTemplate = templates.find(t => t.is_default);

        const validUntil = existingOffer?.valid_until?.split('T')[0] || defaultValidUntil;

        // Helper function to safely parse template content
        function getDefaultBlocks() {
            if (existingOffer?.blocks) {
                console.log('Using existing offer blocks:', existingOffer.blocks);
                return existingOffer.blocks;
            }

            if (defaultTemplate?.content) {
                try {
                    const parsed = JSON.parse(defaultTemplate.content);
                    const blocks = parsed.blocks || parsed;
                    console.log('Using template blocks:', blocks);
                    return blocks;
                } catch (e) {
                    console.error('Failed to parse template content:', e);
                }
            }

            // Fallback to default blocks (matching OfferBlockRegistry)
            console.log('Using fallback blocks');
            return [
                { id: 'header_1', type: 'header', visible: true, data: {
                    introTitle: '{{ __('Your business partner for digital solutions.') }}',
                    introText: '{{ __('We deliver high-quality services tailored to your specific needs.') }}',
                    showLogo: true, showDates: true, showCompanyInfo: true, showClientInfo: true
                }},
                { id: 'services_1', type: 'services', visible: true, data: {
                    title: '{{ __('Proposed Services') }}',
                    showDescriptions: true, showPrices: true, optionalServices: [], notes: '', notesTitle: '{{ __('Notes') }}'
                }},
                { id: 'summary_1', type: 'summary', visible: true, data: {
                    title: '{{ __('Investment Summary') }}',
                    showSubtotal: true, showVAT: false, vatPercent: 19, showDiscount: true, showGrandTotal: true
                }},
                { id: 'acceptance_1', type: 'acceptance', visible: true, data: {
                    title: '{{ __('Offer Acceptance') }}',
                    acceptanceText: '{{ __('By approving this offer, I confirm that I have read and agree to the services and conditions described above.') }}',
                    showClientInfo: true, showDate: true,
                    acceptButtonText: '{{ __('Accept Offer') }}', rejectButtonText: '{{ __('Decline') }}'
                }},
            ];
        }

        return {
            activeTab: 'settings',
            serviceSearch: '',
            isSaving: false,
            previewMode: false,
            selectedBlockId: null,
            exchangeRates: @json($exchangeRates ?? []),
            templates: templates,
            selectedTemplateId: existingOffer ? '' : (defaultTemplate?.id || ''),

            // Save as Template modal
            showSaveTemplateModal: false,
            isSavingTemplate: false,
            newTemplateName: '',
            newTemplateIsDefault: false,

            // Company Info (from organization settings)
            company: {
                name: organization?.name || '',
                address: organization?.address || '',
                tax_id: organization?.tax_id || '',
                phone: organization?.phone || '',
                email: organization?.email || '',
            },
            companyLogo: organization?.logo ? '{{ Storage::url('') }}' + organization.logo : null,

            // New Client (for inline creation)
            newClient: {
                company_name: '',
                contact_person: '',
                email: '',
                phone: '',
                tax_id: '',
                address: '',
            },

            // Selected Client (existing)
            selectedClient: {
                name: '', company: '', email: '', phone: '', address: '', tax: '', contact: '',
            },

            // Offer Data
            offer: {
                id: existingOffer?.id || null,
                client_id: existingOffer?.client_id || '',
                title: existingOffer?.title || '',
                offer_number: existingOffer?.offer_number || '',
                valid_until: validUntil,
                currency: existingOffer?.currency || defaultCurrency,
                discount_percent: existingOffer?.discount_percent || 0,
                notes: existingOffer?.notes || '',
            },

            // Blocks - use default template if available, otherwise fallback
            blocks: getDefaultBlocks(),

            // Service Items
            items: existingOffer?.items?.map((item, idx) => ({
                _key: Date.now() + idx,
                _type: item.type || 'custom',
                _selected: item.is_selected !== false,
                id: item.id,
                service_id: item.service_id,
                title: item.title,
                description: item.description || '',
                quantity: parseFloat(item.quantity) || 1,
                unit: item.unit || 'ora',
                unit_price: parseFloat(item.unit_price) || 0,
                discount_percent: parseFloat(item.discount_percent) || 0,
                currency: item.currency || defaultCurrency,
                total: parseFloat(item.total_price) || 0,
            })) || [],

            hoveredBlockId: null,
            sortableInstance: null,

            init() {
                console.log('offerBuilder init called');
                console.log('Blocks on init:', this.blocks);
                console.log('Active tab:', this.activeTab);

                if (this.offer.client_id && this.offer.client_id !== 'new') {
                    this.$nextTick(() => this.onClientChange());
                }

                // Initialize SortableJS for blocks
                this.$nextTick(() => {
                    this.initBlockSorting();
                    // Widget sorting is now initialized inline in widget-container.blade.php via x-init
                });
            },

            initBlockSorting() {
                const blocksContainer = this.$refs.blocksContainer;
                const widgetPalette = this.$refs.widgetPalette || document.getElementById('widgetPalette');

                console.log('initBlockSorting called', { blocksContainer, widgetPalette, SortableExists: typeof Sortable !== 'undefined' });

                if (blocksContainer && typeof Sortable !== 'undefined') {
                    // Main canvas sortable - blocks only (no widgets from palette here)
                    this.sortableInstance = Sortable.create(blocksContainer, {
                        animation: 150,
                        handle: '.block-drag-handle',
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        onEnd: (evt) => {
                            // Update blocks array order
                            const newBlocks = [...this.blocks];
                            const [movedBlock] = newBlocks.splice(evt.oldIndex, 1);
                            newBlocks.splice(evt.newIndex, 0, movedBlock);
                            this.blocks = newBlocks;
                        },
                    });

                    // Widget palette - clone widgets when dragging out to widget containers
                    if (widgetPalette) {
                        console.log('Initializing widget palette Sortable');
                        Sortable.create(widgetPalette, {
                            group: {
                                name: 'widgets',  // Same group as widget containers
                                pull: 'clone',
                                put: false,
                            },
                            sort: false,
                            animation: 150,
                        });
                    } else {
                        console.warn('Widget palette not found!');
                    }
                }
            },

            getWidgetDefaults(type) {
                const defaults = {
                    'text': { content: '' },
                    'heading': { text: '', level: 'h3' },
                    'image': { src: '', alt: '', caption: '', width: '100%' },
                    'list': { type: 'bullet', items: [''] },
                    'icon_text': { icon: 'check-circle', iconColor: 'green', text: '' },
                    'stat_card': { value: '', label: '', icon: 'trending-up', color: 'green' },
                    'feature_box': { icon: 'star', title: '', description: '', color: 'blue' },
                    'testimonial': { quote: '', author: '', role: '', avatar: '' },
                    'price_box': { title: '', price: '', period: '/month', features: [''], highlighted: false },
                    'divider': { style: 'solid', color: 'gray' },
                    'spacer': { height: 24 },
                    'button': { text: 'Click here', url: '', style: 'primary', align: 'left' },
                };
                return defaults[type] || {};
            },

            get subtotalsByCurrency() {
                const totals = {};
                // Only include 'custom' type items (exclude 'card' type and optional items)
                this.items.filter(item => !item._optionalKey && item._type !== 'card').forEach(item => {
                    const currency = item.currency || this.offer.currency;
                    const itemTotal = parseFloat(item.total) || 0;
                    if (!totals[currency]) {
                        totals[currency] = 0;
                    }
                    totals[currency] += itemTotal;
                });
                return totals;
            },

            get subtotal() {
                // Sum only 'custom' type items (exclude 'card' type and optional services)
                return this.items
                    .filter(item => !item._optionalKey && item._type !== 'card')
                    .reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
            },

            get discountAmount() {
                const percent = parseFloat(this.offer.discount_percent) || 0;
                return Math.round(this.subtotal * percent / 100 * 100) / 100;
            },
            get total() {
                return this.subtotal - this.discountAmount;
            },

            onClientChange() {
                if (this.offer.client_id === 'new') {
                    this.selectedClient = { name: '', company: '', email: '', phone: '', address: '', tax: '', contact: '' };
                    return;
                }
                const select = document.querySelector('select[x-model="offer.client_id"]');
                const option = select?.options[select.selectedIndex];
                if (option && option.value && option.value !== 'new') {
                    this.selectedClient = {
                        name: option.dataset.name || '',
                        company: option.dataset.company || '',
                        email: option.dataset.email || '',
                        phone: option.dataset.phone || '',
                        address: option.dataset.address || '',
                        tax: option.dataset.tax || '',
                        contact: option.dataset.contact || '',
                    };
                }
            },

            loadTemplate() {
                if (!this.selectedTemplateId) return;

                const template = this.templates.find(t => t.id == this.selectedTemplateId);
                if (template && template.content) {
                    try {
                        const content = JSON.parse(template.content);

                        // Handle both old format (array of blocks) and new format (object with blocks and services)
                        let templateBlocks = [];
                        let templateServices = [];

                        if (Array.isArray(content)) {
                            // Old format: just blocks array
                            templateBlocks = content;
                        } else if (content.blocks) {
                            // New format: { blocks: [], services: [] }
                            templateBlocks = content.blocks || [];
                            templateServices = content.services || [];
                        }

                        // Reset IDs to avoid conflicts
                        this.blocks = templateBlocks.map((block, idx) => ({
                            ...block,
                            id: block.type + '_' + Date.now() + '_' + idx,
                        }));

                        // Load template services
                        if (templateServices.length > 0) {
                            this.items = templateServices.map((svc, idx) => ({
                                _key: Date.now() + idx,
                                id: null,
                                service_id: svc.service_id || null,
                                title: svc.title || '',
                                description: svc.description || '',
                                quantity: parseFloat(svc.quantity) || 1,
                                unit: svc.unit || 'ora',
                                unit_price: parseFloat(svc.unit_price) || 0,
                                currency: svc.currency || this.offer.currency,
                                total: (parseFloat(svc.quantity) || 1) * (parseFloat(svc.unit_price) || 0),
                            }));
                        } else {
                            this.items = [];
                        }
                    } catch (e) {
                        console.error('Error loading template:', e);
                    }
                }
            },

            handleLogoUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => { this.companyLogo = e.target.result; };
                    reader.readAsDataURL(file);
                }
            },

            handleBlockImageUpload(event, block) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => { block.data.src = e.target.result; };
                    reader.readAsDataURL(file);
                }
            },

            execCommand(command, value = null) {
                document.execCommand(command, false, value);
            },

            formatCurrency(amount) {
                return new Intl.NumberFormat('ro-RO', {
                    style: 'currency', currency: this.offer.currency || 'RON', minimumFractionDigits: 2
                }).format(amount || 0);
            },

            formatItemCurrency(amount, currency) {
                return new Intl.NumberFormat('ro-RO', {
                    style: 'currency', currency: currency || this.offer.currency || 'RON', minimumFractionDigits: 2
                }).format(amount || 0);
            },

            formatDate(date) {
                if (!date) return '';
                const d = new Date(date);
                return d.toLocaleDateString('ro-RO', { day: '2-digit', month: '2-digit', year: 'numeric' });
            },

            getBlockLabel(type) {
                const labels = {
                    'header': '{{ __('Header') }}',
                    'services': '{{ __('Services') }}',
                    'summary': '{{ __('Summary') }}',
                    'brands': '{{ __('Brands') }}',
                    'acceptance': '{{ __('Acceptance') }}',
                    'optional_services': '{{ __('Optional Services') }}',
                    'content': '{{ __('Text') }}',
                    'paragraph': '{{ __('Paragraph') }}',
                    'image': '{{ __('Image') }}',
                    'divider': '{{ __('Divider') }}',
                    'spacer': '{{ __('Spacer') }}',
                    'terms': '{{ __('Terms') }}',
                    'signature': '{{ __('Signature') }}',
                    'table': '{{ __('Table') }}',
                    'quote': '{{ __('Quote') }}',
                    'columns': '{{ __('Columns') }}',
                    'page_break': '{{ __('Page Break') }}',
                    'list': '{{ __('List') }}',
                    'highlight': '{{ __('Highlight') }}',
                    'note': '{{ __('Note') }}',
                };
                return labels[type] || type;
            },

            getBlockIcon(type) {
                const icons = {
                    'header': { bg: 'bg-slate-100', icon: '<svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' },
                    'services': { bg: 'bg-blue-100', icon: '<svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>' },
                    'summary': { bg: 'bg-green-100', icon: '<svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>' },
                    'brands': { bg: 'bg-amber-100', icon: '<svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>' },
                    'acceptance': { bg: 'bg-purple-100', icon: '<svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
                    'optional_services': { bg: 'bg-fuchsia-100', icon: '<svg class="w-3 h-3 text-fuchsia-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>' },
                    'content': { bg: 'bg-green-100', icon: '<svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>' },
                    'paragraph': { bg: 'bg-emerald-100', icon: '<svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/></svg>' },
                    'image': { bg: 'bg-blue-100', icon: '<svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' },
                    'divider': { bg: 'bg-slate-100', icon: '<svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>' },
                    'spacer': { bg: 'bg-gray-100', icon: '<svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>' },
                    'terms': { bg: 'bg-amber-100', icon: '<svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' },
                    'signature': { bg: 'bg-pink-100', icon: '<svg class="w-3 h-3 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>' },
                    'table': { bg: 'bg-indigo-100', icon: '<svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>' },
                    'quote': { bg: 'bg-violet-100', icon: '<svg class="w-3 h-3 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>' },
                    'columns': { bg: 'bg-teal-100', icon: '<svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>' },
                    'page_break': { bg: 'bg-rose-100', icon: '<svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6M4 12h16M4 7h16"/></svg>' },
                    'list': { bg: 'bg-orange-100', icon: '<svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>' },
                    'highlight': { bg: 'bg-yellow-100', icon: '<svg class="w-3 h-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>' },
                    'note': { bg: 'bg-sky-100', icon: '<svg class="w-3 h-3 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
                };
                return icons[type] || { bg: 'bg-slate-100', icon: '' };
            },

            addBlock(type) {
                // Default data for each block type from registry
                const blockDefaults = {
                    'header': {
                        introTitle: '{{ __('Your business partner for digital solutions.') }}',
                        introText: '{{ __('We deliver high-quality services tailored to your specific needs.') }}',
                        showLogo: true,
                        showDates: true,
                        showCompanyInfo: true,
                        showClientInfo: true,
                    },
                    'services': {
                        title: '{{ __('Proposed Services') }}',
                        showDescriptions: true,
                        showPrices: true,
                        optionalServices: [],
                        notes: '',
                        notesTitle: '{{ __('Notes') }}',
                    },
                    'summary': {
                        title: '{{ __('Investment Summary') }}',
                        showSubtotal: true,
                        showVAT: false,
                        vatPercent: 19,
                        showDiscount: true,
                        showGrandTotal: true,
                    },
                    'brands': {
                        title: '{{ __('Trusted Partners') }}',
                        logos: [],
                        layout: 'grid',
                        columns: 4,
                    },
                    'acceptance': {
                        title: '{{ __('Offer Acceptance') }}',
                        acceptanceText: '{{ __('By approving this offer, I confirm that I have read and agree to the services and conditions described above.') }}',
                        showClientInfo: true,
                        showDate: true,
                        acceptButtonText: '{{ __('Accept Offer') }}',
                        rejectButtonText: '{{ __('Decline') }}',
                    },
                    'table': {
                        title: '',
                        columns: ['{{ __('Column 1') }}', '{{ __('Column 2') }}', '{{ __('Column 3') }}'],
                        rows: [['', '', '']]
                    },
                    'quote': { content: '', author: '' },
                    'columns': { leftTitle: '', leftContent: '', rightTitle: '', rightContent: '', widgets: [] },
                    'list': { listType: 'bullet', items: [''] },
                    'optional_services': { services: [] },
                    'content': { title: '', content: '', widgets: [] },
                    'note': { content: '', widgets: [] },
                    'highlight': { content: '', widgets: [] },
                    'paragraph': { content: '', widgets: [] },
                };

                let blockData = blockDefaults[type] || { title: '', content: '' };

                const block = { id: type + '_' + Date.now(), type: type, visible: true, data: blockData };
                const summaryIndex = this.blocks.findIndex(b => b.type === 'summary');
                if (summaryIndex > -1) {
                    this.blocks.splice(summaryIndex, 0, block);
                } else {
                    this.blocks.push(block);
                }
            },

            removeBlock(index) { this.blocks.splice(index, 1); },
            moveBlockUp(index) { if (index > 0) [this.blocks[index - 1], this.blocks[index]] = [this.blocks[index], this.blocks[index - 1]]; },
            moveBlockDown(index) { if (index < this.blocks.length - 1) [this.blocks[index], this.blocks[index + 1]] = [this.blocks[index + 1], this.blocks[index]]; },
            duplicateBlock(index) {
                const block = this.blocks[index];
                const newBlock = JSON.parse(JSON.stringify(block));
                newBlock.id = block.type + '_' + Date.now();
                this.blocks.splice(index + 1, 0, newBlock);
            },

            // Widget management methods
            getWidgetDefaults(type) {
                const defaults = {
                    'text': { content: '' },
                    'heading': { text: '', level: 'h3' },
                    'image': { src: '', alt: '', caption: '', width: '100%' },
                    'list': { type: 'bullet', items: [''] },
                    'icon_text': { icon: 'check-circle', iconColor: 'green', text: '' },
                    'stat_card': { value: '', label: '', icon: 'trending-up', color: 'green' },
                    'feature_box': { icon: 'star', title: '', description: '', color: 'blue' },
                    'testimonial': { quote: '', author: '', role: '', avatar: '' },
                    'price_box': { title: '', price: '', period: '/{{ __('month') }}', features: [''], highlighted: false },
                    'divider': { style: 'solid', color: 'gray' },
                    'spacer': { height: 24 },
                    'button': { text: '{{ __('Click here') }}', url: '', style: 'primary', align: 'left' },
                };
                return defaults[type] || {};
            },

            addWidgetToBlock(blockId, widgetType) {
                const block = this.blocks.find(b => b.id === blockId);
                if (!block) return;

                // Initialize widgets array if needed
                if (!block.data.widgets) {
                    block.data.widgets = [];
                }

                const widget = {
                    id: 'widget_' + widgetType + '_' + Date.now(),
                    type: widgetType,
                    data: this.getWidgetDefaults(widgetType),
                };

                block.data.widgets.push(widget);

                // Reinitialize sortable after adding widget
                this.$nextTick(() => this.initWidgetSorting());
            },

            removeWidget(block, widgetIndex) {
                if (block.data.widgets) {
                    block.data.widgets.splice(widgetIndex, 1);
                }
            },

            duplicateWidget(block, widgetIndex) {
                if (!block.data.widgets) return;

                const widget = block.data.widgets[widgetIndex];
                const newWidget = JSON.parse(JSON.stringify(widget));
                newWidget.id = 'widget_' + widget.type + '_' + Date.now();
                block.data.widgets.splice(widgetIndex + 1, 0, newWidget);

                // Reinitialize sortable
                this.$nextTick(() => this.initWidgetSorting());
            },

            handleWidgetImageUpload(event, widget, field = 'src') {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    if (field === 'avatar') {
                        widget.data.avatar = e.target.result;
                    } else {
                        widget.data.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            },

            // Table block helper methods
            addTableColumn(block) {
                if (!block.data.columns) block.data.columns = [];
                block.data.columns.push('{{ __('New Column') }}');
                // Add empty cell to each row
                if (block.data.rows) {
                    block.data.rows.forEach(row => row.push(''));
                }
            },

            addTableRow(block) {
                if (!block.data.rows) block.data.rows = [];
                const colCount = block.data.columns?.length || 3;
                block.data.rows.push(Array(colCount).fill(''));
            },

            removeTableRow(block, rowIndex) {
                if (block.data.rows && block.data.rows.length > 1) {
                    block.data.rows.splice(rowIndex, 1);
                }
            },

            updateTableColumn(block, colIndex, value) {
                if (!block.data.columns) block.data.columns = [];
                block.data.columns[colIndex] = value;
            },

            updateTableCell(block, rowIndex, cellIndex, value) {
                if (!block.data.rows) block.data.rows = [];
                if (!block.data.rows[rowIndex]) block.data.rows[rowIndex] = [];
                block.data.rows[rowIndex][cellIndex] = value;
            },

            // List block helper methods
            addListItem(block, afterIndex = null) {
                if (!block.data.items) block.data.items = [];
                if (afterIndex !== null) {
                    block.data.items.splice(afterIndex + 1, 0, '');
                } else {
                    block.data.items.push('');
                }
            },

            removeListItemIfEmpty(block, itemIndex, event) {
                if (block.data.items[itemIndex] === '' && block.data.items.length > 1) {
                    event.preventDefault();
                    block.data.items.splice(itemIndex, 1);
                }
            },

            // Block style customization methods
            getSelectedBlock() {
                return this.blocks.find(b => b.id === this.selectedBlockId);
            },

            getBlockStyle(property) {
                const block = this.getSelectedBlock();
                return block?.data?.style?.[property] || null;
            },

            setBlockStyle(property, value) {
                const block = this.getSelectedBlock();
                if (!block) return;
                if (!block.data) block.data = {};
                if (!block.data.style) block.data.style = {};
                block.data.style[property] = value;
            },

            resetBlockStyle() {
                const block = this.getSelectedBlock();
                if (block?.data) {
                    block.data.style = {};
                }
            },

            getBlockStyleClasses(block) {
                const style = block?.data?.style || {};
                const classes = [];

                // Margin top
                const mtMap = { 'none': 'mt-0', 'sm': 'mt-2', 'md': 'mt-4', 'lg': 'mt-8', 'xl': 'mt-12' };
                if (style.marginTop) classes.push(mtMap[style.marginTop] || '');

                // Margin bottom
                const mbMap = { 'none': 'mb-0', 'sm': 'mb-2', 'md': 'mb-4', 'lg': 'mb-8', 'xl': 'mb-12' };
                if (style.marginBottom) classes.push(mbMap[style.marginBottom] || '');

                // Background
                const bgMap = { 'white': 'bg-white', 'slate-50': 'bg-slate-50', 'blue-50': 'bg-blue-50', 'green-50': 'bg-green-50', 'amber-50': 'bg-amber-50' };
                if (style.background && style.background !== 'white') classes.push(bgMap[style.background] || '');

                // Text align
                const textMap = { 'left': 'text-left', 'center': 'text-center', 'right': 'text-right' };
                if (style.textAlign) classes.push(textMap[style.textAlign] || '');

                // Border
                const borderMap = { 'none': '', 'subtle': 'border border-slate-200 rounded-lg', 'prominent': 'border-2 border-slate-400 rounded-lg' };
                if (style.border) classes.push(borderMap[style.border] || '');

                // Padding
                const pMap = { 'none': 'p-0', 'sm': 'p-2', 'md': 'p-4', 'lg': 'p-6' };
                if (style.padding) classes.push(pMap[style.padding] || '');

                return classes.join(' ');
            },

            addServiceFromCatalog(serviceId, name, description, rate, serviceCurrency, serviceUnit) {
                // Simply take over the currency from the service - no conversion
                serviceCurrency = serviceCurrency || 'RON';
                serviceUnit = serviceUnit || 'ora';

                this.items.push({
                    _key: Date.now(),
                    service_id: serviceId,
                    title: name,
                    description: description,
                    quantity: 1,
                    unit: serviceUnit,
                    unit_price: rate,
                    currency: serviceCurrency, // Store the item's currency
                    discount: 0,
                    total: rate
                });
            },

            addCustomItem() {
                this.items.push({ _key: Date.now(), service_id: null, title: '', description: '', quantity: 1, unit: 'ora', unit_price: 0, currency: this.offer.currency || 'RON', discount: 0, total: 0 });
            },

            removeItem(index) { this.items.splice(index, 1); },
            calculateItemTotal(index) {
                const item = this.items[index];
                const subtotal = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
                const discount = parseFloat(item.discount) || 0;
                item.total = subtotal * (1 - discount / 100);
            },

            // Optional services methods
            addOptionalService(block) {
                this.addOptionalServiceToBlock(block);
            },

            addOptionalServiceToBlock(block) {
                if (!block.data.services) {
                    block.data.services = [];
                }
                block.data.services.push({
                    _key: Date.now(),
                    title: '',
                    description: '',
                    quantity: 1,
                    unit: 'ora',
                    unit_price: 0,
                    currency: this.offer.currency || 'RON',
                    total: 0
                });
            },

            addOptionalServiceFromCatalog(block, serviceId, name, description, rate, serviceCurrency, serviceUnit) {
                if (!block.data.services) {
                    block.data.services = [];
                }
                serviceCurrency = serviceCurrency || 'RON';
                serviceUnit = serviceUnit || 'ora';
                const total = 1 * rate;

                block.data.services.push({
                    _key: Date.now(),
                    service_id: serviceId,
                    title: name,
                    description: description,
                    quantity: 1,
                    unit: serviceUnit,
                    unit_price: rate,
                    currency: serviceCurrency,
                    total: total
                });
            },

            removeOptionalService(block, index) {
                if (block.data.services) {
                    block.data.services.splice(index, 1);
                }
            },

            toggleOptionalService(optionalService) {
                // Check if this optional service is already in items
                const existingIndex = this.items.findIndex(item => item._optionalKey === optionalService._key);

                if (existingIndex >= 0) {
                    // Remove it from items
                    this.items.splice(existingIndex, 1);
                } else {
                    // Add it to items
                    this.items.push({
                        _key: Date.now(),
                        _optionalKey: optionalService._key, // Track which optional service this is from
                        service_id: null,
                        title: optionalService.title,
                        description: optionalService.description,
                        quantity: parseFloat(optionalService.quantity) || 1,
                        unit: optionalService.unit,
                        unit_price: parseFloat(optionalService.unit_price) || 0,
                        currency: optionalService.currency,
                        total: (parseFloat(optionalService.quantity) || 1) * (parseFloat(optionalService.unit_price) || 0)
                    });
                }
            },

            // Summary block calculation helpers
            calculateSubtotal() {
                // Only include 'custom' type items (exclude 'card' type and optional items)
                return this.items
                    .filter(item => !item._optionalKey && item._type !== 'card')
                    .reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
            },

            calculateDiscount() {
                const percent = parseFloat(this.offer.discount_percent) || 0;
                return this.calculateSubtotal() * (percent / 100);
            },

            calculateVAT() {
                const block = this.blocks.find(b => b.type === 'summary');
                const vatPercent = block?.data?.vatPercent || 19;
                const base = this.calculateSubtotal() - this.calculateDiscount();
                return base * (vatPercent / 100);
            },

            calculateGrandTotal() {
                const block = this.blocks.find(b => b.type === 'summary');
                const showVAT = block?.data?.showVAT || false;
                const base = this.calculateSubtotal() - this.calculateDiscount();
                return showVAT ? base + this.calculateVAT() : base;
            },

            // Brand logo upload
            uploadBrandLogo(event, logoIndex) {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    const block = this.blocks.find(b => b.type === 'brands');
                    if (block && block.data.logos && block.data.logos[logoIndex]) {
                        block.data.logos[logoIndex].src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            },

            async saveOffer() {
                if (this.offer.client_id === 'new' && !this.newClient.company_name) {
                    alert('{{ __('Please enter the new client company name') }}');
                    return;
                }
                if (!this.offer.client_id) {
                    alert('{{ __('Please select a client or create a new one') }}');
                    return;
                }
                if (!this.offer.title) {
                    alert('{{ __('Please enter an offer title') }}');
                    return;
                }
                if (this.items.length === 0) {
                    alert('{{ __('Please add at least one service') }}');
                    return;
                }

                this.isSaving = true;

                const data = {
                    client_id: this.offer.client_id === 'new' ? null : this.offer.client_id,
                    new_client: this.offer.client_id === 'new' ? this.newClient : null,
                    title: this.offer.title,
                    valid_until: this.offer.valid_until,
                    currency: this.offer.currency,
                    discount_percent: this.offer.discount_percent,
                    notes: this.offer.notes,
                    blocks: this.blocks,
                    // Filter out optional service items - they are stored in blocks, not items
                    items: this.items.filter(item => !item._optionalKey).map(item => ({
                        id: item.id || null,
                        service_id: item.service_id,
                        type: item._type || 'custom',
                        is_selected: item._selected !== false,
                        title: item.title,
                        description: item.description,
                        quantity: item.quantity,
                        unit: item.unit,
                        unit_price: item.unit_price,
                        discount_percent: item.discount_percent || 0,
                        currency: item.currency,
                    })),
                };

                try {
                    const url = this.offer.id ? '{{ url("offers") }}/' + this.offer.id : '{{ route("offers.store") }}';
                    const response = await fetch(url, {
                        method: this.offer.id ? 'PUT' : 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify(data),
                    });
                    const result = await response.json();
                    if (result.success) {
                        window.location.href = '{{ url("offers") }}/' + (result.offer?.id || this.offer.id);
                    } else {
                        alert(result.message || '{{ __('Error saving offer') }}');
                    }
                } catch (error) {
                    console.error('Save error:', error);
                    alert('{{ __('Error saving offer') }}');
                } finally {
                    this.isSaving = false;
                }
            },

            async approveOffer() {
                // Validate that offer is saved
                if (!this.offer.id) {
                    alert('{{ __('Please save the offer first before approving') }}');
                    return;
                }

                if (!confirm('{{ __('Are you sure you want to approve this offer? This will generate a contract.') }}')) {
                    return;
                }

                this.isSaving = true;

                try {
                    const response = await fetch('{{ url("offers") }}/' + this.offer.id + '/approve', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            signature_date: new Date().toISOString().split('T')[0],
                            signature_text: this.blocks.find(b => b.type === 'signature')?.data?.signatureText || null,
                        }),
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Show success message
                        alert(result.message || '{{ __('Offer approved successfully! Contract has been generated.') }}');

                        // Redirect to contract or offer view
                        if (result.contract_id) {
                            window.location.href = '{{ url("contracts") }}/' + result.contract_id;
                        } else {
                            window.location.href = '{{ url("offers") }}/' + this.offer.id;
                        }
                    } else {
                        alert(result.message || '{{ __('Error approving offer') }}');
                    }
                } catch (error) {
                    console.error('Approval error:', error);
                    alert('{{ __('Error approving offer') }}');
                } finally {
                    this.isSaving = false;
                }
            },

            async saveAsTemplate() {
                if (!this.newTemplateName.trim()) {
                    alert('{{ __('Please enter a template name') }}');
                    return;
                }

                this.isSavingTemplate = true;

                try {
                    const payload = {
                        name: this.newTemplateName,
                        blocks: this.blocks,
                        services: this.items.map(item => ({
                            service_id: item.service_id,
                            title: item.title,
                            description: item.description,
                            quantity: item.quantity,
                            unit: item.unit,
                            unit_price: item.unit_price,
                            currency: item.currency,
                        })),
                        is_default: this.newTemplateIsDefault,
                    };

                    const response = await fetch('{{ route("offers.save-as-template") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Add new template to dropdown
                        this.templates.push({
                            id: result.template.id,
                            name: result.template.name,
                            is_default: this.newTemplateIsDefault,
                        });

                        // Reset modal
                        this.showSaveTemplateModal = false;
                        this.newTemplateName = '';
                        this.newTemplateIsDefault = false;

                        alert(result.message || '{{ __('Template saved successfully!') }}');
                    } else {
                        alert(result.message || '{{ __('Error saving template') }}');
                    }
                } catch (error) {
                    console.error('Save template error:', error);
                    alert('{{ __('Error saving template') }}');
                } finally {
                    this.isSavingTemplate = false;
                }
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
