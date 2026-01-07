{{-- Summary Block - Shows selected services + totals (like Plutio) --}}
<div class="px-6 py-6">
    {{-- Block Heading --}}
    <div class="mb-6">
        <input x-show="!previewMode" type="text" x-model="block.data.heading"
               placeholder="{{ __('Summary heading...') }}"
               class="w-full text-xl font-bold text-slate-800 bg-transparent border-none p-0 focus:ring-0">
        <h2 x-show="previewMode" class="text-xl font-bold text-slate-800">
            <span x-text="block.data.heading || '{{ __('Sumar servicii selectate') }}'"></span>
            <div class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
        </h2>
        <div x-show="!previewMode" class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
    </div>

    {{-- Services Table with Totals --}}
    <div x-show="selectedItems.length > 0">
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            {{-- Table Header --}}
            <div class="grid grid-cols-12 gap-2 px-4 py-3 bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
                <div class="col-span-5">{{ __('Serviciu') }}</div>
                <div class="col-span-2 text-center">{{ __('Cant.') }}</div>
                <div class="col-span-2 text-right">{{ __('Preț') }}</div>
                <div class="col-span-1 text-center">{{ __('Disc.') }}</div>
                <div class="col-span-2 text-right">{{ __('Total') }}</div>
            </div>

            {{-- Selected Items --}}
            <div class="divide-y divide-slate-100">
                <template x-for="(item, index) in items" :key="item._key">
                    <div x-show="item._selected"
                         class="grid grid-cols-12 gap-2 px-4 py-3 items-center transition-colors"
                         :class="item._type === 'card' ? 'bg-green-50 hover:bg-green-100' : 'hover:bg-slate-50'">
                        {{-- Service Name --}}
                        <div class="col-span-5">
                            <span class="font-medium text-slate-800" x-text="item.title"></span>
                            <span x-show="item._type === 'card'" class="text-xs text-green-600 font-medium ml-2">({{ __('Extra') }})</span>
                        </div>

                        {{-- Quantity --}}
                        <div class="col-span-2 text-center">
                            <span class="text-sm text-slate-600" x-text="item.quantity + ' ' + item.unit"></span>
                        </div>

                        {{-- Unit Price --}}
                        <div class="col-span-2 text-right">
                            <span class="text-sm text-slate-600" x-text="formatCurrency(item.unit_price)"></span>
                        </div>

                        {{-- Discount --}}
                        <div class="col-span-1 text-center">
                            <template x-if="item.discount_percent > 0">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    -<span x-text="item.discount_percent"></span>%
                                </span>
                            </template>
                            <span x-show="!item.discount_percent || item.discount_percent <= 0" class="text-sm text-slate-300">—</span>
                        </div>

                        {{-- Total --}}
                        <div class="col-span-2 text-right">
                            <span class="font-semibold" :class="item._type === 'card' ? 'text-green-700' : 'text-slate-800'" x-text="formatCurrency(item.total)"></span>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Totals Section (inside table) --}}
            <div class="border-t-2 border-slate-200 bg-gradient-to-br from-slate-50 to-slate-100">
                {{-- Subtotal --}}
                <div x-show="block.data.showSubtotal !== false" class="grid grid-cols-12 gap-2 px-4 py-3 items-center">
                    <div class="col-span-10 text-right">
                        <span class="text-sm font-medium text-slate-600">{{ __('Subtotal') }}</span>
                    </div>
                    <div class="col-span-2 text-right">
                        <span class="font-semibold text-slate-800" x-text="formatCurrency(subtotal)"></span>
                    </div>
                </div>

                {{-- Discount --}}
                <div x-show="block.data.showDiscount !== false && offer.discount_percent > 0"
                     class="grid grid-cols-12 gap-2 px-4 py-2 items-center border-t border-dashed border-slate-200">
                    <div class="col-span-10 text-right">
                        <span class="text-sm text-slate-600">
                            {{ __('Discount') }}
                            <span class="text-xs text-green-600 ml-1">(-<span x-text="offer.discount_percent"></span>%)</span>
                        </span>
                    </div>
                    <div class="col-span-2 text-right">
                        <span class="font-medium text-green-600">-<span x-text="formatCurrency(discountAmount)"></span></span>
                    </div>
                </div>

                {{-- Grand Total --}}
                <div x-show="block.data.showGrandTotal !== false"
                     class="grid grid-cols-12 gap-2 px-4 py-4 items-center border-t-2 border-slate-300 bg-slate-100">
                    <div class="col-span-10 text-right">
                        <span class="text-base font-bold text-slate-800">{{ __('Total de plată') }}</span>
                    </div>
                    <div class="col-span-2 text-right">
                        <span class="text-xl font-bold text-green-600" x-text="formatCurrency(grandTotal)"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Empty Selection State --}}
    <div x-show="selectedItems.length === 0" class="text-center py-8 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl">
        <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-slate-500 font-medium">{{ __('Niciun serviciu selectat') }}</p>
        <p class="text-sm text-slate-400 mt-1">{{ __('Bifează serviciile dorite din lista de mai sus') }}</p>
    </div>

    {{-- Block Options (Edit Mode Only) --}}
    <div x-show="!previewMode" class="mt-4 pt-4 border-t border-dashed border-slate-200">
        <div class="space-y-2">
            <span class="text-xs font-medium text-slate-600">{{ __('Display Options') }}</span>
            <div class="flex flex-wrap gap-4">
                <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-500">
                    <input type="checkbox" x-model="block.data.showSubtotal" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                    <span>{{ __('Show subtotal') }}</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-500">
                    <input type="checkbox" x-model="block.data.showDiscount" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                    <span>{{ __('Show discount') }}</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-500">
                    <input type="checkbox" x-model="block.data.showGrandTotal" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                    <span>{{ __('Show grand total') }}</span>
                </label>
            </div>
        </div>
    </div>
</div>
