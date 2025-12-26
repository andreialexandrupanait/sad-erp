{{-- Optional Services Block - Plutio-style selectable service cards --}}
{{-- Allows customers to select additional services to add to their offer --}}

<div class="offer-optional-services-block px-6 py-6" x-data="{ showCatalog: false, catalogSearch: '' }">
    {{-- Section Title --}}
    <div class="mb-6">
        <textarea x-model="block.data.title"
                  x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' })"
                  @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                  placeholder="{{ __('Optional Services') }}"
                  rows="1"
                  :readonly="previewMode"
                  class="w-full text-xl font-bold text-slate-900 bg-transparent border-none p-0 focus:ring-0 placeholder:text-slate-400 resize-none overflow-hidden"></textarea>
        <div class="h-1 w-20 bg-slate-800 mt-2 rounded"></div>
    </div>

    {{-- Description --}}
    <div class="mb-6" x-show="!previewMode || block.data.description">
        <textarea x-model="block.data.description"
                  x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' })"
                  @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                  placeholder="{{ __('Select additional services to enhance your package...') }}"
                  rows="1"
                  :readonly="previewMode"
                  class="w-full text-sm text-slate-600 bg-transparent border-none p-0 focus:ring-0 placeholder:text-slate-400 resize-none overflow-hidden"></textarea>
    </div>

    {{-- Table Header (Preview Mode) --}}
    <div x-show="previewMode && block.data.services && block.data.services.length > 0" class="flex items-center justify-end gap-4 mb-2 text-xs font-medium text-slate-400 uppercase tracking-wider px-2">
        <span class="w-24 text-right">{{ __('Discount') }}</span>
        <span class="w-28 text-right">{{ __('Total') }}</span>
    </div>

    {{-- Optional Services List - Plutio Style --}}
    <div class="space-y-3">
        <template x-for="(optService, optIdx) in (block.data.services || [])" :key="optService._key || optIdx">
            <div class="border rounded-xl transition-all"
                 :class="items.some(i => i._optionalKey === optService._key)
                    ? 'border-slate-300 bg-white shadow-sm'
                    : 'border-slate-200 bg-white hover:border-slate-300'">

                {{-- Main Row: Checkbox + Title + Price --}}
                <div class="flex items-start gap-4 p-4 cursor-pointer"
                     @click="previewMode && toggleOptionalService(optService)">

                    {{-- Checkbox --}}
                    <div class="pt-0.5">
                        <template x-if="previewMode">
                            <input type="checkbox"
                                   :checked="items.some(i => i._optionalKey === optService._key)"
                                   @click.stop="toggleOptionalService(optService)"
                                   class="rounded border-slate-300 text-slate-800 focus:ring-slate-500 w-5 h-5 cursor-pointer">
                        </template>
                        <template x-if="!previewMode">
                            <div class="w-5 h-5 rounded border-2 border-dashed border-slate-300 flex items-center justify-center">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </template>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        {{-- Edit Mode --}}
                        <template x-if="!previewMode">
                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-3">
                                    <input type="text" x-model="optService.title"
                                           placeholder="{{ __('Service name') }}"
                                           class="flex-1 font-semibold text-slate-900 bg-transparent border-none focus:ring-0 p-0 text-base">
                                    <button type="button" @click.stop="block.data.services.splice(optIdx, 1)"
                                            class="p-1 text-slate-400 hover:text-red-600 rounded transition-colors flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <textarea x-model="optService.description"
                                          x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = Math.max(40, $el.scrollHeight) + 'px' })"
                                          @input="$el.style.height = 'auto'; $el.style.height = Math.max(40, $el.scrollHeight) + 'px'"
                                          placeholder="{{ __('Describe what this service includes...') }}"
                                          rows="2"
                                          class="w-full text-sm text-slate-600 bg-transparent border-none focus:ring-0 p-0 placeholder:text-slate-400 resize-none overflow-hidden leading-relaxed"></textarea>
                            </div>
                        </template>

                        {{-- Preview Mode --}}
                        <template x-if="previewMode">
                            <div>
                                <h4 class="font-semibold text-slate-900 text-base" x-text="optService.title"></h4>
                                <p class="text-sm text-slate-500 mt-1 leading-relaxed" x-text="optService.description"></p>
                            </div>
                        </template>
                    </div>

                    {{-- Price Column --}}
                    <div class="flex-shrink-0 text-right">
                        {{-- Edit Mode --}}
                        <template x-if="!previewMode">
                            <div class="flex items-center gap-2">
                                <input type="number" x-model="optService.quantity" step="0.01" min="1"
                                       @input="optService.total = optService.quantity * optService.unit_price"
                                       class="w-14 text-center border border-slate-200 rounded px-1 py-1 text-sm">
                                <span class="text-slate-400 text-sm">×</span>
                                <input type="number" x-model="optService.unit_price" step="0.01" min="0"
                                       @input="optService.total = optService.quantity * optService.unit_price"
                                       class="w-20 text-right border border-slate-200 rounded px-2 py-1 text-sm">
                                <select x-model="optService.currency" class="border border-slate-200 rounded px-1 py-1 text-sm">
                                    <option value="RON">RON</option>
                                    <option value="EUR">EUR</option>
                                    <option value="USD">USD</option>
                                </select>
                            </div>
                        </template>

                        {{-- Preview Mode --}}
                        <template x-if="previewMode">
                            <div class="flex items-center gap-4">
                                <span class="text-sm text-slate-400 w-24 text-right" x-text="optService.discount ? '-' + optService.discount + '%' : ''"></span>
                                <span class="font-bold text-slate-900 w-28 text-right"
                                      :class="items.some(i => i._optionalKey === optService._key) ? 'text-green-600' : ''"
                                      x-text="formatCurrency((optService.unit_price || 0) * (optService.quantity || 1), optService.currency)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Edit Mode: Unit selector row --}}
                <div x-show="!previewMode" class="px-4 pb-3 flex items-center gap-4 border-t border-slate-100 pt-3 ml-9">
                    <label class="text-xs text-slate-500">{{ __('Unit') }}:</label>
                    <select x-model="optService.unit" class="border border-slate-200 rounded px-2 py-1 text-xs">
                        <option value="ora">{{ __('hours') }}</option>
                        <option value="buc">{{ __('pcs') }}</option>
                        <option value="luna">{{ __('months') }}</option>
                        <option value="proiect">{{ __('project') }}</option>
                    </select>
                    <label class="text-xs text-slate-500 ml-4">{{ __('Discount') }} (%):</label>
                    <input type="number" x-model="optService.discount" step="1" min="0" max="100"
                           class="w-16 text-center border border-slate-200 rounded px-2 py-1 text-xs"
                           placeholder="0">
                </div>
            </div>
        </template>

        {{-- Empty State (Preview Mode) --}}
        <div x-show="previewMode && (!block.data.services || block.data.services.length === 0)"
             class="py-12 text-center text-slate-400 border-2 border-dashed border-slate-200 rounded-xl">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p>{{ __('No optional services available') }}</p>
        </div>
    </div>

    {{-- Add Buttons (Edit Mode) --}}
    <div x-show="!previewMode" class="mt-4 flex gap-3">
        {{-- Add Custom --}}
        <button type="button"
                @click="addOptionalServiceToBlock(block)"
                class="flex-1 py-3 border-2 border-dashed border-slate-300 rounded-xl text-slate-600 hover:border-slate-400 hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="font-medium">{{ __('Add Custom Service') }}</span>
        </button>
        {{-- Add from Catalog --}}
        <button type="button"
                @click="showCatalog = !showCatalog"
                class="flex-1 py-3 border-2 border-dashed border-green-300 rounded-xl text-green-700 hover:border-green-400 hover:bg-green-50 transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span class="font-medium">{{ __('From Catalog') }}</span>
        </button>
    </div>

    {{-- Service Catalog Dropdown (Edit Mode) --}}
    <div x-show="showCatalog && !previewMode" x-transition class="mt-4 bg-slate-50 border border-slate-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-semibold text-slate-900 text-sm">{{ __('Add from Service Catalog') }}</h4>
            <button type="button" @click="showCatalog = false" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Search --}}
        <div class="relative mb-3">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="catalogSearch" placeholder="{{ __('Search services...') }}"
                   class="w-full h-9 pl-10 pr-4 text-sm border border-slate-200 rounded-lg focus:border-slate-400 focus:ring-1 focus:ring-slate-400">
        </div>

        {{-- Service List --}}
        <div class="space-y-2 max-h-60 overflow-y-auto">
            @foreach($services ?? [] as $service)
                <div x-show="!catalogSearch || '{{ strtolower($service->name) }}'.includes(catalogSearch.toLowerCase())"
                     @click="addOptionalServiceFromCatalog(block, {{ $service->id }}, '{{ addslashes($service->name) }}', '{{ addslashes($service->description ?? '') }}', {{ $service->default_rate ?? $service->price ?? 0 }}, '{{ $service->currency ?? 'RON' }}', '{{ $service->unit ?? 'ora' }}'); showCatalog = false"
                     class="p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:border-green-400 hover:bg-green-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm text-slate-900">{{ $service->name }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($service->default_rate ?? $service->price ?? 0, 2, ',', '.') }} {{ $service->currency ?? 'RON' }}/{{ $service->unit ?? 'ora' }}</p>
                        </div>
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                </div>
            @endforeach
            @if(empty($services) || count($services ?? []) === 0)
                <p class="text-center text-sm text-slate-400 py-4">{{ __('No services in catalog') }}</p>
            @endif
        </div>
    </div>

    {{-- Selected Services Summary (Preview Mode) --}}
    <div x-show="previewMode && items.filter(i => i._optionalKey).length > 0" class="mt-6 pt-4 border-t border-slate-200">
        <div class="flex items-center justify-between">
            <span class="text-sm text-slate-600">
                <span class="font-semibold" x-text="items.filter(i => i._optionalKey).length"></span>
                {{ __('optional service(s) selected') }}
            </span>
            <span class="text-lg font-bold text-green-600">
                +<span x-text="formatCurrency(items.filter(i => i._optionalKey).reduce((sum, i) => sum + (i.total || 0), 0), offer.currency || 'RON')"></span>
            </span>
        </div>
    </div>
</div>
