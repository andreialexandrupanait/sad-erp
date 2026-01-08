{{-- Services Block - Combined Section --}}
{{--
    SECTION 1: Custom services with checkboxes (from sidebar predefined or manual)
    SECTION 2: Extra service cards (Plutio style - client Add/Remove)
--}}
<div class="px-6 py-6">
    {{-- Main Block Heading --}}
    <div class="mb-6">
        <input x-show="!previewMode" type="text" x-model="block.data.heading"
               placeholder="{{ __('Section heading...') }}"
               class="w-full text-xl font-bold text-slate-800 bg-transparent border-none p-0 focus:ring-0">
        <h2 x-show="previewMode" class="text-xl font-bold text-slate-800">
            <span x-text="block.data.heading || '{{ __('Oferta include următoarele servicii:') }}'"></span>
            <div class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
        </h2>
        <div x-show="!previewMode" class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
    </div>

    {{-- ================================================ --}}
    {{-- SECTION 1: Services List (checkbox list) --}}
    {{-- ================================================ --}}
    <div x-show="customServices.length > 0 || !previewMode">
        {{-- Table Header --}}
        <div class="hidden md:flex items-center px-4 py-2 text-xs font-medium text-slate-500 uppercase tracking-wide border-b border-slate-200">
            <div class="flex-1">{{ __('Service') }}</div>
            <div class="w-32 text-right" x-show="block.data.showDiscount">{{ __('Discount') }}</div>
            <div class="w-28 text-right">{{ __('Total') }}</div>
        </div>

        {{-- Services List --}}
        <div class="space-y-3">
            <template x-for="(item, index) in items" :key="item._key">
                <div x-show="item._type === 'custom'"
                     class="bg-white rounded-xl border transition-all"
                     :class="item._selected ? 'border-green-200 shadow-md' : 'border-slate-100 shadow-sm hover:shadow-md'">
                    <div class="flex items-start gap-4 p-5">
                        {{-- Checkbox --}}
                        <div class="pt-0.5">
                            <input type="checkbox" x-model="item._selected"
                                   @change="if(!item._selected) { item.quantity = 1; updateServiceTotal(index); }"
                                   class="w-6 h-6 rounded-md border-slate-300 text-green-600 focus:ring-green-500 cursor-pointer">
                        </div>

                        {{-- Service Info --}}
                        <div class="flex-1 min-w-0">
                            {{-- Title with optional predefined label --}}
                            <div class="flex items-center gap-3 flex-wrap">
                                <input x-show="!previewMode" type="text" x-model="item.title"
                                       placeholder="{{ __('Service name...') }}"
                                       class="flex-1 min-w-[200px] font-semibold text-slate-900 text-lg bg-transparent border-none p-0 focus:ring-0">
                                <h4 x-show="previewMode" class="font-semibold text-slate-900 text-lg" x-text="item.title"></h4>
                                {{-- Predefined service label --}}
                                <span x-show="item.service_id" class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-green-50 text-green-700 rounded-full border border-green-200">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ __('Predefined') }}
                                </span>
                            </div>

                            {{-- Description --}}
                            <textarea x-show="!previewMode" x-model="item.description"
                                      placeholder="{{ __('Service description...') }}"
                                      x-ref="customDesc"
                                      x-effect="$nextTick(() => { if($el && $el.tagName === 'TEXTAREA') { $el.style.height = 'auto'; $el.style.height = Math.max($el.scrollHeight, 40) + 'px'; } })"
                                      x-on:input="$el.style.height = 'auto'; $el.style.height = Math.max($el.scrollHeight, 40) + 'px'"
                                      class="w-full text-sm text-slate-500 bg-transparent border-none p-0 focus:ring-0 resize-none mt-2 leading-relaxed overflow-hidden min-h-[40px]"></textarea>
                            <p x-show="previewMode && item.description" class="text-sm text-slate-500 mt-2 leading-relaxed whitespace-pre-line" x-text="item.description"></p>

                            {{-- Price controls (edit mode) --}}
                            <div x-show="!previewMode" class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-100 flex-wrap">
                                <span class="text-xs text-slate-400 uppercase tracking-wide">{{ __('Price') }}</span>
                                <input type="number" x-model.number="item.unit_price" min="0" step="0.01"
                                       @input="updateServiceTotal(index)"
                                       class="w-28 text-sm border-slate-200 rounded-lg px-3 py-1.5 bg-slate-50">
                                <span class="text-slate-400">/</span>
                                <select x-model="item.unit" class="text-sm border-slate-200 rounded-lg px-3 py-1.5 bg-slate-50">
                                    <option value="buc">buc</option>
                                    <option value="ora">oră</option>
                                    <option value="luna">lună</option>
                                    <option value="an">an</option>
                                    <option value="proiect">proiect</option>
                                </select>

                                {{-- Per-service discount --}}
                                <div class="flex items-center gap-2 ml-4">
                                    <span class="text-xs text-slate-400 uppercase tracking-wide">{{ __('Discount') }}</span>
                                    <div class="flex items-center">
                                        <input type="number" x-model.number="item.discount_percent" min="0" max="100" step="1"
                                               @input="updateServiceTotal(index)"
                                               class="w-16 text-sm border-slate-200 rounded-l-lg px-2 py-1.5 bg-slate-50 text-right">
                                        <span class="px-2 py-1.5 bg-slate-100 border border-l-0 border-slate-200 rounded-r-lg text-sm text-slate-500">%</span>
                                    </div>
                                </div>

                                {{-- Save as predefined button (only for non-predefined services with title) --}}
                                <button x-show="!item.service_id && item.title"
                                        type="button"
                                        @click="saveAsPredefined(index)"
                                        class="ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border border-blue-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                    </svg>
                                    {{ __('Save as predefined') }}
                                </button>
                            </div>

                            {{-- Price display (preview mode) --}}
                            <div x-show="previewMode" class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-100">
                                <span class="text-sm text-slate-400">{{ __('Cost') }}:</span>
                                <span class="text-sm font-semibold text-slate-700" x-text="formatCurrency(item.unit_price)"></span>
                                <span x-show="item.unit !== 'buc' && item.unit !== 'proiect'" class="text-sm text-slate-400">/ <span x-text="item.unit"></span></span>
                            </div>

                            {{-- Quantity selector (when selected, for units that need it) --}}
                            <div x-show="item._selected && item.unit !== 'proiect'"
                                 class="flex items-center gap-4 mt-4">
                                <span class="text-sm font-medium text-slate-600">{{ __('Quantity') }}:</span>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="item.quantity = Math.max(1, item.quantity - 1); updateServiceTotal(index)"
                                            class="w-8 h-8 flex items-center justify-center bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <input type="number" x-model.number="item.quantity" min="1"
                                           @input="updateServiceTotal(index)"
                                           class="w-16 text-center text-sm border-slate-200 rounded-lg py-1.5 font-medium">
                                    <button type="button" @click="item.quantity++; updateServiceTotal(index)"
                                            class="w-8 h-8 flex items-center justify-center bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Discount Column --}}
                        <div x-show="block.data.showDiscount" class="w-32 text-right pt-1">
                            <template x-if="item._selected && item.discount_percent > 0">
                                <span class="text-sm text-green-600 font-medium">
                                    -<span x-text="formatCurrency(item.unit_price * item.quantity * item.discount_percent / 100)"></span>
                                    (<span x-text="item.discount_percent"></span>%)
                                </span>
                            </template>
                            <span x-show="!item._selected || !item.discount_percent" class="text-sm text-slate-300">—</span>
                        </div>

                        {{-- Total Column --}}
                        <div class="w-32 text-right pt-1">
                            <span class="text-lg font-bold" :class="item._selected ? 'text-green-600' : 'text-slate-800'" x-text="formatCurrency(item.total)"></span>
                        </div>

                        {{-- Delete button (edit mode only) --}}
                        <button x-show="!previewMode" type="button" @click="removeService(index)"
                                class="p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Add Custom Service (Edit Mode Only) --}}
        <div x-show="!previewMode" class="mt-4">
            <button type="button" @click="addCustomService()"
                    class="w-full py-2.5 border-2 border-dashed border-slate-300 rounded-lg text-slate-500 hover:border-blue-500 hover:text-blue-600 transition-colors flex items-center justify-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Add custom service') }}
            </button>
        </div>
    </div>

    {{-- ================================================ --}}
    {{-- SECTION 2: Extra Services Cards (Plutio style) --}}
    {{-- ================================================ --}}
    <div x-show="cardServices.length > 0 || !previewMode" class="mt-8">
        {{-- Section Title --}}
        <div class="mb-4">
            <input x-show="!previewMode" type="text" x-model="block.data.cardsHeading"
                   placeholder="{{ __('Extra services section heading...') }}"
                   class="w-full text-lg font-semibold text-slate-700 bg-transparent border-none p-0 focus:ring-0">
            <h3 x-show="previewMode" class="text-lg font-semibold text-slate-700">
                <span x-text="block.data.cardsHeading || '{{ __('Servicii extra disponibile') }}'"></span>
            </h3>
        </div>

        {{-- Note: Predefined services are added via sidebar to MAIN services list, not to card area --}}

        {{-- Service Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="(item, index) in items" :key="item._key">
                <div x-show="item._type === 'card'"
                     class="rounded-xl p-5 transition-all border flex flex-col"
                     :class="item._selected
                         ? 'bg-slate-800 border-green-500 shadow-lg'
                         : 'bg-slate-900 border-slate-700 hover:border-slate-600 hover:shadow-lg'">

                    {{-- Top Content Area (grows to fill space) --}}
                    <div class="flex-1">
                        {{-- Card Header with Icon --}}
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                                 :class="item._selected ? 'bg-green-500' : 'bg-slate-700'">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                {{-- Title --}}
                                <input x-show="!previewMode" type="text" x-model="item.title"
                                       placeholder="{{ __('Service name...') }}"
                                       class="w-full font-semibold text-white text-base bg-transparent border-none p-0 focus:ring-0 placeholder:text-slate-500">
                                <h4 x-show="previewMode" class="font-semibold text-white text-base" x-text="item.title"></h4>
                                {{-- Predefined label --}}
                                <span x-show="item.service_id" class="inline-flex items-center mt-1 px-2 py-0.5 text-xs font-medium bg-slate-700 text-slate-300 rounded">
                                    {{ __('Extra') }}
                                </span>
                            </div>
                        </div>

                        {{-- Description --}}
                        <textarea x-show="!previewMode" x-model="item.description"
                                  placeholder="{{ __('Service description...') }}"
                                  x-effect="$nextTick(() => { if($el && $el.tagName === 'TEXTAREA') { $el.style.height = 'auto'; $el.style.height = Math.max($el.scrollHeight, 40) + 'px'; } })"
                                  x-on:input="$el.style.height = 'auto'; $el.style.height = Math.max($el.scrollHeight, 40) + 'px'"
                                  class="w-full text-sm text-slate-400 bg-transparent border-none p-0 focus:ring-0 resize-none mb-3 placeholder:text-slate-600 overflow-hidden min-h-[40px]"></textarea>
                        <p x-show="previewMode && item.description" class="text-sm text-slate-400 mb-3 whitespace-pre-line" x-text="item.description"></p>

                        {{-- Features List (if any) --}}
                        <div x-show="item.features && item.features.length > 0" class="mb-3">
                            <ul class="space-y-1">
                                <template x-for="(feature, fIndex) in (item.features || [])" :key="fIndex">
                                    <li class="flex items-start gap-2 text-xs text-slate-400">
                                        <svg class="w-3.5 h-3.5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span x-text="feature"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        {{-- Price (edit mode) --}}
                        <div x-show="!previewMode" class="flex items-center gap-2 mb-3">
                            <input type="number" x-model.number="item.unit_price" min="0" step="0.01"
                                   @input="updateServiceTotal(index)"
                                   class="w-24 text-sm border-slate-600 rounded px-2 py-1 bg-slate-800 text-white">
                            <select x-model="item.unit" class="text-sm border-slate-600 rounded px-2 py-1 bg-slate-800 text-white">
                                <option value="buc">buc</option>
                                <option value="luna">lună</option>
                                <option value="an">an</option>
                                <option value="proiect">proiect</option>
                            </select>
                        </div>
                    </div>

                    {{-- Bottom Area (always at bottom) --}}
                    <div class="mt-auto">
                        {{-- Price Display --}}
                        <div class="flex items-baseline justify-between mb-3 pt-2 border-t"
                             :class="item._selected ? 'border-green-500/30' : 'border-slate-700'">
                            <span class="text-xs text-slate-500">{{ __('Price') }}</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-xl font-bold" :class="item._selected ? 'text-green-400' : 'text-white'"
                                      x-text="formatCurrency(item.unit_price).split(' ')[0]"></span>
                                <span class="text-xs text-slate-500" x-text="offer.currency"></span>
                            </div>
                        </div>

                        {{-- Add/Remove Button --}}
                        <button type="button" @click="item._selected = !item._selected; updateServiceTotal(index)"
                                class="w-full py-2.5 rounded-lg font-medium text-sm transition-all flex items-center justify-center gap-2"
                                :class="item._selected
                                    ? 'bg-red-500 text-white hover:bg-red-600'
                                    : 'bg-green-500 text-white hover:bg-green-600'">
                            <svg x-show="!item._selected" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <svg x-show="item._selected" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span x-text="item._selected ? '{{ __('Remove') }}' : '{{ __('Add to offer') }}'"></span>
                        </button>

                        {{-- Delete card (edit mode only) --}}
                        <button x-show="!previewMode" type="button" @click="removeService(index)"
                                class="w-full mt-2 py-1 text-xs text-slate-500 hover:text-red-400 transition-colors">
                            {{ __('Delete') }}
                        </button>
                    </div>
                </div>
            </template>

            {{-- Add Card Button (Edit Mode) --}}
            <div x-show="!previewMode"
                 class="border-2 border-dashed border-slate-600 rounded-xl p-5 flex flex-col items-center justify-center min-h-[180px] hover:border-green-500 hover:bg-slate-800/50 transition-colors cursor-pointer bg-slate-800/30"
                 @click="addCardService()">
                <svg class="w-8 h-8 text-slate-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="text-sm text-slate-500">{{ __('Add extra service') }}</span>
            </div>
        </div>
    </div>

    {{-- Block Options (Edit Mode Only) --}}
    <div x-show="!previewMode" class="mt-6 pt-4 border-t border-dashed border-slate-200">
        <div class="flex items-center gap-4 text-xs text-slate-500">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" x-model="block.data.showDiscount" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span>{{ __('Show discount column') }}</span>
            </label>
        </div>
    </div>
</div>
