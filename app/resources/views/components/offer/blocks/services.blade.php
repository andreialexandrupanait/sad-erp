{{-- Services Block - Builder View --}}
{{-- Contains 3 sub-zones: Selected Services, Optional Services (upsell), Notes --}}

<div class="offer-services-block px-6 py-6">
    {{-- Section Title --}}
    <div class="mb-6">
        <input type="text" x-model="block.data.title"
               class="text-xl font-bold text-slate-900 bg-transparent border-none focus:ring-0 p-0 w-full"
               placeholder="{{ __('Proposed Services') }}">
        <div class="h-1 w-20 bg-blue-600 mt-2 rounded"></div>
    </div>

    {{-- Zone 1: Selected Services (from offer items) --}}
    <div class="mb-8">
        <div class="space-y-4">
            <template x-for="(item, index) in items" :key="item._key">
                <div class="bg-white border border-slate-200 rounded-xl p-5 hover:border-slate-300 transition-all shadow-sm">
                    {{-- Service Header with Icon --}}
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <input type="text" x-model="item.title"
                                   class="font-semibold text-slate-900 bg-transparent border-none focus:ring-0 p-0 w-full text-base"
                                   placeholder="{{ __('Service name') }}">
                            <template x-if="block.data.showDescriptions !== false">
                                <textarea x-model="item.description"
                                          x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = Math.max(60, $el.scrollHeight) + 'px'; })"
                                          @input="$el.style.height = 'auto'; $el.style.height = Math.max(60, $el.scrollHeight) + 'px'"
                                          class="mt-2 w-full text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded-lg p-3 focus:border-slate-400 resize-none overflow-hidden"
                                          placeholder="{{ __('Service description...') }}"></textarea>
                            </template>
                        </div>
                    </div>

                    {{-- Pricing Row --}}
                    <template x-if="block.data.showPrices">
                        <div class="flex items-center gap-3 mt-4 pt-4 border-t border-slate-100">
                            <input type="number" x-model="item.quantity" @input="calculateItemTotal(index)"
                                   step="0.01" min="0.01"
                                   class="w-20 text-center border border-slate-200 rounded-lg px-2 py-2 text-sm focus:border-blue-400">
                            <select x-model="item.unit" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400">
                                <option value="ora">{{ __('hours') }}</option>
                                <option value="buc">{{ __('pcs') }}</option>
                                <option value="luna">{{ __('months') }}</option>
                                <option value="zi">{{ __('days') }}</option>
                                <option value="an">{{ __('years') }}</option>
                                <option value="proiect">{{ __('project') }}</option>
                            </select>
                            <span class="text-slate-400">×</span>
                            <input type="number" x-model="item.unit_price" @input="calculateItemTotal(index)"
                                   step="0.01" min="0"
                                   class="w-28 text-right border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-blue-400">
                            <select x-model="item.currency" class="border border-slate-200 rounded-lg px-2 py-2 text-sm focus:border-blue-400">
                                <option value="RON">RON</option>
                                <option value="EUR">EUR</option>
                                <option value="USD">USD</option>
                            </select>
                            <span class="text-slate-400">=</span>
                            <div class="w-32 text-right font-bold text-slate-900" x-text="formatCurrency(item.total || 0, item.currency)"></div>
                            <button type="button" @click="removeItem(index)"
                                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Empty State --}}
            <div x-show="items.length === 0" class="py-12 text-center border-2 border-dashed border-slate-200 rounded-xl">
                <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-slate-500 font-medium">{{ __('No services added yet') }}</p>
                <p class="text-sm text-slate-400 mt-1">{{ __('Add services from the "Services" tab in the sidebar') }}</p>
            </div>
        </div>
    </div>

    {{-- Zone 2: Optional Services (Upsell Cards) --}}
    <template x-if="block.data.optionalServices && block.data.optionalServices.length > 0">
        <div class="mb-8">
            <h4 class="text-lg font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('Optional Services') }}
            </h4>
            <div class="grid grid-cols-2 gap-4">
                <template x-for="(optService, optIndex) in block.data.optionalServices" :key="optService._key || optIndex">
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-4">
                        <input type="text" x-model="optService.title"
                               class="font-semibold text-slate-900 bg-transparent border-none focus:ring-0 p-0 w-full"
                               placeholder="{{ __('Service name') }}">
                        <textarea x-model="optService.description"
                                  rows="2"
                                  class="mt-2 w-full text-sm text-slate-600 bg-white/50 border border-amber-200 rounded-lg p-2 resize-none"
                                  placeholder="{{ __('Description...') }}"></textarea>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-amber-200">
                            <div class="flex items-center gap-2">
                                <input type="number" x-model="optService.unit_price" step="0.01"
                                       class="w-24 text-right border border-amber-200 rounded px-2 py-1 text-sm bg-white">
                                <span class="text-sm text-slate-500" x-text="optService.currency || 'RON'"></span>
                            </div>
                            <button type="button" @click="block.data.optionalServices.splice(optIndex, 1)"
                                    class="p-1.5 text-slate-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
            <button type="button"
                    @click="block.data.optionalServices.push({_key: Date.now(), title: '', description: '', unit_price: 0, currency: offer.currency || 'RON'})"
                    class="mt-3 text-sm text-amber-600 hover:text-amber-700 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Add Optional Service') }}
            </button>
        </div>
    </template>

    {{-- Zone 3: Notes / Precizări (WYSIWYG) --}}
    <div class="mt-6 pt-6 border-t border-slate-100" x-show="block.data.showNotes !== false">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <input type="text" x-model="block.data.notesTitle"
                   class="font-semibold text-slate-700 bg-transparent border-none focus:ring-0 p-0"
                   placeholder="{{ __('Notes') }}">
        </div>
        <div contenteditable="true"
             x-html="block.data.notes"
             @blur="block.data.notes = $event.target.innerHTML"
             class="min-h-[60px] p-4 bg-slate-50 border border-slate-200 rounded-xl focus:border-blue-300 focus:outline-none text-slate-600 leading-relaxed prose prose-sm max-w-none"
             data-placeholder="{{ __('Add notes, clarifications, or additional information...') }}"></div>
    </div>
</div>
