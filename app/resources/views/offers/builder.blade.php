<x-app-layout>
    <x-slot name="pageTitle">{{ isset($offer) && $offer ? __('Edit Offer') . ' - ' . $offer->offer_number : __('New Offer') }}</x-slot>

    @php
        $defaultValidUntil = now()->addDays($organization->settings['offer_validity_days'] ?? 30)->format('Y-m-d');
        $defaultCurrency = $organization->settings['default_currency'] ?? 'RON';
        $defaultTerms = $organization->settings['default_terms'] ?? '';
    @endphp

    <div class="h-[calc(100vh-4rem)] flex" x-data="offerBuilder()">
        {{-- Main Canvas: Document Preview --}}
        <div class="flex-1 bg-slate-100 overflow-y-auto p-8">
            <div class="max-w-3xl mx-auto">
                {{-- Document Container --}}
                <div class="bg-white shadow-lg rounded-lg overflow-hidden print:shadow-none">
                    <div>
                        {{-- Render blocks in order --}}
                        <div x-ref="blocksContainer">
                        <template x-for="(block, blockIndex) in blocks" :key="block.id">
                            <div x-show="block.visible"
                                 class="relative group transition-all duration-200"
                                 :class="[
                                     hoveredBlockId === block.id ? 'ring-2 ring-blue-300 ring-inset rounded-lg' : '',
                                     block.type !== 'header' ? 'mx-8 mb-6' : ''
                                 ]"
                                 @mouseenter="hoveredBlockId = block.id"
                                 @mouseleave="hoveredBlockId = null">

                                {{-- Block hover toolbar - Plutio style at top-right --}}
                                <div class="absolute -top-3 right-4 opacity-0 group-hover:opacity-100 transition-all duration-200 z-20">
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

                                {{-- Block content --}}
                                <div class="transition-colors" :class="getBlockStyleClasses(block)">

                                {{-- HEADER BLOCK - Plutio Style --}}
                                <template x-if="block.type === 'header'">
                                    <div>
                                        {{-- Top Navy Bar with Date, Title, Validity + Logo --}}
                                        <div class="bg-slate-800 text-white px-8 py-6">
                                            <div class="flex justify-between items-start">
                                                {{-- Left: Date, Title, Validity --}}
                                                <div class="space-y-1">
                                                    <p class="text-sm text-slate-300">{{ __('Date') }}: <span x-text="formatDate(new Date())"></span></p>
                                                    <p class="text-lg font-medium">
                                                        {{ __('Service proposal for') }}:
                                                        <template x-if="offer.client_id === 'new'">
                                                            <span x-text="newClient.contact_person || newClient.company_name || '{{ __('New Client') }}'"></span>
                                                        </template>
                                                        <template x-if="offer.client_id && offer.client_id !== 'new'">
                                                            <span x-text="selectedClient.contact || selectedClient.company || selectedClient.name || '{{ __('Client') }}'"></span>
                                                        </template>
                                                        <template x-if="!offer.client_id">
                                                            <span class="text-slate-400">{{ __('Select a client') }}</span>
                                                        </template>
                                                    </p>
                                                    <p class="text-sm text-slate-300">{{ __('Valid until') }}: <span x-text="formatDate(offer.valid_until)"></span></p>
                                                </div>
                                                {{-- Right: Logo --}}
                                                <div class="flex items-center">
                                                    @if($organization->logo ?? false)
                                                        <img src="{{ Storage::url($organization->logo) }}" alt="{{ $organization->name }}" class="h-14 w-auto object-contain brightness-0 invert">
                                                    @else
                                                        <span class="text-2xl font-bold">{{ $organization->name ?? config('app.name') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Bottom Section: Intro Text + Company Contact --}}
                                        <div class="bg-slate-700 text-white px-8 py-6">
                                            <div class="flex gap-8">
                                                {{-- Left: Intro/Pitch Text --}}
                                                <div class="flex-1">
                                                    <div>
                                                        <h3 class="text-lg font-semibold mb-2" x-text="block.data.introTitle || '{{ __('Your business partner for digital solutions.') }}'"></h3>
                                                        <p class="text-sm text-slate-300 leading-relaxed" x-text="block.data.introText || '{{ __('We deliver high-quality services tailored to your specific needs. Our team is dedicated to helping you achieve your business goals.') }}'"></p>
                                                    </div>
                                                </div>
                                                {{-- Right: Company Contact Details --}}
                                                <div class="w-72 text-sm space-y-1">
                                                    @if($organization->email ?? false)
                                                        <p><span class="text-slate-400">{{ __('Email') }}:</span> {{ $organization->email }}</p>
                                                    @endif
                                                    @if($organization->phone ?? false)
                                                        <p><span class="text-slate-400">{{ __('Phone') }}:</span> {{ $organization->phone }}</p>
                                                    @endif
                                                    @if($organization->whatsapp ?? false)
                                                        <p><span class="text-slate-400">{{ __('WhatsApp') }}:</span> <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $organization->whatsapp) }}" class="text-blue-300 hover:underline">{{ $organization->whatsapp }}</a></p>
                                                    @endif
                                                    @if($organization->address ?? false)
                                                        <p><span class="text-slate-400">{{ __('Address') }}:</span> {{ $organization->address }}</p>
                                                    @endif
                                                    @if($organization->registration_number ?? false)
                                                        <p><span class="text-slate-400">{{ __('Reg. No.') }}:</span> {{ $organization->registration_number }}</p>
                                                    @endif
                                                    @if($organization->tax_id ?? false)
                                                        <p><span class="text-slate-400">{{ __('Tax ID') }}:</span> {{ $organization->tax_id }}</p>
                                                    @endif
                                                    @if(isset($bankAccounts) && $bankAccounts->count() > 0)
                                                        <div class="mt-2 pt-2 border-t border-slate-600">
                                                            @foreach($bankAccounts as $account)
                                                                @php
                                                                    // Get first letter of each word: "Banca Transilvania" -> "BT"
                                                                    $bankName = $account['bank'] ?? '';
                                                                    $words = preg_split('/\s+/', trim($bankName));
                                                                    $bankAbbr = '';
                                                                    foreach ($words as $word) {
                                                                        if (strlen($word) > 0) {
                                                                            $bankAbbr .= strtoupper($word[0]);
                                                                        }
                                                                    }
                                                                @endphp
                                                                <p><span class="text-slate-400">{{ $bankAbbr }} {{ $account['currency'] ?? 'RON' }}:</span> {{ $account['iban'] ?? '' }}</p>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Offer Number Badge --}}
                                        <div class="px-8 py-4 bg-white border-b border-slate-200">
                                            <div class="flex justify-between items-center">
                                                {{-- Client Details --}}
                                                <div>
                                                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">{{ __('Proposal for') }}</p>
                                                    <template x-if="offer.client_id === 'new'">
                                                        <div>
                                                            <p class="font-semibold text-slate-900" x-text="newClient.company_name || '{{ __('New Client') }}'"></p>
                                                            <p x-show="newClient.address" class="text-sm text-slate-600" x-text="newClient.address"></p>
                                                            <p x-show="newClient.tax_id" class="text-sm text-slate-600">{{ __('Tax ID') }}: <span x-text="newClient.tax_id"></span></p>
                                                        </div>
                                                    </template>
                                                    <template x-if="offer.client_id && offer.client_id !== 'new'">
                                                        <div>
                                                            <p class="font-semibold text-slate-900" x-text="selectedClient.company || selectedClient.name || '{{ __('Select a client') }}'"></p>
                                                            <p x-show="selectedClient.address" class="text-sm text-slate-600" x-text="selectedClient.address"></p>
                                                            <p x-show="selectedClient.tax" class="text-sm text-slate-600">{{ __('Tax ID') }}: <span x-text="selectedClient.tax"></span></p>
                                                        </div>
                                                    </template>
                                                    <template x-if="!offer.client_id">
                                                        <p class="text-slate-400 italic">{{ __('Select a client') }}</p>
                                                    </template>
                                                </div>
                                                {{-- Offer Number --}}
                                                <div class="text-right">
                                                    <p class="text-2xl font-bold text-slate-900">OFR-<span x-text="new Date().getFullYear()"></span>-XXX</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- SPACER BLOCK --}}
                                <template x-if="block.type === 'spacer'">
                                    <div class="h-8"></div>
                                </template>

                                {{-- SERVICES BLOCK --}}
                                <template x-if="block.type === 'services'">
                                    <div>
                                        <h3 class="text-xl font-semibold text-slate-900 mb-4">{{ __('Proposed Services') }}</h3>
                                        <div class="space-y-3">
                                            <template x-for="(item, index) in items" :key="item._key">
                                                <div class="border border-slate-200 rounded-lg p-4 hover:border-slate-300 transition-colors">
                                                    {{-- Title and Description --}}
                                                    <div class="mb-3">
                                                        <input type="text" x-model="item.title" placeholder="{{ __('Service name') }}"
                                                               class="font-medium text-slate-900 bg-transparent border-none focus:ring-0 p-0 w-full text-base">
                                                        <textarea x-model="item.description" placeholder="{{ __('Service description (optional)...') }}"
                                                                  rows="2" class="mt-2 w-full text-sm text-slate-500 bg-slate-50 border border-slate-200 rounded p-2 focus:border-slate-400 resize-none"></textarea>
                                                    </div>

                                                    {{-- Single Row: Qty | Unit | x | Price | Currency | = | Total | Delete --}}
                                                    <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                                                        {{-- Quantity --}}
                                                        <input type="number" x-model="item.quantity" @input="calculateItemTotal(index)"
                                                               step="0.01" min="0.01"
                                                               class="w-20 text-right border border-slate-200 rounded px-2 py-1.5 text-sm focus:border-slate-400 focus:ring-1 focus:ring-slate-400">

                                                        {{-- Unit --}}
                                                        <select x-model="item.unit" class="w-24 border border-slate-200 rounded px-2 py-1.5 text-sm focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                                                            <option value="ora">{{ __('hours') }}</option>
                                                            <option value="buc">{{ __('pcs') }}</option>
                                                            <option value="luna">{{ __('months') }}</option>
                                                            <option value="zi">{{ __('days') }}</option>
                                                            <option value="proiect">{{ __('project') }}</option>
                                                        </select>

                                                        {{-- Multiply --}}
                                                        <span class="text-slate-400">×</span>

                                                        {{-- Unit Price --}}
                                                        <input type="number" x-model="item.unit_price" @input="calculateItemTotal(index)"
                                                               step="0.01" min="0"
                                                               class="w-28 text-right border border-slate-200 rounded px-2 py-1.5 text-sm focus:border-slate-400 focus:ring-1 focus:ring-slate-400">

                                                        {{-- Currency selector per item --}}
                                                        <select x-model="item.currency" class="w-16 border border-slate-200 rounded px-1 py-1.5 text-sm focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                                                            <option value="RON">RON</option>
                                                            <option value="EUR">EUR</option>
                                                            <option value="USD">USD</option>
                                                        </select>

                                                        {{-- Equals --}}
                                                        <span class="text-slate-400">=</span>

                                                        {{-- Total --}}
                                                        <div class="w-32 text-right font-bold text-slate-900" x-text="formatItemCurrency(item.total, item.currency)"></div>

                                                        {{-- Delete button --}}
                                                        <button type="button" @click="removeItem(index)"
                                                                class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            <div x-show="items.length === 0" class="py-12 text-center border-2 border-dashed border-slate-200 rounded-lg">
                                                <svg class="mx-auto h-12 w-12 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <p class="text-slate-500">{{ __('Add services from the "Services" tab') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- SUMMARY BLOCK --}}
                                <template x-if="block.type === 'summary'">
                                    <div class="bg-slate-50 rounded-lg p-6">
                                        <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Investment Summary') }}</h3>
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="border-b border-slate-200">
                                                    <th class="text-left py-2 font-medium text-slate-500">{{ __('Service') }}</th>
                                                    <th class="text-right py-2 font-medium text-slate-500">{{ __('Qty') }}</th>
                                                    <th class="text-right py-2 font-medium text-slate-500">{{ __('Unit Price') }}</th>
                                                    <th class="text-right py-2 font-medium text-slate-500">{{ __('Total') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="item in items" :key="item._key">
                                                    <tr class="border-b border-slate-100">
                                                        <td class="py-2" x-text="item.title || '{{ __('Untitled') }}'"></td>
                                                        <td class="py-2 text-right" x-text="item.quantity + ' ' + item.unit"></td>
                                                        <td class="py-2 text-right" x-text="formatItemCurrency(item.unit_price, item.currency)"></td>
                                                        <td class="py-2 text-right font-medium" x-text="formatItemCurrency(item.total, item.currency)"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                        <div class="mt-4 pt-4 border-t border-slate-200 space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-slate-500">{{ __('Subtotal') }}</span>
                                                <span x-text="formatCurrency(subtotal)"></span>
                                            </div>
                                            <div x-show="offer.discount_percent > 0" class="flex justify-between text-red-600">
                                                <span>{{ __('Discount') }} (<span x-text="offer.discount_percent"></span>%)</span>
                                                <span>-<span x-text="formatCurrency(subtotal * (offer.discount_percent / 100))"></span></span>
                                            </div>
                                            <div class="flex justify-between text-lg font-bold pt-2 border-t border-slate-300">
                                                <span>{{ __('TOTAL') }}</span>
                                                <span x-text="formatCurrency(total)"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- TERMS BLOCK --}}
                                <template x-if="block.type === 'terms'">
                                    <div class="border-t border-slate-200 pt-6">
                                        <h3 class="text-lg font-semibold text-slate-900 mb-3">{{ __('Terms and Conditions') }}</h3>
                                        <textarea x-model="block.data.content" rows="4"
                                                  placeholder="{{ __('Add terms and conditions...') }}"
                                                  class="w-full text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded-lg p-3 focus:border-slate-400 resize-none"></textarea>
                                    </div>
                                </template>

                                {{-- CONTENT/TEXT BLOCK --}}
                                <template x-if="block.type === 'content'">
                                    <div class="prose max-w-none">
                                        <input type="text" x-model="block.data.title" placeholder="{{ __('Section Title (optional)') }}"
                                               class="w-full text-lg font-semibold text-blue-600 bg-transparent border-none focus:ring-0 p-0 mb-2">
                                        <div contenteditable="true"
                                             x-html="block.data.content"
                                             @blur="block.data.content = $event.target.innerHTML"
                                             class="min-h-[60px] p-3 bg-slate-50 border border-slate-200 rounded-lg focus:border-slate-400 focus:outline-none text-slate-600"
                                             data-placeholder="{{ __('Write your content here...') }}"></div>
                                    </div>
                                </template>

                                {{-- IMAGE BLOCK --}}
                                <template x-if="block.type === 'image'">
                                    <div class="text-center">
                                        <template x-if="block.data.src">
                                            <img :src="block.data.src" :alt="block.data.alt || ''" class="max-w-full h-auto rounded-lg mx-auto">
                                        </template>
                                        <template x-if="!block.data.src">
                                            <label class="block cursor-pointer">
                                                <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 hover:border-slate-400 transition-colors">
                                                    <svg class="mx-auto h-12 w-12 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    <p class="text-slate-500">{{ __('Click to upload image') }}</p>
                                                </div>
                                                <input type="file" accept="image/*" class="hidden" @change="handleBlockImageUpload($event, block)">
                                            </label>
                                        </template>
                                    </div>
                                </template>

                                {{-- DIVIDER BLOCK --}}
                                <template x-if="block.type === 'divider'">
                                    <hr class="border-t-2 border-slate-200">
                                </template>

                                {{-- SIGNATURE BLOCK --}}
                                <template x-if="block.type === 'signature'">
                                    <div class="border-t border-slate-200 pt-6">
                                        <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Offer Acceptance') }}</h3>
                                        <p class="text-sm text-slate-500 mb-6">{{ __('By signing below, I confirm that I have read and agree to the services and conditions described in this offer.') }}</p>
                                        <div class="grid grid-cols-2 gap-8">
                                            <div>
                                                <div class="border-b-2 border-slate-300 h-16 mb-2"></div>
                                                <p class="text-sm text-slate-500">{{ __('Client Signature') }}</p>
                                                <p class="text-sm text-slate-500">{{ __('Date') }}: _______________</p>
                                            </div>
                                            <div>
                                                <div class="border-b-2 border-slate-300 h-16 mb-2"></div>
                                                <p class="text-sm text-slate-500">{{ __('Provider Signature') }}</p>
                                                <p class="text-sm text-slate-500">{{ __('Date') }}: _______________</p>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- TABLE BLOCK --}}
                                <template x-if="block.type === 'table'">
                                    <div>
                                        <input type="text" x-model="block.data.title" placeholder="{{ __('Table Title (optional)') }}"
                                               class="w-full text-lg font-semibold text-slate-900 bg-transparent border-none focus:ring-0 p-0 mb-3">
                                        <div class="overflow-x-auto">
                                            <table class="w-full border-collapse border border-slate-200">
                                                <thead>
                                                    <tr>
                                                        <template x-for="(col, colIndex) in block.data.columns" :key="'col-'+colIndex">
                                                            <th class="border border-slate-200 bg-slate-50 p-2">
                                                                <input type="text" :value="col" @input="updateTableColumn(block, colIndex, $event.target.value)"
                                                                       class="w-full text-center text-sm font-medium bg-transparent border-none focus:ring-0 p-0">
                                                            </th>
                                                        </template>
                                                        <th class="border border-slate-200 bg-slate-50 w-10">
                                                            <button type="button" @click="addTableColumn(block)" class="text-slate-400 hover:text-slate-600">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                                </svg>
                                                            </button>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="(row, rowIndex) in block.data.rows" :key="'row-'+rowIndex">
                                                        <tr>
                                                            <template x-for="(cell, cellIndex) in row" :key="'cell-'+rowIndex+'-'+cellIndex">
                                                                <td class="border border-slate-200 p-2">
                                                                    <input type="text" :value="cell" @input="updateTableCell(block, rowIndex, cellIndex, $event.target.value)"
                                                                           class="w-full text-sm bg-transparent border-none focus:ring-0 p-0">
                                                                </td>
                                                            </template>
                                                            <td class="border border-slate-200 w-10">
                                                                <button type="button" @click="removeTableRow(block, rowIndex)" class="text-slate-400 hover:text-red-600 p-1">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                    </svg>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                        <button type="button" @click="addTableRow(block)" class="mt-2 text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            {{ __('Add Row') }}
                                        </button>
                                    </div>
                                </template>

                                {{-- QUOTE BLOCK --}}
                                <template x-if="block.type === 'quote'">
                                    <div class="border-l-4 border-blue-400 bg-blue-50 p-4 rounded-r-lg">
                                        <textarea x-model="block.data.content" rows="3" placeholder="{{ __('Enter quote text...') }}"
                                                  class="w-full text-slate-700 italic bg-transparent border-none focus:ring-0 p-0 resize-none"></textarea>
                                        <input type="text" x-model="block.data.author" placeholder="{{ __('Quote author (optional)') }}"
                                               class="mt-2 text-sm text-blue-600 bg-transparent border-none focus:ring-0 p-0 w-full">
                                    </div>
                                </template>

                                {{-- COLUMNS BLOCK --}}
                                <template x-if="block.type === 'columns'">
                                    <div class="grid grid-cols-2 gap-6">
                                        <div>
                                            <input type="text" x-model="block.data.leftTitle" placeholder="{{ __('Left Column Title') }}"
                                                   class="w-full text-base font-semibold text-slate-900 bg-transparent border-none focus:ring-0 p-0 mb-2">
                                            <textarea x-model="block.data.leftContent" rows="4" placeholder="{{ __('Left column content...') }}"
                                                      class="w-full text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded p-2 focus:border-slate-400 resize-none"></textarea>
                                        </div>
                                        <div>
                                            <input type="text" x-model="block.data.rightTitle" placeholder="{{ __('Right Column Title') }}"
                                                   class="w-full text-base font-semibold text-slate-900 bg-transparent border-none focus:ring-0 p-0 mb-2">
                                            <textarea x-model="block.data.rightContent" rows="4" placeholder="{{ __('Right column content...') }}"
                                                      class="w-full text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded p-2 focus:border-slate-400 resize-none"></textarea>
                                        </div>
                                    </div>
                                </template>

                                {{-- PAGE BREAK BLOCK --}}
                                <template x-if="block.type === 'page_break'">
                                    <div class="flex items-center gap-4 py-4">
                                        <div class="flex-1 border-t-2 border-dashed border-slate-300"></div>
                                        <span class="text-xs text-slate-400 uppercase tracking-wider">{{ __('Page Break') }}</span>
                                        <div class="flex-1 border-t-2 border-dashed border-slate-300"></div>
                                    </div>
                                </template>

                                {{-- PARAGRAPH BLOCK (simple text without title) --}}
                                <template x-if="block.type === 'paragraph'">
                                    <div contenteditable="true"
                                         x-html="block.data.content"
                                         @blur="block.data.content = $event.target.innerHTML"
                                         class="min-h-[40px] p-3 bg-slate-50 border border-slate-200 rounded-lg focus:border-slate-400 focus:outline-none text-slate-600"
                                         data-placeholder="{{ __('Write text here...') }}"></div>
                                </template>

                                {{-- LIST BLOCK --}}
                                <template x-if="block.type === 'list'">
                                    <div>
                                        <div class="flex gap-2 mb-2">
                                            <button type="button" @click="block.data.listType = 'bullet'"
                                                    :class="{'bg-slate-200': block.data.listType === 'bullet' || !block.data.listType}"
                                                    class="px-2 py-1 text-xs rounded hover:bg-slate-100">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                                </svg>
                                            </button>
                                            <button type="button" @click="block.data.listType = 'numbered'"
                                                    :class="{'bg-slate-200': block.data.listType === 'numbered'}"
                                                    class="px-2 py-1 text-xs rounded hover:bg-slate-100">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <template x-for="(item, itemIdx) in (block.data.items || [''])" :key="itemIdx">
                                            <div class="flex items-start gap-2 mb-1">
                                                <span class="text-slate-400 mt-2" x-text="block.data.listType === 'numbered' ? (itemIdx + 1) + '.' : '•'"></span>
                                                <input type="text" x-model="block.data.items[itemIdx]"
                                                       @keydown.enter.prevent="addListItem(block, itemIdx)"
                                                       @keydown.backspace="removeListItemIfEmpty(block, itemIdx, $event)"
                                                       placeholder="{{ __('List item...') }}"
                                                       class="flex-1 bg-transparent border-none focus:ring-0 p-1 text-slate-600">
                                            </div>
                                        </template>
                                        <button type="button" @click="addListItem(block)" class="text-xs text-slate-400 hover:text-slate-600 mt-1">
                                            + {{ __('Add item') }}
                                        </button>
                                    </div>
                                </template>

                                {{-- HIGHLIGHT BLOCK --}}
                                <template x-if="block.type === 'highlight'">
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                                        <textarea x-model="block.data.content" rows="2"
                                                  placeholder="{{ __('Important information...') }}"
                                                  class="w-full text-slate-700 font-medium bg-transparent border-none focus:ring-0 resize-none"></textarea>
                                    </div>
                                </template>

                                {{-- NOTE BLOCK --}}
                                <template x-if="block.type === 'note'">
                                    <div class="bg-sky-50 border border-sky-200 p-4 rounded-lg">
                                        <div class="flex gap-3">
                                            <svg class="w-5 h-5 text-sky-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <textarea x-model="block.data.content" rows="2"
                                                      placeholder="{{ __('Add a note...') }}"
                                                      class="flex-1 text-slate-600 text-sm bg-transparent border-none focus:ring-0 resize-none"></textarea>
                                        </div>
                                    </div>
                                </template>

                                </div>{{-- End block content --}}
                            </div>
                        </template>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Left Sidebar --}}
        <div class="w-[420px] bg-white border-r border-slate-200 flex flex-col overflow-hidden order-first">
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
                <button type="button" @click="activeTab = 'services'"
                        :class="activeTab === 'services' ? 'border-b-2 border-slate-900 text-slate-900 bg-white' : 'text-slate-500 hover:text-slate-700'"
                        class="flex-1 px-3 py-3 text-sm font-medium transition-colors">
                    {{ __('Services') }}
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
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">{{ __('Subtotal') }}</span>
                            <span class="font-medium" x-text="formatCurrency(subtotal)"></span>
                        </div>
                        <div x-show="discountAmount > 0" class="flex justify-between text-sm text-red-600">
                            <span>{{ __('Discount') }} (<span x-text="offer.discount_percent"></span>%)</span>
                            <span x-text="'-' + formatCurrency(discountAmount)"></span>
                        </div>
                        <div class="flex justify-between text-lg font-bold pt-2 border-t border-slate-200">
                            <span>{{ __('Total') }}</span>
                            <span x-text="formatCurrency(total)"></span>
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
                    <p class="text-xs text-slate-500 mt-4 font-medium">{{ __('Add new block') }}:</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="addBlock('content')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-green-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Text') }}</span>
                            </div>
                        </button>
                        <button type="button" @click="addBlock('image')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-blue-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Image') }}</span>
                            </div>
                        </button>
                        <button type="button" @click="addBlock('divider')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-slate-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Divider') }}</span>
                            </div>
                        </button>
                        <button type="button" @click="addBlock('terms')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-amber-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Terms') }}</span>
                            </div>
                        </button>
                        <button type="button" @click="addBlock('signature')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-pink-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Signature') }}</span>
                            </div>
                        </button>
                        <button type="button" @click="addBlock('spacer')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Spacer') }}</span>
                            </div>
                        </button>
                        <button type="button" @click="addBlock('table')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-indigo-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Table') }}</span>
                            </div>
                        </button>
                        <button type="button" @click="addBlock('quote')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-violet-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Quote') }}</span>
                            </div>
                        </button>
                        <button type="button" @click="addBlock('columns')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-teal-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Columns') }}</span>
                            </div>
                        </button>
                        <button type="button" @click="addBlock('page_break')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-rose-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6M4 12h16M4 7h16"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Page Break') }}</span>
                            </div>
                        </button>
                        <button type="button" @click="addBlock('paragraph')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-emerald-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Paragraph') }}</span>
                            </div>
                        </button>
                        <button type="button" @click="addBlock('list')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-orange-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('List') }}</span>
                            </div>
                        </button>
                        <button type="button" @click="addBlock('highlight')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-yellow-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Highlight') }}</span>
                            </div>
                        </button>
                        <button type="button" @click="addBlock('note')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-sky-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Note') }}</span>
                            </div>
                        </button>
                    </div>

                    {{-- Block Style Customization Panel --}}
                    <template x-if="selectedBlockId && getSelectedBlock()">
                        <div class="mt-4 pt-4 border-t border-slate-200">
                            <p class="text-xs text-slate-500 font-medium mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                                {{ __('Block Styling') }}
                            </p>

                            {{-- Top Spacing --}}
                            <div class="mb-3">
                                <label class="block text-xs text-slate-600 mb-1">{{ __('Top Spacing') }}</label>
                                <div class="flex gap-1">
                                    <template x-for="spacing in ['none', 'sm', 'md', 'lg', 'xl']" :key="'mt-'+spacing">
                                        <button type="button"
                                                @click="setBlockStyle('marginTop', spacing)"
                                                :class="getBlockStyle('marginTop') === spacing ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                                class="flex-1 px-2 py-1 text-xs rounded transition-colors"
                                                x-text="spacing === 'none' ? '0' : spacing.toUpperCase()">
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Bottom Spacing --}}
                            <div class="mb-3">
                                <label class="block text-xs text-slate-600 mb-1">{{ __('Bottom Spacing') }}</label>
                                <div class="flex gap-1">
                                    <template x-for="spacing in ['none', 'sm', 'md', 'lg', 'xl']" :key="'mb-'+spacing">
                                        <button type="button"
                                                @click="setBlockStyle('marginBottom', spacing)"
                                                :class="getBlockStyle('marginBottom') === spacing ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                                class="flex-1 px-2 py-1 text-xs rounded transition-colors"
                                                x-text="spacing === 'none' ? '0' : spacing.toUpperCase()">
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Background Color --}}
                            <div class="mb-3">
                                <label class="block text-xs text-slate-600 mb-1">{{ __('Background') }}</label>
                                <div class="flex gap-1">
                                    <button type="button" @click="setBlockStyle('background', 'white')"
                                            :class="getBlockStyle('background') === 'white' || !getBlockStyle('background') ? 'ring-2 ring-slate-900' : ''"
                                            class="w-6 h-6 rounded border border-slate-200 bg-white" title="{{ __('White') }}"></button>
                                    <button type="button" @click="setBlockStyle('background', 'slate-50')"
                                            :class="getBlockStyle('background') === 'slate-50' ? 'ring-2 ring-slate-900' : ''"
                                            class="w-6 h-6 rounded border border-slate-200 bg-slate-50" title="{{ __('Light Gray') }}"></button>
                                    <button type="button" @click="setBlockStyle('background', 'blue-50')"
                                            :class="getBlockStyle('background') === 'blue-50' ? 'ring-2 ring-slate-900' : ''"
                                            class="w-6 h-6 rounded border border-slate-200 bg-blue-50" title="{{ __('Light Blue') }}"></button>
                                    <button type="button" @click="setBlockStyle('background', 'green-50')"
                                            :class="getBlockStyle('background') === 'green-50' ? 'ring-2 ring-slate-900' : ''"
                                            class="w-6 h-6 rounded border border-slate-200 bg-green-50" title="{{ __('Light Green') }}"></button>
                                    <button type="button" @click="setBlockStyle('background', 'amber-50')"
                                            :class="getBlockStyle('background') === 'amber-50' ? 'ring-2 ring-slate-900' : ''"
                                            class="w-6 h-6 rounded border border-slate-200 bg-amber-50" title="{{ __('Light Amber') }}"></button>
                                </div>
                            </div>

                            {{-- Text Alignment --}}
                            <div class="mb-3">
                                <label class="block text-xs text-slate-600 mb-1">{{ __('Text Align') }}</label>
                                <div class="flex gap-1">
                                    <button type="button" @click="setBlockStyle('textAlign', 'left')"
                                            :class="getBlockStyle('textAlign') === 'left' || !getBlockStyle('textAlign') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                            class="flex-1 p-1.5 rounded transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h14M3 12h10M3 18h14"/>
                                        </svg>
                                    </button>
                                    <button type="button" @click="setBlockStyle('textAlign', 'center')"
                                            :class="getBlockStyle('textAlign') === 'center' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                            class="flex-1 p-1.5 rounded transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M7 12h10M3 18h18"/>
                                        </svg>
                                    </button>
                                    <button type="button" @click="setBlockStyle('textAlign', 'right')"
                                            :class="getBlockStyle('textAlign') === 'right' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                            class="flex-1 p-1.5 rounded transition-colors flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M10 12h11M3 18h18"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Border Style --}}
                            <div class="mb-3">
                                <label class="block text-xs text-slate-600 mb-1">{{ __('Border') }}</label>
                                <div class="flex gap-1">
                                    <button type="button" @click="setBlockStyle('border', 'none')"
                                            :class="getBlockStyle('border') === 'none' || !getBlockStyle('border') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                            class="flex-1 px-2 py-1 text-xs rounded transition-colors">
                                        {{ __('None') }}
                                    </button>
                                    <button type="button" @click="setBlockStyle('border', 'subtle')"
                                            :class="getBlockStyle('border') === 'subtle' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                            class="flex-1 px-2 py-1 text-xs rounded transition-colors">
                                        {{ __('Subtle') }}
                                    </button>
                                    <button type="button" @click="setBlockStyle('border', 'prominent')"
                                            :class="getBlockStyle('border') === 'prominent' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                            class="flex-1 px-2 py-1 text-xs rounded transition-colors">
                                        {{ __('Bold') }}
                                    </button>
                                </div>
                            </div>

                            {{-- Padding --}}
                            <div class="mb-3">
                                <label class="block text-xs text-slate-600 mb-1">{{ __('Padding') }}</label>
                                <div class="flex gap-1">
                                    <template x-for="padding in ['none', 'sm', 'md', 'lg']" :key="'p-'+padding">
                                        <button type="button"
                                                @click="setBlockStyle('padding', padding)"
                                                :class="getBlockStyle('padding') === padding ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                                class="flex-1 px-2 py-1 text-xs rounded transition-colors"
                                                x-text="padding === 'none' ? '0' : padding.toUpperCase()">
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Header Block Specific: Intro Text --}}
                            <template x-if="getSelectedBlock()?.type === 'header'">
                                <div class="mt-4 pt-4 border-t border-slate-200 space-y-3">
                                    <p class="text-xs text-slate-500 font-medium flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                                        </svg>
                                        {{ __('Header Content') }}
                                    </p>
                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1">{{ __('Intro Title') }}</label>
                                        <input type="text" x-model="getSelectedBlock().data.introTitle"
                                               placeholder="{{ __('Your business partner for digital solutions.') }}"
                                               class="w-full text-sm border border-slate-200 rounded px-2 py-1.5 focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1">{{ __('Intro Text') }}</label>
                                        <textarea x-model="getSelectedBlock().data.introText"
                                                  placeholder="{{ __('We deliver high-quality services tailored to your specific needs...') }}"
                                                  rows="3"
                                                  class="w-full text-sm border border-slate-200 rounded px-2 py-1.5 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 resize-none"></textarea>
                                    </div>
                                </div>
                            </template>

                            {{-- Reset Button --}}
                            <button type="button" @click="resetBlockStyle()"
                                    class="w-full mt-2 text-xs text-slate-500 hover:text-slate-700 py-1">
                                <span class="flex items-center justify-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    {{ __('Reset Styles') }}
                                </span>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Services Tab --}}
                <div x-show="activeTab === 'services'" class="space-y-3">
                    <p class="text-sm text-slate-500 mb-4">{{ __('Add services from catalog or create custom items') }}</p>

                    {{-- Search --}}
                    <div class="relative mb-4">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" x-model="serviceSearch" placeholder="{{ __('Search services...') }}"
                               class="w-full h-9 pl-10 pr-4 text-sm border border-slate-200 rounded-md focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
                    </div>

                    {{-- Service Catalog --}}
                    <div class="space-y-2">
                        @foreach($services as $service)
                            <div x-show="!serviceSearch || '{{ strtolower($service->name) }}'.includes(serviceSearch.toLowerCase())"
                                 @click="addServiceFromCatalog({{ $service->id }}, '{{ addslashes($service->name) }}', '{{ addslashes($service->description ?? '') }}', {{ $service->default_rate ?? 0 }}, '{{ $service->currency ?? 'RON' }}', '{{ $service->unit ?? 'ora' }}')"
                                 class="p-3 border border-slate-200 rounded-lg cursor-pointer hover:border-slate-400 hover:bg-slate-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-sm text-slate-900">{{ $service->name }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ number_format($service->default_rate, 2, ',', '.') }}
                                            <span class="{{ ($service->currency ?? 'RON') !== ($defaultCurrency ?? 'RON') ? 'text-amber-600 font-medium' : '' }}">{{ $service->currency ?? 'RON' }}</span>/{{ __($service->unit ?? 'hour') }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if(($service->currency ?? 'RON') !== ($defaultCurrency ?? 'RON'))
                                            <span class="text-xs px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded">
                                                {{ $service->currency }}
                                            </span>
                                        @endif
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if($services->isEmpty())
                            <div class="text-center py-8 text-slate-500">
                                <p>{{ __('No services defined yet.') }}</p>
                                <a href="{{ route('settings.services') }}" class="text-blue-600 hover:underline text-sm">{{ __('Add services in settings') }}</a>
                            </div>
                        @endif

                        {{-- Add Custom Item --}}
                        <div @click="addCustomItem()" class="p-3 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-center gap-2 text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span class="text-sm font-medium">{{ __('Add Custom Item') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="p-4 border-t border-slate-200 bg-slate-100 space-y-2">
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
                <button type="button" onclick="window.history.back()" class="w-full h-10 border border-slate-300 rounded-md font-medium hover:bg-slate-100">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- SortableJS for drag-and-drop --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        .sortable-ghost { opacity: 0.4; background-color: rgb(191 219 254); }
        .sortable-chosen { box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); }
        .sortable-drag { opacity: 1; }
        .block-drag-handle { cursor: grab; }
        .block-drag-handle:active { cursor: grabbing; }
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

        return {
            activeTab: 'settings',
            serviceSearch: '',
            isSaving: false,
            selectedBlockId: null,
            exchangeRates: @json($exchangeRates ?? []),
            templates: templates,
            selectedTemplateId: existingOffer ? '' : (defaultTemplate?.id || ''),

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
            blocks: existingOffer?.blocks || (defaultTemplate?.content ? JSON.parse(defaultTemplate.content) : [
                { id: 'header_1', type: 'header', visible: true, data: { introTitle: '', introText: '' } },
                { id: 'services_1', type: 'services', visible: true, data: {} },
                { id: 'summary_1', type: 'summary', visible: true, data: {} },
                { id: 'terms_1', type: 'terms', visible: true, data: { content: defaultTerms } },
                { id: 'signature_1', type: 'signature', visible: true, data: {} },
            ]),

            // Service Items
            items: existingOffer?.items?.map((item, idx) => ({
                _key: Date.now() + idx,
                id: item.id,
                service_id: item.service_id,
                title: item.title,
                description: item.description || '',
                quantity: parseFloat(item.quantity) || 1,
                unit: item.unit || 'ora',
                unit_price: parseFloat(item.unit_price) || 0,
                currency: item.currency || defaultCurrency,
                total: parseFloat(item.total_price) || 0,
            })) || [],

            hoveredBlockId: null,
            sortableInstance: null,

            init() {
                if (this.offer.client_id && this.offer.client_id !== 'new') {
                    this.$nextTick(() => this.onClientChange());
                }

                // Initialize SortableJS for blocks
                this.$nextTick(() => {
                    this.initBlockSorting();
                });
            },

            initBlockSorting() {
                const blocksContainer = this.$refs.blocksContainer;
                if (blocksContainer && typeof Sortable !== 'undefined') {
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
                }
            },

            get subtotal() {
                return this.items.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
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
                    'header': { bg: 'bg-emerald-100', icon: '<svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>' },
                    'services': { bg: 'bg-purple-100', icon: '<svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>' },
                    'summary': { bg: 'bg-cyan-100', icon: '<svg class="w-3 h-3 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>' },
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
                let blockData = { title: '', content: '' };

                // Initialize block-specific data structures
                if (type === 'table') {
                    blockData = {
                        title: '',
                        columns: ['{{ __('Column 1') }}', '{{ __('Column 2') }}', '{{ __('Column 3') }}'],
                        rows: [['', '', '']]
                    };
                } else if (type === 'quote') {
                    blockData = { content: '', author: '' };
                } else if (type === 'columns') {
                    blockData = { leftTitle: '', leftContent: '', rightTitle: '', rightContent: '' };
                } else if (type === 'list') {
                    blockData = { listType: 'bullet', items: [''] };
                } else if (type === 'paragraph' || type === 'highlight' || type === 'note') {
                    blockData = { content: '' };
                }

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
                    total: rate
                });
            },

            addCustomItem() {
                this.items.push({ _key: Date.now(), service_id: null, title: '', description: '', quantity: 1, unit: 'ora', unit_price: 0, currency: this.offer.currency || 'RON', total: 0 });
            },

            removeItem(index) { this.items.splice(index, 1); },
            calculateItemTotal(index) { const item = this.items[index]; item.total = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0); },

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
                    items: this.items.map(item => ({
                        id: item.id || null,
                        service_id: item.service_id,
                        title: item.title,
                        description: item.description,
                        quantity: item.quantity,
                        unit: item.unit,
                        unit_price: item.unit_price,
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
        };
    }
    </script>
    @endpush
</x-app-layout>
