{{--
    Shared Summary Table Component
    Used in both admin builder (simple-builder) and public view

    Props:
    - $items: Collection of offer items (or use Alpine x-data)
    - $currency: Currency code (EUR, RON, etc.)
    - $discountPercent: Global discount percentage
    - $showVAT: Whether to show VAT
    - $vatPercent: VAT percentage
    - $mode: 'static' (Blade) or 'dynamic' (Alpine.js)
    - $cardItems: Collection of card type items (for static mode)
    - $optionalServices: Array of optional services (for static mode)
--}}

@props([
    'items' => collect(),
    'currency' => 'EUR',
    'discountPercent' => 0,
    'showVAT' => false,
    'vatPercent' => 19,
    'mode' => 'static',
    'cardItems' => collect(),
    'optionalServices' => [],
    'heading' => null,
    'showSubtotal' => true,
    'showDiscount' => true,
    'showGrandTotal' => true,
])

<div class="summary-table-component">
    {{-- Heading --}}
    @if($heading)
        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-800">
                {{ $heading }}
                <div class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
            </h2>
        </div>
    @endif

    {{-- Services Table --}}
    <div class="mb-6">
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            {{-- Table Header --}}
            <div class="hidden md:grid grid-cols-12 gap-2 px-4 py-3 bg-slate-50 border-b border-slate-200 text-xs font-medium text-slate-500 uppercase tracking-wide">
                <div class="col-span-5">{{ __('Serviciu') }}</div>
                <div class="col-span-2 text-center">{{ __('Cant.') }}</div>
                <div class="col-span-2 text-right">{{ __('Preț') }}</div>
                <div class="col-span-1 text-center">{{ __('Disc.') }}</div>
                <div class="col-span-2 text-right">{{ __('Total') }}</div>
            </div>

            {{-- Table Body --}}
            <div class="divide-y divide-slate-100">
                @if($mode === 'static')
                    {{-- Static Mode: Render with Blade --}}
                    @foreach($items as $item)
                        <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center hover:bg-slate-50 transition-colors"
                             @if($mode === 'dynamic') x-show="!deselectedServices.includes({{ $item->id }})" @endif>
                            {{-- Service Name --}}
                            <div class="col-span-12 md:col-span-5">
                                <span class="font-medium text-slate-800">{{ $item->title }}</span>
                            </div>
                            {{-- Quantity --}}
                            <div class="col-span-4 md:col-span-2 text-left md:text-center">
                                <span class="text-sm text-slate-600">{{ number_format($item->quantity, $item->quantity == floor($item->quantity) ? 0 : 2) }} {{ $item->unit }}</span>
                            </div>
                            {{-- Unit Price --}}
                            <div class="col-span-4 md:col-span-2 text-center md:text-right">
                                <span class="text-sm text-slate-600">{{ number_format($item->unit_price, 2) }} {{ $currency }}</span>
                            </div>
                            {{-- Discount --}}
                            <div class="col-span-2 md:col-span-1 text-center">
                                @if($item->discount_percent > 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        -{{ number_format($item->discount_percent, 0) }}%
                                    </span>
                                @else
                                    <span class="text-sm text-slate-300">—</span>
                                @endif
                            </div>
                            {{-- Total --}}
                            <div class="col-span-2 md:col-span-2 text-right">
                                <span class="font-semibold text-slate-800">{{ number_format($item->total_price, 2) }} {{ $currency }}</span>
                            </div>
                        </div>
                    @endforeach

                    {{-- Card Services (Extra) --}}
                    @foreach($cardItems as $item)
                        <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center bg-green-50 hover:bg-green-100 transition-colors"
                             x-show="selectedCards.includes({{ $item->id }})"
                             x-transition>
                            <div class="col-span-12 md:col-span-5">
                                <span class="font-medium text-slate-800">{{ $item->title }}</span>
                                <span class="text-xs text-green-600 font-medium ml-2">({{ __('Extra') }})</span>
                            </div>
                            <div class="col-span-4 md:col-span-2 text-left md:text-center">
                                <span class="text-sm text-slate-600">{{ number_format($item->quantity, $item->quantity == floor($item->quantity) ? 0 : 2) }} {{ $item->unit }}</span>
                            </div>
                            <div class="col-span-4 md:col-span-2 text-center md:text-right">
                                <span class="text-sm text-slate-600">{{ number_format($item->unit_price, 2) }} {{ $currency }}</span>
                            </div>
                            <div class="col-span-2 md:col-span-1 text-center">
                                <span class="text-sm text-slate-300">—</span>
                            </div>
                            <div class="col-span-2 md:col-span-2 text-right">
                                <span class="font-semibold text-green-700">{{ number_format($item->total_price, 2) }} {{ $currency }}</span>
                            </div>
                        </div>
                    @endforeach

                    {{-- Optional Services --}}
                    @foreach($optionalServices as $optService)
                        @php
                            $optKey = $optService['_key'] ?? uniqid();
                            $optTitle = $optService['title'] ?? '';
                            $optQuantity = $optService['quantity'] ?? 1;
                            $optUnit = $optService['unit'] ?? 'ora';
                            $optUnitPrice = $optService['unit_price'] ?? 0;
                            $optCurrency = $optService['currency'] ?? $currency;
                            $optTotal = $optQuantity * $optUnitPrice;
                        @endphp
                        <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center bg-green-50 hover:bg-green-100 transition-colors"
                             x-show="selectedOptionalServices.includes('{{ $optKey }}')"
                             x-transition>
                            <div class="col-span-12 md:col-span-5">
                                <span class="font-medium text-slate-800">{{ $optTitle }}</span>
                                <span class="text-xs text-green-600 font-medium ml-2">({{ __('Optional') }})</span>
                            </div>
                            <div class="col-span-4 md:col-span-2 text-left md:text-center">
                                <span class="text-sm text-slate-600">{{ number_format($optQuantity, $optQuantity == floor($optQuantity) ? 0 : 2) }} {{ $optUnit }}</span>
                            </div>
                            <div class="col-span-4 md:col-span-2 text-center md:text-right">
                                <span class="text-sm text-slate-600">{{ number_format($optUnitPrice, 2) }} {{ $optCurrency }}</span>
                            </div>
                            <div class="col-span-2 md:col-span-1 text-center">
                                <span class="text-sm text-slate-300">—</span>
                            </div>
                            <div class="col-span-2 md:col-span-2 text-right">
                                <span class="font-semibold text-green-700">{{ number_format($optTotal, 2) }} {{ $optCurrency }}</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    {{-- Dynamic Mode: Use Alpine.js --}}
                    <template x-for="(item, index) in items" :key="item._key">
                        <div x-show="item._selected"
                             class="grid grid-cols-12 gap-2 px-4 py-3 items-center hover:bg-slate-50 transition-colors">
                            <div class="col-span-12 md:col-span-5">
                                <span class="font-medium text-slate-800" x-text="item.title"></span>
                                <span x-show="item._type === 'card'" class="text-xs text-green-600 font-medium ml-2">({{ __('Extra') }})</span>
                            </div>
                            <div class="col-span-4 md:col-span-2 text-left md:text-center">
                                <span class="text-sm text-slate-600" x-text="item.quantity + ' ' + item.unit"></span>
                            </div>
                            <div class="col-span-4 md:col-span-2 text-center md:text-right">
                                <span class="text-sm text-slate-600" x-text="formatCurrency(item.unit_price)"></span>
                            </div>
                            <div class="col-span-2 md:col-span-1 text-center">
                                <template x-if="item.discount_percent > 0">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        -<span x-text="item.discount_percent"></span>%
                                    </span>
                                </template>
                                <span x-show="!item.discount_percent || item.discount_percent <= 0" class="text-sm text-slate-300">—</span>
                            </div>
                            <div class="col-span-2 md:col-span-2 text-right">
                                <span class="font-semibold text-slate-800" x-text="formatCurrency(item.total)"></span>
                            </div>
                        </div>
                    </template>
                @endif
            </div>

            {{-- Totals Section (inside table) --}}
            <div class="border-t-2 border-slate-200 bg-gradient-to-br from-slate-50 to-slate-100">
                @if($mode === 'static')
                    {{-- Subtotal --}}
                    @if($showSubtotal)
                        <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center">
                            <div class="col-span-8 md:col-span-10 text-right">
                                <span class="text-sm font-medium text-slate-600">{{ __('Subtotal') }}</span>
                            </div>
                            <div class="col-span-4 md:col-span-2 text-right">
                                <span class="font-semibold text-slate-800" x-text="formatCurrency(currentSubtotal)"></span>
                            </div>
                        </div>
                    @endif

                    {{-- Discount --}}
                    @if($showDiscount && $discountPercent > 0)
                        <div class="grid grid-cols-12 gap-2 px-4 py-2 items-center border-t border-dashed border-slate-200">
                            <div class="col-span-8 md:col-span-10 text-right">
                                <span class="text-sm text-slate-600">
                                    {{ __('Discount') }}
                                    <span class="text-xs text-green-600 ml-1">(-{{ number_format($discountPercent, 0) }}%)</span>
                                </span>
                            </div>
                            <div class="col-span-4 md:col-span-2 text-right">
                                <span class="font-medium text-green-600">-<span x-text="formatCurrency(currentDiscount)"></span></span>
                            </div>
                        </div>
                    @endif

                    {{-- VAT --}}
                    @if($showVAT)
                        <div class="grid grid-cols-12 gap-2 px-4 py-2 items-center border-t border-dashed border-slate-200">
                            <div class="col-span-8 md:col-span-10 text-right">
                                <span class="text-sm text-slate-600">
                                    {{ __('TVA') }}
                                    <span class="text-xs text-slate-400 ml-1">({{ $vatPercent }}%)</span>
                                </span>
                            </div>
                            <div class="col-span-4 md:col-span-2 text-right">
                                <span class="font-medium text-slate-700">+<span x-text="formatCurrency(currentVAT)"></span></span>
                            </div>
                        </div>
                    @endif

                    {{-- Grand Total --}}
                    @if($showGrandTotal)
                        <div class="grid grid-cols-12 gap-2 px-4 py-4 items-center border-t-2 border-slate-300 bg-slate-100">
                            <div class="col-span-8 md:col-span-10 text-right">
                                <span class="text-base font-bold text-slate-800">{{ __('Total de plată') }}</span>
                            </div>
                            <div class="col-span-4 md:col-span-2 text-right">
                                <span class="text-xl font-bold text-green-600" x-text="formatCurrency(currentGrandTotal)"></span>
                            </div>
                        </div>
                    @endif
                @else
                    {{-- Dynamic totals for admin --}}
                    <div x-show="block.data.showSubtotal !== false" class="grid grid-cols-12 gap-2 px-4 py-3 items-center">
                        <div class="col-span-8 md:col-span-10 text-right">
                            <span class="text-sm font-medium text-slate-600">{{ __('Subtotal') }}</span>
                        </div>
                        <div class="col-span-4 md:col-span-2 text-right">
                            <span class="font-semibold text-slate-800" x-text="formatCurrency(subtotal)"></span>
                        </div>
                    </div>

                    <div x-show="block.data.showDiscount !== false && offer.discount_percent > 0"
                         class="grid grid-cols-12 gap-2 px-4 py-2 items-center border-t border-dashed border-slate-200">
                        <div class="col-span-8 md:col-span-10 text-right">
                            <span class="text-sm text-slate-600">
                                {{ __('Discount') }}
                                <span class="text-xs text-green-600 ml-1">(-<span x-text="offer.discount_percent"></span>%)</span>
                            </span>
                        </div>
                        <div class="col-span-4 md:col-span-2 text-right">
                            <span class="font-medium text-green-600">-<span x-text="formatCurrency(discountAmount)"></span></span>
                        </div>
                    </div>

                    <div x-show="block.data.showVAT !== false"
                         class="grid grid-cols-12 gap-2 px-4 py-2 items-center border-t border-dashed border-slate-200">
                        <div class="col-span-8 md:col-span-10 text-right">
                            <span class="text-sm text-slate-600">
                                {{ __('TVA') }}
                                <span class="text-xs text-slate-400 ml-1">(<span x-text="block.data.vatPercent || 19"></span>%)</span>
                            </span>
                        </div>
                        <div class="col-span-4 md:col-span-2 text-right">
                            <span class="font-medium text-slate-700">+<span x-text="formatCurrency(vatAmount)"></span></span>
                        </div>
                    </div>

                    <div x-show="block.data.showGrandTotal !== false"
                         class="grid grid-cols-12 gap-2 px-4 py-4 items-center border-t-2 border-slate-300 bg-slate-100">
                        <div class="col-span-8 md:col-span-10 text-right">
                            <span class="text-base font-bold text-slate-800">{{ __('Total de plată') }}</span>
                        </div>
                        <div class="col-span-4 md:col-span-2 text-right">
                            <span class="text-xl font-bold text-green-600" x-text="formatCurrency(grandTotal)"></span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
