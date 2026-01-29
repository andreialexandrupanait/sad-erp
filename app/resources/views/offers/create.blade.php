<x-app-layout>
    <x-slot name="pageTitle">{{ __('New Offer') }}</x-slot>

    <div class="p-4 md:p-6">
        <form action="{{ route('offers.store') }}" method="POST" x-data="offerForm(@js($clients), @js($services), @js($selectedClient))">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Form --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Basic Info --}}
                    <x-ui.card>
                        <x-ui.card-header>
                            <h2 class="font-semibold text-slate-900">{{ __('Offer Details') }}</h2>
                        </x-ui.card-header>
                        <x-ui.card-content class="space-y-4">
                            <div class="field-wrapper">
                                <x-ui.label for="client_id" :required="true">{{ __('Client') }}</x-ui.label>
                                <div class="mt-2">
                                    <x-ui.client-select
                                        name="client_id"
                                        :clients="$clients"
                                        :selected="$selectedClient?->id"
                                        :placeholder="__('Select Client')"
                                        :searchPlaceholder="__('Search clients...')"
                                        :allowEmpty="false"
                                        :clientStatuses="$clientStatuses ?? []"
                                    />
                                </div>
                                @error('client_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="field-wrapper">
                                <x-ui.label for="title" :required="true">{{ __('Title') }}</x-ui.label>
                                <div class="mt-2">
                                    <x-ui.input
                                        type="text"
                                        name="title"
                                        id="title"
                                        required
                                        x-model="form.title"
                                        placeholder="{{ __('e.g., Web Development Services') }}"
                                    />
                                </div>
                                @error('title')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="field-wrapper">
                                    <x-ui.label for="valid_until" :required="true">{{ __('Valid Until') }}</x-ui.label>
                                    <div class="mt-2">
                                        <x-ui.input
                                            type="date"
                                            name="valid_until"
                                            id="valid_until"
                                            required
                                            x-model="form.valid_until"
                                            min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                        />
                                    </div>
                                    @error('valid_until')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="field-wrapper">
                                    <x-ui.label for="currency" :required="true">{{ __('Currency') }}</x-ui.label>
                                    <div class="mt-2">
                                        <x-ui.select name="currency" id="currency" required x-model="form.currency">
                                            <option value="RON">RON</option>
                                            <option value="EUR">EUR</option>
                                            <option value="USD">USD</option>
                                        </x-ui.select>
                                    </div>
                                </div>
                            </div>

                            <div class="field-wrapper">
                                <x-ui.label for="introduction">{{ __('Introduction') }}</x-ui.label>
                                <div class="mt-2" x-data="{ content: '' }" x-init="
                                    $watch('content', value => form.introduction = value);
                                    $refs.introEditor.addEventListener('trix-change', (e) => content = e.target.innerHTML);
                                ">
                                    <input type="hidden" name="introduction" x-model="form.introduction" id="introduction-input">
                                    <trix-editor x-ref="introEditor" input="introduction-input" placeholder="{{ __('Optional introduction text...') }}" class="trix-content prose prose-sm max-w-none bg-white"></trix-editor>
                                </div>
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>

                    {{-- Items Section --}}
                    <x-ui.card>
                        <x-ui.card-header>
                            <h2 class="font-semibold text-slate-900">{{ __('Items') }}</h2>
                        </x-ui.card-header>
                        <x-ui.card-content class="space-y-4">
                            {{-- Add Item Form (always visible) --}}
                            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-medium text-slate-700">{{ __('Add Item') }}</h3>
                                    <x-ui.button type="button" variant="outline" size="sm" @click="showServicePicker = true" x-show="services.length > 0">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        {{ __('From Catalog') }}
                                    </x-ui.button>
                                </div>

                                {{-- New Item Fields --}}
                                <div class="space-y-3">
                                    <div class="field-wrapper">
                                        <x-ui.label>{{ __('Title') }}</x-ui.label>
                                        <div class="mt-1">
                                            <x-ui.input
                                                type="text"
                                                x-model="newItem.title"
                                                placeholder="{{ __('Item name') }}"
                                            />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <div class="field-wrapper">
                                            <x-ui.label>{{ __('Quantity') }}</x-ui.label>
                                            <div class="mt-1">
                                                <x-ui.input
                                                    type="number"
                                                    x-model="newItem.quantity"
                                                    step="0.01"
                                                    min="0.01"
                                                    @input="calculateNewItemTotal()"
                                                />
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <x-ui.label>{{ __('Unit') }}</x-ui.label>
                                            <div class="mt-1">
                                                <x-ui.select x-model="newItem.unit">
                                                    <option value="buc">{{ __('Pieces') }}</option>
                                                    <option value="ora">{{ __('Hours') }}</option>
                                                    <option value="luna">{{ __('Months') }}</option>
                                                    <option value="an">{{ __('Years') }}</option>
                                                    <option value="proiect">{{ __('Projects') }}</option>
                                                </x-ui.select>
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <x-ui.label>{{ __('Unit Price') }}</x-ui.label>
                                            <div class="mt-1">
                                                <x-ui.input
                                                    type="number"
                                                    x-model="newItem.unit_price"
                                                    step="0.01"
                                                    min="0"
                                                    @input="calculateNewItemTotal()"
                                                />
                                            </div>
                                        </div>
                                        <div class="field-wrapper">
                                            <x-ui.label>{{ __('Total') }}</x-ui.label>
                                            <div class="mt-1 flex h-10 w-full items-center rounded-md border border-slate-200 bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-900"
                                                 x-text="formatCurrency(newItem.total)"></div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-2">
                                        <x-ui.button type="button" variant="default" size="sm" @click="addItemFromForm()" x-bind:disabled="!newItem.title.trim()">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            {{ __('Add Item') }}
                                        </x-ui.button>
                                    </div>
                                </div>
                            </div>

                            {{-- Items List --}}
                            <div class="space-y-2" x-show="items.length > 0">
                                <template x-for="(item, index) in items" :key="item._key">
                                    <div class="border border-slate-200 rounded-lg overflow-hidden bg-white transition-all duration-200"
                                         :class="{ 'ring-2 ring-slate-900 ring-offset-1': expandedItemIndex === index }">

                                        {{-- Item Row (collapsed view) --}}
                                        <div class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50 transition-colors"
                                             @click="toggleItemExpand(index)">

                                            {{-- Expand Icon --}}
                                            <button type="button" class="p-1 text-slate-400 hover:text-slate-600 transition-transform duration-200"
                                                    :class="{ 'rotate-90': expandedItemIndex === index }">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </button>

                                            {{-- Service Badge --}}
                                            <template x-if="item.service_id">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                      :class="getServiceBadgeClass(item.service_id)">
                                                    <span x-text="getServiceName(item.service_id)"></span>
                                                </span>
                                            </template>

                                            {{-- Item Info --}}
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium text-slate-900 truncate"
                                                          x-text="item.title || '{{ __('Untitled Item') }}'"></span>
                                                    <template x-if="item.is_recurring">
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-blue-100 text-blue-700">
                                                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                            </svg>
                                                            <span x-text="getBillingCycleLabel(item.billing_cycle)"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                                <p class="text-sm text-slate-500 mt-0.5">
                                                    <span x-text="item.quantity"></span>
                                                    <span x-text="getUnitLabel(item.unit)"></span>
                                                    <span class="mx-1">&times;</span>
                                                    <span x-text="formatCurrency(item.unit_price)"></span>
                                                </p>
                                            </div>

                                            {{-- Total --}}
                                            <div class="text-right">
                                                <span class="font-semibold text-slate-900" x-text="formatCurrency(item.total)"></span>
                                            </div>

                                            {{-- Delete Button --}}
                                            <button type="button"
                                                    @click.stop="removeItem(index)"
                                                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>

                                        {{-- Expanded Edit Form --}}
                                        <div x-show="expandedItemIndex === index" x-collapse>
                                            <div class="border-t border-slate-200 p-4 bg-slate-50/50 space-y-4">
                                                {{-- Hidden fields --}}
                                                <input type="hidden" x-show="item.id" :name="'items['+index+'][id]'" x-model="item.id">
                                                <input type="hidden" :name="'items['+index+'][service_id]'" x-model="item.service_id">

                                                {{-- Title & Description --}}
                                                <div class="grid grid-cols-1 gap-4">
                                                    <div class="field-wrapper">
                                                        <x-ui.label :required="true">{{ __('Title') }}</x-ui.label>
                                                        <div class="mt-1">
                                                            <x-ui.input
                                                                type="text"
                                                                x-bind:name="'items['+index+'][title]'"
                                                                required
                                                                x-model="item.title"
                                                                placeholder="{{ __('Item name') }}"
                                                            />
                                                        </div>
                                                    </div>
                                                    <div class="field-wrapper">
                                                        <x-ui.label>{{ __('Description') }}</x-ui.label>
                                                        <div class="mt-1">
                                                            <x-ui.textarea
                                                                x-bind:name="'items['+index+'][description]'"
                                                                rows="2"
                                                                x-model="item.description"
                                                                placeholder="{{ __('Optional description...') }}"
                                                            ></x-ui.textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Pricing Grid --}}
                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                                    <div class="field-wrapper">
                                                        <x-ui.label :required="true">{{ __('Quantity') }}</x-ui.label>
                                                        <div class="mt-1">
                                                            <x-ui.input
                                                                type="number"
                                                                x-bind:name="'items['+index+'][quantity]'"
                                                                required
                                                                x-model="item.quantity"
                                                                step="0.01"
                                                                min="0.01"
                                                                @input="calculateItemTotal(index)"
                                                            />
                                                        </div>
                                                    </div>
                                                    <div class="field-wrapper">
                                                        <x-ui.label>{{ __('Unit') }}</x-ui.label>
                                                        <div class="mt-1">
                                                            <x-ui.select x-bind:name="'items['+index+'][unit]'" x-model="item.unit">
                                                                <option value="buc">{{ __('Pieces') }}</option>
                                                                <option value="ora">{{ __('Hours') }}</option>
                                                                <option value="luna">{{ __('Months') }}</option>
                                                                <option value="an">{{ __('Years') }}</option>
                                                                <option value="proiect">{{ __('Projects') }}</option>
                                                            </x-ui.select>
                                                        </div>
                                                    </div>
                                                    <div class="field-wrapper">
                                                        <x-ui.label :required="true">{{ __('Unit Price') }}</x-ui.label>
                                                        <div class="mt-1">
                                                            <x-ui.input
                                                                type="number"
                                                                x-bind:name="'items['+index+'][unit_price]'"
                                                                required
                                                                x-model="item.unit_price"
                                                                step="0.01"
                                                                min="0"
                                                                @input="calculateItemTotal(index)"
                                                            />
                                                        </div>
                                                    </div>
                                                    <div class="field-wrapper">
                                                        <x-ui.label>{{ __('Total') }}</x-ui.label>
                                                        <div class="mt-1 flex h-10 w-full items-center rounded-md border border-slate-200 bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-900"
                                                             x-text="formatCurrency(item.total)"></div>
                                                    </div>
                                                </div>

                                                {{-- Recurring Options --}}
                                                <div class="pt-3 border-t border-slate-200">
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="checkbox" x-bind:name="'items['+index+'][is_recurring]'" x-model="item.is_recurring"
                                                               class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                                        <span class="text-sm text-slate-700">{{ __('Recurring item') }}</span>
                                                    </label>
                                                    <div x-show="item.is_recurring" x-collapse class="mt-3">
                                                        <x-ui.select x-bind:name="'items['+index+'][billing_cycle]'" x-model="item.billing_cycle">
                                                            <option value="monthly">{{ __('Monthly') }}</option>
                                                            <option value="yearly">{{ __('Yearly') }}</option>
                                                            <option value="custom">{{ __('Custom') }}</option>
                                                        </x-ui.select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Empty State --}}
                            <div x-show="items.length === 0" class="text-center py-8 text-slate-500">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="mt-2 text-sm">{{ __('No items added yet. Use the form above to add items.') }}</p>
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>

                    {{-- Terms --}}
                    <x-ui.card>
                        <x-ui.card-header>
                            <h2 class="font-semibold text-slate-900">{{ __('Terms & Conditions') }}</h2>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <div x-data="{ content: '' }" x-init="
                                $watch('content', value => form.terms = value);
                                $refs.termsEditor.addEventListener('trix-change', (e) => content = e.target.innerHTML);
                            ">
                                <input type="hidden" name="terms" x-model="form.terms" id="terms-input">
                                <trix-editor x-ref="termsEditor" input="terms-input" placeholder="{{ __('Optional terms and conditions...') }}" class="trix-content prose prose-sm max-w-none bg-white"></trix-editor>
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Summary (read-only) --}}
                    <x-ui.card>
                        <x-ui.card-header>
                            <h2 class="font-semibold text-slate-900">{{ __('Summary') }}</h2>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            {{-- Items Summary List --}}
                            <div class="space-y-2 mb-4" x-show="items.length > 0">
                                <template x-for="(item, index) in items" :key="item._key">
                                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 cursor-pointer hover:bg-slate-50 rounded px-2 -mx-2 transition-colors"
                                         @click="expandedItemIndex = index; $el.closest('.lg\\:col-span-2, .space-y-6').scrollIntoView({behavior: 'smooth'})">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-slate-700 truncate" x-text="item.title || '{{ __('Item') }} ' + (index + 1)"></p>
                                            <p class="text-xs text-slate-500 mt-0.5">
                                                <span x-text="item.quantity"></span>
                                                <span x-text="getUnitLabel(item.unit)"></span>
                                                <span>&times;</span>
                                                <span x-text="formatCurrency(item.unit_price)"></span>
                                            </p>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-900 ml-2" x-text="formatCurrency(item.total)"></span>
                                    </div>
                                </template>
                            </div>

                            <dl class="space-y-3" :class="{ 'pt-3 border-t border-slate-200': items.length > 0 }">
                                <div class="flex justify-between text-sm">
                                    <dt class="text-slate-500">{{ __('Subtotal') }}</dt>
                                    <dd class="font-medium text-slate-900" x-text="formatCurrency(subtotal)"></dd>
                                </div>
                                <div class="flex justify-between text-sm items-center">
                                    <dt class="text-slate-500">{{ __('Discount') }}</dt>
                                    <dd class="flex items-center gap-2">
                                        <input type="number" name="discount_percent" x-model="form.discount_percent"
                                               step="0.1" min="0" max="100"
                                               class="w-16 h-9 text-sm text-right border border-slate-200 rounded-md bg-white px-2 py-1 focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2">
                                        <span class="text-slate-500">%</span>
                                    </dd>
                                </div>
                                <div x-show="discountAmount > 0" x-cloak class="flex justify-between text-sm text-red-600">
                                    <dt>{{ __('Discount Amount') }}</dt>
                                    <dd x-text="'-' + formatCurrency(discountAmount)"></dd>
                                </div>
                                <input type="hidden" name="discount_amount" x-model="discountAmount">
                                <div class="flex justify-between pt-3 border-t border-slate-200 text-lg font-bold">
                                    <dt class="text-slate-900">{{ __('Total') }}</dt>
                                    <dd class="text-slate-900" x-text="formatCurrency(total)"></dd>
                                </div>
                            </dl>
                        </x-ui.card-content>
                    </x-ui.card>

                    {{-- Internal Notes --}}
                    <x-ui.card>
                        <x-ui.card-header>
                            <h2 class="font-semibold text-slate-900">{{ __('Internal Notes') }}</h2>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <x-ui.textarea
                                name="notes"
                                rows="3"
                                x-model="form.notes"
                                placeholder="{{ __('Notes visible only to your team...') }}"
                            ></x-ui.textarea>
                        </x-ui.card-content>
                    </x-ui.card>

                    {{-- Actions --}}
                    <div class="flex flex-col gap-3">
                        <x-ui.button variant="default" type="submit" class="w-full justify-center">
                            {{ __('Create Offer') }}
                        </x-ui.button>
                        <x-ui.button variant="ghost" type="button" onclick="window.history.back()" class="w-full justify-center">
                            {{ __('Cancel') }}
                        </x-ui.button>
                    </div>
                </div>
            </div>

            {{-- Service Picker Slide-Over --}}
            <div x-show="showServicePicker"
                 x-cloak
                 class="fixed inset-0 z-50 overflow-hidden"
                 aria-labelledby="service-picker-title"
                 role="dialog"
                 aria-modal="true">

                {{-- Backdrop --}}
                <div x-show="showServicePicker"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="showServicePicker = false"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity">
                </div>

                {{-- Panel --}}
                <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="showServicePicker"
                         x-transition:enter="transform transition ease-in-out duration-300"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transform transition ease-in-out duration-200"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full"
                         class="w-screen max-w-md">

                        <div class="flex h-full flex-col bg-white shadow-xl">
                            {{-- Header --}}
                            <div class="px-4 py-5 sm:px-6 border-b border-slate-200 bg-slate-100">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h2 id="service-picker-title" class="text-lg font-semibold text-slate-900">
                                            {{ __('Service Catalog') }}
                                        </h2>
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ __('Select a service to add to this offer') }}
                                        </p>
                                    </div>
                                    <button type="button"
                                            @click="showServicePicker = false"
                                            class="rounded-md text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Search --}}
                                <div class="mt-4">
                                    <div class="relative">
                                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <input type="text"
                                               x-model="serviceSearch"
                                               placeholder="{{ __('Search services...') }}"
                                               class="w-full h-10 pl-10 pr-4 text-sm border border-slate-200 rounded-md focus:border-slate-400 focus:ring-1 focus:ring-slate-400 focus:outline-none">
                                    </div>
                                </div>
                            </div>

                            {{-- Service List --}}
                            <div class="flex-1 overflow-y-auto">
                                <div class="divide-y divide-slate-100">
                                    <template x-for="service in filteredServices" :key="service.id">
                                        <div class="p-4 hover:bg-slate-50 transition-colors cursor-pointer group"
                                             @click="addItemFromService(service); showServicePicker = false;">
                                            <div class="flex items-start gap-3">
                                                {{-- Color indicator --}}
                                                <div class="w-1.5 h-12 rounded-full flex-shrink-0"
                                                     :class="'bg-' + (service.color_class || 'slate') + '-500'"></div>

                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <h4 class="font-medium text-slate-900" x-text="service.name"></h4>
                                                    </div>
                                                    <p x-show="service.description"
                                                       class="mt-1 text-sm text-slate-500 line-clamp-2"
                                                       x-text="service.description"></p>
                                                    <div class="mt-2 flex items-center gap-1 text-sm">
                                                        <span class="font-semibold text-slate-900" x-text="formatCurrency(service.default_rate)"></span>
                                                        <span class="text-slate-500">/ {{ __('hour') }}</span>
                                                    </div>
                                                </div>

                                                {{-- Add indicator --}}
                                                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Empty state --}}
                                    <div x-show="filteredServices.length === 0" class="p-8 text-center">
                                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="mt-2 text-sm text-slate-500" x-text="serviceSearch ? '{{ __('No services match your search') }}' : '{{ __('No services available') }}'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function offerForm(clients, services, selectedClient) {
        return {
            // Form data
            form: {
                client_id: selectedClient?.id || '',
                title: '',
                valid_until: '{{ date("Y-m-d", strtotime("+30 days")) }}',
                currency: 'RON',
                introduction: '',
                terms: '',
                discount_percent: 0,
                notes: ''
            },

            // New item form (for adding)
            newItem: {
                title: '',
                description: '',
                quantity: 1,
                unit: 'ora',
                unit_price: 0,
                total: 0
            },

            // Items list (already added)
            items: [],

            // UI State
            expandedItemIndex: -1,
            showServicePicker: false,
            serviceSearch: '',

            // Data references
            clients: clients,
            services: services,

            // Unit labels
            unitLabels: {
                'buc': '{{ __("pcs") }}',
                'ora': '{{ __("hrs") }}',
                'luna': '{{ __("mo") }}',
                'an': '{{ __("yr") }}',
                'proiect': '{{ __("proj") }}'
            },

            // Billing cycle labels
            billingCycleLabels: {
                'monthly': '{{ __("Monthly") }}',
                'yearly': '{{ __("Yearly") }}',
                'custom': '{{ __("Custom") }}'
            },

            // Computed: Subtotal
            get subtotal() {
                return this.items.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
            },

            // Computed: Discount amount
            get discountAmount() {
                const percent = parseFloat(this.form.discount_percent) || 0;
                return Math.round(this.subtotal * percent / 100 * 100) / 100;
            },

            // Computed: Total
            get total() {
                return this.subtotal - this.discountAmount;
            },

            // Computed: Filtered services
            get filteredServices() {
                const activeServices = this.services.filter(s => s.is_active !== false);
                if (!this.serviceSearch.trim()) {
                    return activeServices;
                }
                const q = this.serviceSearch.toLowerCase().trim();
                return activeServices.filter(s =>
                    s.name.toLowerCase().includes(q) ||
                    (s.description && s.description.toLowerCase().includes(q))
                );
            },

            // Calculate new item total
            calculateNewItemTotal() {
                this.newItem.total = (parseFloat(this.newItem.quantity) || 0) * (parseFloat(this.newItem.unit_price) || 0);
            },

            // Add item from the form
            addItemFromForm() {
                if (!this.newItem.title.trim()) return;

                const item = {
                    _key: Date.now(),
                    title: this.newItem.title,
                    description: this.newItem.description || '',
                    quantity: parseFloat(this.newItem.quantity) || 1,
                    unit: this.newItem.unit,
                    unit_price: parseFloat(this.newItem.unit_price) || 0,
                    total: parseFloat(this.newItem.total) || 0,
                    is_recurring: false,
                    billing_cycle: 'monthly',
                    service_id: null
                };
                this.items.push(item);

                // Reset form
                this.newItem = {
                    title: '',
                    description: '',
                    quantity: 1,
                    unit: 'ora',
                    unit_price: 0,
                    total: 0
                };
            },

            // Add item from service catalog
            addItemFromService(service) {
                const newItem = {
                    _key: Date.now(),
                    service_id: service.id,
                    title: service.name,
                    description: service.description || '',
                    quantity: 1,
                    unit: 'ora',
                    unit_price: parseFloat(service.default_rate) || 0,
                    total: parseFloat(service.default_rate) || 0,
                    is_recurring: false,
                    billing_cycle: 'monthly'
                };
                this.items.push(newItem);
            },

            // Remove item
            removeItem(index) {
                this.items.splice(index, 1);
                if (this.expandedItemIndex >= this.items.length) {
                    this.expandedItemIndex = -1;
                }
            },

            // Toggle item expand/collapse
            toggleItemExpand(index) {
                this.expandedItemIndex = this.expandedItemIndex === index ? -1 : index;
            },

            // Calculate item total
            calculateItemTotal(index) {
                const item = this.items[index];
                item.total = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
            },

            // Format currency
            formatCurrency(amount) {
                return new Intl.NumberFormat('ro-RO', {
                    style: 'currency',
                    currency: this.form.currency || 'RON'
                }).format(amount || 0);
            },

            // Get unit label
            getUnitLabel(unit) {
                return this.unitLabels[unit] || unit;
            },

            // Get billing cycle label
            getBillingCycleLabel(cycle) {
                return this.billingCycleLabels[cycle] || cycle;
            },

            // Get service name by ID
            getServiceName(serviceId) {
                const service = this.services.find(s => s.id === serviceId);
                return service ? service.name : '';
            },

            // Get service badge class by ID
            getServiceBadgeClass(serviceId) {
                const service = this.services.find(s => s.id === serviceId);
                if (!service || !service.color_class) {
                    return 'bg-slate-100 text-slate-700';
                }
                const colorMap = {
                    'slate': 'bg-slate-100 text-slate-700',
                    'blue': 'bg-blue-100 text-blue-700',
                    'green': 'bg-green-100 text-green-700',
                    'red': 'bg-red-100 text-red-700',
                    'yellow': 'bg-yellow-100 text-yellow-700',
                    'purple': 'bg-purple-100 text-purple-700',
                    'orange': 'bg-orange-100 text-orange-700',
                    'pink': 'bg-pink-100 text-pink-700',
                    'cyan': 'bg-cyan-100 text-cyan-700',
                    'amber': 'bg-amber-100 text-amber-700'
                };
                return colorMap[service.color_class] || colorMap['slate'];
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
