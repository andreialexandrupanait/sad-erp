<x-app-layout>
    <x-slot name="pageTitle">{{ __('Edit Template') }} - {{ $template->name }}</x-slot>

    <div class="h-[calc(100vh-4rem)] flex" x-data="templateBuilder()">
        {{-- Success Toast Notification --}}
        <div x-show="showSaveSuccess"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="fixed top-20 right-6 z-50 flex items-center gap-3 px-4 py-3 bg-green-600 text-white rounded-lg shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="font-medium">{{ __('Template saved successfully') }}</span>
        </div>

        {{-- Main Canvas: Template Preview --}}
        <div class="flex-1 bg-slate-100 overflow-y-auto p-8">
            <div class="max-w-3xl mx-auto">
                {{-- Document Container --}}
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
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

                                {{-- Block hover toolbar --}}
                                <div class="absolute -top-3 right-4 opacity-0 group-hover:opacity-100 transition-all duration-200 z-20">
                                    <div class="flex items-center gap-0.5 bg-slate-800 rounded-lg shadow-lg px-1 py-1">
                                        <div class="block-drag-handle w-7 h-7 rounded hover:bg-slate-700 flex items-center justify-center cursor-grab">
                                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                            </svg>
                                        </div>
                                        <button type="button" @click="moveBlock(blockIndex, -1)" :disabled="blockIndex === 0" class="w-7 h-7 rounded hover:bg-slate-700 flex items-center justify-center disabled:opacity-30">
                                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                        </button>
                                        <button type="button" @click="moveBlock(blockIndex, 1)" :disabled="blockIndex === blocks.length - 1" class="w-7 h-7 rounded hover:bg-slate-700 flex items-center justify-center disabled:opacity-30">
                                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <button type="button" @click="selectedBlockId = block.id" class="w-7 h-7 rounded hover:bg-slate-700 flex items-center justify-center" :class="selectedBlockId === block.id ? 'bg-blue-600' : ''">
                                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </button>
                                        <button type="button" @click="duplicateBlock(blockIndex)" class="w-7 h-7 rounded hover:bg-slate-700 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                        <button type="button" @click="removeBlock(blockIndex)" x-show="!['header', 'services', 'summary'].includes(block.type)" class="w-7 h-7 rounded hover:bg-red-600 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Block content --}}
                                <div class="transition-colors" :class="getBlockStyleClasses(block)">

                                {{-- HEADER BLOCK --}}
                                <template x-if="block.type === 'header'">
                                    <div>
                                        <div class="bg-slate-800 text-white px-8 py-6">
                                            <div class="flex justify-between items-start">
                                                <div class="space-y-1">
                                                    <p class="text-sm text-slate-300">{{ __('Date') }}: <span class="text-white">DD.MM.YYYY</span></p>
                                                    <p class="text-lg font-medium">{{ __('Service proposal for') }}: <span class="text-slate-300">{{ __('Client Name') }}</span></p>
                                                    <p class="text-sm text-slate-300">{{ __('Valid until') }}: <span class="text-white">DD.MM.YYYY</span></p>
                                                </div>
                                                <div class="flex items-center">
                                                    @if($organization->logo ?? false)
                                                        <img src="{{ Storage::url($organization->logo) }}" alt="{{ $organization->name }}" class="h-14 w-auto object-contain brightness-0 invert">
                                                    @else
                                                        <span class="text-2xl font-bold">{{ $organization->name ?? config('app.name') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-slate-700 text-white px-8 py-6">
                                            <div class="flex gap-8">
                                                <div class="flex-1">
                                                    <h3 class="text-lg font-semibold mb-2" x-text="block.data.introTitle || '{{ __('Your business partner for digital solutions.') }}'"></h3>
                                                    <p class="text-sm text-slate-300 leading-relaxed" x-text="block.data.introText || '{{ __('We deliver high-quality services tailored to your specific needs.') }}'"></p>
                                                </div>
                                                <div class="w-72 text-sm space-y-1">
                                                    <p><span class="text-slate-400">{{ __('Email') }}:</span> {{ $organization->email ?? 'email@company.com' }}</p>
                                                    <p><span class="text-slate-400">{{ __('Phone') }}:</span> {{ $organization->phone ?? '0700 000 000' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="px-8 py-4 bg-white border-b border-slate-200">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">{{ __('Proposal for') }}</p>
                                                    <p class="font-semibold text-slate-900">{{ __('Client Company Name') }}</p>
                                                </div>
                                                <p class="text-2xl font-bold text-slate-900">OFR-YYYY-XXX</p>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- SERVICES BLOCK --}}
                                <template x-if="block.type === 'services'">
                                    <div>
                                        <h3 class="text-xl font-semibold text-slate-900 mb-4">{{ __('Proposed Services') }}</h3>
                                        <div class="space-y-3">
                                            <template x-for="(item, index) in templateServices" :key="item._key">
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
                                                        <input type="number" x-model="item.quantity" @input="calculateServiceTotal(index)"
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
                                                        <input type="number" x-model="item.unit_price" @input="calculateServiceTotal(index)"
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
                                                        <div class="w-32 text-right font-bold text-slate-900" x-text="formatServiceCurrency(item.total || (item.quantity * item.unit_price), item.currency)"></div>

                                                        {{-- Delete button --}}
                                                        <button type="button" @click="removeTemplateService(index)"
                                                                class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            <div x-show="templateServices.length === 0" class="py-12 text-center border-2 border-dashed border-slate-200 rounded-lg">
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
                                    <div class="pt-4 border-t border-slate-200">
                                        <div class="flex justify-end">
                                            <div class="w-64 space-y-2">
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-slate-600">{{ __('Subtotal') }}</span>
                                                    <span class="text-slate-900" x-text="formatCurrency(templateSubtotal)"></span>
                                                </div>
                                                <div class="flex justify-between text-lg font-bold border-t border-slate-200 pt-2">
                                                    <span>{{ __('Total') }}</span>
                                                    <span class="text-slate-900" x-text="formatCurrency(templateSubtotal)"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- TEXT BLOCK --}}
                                <template x-if="block.type === 'text'">
                                    <div class="py-4">
                                        <template x-if="block.data.title">
                                            <h3 class="text-lg font-semibold text-slate-900 mb-3" x-text="block.data.title"></h3>
                                        </template>
                                        <div class="text-slate-700 whitespace-pre-wrap" x-text="block.data.content || '{{ __('Write your content here...') }}'"></div>
                                    </div>
                                </template>

                                {{-- TERMS BLOCK --}}
                                <template x-if="block.type === 'terms'">
                                    <div class="py-4 border-t border-slate-200">
                                        <h3 class="text-lg font-semibold text-slate-900 mb-3">{{ __('Terms and Conditions') }}</h3>
                                        <textarea x-model="block.data.content" rows="6"
                                                  placeholder="{{ __('Add terms and conditions...') }}"
                                                  class="w-full text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded-lg p-3 focus:border-slate-400 resize-none"></textarea>
                                    </div>
                                </template>

                                {{-- SIGNATURE BLOCK --}}
                                <template x-if="block.type === 'signature'">
                                    <div class="py-6 border-t border-slate-200">
                                        <p class="text-sm text-slate-500 mb-6">{{ __('By signing below, I confirm that I have read and agree to the services and conditions described in this offer.') }}</p>
                                        <div class="grid grid-cols-2 gap-8">
                                            <div>
                                                <p class="text-sm font-medium text-slate-700 mb-2">{{ __('Client Signature') }}</p>
                                                <div class="h-24 border-b-2 border-slate-300"></div>
                                                <p class="text-xs text-slate-500 mt-2">{{ __('Date') }}: ____________</p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-700 mb-2">{{ __('Provider Signature') }}</p>
                                                <div class="h-24 border-b-2 border-slate-300"></div>
                                                <p class="text-xs text-slate-500 mt-2">{{ __('Date') }}: ____________</p>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- DIVIDER BLOCK --}}
                                <template x-if="block.type === 'divider'">
                                    <div class="py-4">
                                        <hr class="border-slate-300">
                                    </div>
                                </template>

                                {{-- SPACER BLOCK --}}
                                <template x-if="block.type === 'spacer'">
                                    <div :style="'height: ' + (block.data.height || 40) + 'px'"></div>
                                </template>

                                {{-- HEADING BLOCK --}}
                                <template x-if="block.type === 'heading'">
                                    <div class="py-2">
                                        <input type="text" x-model="block.data.content"
                                               :class="{
                                                   'text-3xl': block.data.level === 'h1',
                                                   'text-2xl': block.data.level === 'h2',
                                                   'text-xl': block.data.level === 'h3' || !block.data.level
                                               }"
                                               class="w-full font-bold text-slate-900 bg-transparent border-none focus:ring-0 p-0"
                                               placeholder="{{ __('Enter heading...') }}">
                                    </div>
                                </template>

                                {{-- CONTENT/TEXT BLOCK (with optional title) --}}
                                <template x-if="block.type === 'content'">
                                    <div class="py-4">
                                        <input type="text" x-model="block.data.title" placeholder="{{ __('Section Title (optional)') }}"
                                               class="w-full text-lg font-semibold text-blue-600 bg-transparent border-none focus:ring-0 p-0 mb-2">
                                        <textarea x-model="block.data.content" rows="3"
                                                  placeholder="{{ __('Write your content here...') }}"
                                                  class="w-full text-slate-700 bg-slate-50 border border-slate-200 rounded-lg p-3 focus:border-slate-400 resize-none"></textarea>
                                    </div>
                                </template>

                                {{-- PARAGRAPH BLOCK (simple text without title) --}}
                                <template x-if="block.type === 'paragraph'">
                                    <div class="py-2">
                                        <textarea x-model="block.data.content" rows="2"
                                                  placeholder="{{ __('Write text here...') }}"
                                                  class="w-full text-slate-600 bg-transparent border border-slate-200 rounded-lg p-2 focus:border-slate-400 resize-none"></textarea>
                                    </div>
                                </template>

                                {{-- LIST BLOCK --}}
                                <template x-if="block.type === 'list'">
                                    <div class="py-2">
                                        <div class="flex items-center gap-2 mb-2">
                                            <button type="button" @click="block.data.listType = 'bullet'"
                                                    :class="block.data.listType !== 'numbered' ? 'bg-slate-200' : 'bg-slate-100'"
                                                    class="px-2 py-1 text-xs rounded">{{ __('Bullets') }}</button>
                                            <button type="button" @click="block.data.listType = 'numbered'"
                                                    :class="block.data.listType === 'numbered' ? 'bg-slate-200' : 'bg-slate-100'"
                                                    class="px-2 py-1 text-xs rounded">{{ __('Numbered') }}</button>
                                        </div>
                                        <div class="space-y-1">
                                            <template x-for="(item, idx) in block.data.items" :key="idx">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-slate-400 w-4 text-center" x-text="block.data.listType === 'numbered' ? (idx + 1) + '.' : '•'"></span>
                                                    <input type="text" x-model="block.data.items[idx]"
                                                           @keydown.enter.prevent="block.data.items.splice(idx + 1, 0, ''); $nextTick(() => $event.target.parentElement.nextElementSibling?.querySelector('input')?.focus())"
                                                           @keydown.backspace="if (!block.data.items[idx] && block.data.items.length > 1) { block.data.items.splice(idx, 1); $event.preventDefault(); }"
                                                           placeholder="{{ __('List item...') }}"
                                                           class="flex-1 text-slate-600 bg-transparent border-none focus:ring-0 p-0">
                                                    <button type="button" @click="block.data.items.splice(idx, 1)" x-show="block.data.items.length > 1"
                                                            class="text-slate-400 hover:text-red-500">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                        <button type="button" @click="block.data.items.push('')"
                                                class="mt-2 text-sm text-slate-500 hover:text-slate-700">
                                            + {{ __('Add item') }}
                                        </button>
                                    </div>
                                </template>

                                {{-- HIGHLIGHT BLOCK --}}
                                <template x-if="block.type === 'highlight'">
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                                        <textarea x-model="block.data.content" rows="2"
                                                  placeholder="{{ __('Important information...') }}"
                                                  class="w-full text-slate-700 font-medium bg-transparent border-none focus:ring-0 p-0 resize-none"></textarea>
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
                                                      class="flex-1 text-slate-600 text-sm bg-transparent border-none focus:ring-0 p-0 resize-none"></textarea>
                                        </div>
                                    </div>
                                </template>

                                {{-- IMAGE BLOCK --}}
                                <template x-if="block.type === 'image'">
                                    <div class="text-center">
                                        <template x-if="block.data.src">
                                            <div class="relative group">
                                                <img :src="block.data.src" :alt="block.data.alt || ''" class="max-w-full h-auto rounded-lg mx-auto">
                                                <label class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded-lg">
                                                    <span class="text-white text-sm font-medium">{{ __('Change image') }}</span>
                                                    <input type="file" accept="image/*" class="hidden" @change="handleBlockImageUpload($event, block)">
                                                </label>
                                            </div>
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

                                {{-- QUOTE BLOCK --}}
                                <template x-if="block.type === 'quote'">
                                    <div class="py-4 border-l-4 border-slate-300 pl-4">
                                        <textarea x-model="block.data.content" rows="2"
                                                  placeholder="{{ __('Enter quote text...') }}"
                                                  class="w-full text-slate-600 italic bg-transparent border-none focus:ring-0 p-0 resize-none"></textarea>
                                        <input type="text" x-model="block.data.author"
                                               placeholder="{{ __('Author (optional)') }}"
                                               class="text-sm text-slate-500 mt-2 bg-transparent border-none focus:ring-0 p-0 w-full">
                                    </div>
                                </template>

                                {{-- TABLE BLOCK --}}
                                <template x-if="block.type === 'table'">
                                    <div class="py-4">
                                        <input type="text" x-model="block.data.title"
                                               placeholder="{{ __('Table title (optional)') }}"
                                               class="w-full text-lg font-semibold text-slate-900 mb-3 bg-transparent border-none focus:ring-0 p-0">
                                        <table class="w-full border border-slate-200">
                                            <thead class="bg-slate-50">
                                                <tr>
                                                    <template x-for="(col, colIdx) in (block.data.columns || [])" :key="'col-'+colIdx">
                                                        <th class="px-3 py-2 text-left text-sm font-medium text-slate-700 border-b border-slate-200">
                                                            <input type="text" x-model="block.data.columns[colIdx]"
                                                                   class="w-full bg-transparent border-none focus:ring-0 p-0 font-medium">
                                                        </th>
                                                    </template>
                                                    <th class="w-8 border-b border-slate-200">
                                                        <button type="button" @click="block.data.columns.push(''); block.data.rows.forEach(r => r.push(''))"
                                                                class="text-slate-400 hover:text-green-600">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                        </button>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="(row, rowIdx) in (block.data.rows || [])" :key="'row-'+rowIdx">
                                                    <tr>
                                                        <template x-for="(cell, cellIdx) in row" :key="'cell-'+rowIdx+'-'+cellIdx">
                                                            <td class="px-3 py-2 text-sm text-slate-600 border-b border-slate-200">
                                                                <input type="text" x-model="block.data.rows[rowIdx][cellIdx]"
                                                                       class="w-full bg-transparent border-none focus:ring-0 p-0">
                                                            </td>
                                                        </template>
                                                        <td class="w-8 border-b border-slate-200">
                                                            <button type="button" @click="block.data.rows.splice(rowIdx, 1)"
                                                                    class="text-slate-400 hover:text-red-600">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                </svg>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                        <button type="button" @click="block.data.rows.push(Array(block.data.columns.length).fill(''))"
                                                class="mt-2 text-sm text-slate-500 hover:text-slate-700">
                                            + {{ __('Add row') }}
                                        </button>
                                    </div>
                                </template>

                                {{-- COLUMNS BLOCK --}}
                                <template x-if="block.type === 'columns'">
                                    <div class="py-4 grid grid-cols-2 gap-8">
                                        <div class="space-y-2">
                                            <input type="text" x-model="block.data.leftTitle"
                                                   placeholder="{{ __('Left title (optional)') }}"
                                                   class="w-full font-semibold text-slate-900 bg-transparent border-none focus:ring-0 p-0">
                                            <textarea x-model="block.data.leftContent" rows="3"
                                                      placeholder="{{ __('Left column content...') }}"
                                                      class="w-full text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded p-2 resize-none"></textarea>
                                        </div>
                                        <div class="space-y-2">
                                            <input type="text" x-model="block.data.rightTitle"
                                                   placeholder="{{ __('Right title (optional)') }}"
                                                   class="w-full font-semibold text-slate-900 bg-transparent border-none focus:ring-0 p-0">
                                            <textarea x-model="block.data.rightContent" rows="3"
                                                      placeholder="{{ __('Right column content...') }}"
                                                      class="w-full text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded p-2 resize-none"></textarea>
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

                                </div>
                            </div>
                        </template>
                        </div>

                        {{-- Add Block Button --}}
                        <div class="p-8 border-t border-dashed border-slate-200">
                            <button type="button" @click="showAddBlockMenu = !showAddBlockMenu" class="w-full py-3 border-2 border-dashed border-slate-300 rounded-lg text-slate-500 hover:border-slate-400 hover:text-slate-600 transition-colors">
                                + {{ __('Add new block') }}
                            </button>
                            <div x-show="showAddBlockMenu" x-transition class="mt-4 grid grid-cols-4 gap-2">
                                <template x-for="blockType in availableBlocks" :key="blockType.type">
                                    <button type="button" @click="addBlock(blockType.type); showAddBlockMenu = false"
                                            class="p-3 text-center border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                        <span class="text-sm text-slate-700" x-text="blockType.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Left Sidebar --}}
        <div class="w-[420px] bg-white border-r border-slate-200 flex flex-col overflow-hidden order-first">
            {{-- Tab Navigation --}}
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
                    <div class="field-wrapper">
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Template Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" x-model="templateName" class="w-full h-10 border border-slate-300 rounded-md px-3 text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                    </div>

                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="isDefault" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                            <span class="text-sm text-slate-700">{{ __('Default template') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="isActive" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                            <span class="text-sm text-slate-700">{{ __('Active') }}</span>
                        </label>
                    </div>

                    {{-- Block Settings (when a block is selected) --}}
                    <template x-if="selectedBlockId && getSelectedBlock()">
                        <div class="mt-6 p-4 bg-slate-50 rounded-lg space-y-4">
                            <h3 class="font-medium text-slate-900">{{ __('Block settings') }}</h3>

                            {{-- Header block settings --}}
                            <template x-if="getSelectedBlock()?.type === 'header'">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1">{{ __('Intro Title') }}</label>
                                        <input type="text" x-model="getSelectedBlock().data.introTitle" class="w-full text-sm border border-slate-200 rounded px-2 py-1.5">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1">{{ __('Intro Text') }}</label>
                                        <textarea x-model="getSelectedBlock().data.introText" rows="3" class="w-full text-sm border border-slate-200 rounded px-2 py-1.5 resize-none"></textarea>
                                    </div>
                                </div>
                            </template>

                            {{-- Text block settings --}}
                            <template x-if="getSelectedBlock()?.type === 'text'">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1">{{ __('Title') }} ({{ __('optional') }})</label>
                                        <input type="text" x-model="getSelectedBlock().data.title" class="w-full text-sm border border-slate-200 rounded px-2 py-1.5">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1">{{ __('Content') }}</label>
                                        <textarea x-model="getSelectedBlock().data.content" rows="5" class="w-full text-sm border border-slate-200 rounded px-2 py-1.5 resize-none"></textarea>
                                    </div>
                                </div>
                            </template>

                            {{-- Heading block settings --}}
                            <template x-if="getSelectedBlock()?.type === 'heading'">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1">{{ __('Heading Level') }}</label>
                                        <select x-model="getSelectedBlock().data.level" class="w-full text-sm border border-slate-200 rounded px-2 py-1.5">
                                            <option value="h1">{{ __('H1 - Large') }}</option>
                                            <option value="h2">{{ __('H2 - Medium') }}</option>
                                            <option value="h3">{{ __('H3 - Small') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1">{{ __('Heading Text') }}</label>
                                        <input type="text" x-model="getSelectedBlock().data.content" class="w-full text-sm border border-slate-200 rounded px-2 py-1.5">
                                    </div>
                                </div>
                            </template>

                            {{-- Terms block settings --}}
                            <template x-if="getSelectedBlock()?.type === 'terms'">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1">{{ __('Terms and Conditions') }}</label>
                                        <textarea x-model="getSelectedBlock().data.content" rows="8" class="w-full text-sm border border-slate-200 rounded px-2 py-1.5 resize-none"></textarea>
                                    </div>
                                </div>
                            </template>

                            {{-- Spacer block settings --}}
                            <template x-if="getSelectedBlock()?.type === 'spacer'">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs text-slate-600 mb-1">{{ __('Height') }} (px)</label>
                                        <input type="number" x-model="getSelectedBlock().data.height" min="10" max="200" class="w-full text-sm border border-slate-200 rounded px-2 py-1.5">
                                    </div>
                                </div>
                            </template>

                            <button type="button" @click="selectedBlockId = null" class="text-xs text-slate-500 hover:text-slate-700">
                                {{ __('Close') }}
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
                                 @click="addServiceFromCatalog({{ $service->id }}, '{{ addslashes($service->name) }}', '{{ addslashes($service->description ?? '') }}', {{ $service->default_rate ?? $service->price ?? 0 }}, '{{ $service->currency ?? 'RON' }}', '{{ $service->unit ?? 'ora' }}')"
                                 class="p-3 border border-slate-200 rounded-lg cursor-pointer hover:border-slate-400 hover:bg-slate-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-sm text-slate-900">{{ $service->name }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ number_format($service->default_rate ?? $service->price ?? 0, 2, ',', '.') }} {{ $service->currency ?? 'RON' }}/{{ $service->unit ?? 'ora' }}
                                        </p>
                                    </div>
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
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
                        <div @click="addCustomService()" class="p-3 border-2 border-dashed border-slate-300 rounded-lg cursor-pointer hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-center gap-2 text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span class="text-sm font-medium">{{ __('Add Custom Item') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Blocks Tab --}}
                <div x-show="activeTab === 'blocks'" class="space-y-3">
                    <p class="text-sm text-slate-500 mb-4">{{ __('Add and arrange template sections') }}</p>

                    {{-- Active Blocks List --}}
                    <div class="space-y-2 mb-4">
                        <template x-for="(block, index) in blocks" :key="block.id">
                            <div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg border border-slate-200"
                                 :class="selectedBlockId === block.id ? 'ring-2 ring-blue-500' : ''"
                                 @click="selectedBlockId = block.id">
                                <div class="flex flex-col gap-0.5">
                                    <button type="button" @click.stop="moveBlock(index, -1)" :disabled="index === 0"
                                            class="p-0.5 hover:bg-slate-200 rounded disabled:opacity-30">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    </button>
                                    <button type="button" @click.stop="moveBlock(index, 1)" :disabled="index === blocks.length - 1"
                                            class="p-0.5 hover:bg-slate-200 rounded disabled:opacity-30">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="w-6 h-6 rounded flex items-center justify-center" :class="getBlockIconBg(block.type)">
                                    <span x-html="getBlockIconSvg(block.type)" class="w-3 h-3"></span>
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
                        <button type="button" @click="addBlock('heading')"
                                class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-purple-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium">{{ __('Heading') }}</span>
                            </div>
                        </button>
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
            </div>

            {{-- Footer Actions --}}
            <div class="p-4 border-t border-slate-200 space-y-2">
                <button type="button" @click="saveTemplate()" :disabled="isSaving" class="w-full h-10 bg-slate-900 text-white rounded-md hover:bg-slate-800 disabled:opacity-50 transition-colors">
                    <span x-show="!isSaving">{{ __('Save Template') }}</span>
                    <span x-show="isSaving">{{ __('Saving...') }}</span>
                </button>
                <a href="{{ route('settings.document-templates.index') }}" class="block w-full h-10 leading-10 text-center border border-slate-300 text-slate-700 rounded-md hover:bg-slate-50 transition-colors">
                    {{ __('Cancel') }}
                </a>
            </div>
        </div>
    </div>

    <style>
        .sortable-ghost { opacity: 0.4; }
        .block-drag-handle { cursor: grab; }
        .block-drag-handle:active { cursor: grabbing; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
    function templateBuilder() {
        const existingBlocks = @json($existingBlocks ?? []);
        const existingServices = @json($templateServices ?? []);

        return {
            activeTab: 'settings',
            serviceSearch: '',
            isSaving: false,
            showSaveSuccess: false,
            selectedBlockId: null,
            hoveredBlockId: null,
            showAddBlockMenu: false,

            templateName: '{{ $template->name }}',
            isDefault: {{ $template->is_default ? 'true' : 'false' }},
            isActive: {{ $template->is_active ? 'true' : 'false' }},

            blocks: existingBlocks.length > 0 ? existingBlocks : [
                { id: 'header_1', type: 'header', visible: true, data: { introTitle: '', introText: '' } },
                { id: 'services_1', type: 'services', visible: true, data: {} },
                { id: 'summary_1', type: 'summary', visible: true, data: {} },
                { id: 'terms_1', type: 'terms', visible: true, data: { content: '' } },
                { id: 'signature_1', type: 'signature', visible: true, data: {} },
            ],

            templateServices: existingServices.map((s, idx) => ({ ...s, _key: Date.now() + idx })),

            availableBlocks: [
                { type: 'heading', label: '{{ __('Heading') }}' },
                { type: 'content', label: '{{ __('Text') }}' },
                { type: 'paragraph', label: '{{ __('Paragraph') }}' },
                { type: 'list', label: '{{ __('List') }}' },
                { type: 'highlight', label: '{{ __('Highlight') }}' },
                { type: 'note', label: '{{ __('Note') }}' },
                { type: 'terms', label: '{{ __('Terms') }}' },
                { type: 'signature', label: '{{ __('Signature') }}' },
                { type: 'divider', label: '{{ __('Divider') }}' },
                { type: 'spacer', label: '{{ __('Spacer') }}' },
                { type: 'image', label: '{{ __('Image') }}' },
                { type: 'quote', label: '{{ __('Quote') }}' },
                { type: 'table', label: '{{ __('Table') }}' },
                { type: 'columns', label: '{{ __('Columns') }}' },
                { type: 'page_break', label: '{{ __('Page Break') }}' },
            ],

            get templateSubtotal() {
                return this.templateServices.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
            },

            init() {
                this.$nextTick(() => this.initBlockSorting());
            },

            initBlockSorting() {
                const container = this.$refs.blocksContainer;
                if (container && typeof Sortable !== 'undefined') {
                    Sortable.create(container, {
                        animation: 150,
                        handle: '.block-drag-handle',
                        onEnd: (evt) => {
                            const moved = this.blocks.splice(evt.oldIndex, 1)[0];
                            this.blocks.splice(evt.newIndex, 0, moved);
                        }
                    });
                }
            },

            getSelectedBlock() {
                return this.blocks.find(b => b.id === this.selectedBlockId);
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

            setBlockStyle(property, value) {
                const block = this.getSelectedBlock();
                if (!block) return;
                if (!block.data) block.data = {};
                if (!block.data.style) block.data.style = {};
                block.data.style[property] = value;
            },

            getBlockStyle(property) {
                const block = this.getSelectedBlock();
                return block?.data?.style?.[property] || null;
            },

            resetBlockStyle() {
                const block = this.getSelectedBlock();
                if (block?.data) {
                    block.data.style = {};
                }
            },

            getBlockLabel(type) {
                const labels = {
                    'header': '{{ __('Header') }}',
                    'services': '{{ __('Services') }}',
                    'summary': '{{ __('Summary') }}',
                    'heading': '{{ __('Heading') }}',
                    'content': '{{ __('Text') }}',
                    'paragraph': '{{ __('Paragraph') }}',
                    'list': '{{ __('List') }}',
                    'highlight': '{{ __('Highlight') }}',
                    'note': '{{ __('Note') }}',
                    'terms': '{{ __('Terms') }}',
                    'signature': '{{ __('Signature') }}',
                    'divider': '{{ __('Divider') }}',
                    'spacer': '{{ __('Spacer') }}',
                    'image': '{{ __('Image') }}',
                    'quote': '{{ __('Quote') }}',
                    'table': '{{ __('Table') }}',
                    'columns': '{{ __('Columns') }}',
                    'page_break': '{{ __('Page Break') }}',
                };
                return labels[type] || type;
            },

            getBlockIconBg(type) {
                const bgs = {
                    'header': 'bg-slate-800',
                    'services': 'bg-blue-100',
                    'summary': 'bg-green-100',
                    'heading': 'bg-purple-100',
                    'content': 'bg-green-100',
                    'paragraph': 'bg-emerald-100',
                    'list': 'bg-orange-100',
                    'highlight': 'bg-yellow-100',
                    'note': 'bg-sky-100',
                    'terms': 'bg-amber-100',
                    'signature': 'bg-pink-100',
                    'divider': 'bg-slate-100',
                    'spacer': 'bg-gray-100',
                    'image': 'bg-blue-100',
                    'quote': 'bg-violet-100',
                    'table': 'bg-indigo-100',
                    'columns': 'bg-teal-100',
                    'page_break': 'bg-rose-100',
                };
                return bgs[type] || 'bg-slate-100';
            },

            getBlockIconSvg(type) {
                const icons = {
                    'header': '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/></svg>',
                    'services': '<svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>',
                    'summary': '<svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
                    'heading': '<svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>',
                    'content': '<svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>',
                    'paragraph': '<svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/></svg>',
                    'list': '<svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>',
                    'highlight': '<svg class="w-3 h-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>',
                    'note': '<svg class="w-3 h-3 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                    'terms': '<svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'signature': '<svg class="w-3 h-3 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>',
                    'divider': '<svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>',
                    'spacer': '<svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>',
                    'image': '<svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                    'quote': '<svg class="w-3 h-3 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>',
                    'table': '<svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>',
                    'columns': '<svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>',
                    'page_break': '<svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6M4 12h16M4 7h16"/></svg>',
                };
                return icons[type] || '<svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16"/></svg>';
            },

            addBlock(type) {
                let blockData = { title: '', content: '' };

                // Initialize block-specific data structures
                if (type === 'heading') {
                    blockData = { content: '', level: 'h2' };
                } else if (type === 'spacer') {
                    blockData = { height: 40 };
                } else if (type === 'table') {
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
                } else if (type === 'paragraph' || type === 'highlight' || type === 'note' || type === 'terms') {
                    blockData = { content: '' };
                } else if (type === 'image') {
                    blockData = { src: '', alt: '' };
                }

                const newBlock = {
                    id: type + '_' + Date.now(),
                    type: type,
                    visible: true,
                    data: blockData,
                };
                this.blocks.push(newBlock);
            },

            removeBlock(index) {
                this.blocks.splice(index, 1);
            },

            moveBlock(index, direction) {
                const newIndex = index + direction;
                if (newIndex < 0 || newIndex >= this.blocks.length) return;
                const block = this.blocks.splice(index, 1)[0];
                this.blocks.splice(newIndex, 0, block);
            },

            duplicateBlock(index) {
                const block = this.blocks[index];
                const newBlock = JSON.parse(JSON.stringify(block));
                newBlock.id = block.type + '_' + Date.now();
                this.blocks.splice(index + 1, 0, newBlock);
            },

            addTemplateService(service) {
                this.templateServices.push({
                    ...service,
                    _key: Date.now(),
                });
            },

            removeTemplateService(index) {
                this.templateServices.splice(index, 1);
            },

            formatCurrency(amount) {
                return new Intl.NumberFormat('ro-RO', { style: 'currency', currency: 'RON' }).format(amount);
            },

            formatServiceCurrency(amount, currency) {
                return new Intl.NumberFormat('ro-RO', { style: 'currency', currency: currency || 'RON' }).format(amount || 0);
            },

            calculateServiceTotal(index) {
                const item = this.templateServices[index];
                if (item) {
                    item.total = parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0);
                }
            },

            addServiceFromCatalog(id, title, description, rate, currency, unit) {
                this.templateServices.push({
                    _key: Date.now(),
                    service_id: id,
                    title: title,
                    description: description,
                    quantity: 1,
                    unit: unit || 'ora',
                    unit_price: rate,
                    currency: currency || 'RON',
                    total: rate,
                });
            },

            addCustomService() {
                this.templateServices.push({
                    _key: Date.now(),
                    service_id: null,
                    title: '',
                    description: '',
                    quantity: 1,
                    unit: 'ora',
                    unit_price: 0,
                    currency: 'RON',
                    total: 0,
                });
            },

            handleBlockImageUpload(event, block) {
                const file = event.target.files[0];
                if (!file) return;

                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('{{ __('Image must be less than 5MB') }}');
                    return;
                }

                // Convert to base64 for preview (you might want to upload to server instead)
                const reader = new FileReader();
                reader.onload = (e) => {
                    block.data.src = e.target.result;
                    block.data.alt = file.name;
                };
                reader.readAsDataURL(file);
            },

            async saveTemplate() {
                if (!this.templateName.trim()) {
                    alert('{{ __('Please enter a template name') }}');
                    return;
                }

                this.isSaving = true;

                try {
                    const response = await fetch('{{ route('settings.document-templates.builder.update', $template) }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            name: this.templateName,
                            blocks: this.blocks,
                            services: this.templateServices.map(s => ({
                                service_id: s.service_id,
                                title: s.title,
                                description: s.description,
                                quantity: s.quantity,
                                unit: s.unit,
                                unit_price: s.unit_price,
                                currency: s.currency,
                            })),
                            is_default: this.isDefault,
                            is_active: this.isActive,
                        }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Stay on page and show success notification
                        this.showSaveSuccess = true;
                        setTimeout(() => this.showSaveSuccess = false, 3000);
                    } else {
                        alert(data.message || '{{ __('Error saving template') }}');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('{{ __('Error saving template') }}');
                } finally {
                    this.isSaving = false;
                }
            },
        };
    }
    </script>
</x-app-layout>
