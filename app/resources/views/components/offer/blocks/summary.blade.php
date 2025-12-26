{{-- Summary Block - Builder View (Plutio Style) --}}
{{-- Contains: Service breakdown table with columns, subtotal, VAT, discounts, grand total --}}

<div class="offer-summary-block px-6 py-6">
    {{-- Section Title --}}
    <div class="mb-6">
        <input type="text" x-model="block.data.title"
               class="text-xl font-bold text-slate-900 bg-transparent border-none focus:ring-0 p-0 w-full"
               placeholder="{{ __('Investment Summary') }}">
        <div class="h-1 w-20 bg-green-600 mt-2 rounded"></div>
    </div>

    {{-- Summary Table - Plutio Style with Columns --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        {{-- Table Header --}}
        <div class="grid grid-cols-12 gap-4 px-5 py-3 bg-slate-50 border-b border-slate-200 text-sm font-semibold text-slate-600">
            <div class="col-span-6">{{ __('Service') }}</div>
            <div class="col-span-2 text-center">{{ __('Quantity') }}</div>
            <div class="col-span-2 text-right">{{ __('Unit Price') }}</div>
            <div class="col-span-2 text-right">{{ __('Total') }}</div>
        </div>

        {{-- Services Rows --}}
        <div class="divide-y divide-slate-100">
            <template x-for="(item, index) in items" :key="item._key">
                <div class="grid grid-cols-12 gap-4 px-5 py-4 hover:bg-slate-50 transition-colors items-center">
                    {{-- Service Name --}}
                    <div class="col-span-6">
                        <span class="text-slate-900 font-medium" x-text="item.title || '{{ __('Untitled Service') }}'"></span>
                        <template x-if="item.description && block.data.showDescriptions !== false">
                            <p class="text-sm text-slate-500 mt-1 line-clamp-2" x-text="item.description"></p>
                        </template>
                    </div>
                    {{-- Quantity --}}
                    <div class="col-span-2 text-center text-slate-600">
                        <span x-text="item.quantity"></span>
                        <span class="text-slate-400 text-sm ml-1" x-text="item.unit"></span>
                    </div>
                    {{-- Unit Price --}}
                    <div class="col-span-2 text-right text-slate-600" x-text="formatCurrency(item.unit_price || 0, item.currency)"></div>
                    {{-- Total --}}
                    <div class="col-span-2 text-right font-semibold text-slate-900" x-text="formatCurrency(item.total || 0, item.currency)"></div>
                </div>
            </template>

            {{-- Empty State --}}
            <div x-show="items.length === 0" class="px-5 py-8 text-center text-slate-400">
                {{ __('No services added yet') }}
            </div>
        </div>

        {{-- Totals Section --}}
        <div class="bg-slate-50 border-t border-slate-200">
            {{-- Subtotal --}}
            <template x-if="block.data.showSubtotal !== false">
                <div class="grid grid-cols-12 gap-4 px-5 py-3 border-b border-slate-200">
                    <div class="col-span-10 text-right text-slate-600">{{ __('Subtotal') }}</div>
                    <div class="col-span-2 text-right font-semibold text-slate-900" x-text="formatCurrency(calculateSubtotal(), offer.currency || 'RON')"></div>
                </div>
            </template>

            {{-- Discount --}}
            <template x-if="block.data.showDiscount && offer.discount_percent > 0">
                <div class="grid grid-cols-12 gap-4 px-5 py-3 border-b border-slate-200">
                    <div class="col-span-10 text-right text-green-600 flex items-center justify-end gap-2">
                        <span>{{ __('Discount') }}</span>
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full" x-text="offer.discount_percent + '%'"></span>
                    </div>
                    <div class="col-span-2 text-right font-semibold text-green-600">
                        -<span x-text="formatCurrency(calculateDiscount(), offer.currency || 'RON')"></span>
                    </div>
                </div>
            </template>

            {{-- VAT --}}
            <template x-if="block.data.showVAT">
                <div class="grid grid-cols-12 gap-4 px-5 py-3 border-b border-slate-200">
                    <div class="col-span-10 text-right text-slate-600 flex items-center justify-end gap-2">
                        <span>{{ __('VAT') }}</span>
                        <input type="number" x-model="block.data.vatPercent"
                               class="w-14 text-center text-xs border border-slate-200 rounded px-1 py-0.5"
                               min="0" max="100" step="1">
                        <span class="text-xs text-slate-400">%</span>
                    </div>
                    <div class="col-span-2 text-right font-semibold text-slate-900" x-text="formatCurrency(calculateVAT(), offer.currency || 'RON')"></div>
                </div>
            </template>

            {{-- Grand Total --}}
            <template x-if="block.data.showGrandTotal !== false">
                <div class="grid grid-cols-12 gap-4 px-5 py-4 bg-slate-900 text-white">
                    <div class="col-span-10 text-right text-lg font-bold">{{ __('Total') }}</div>
                    <div class="col-span-2 text-right text-xl font-bold" x-text="formatCurrency(calculateGrandTotal(), offer.currency || 'RON')"></div>
                </div>
            </template>
        </div>
    </div>

    {{-- Display Options (Toggle) --}}
    <div class="mt-4 flex flex-wrap gap-4 text-sm" x-show="!previewMode">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" x-model="block.data.showSubtotal" class="rounded border-slate-300 text-green-600 focus:ring-green-500">
            <span class="text-slate-600">{{ __('Show Subtotal') }}</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" x-model="block.data.showVAT" class="rounded border-slate-300 text-green-600 focus:ring-green-500">
            <span class="text-slate-600">{{ __('Show VAT') }}</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" x-model="block.data.showDiscount" class="rounded border-slate-300 text-green-600 focus:ring-green-500">
            <span class="text-slate-600">{{ __('Show Discount') }}</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" x-model="block.data.showGrandTotal" class="rounded border-slate-300 text-green-600 focus:ring-green-500">
            <span class="text-slate-600">{{ __('Show Total') }}</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" x-model="block.data.showDescriptions" class="rounded border-slate-300 text-green-600 focus:ring-green-500">
            <span class="text-slate-600">{{ __('Show Descriptions') }}</span>
        </label>
    </div>
</div>
