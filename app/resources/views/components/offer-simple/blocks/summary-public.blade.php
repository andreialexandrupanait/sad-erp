{{-- Summary Block - Public View --}}
@php
    $subtotal = collect($items)->sum('total');
    $discountPercent = $offer->discount_percent ?? 0;
    $discountAmount = $subtotal * ($discountPercent / 100);
    $netTotal = $subtotal - $discountAmount;
    $vatPercent = $block['data']['vatPercent'] ?? 19;
    $vatAmount = $netTotal * ($vatPercent / 100);
    $grandTotal = $netTotal + $vatAmount;
    $currency = $offer->currency ?? 'EUR';
@endphp

<div class="mb-8">
    @if(!empty($block['data']['heading']))
        <h2 class="text-xl font-semibold text-slate-800 border-b-2 border-green-500 pb-2 mb-4 inline-block">
            {{ $block['data']['heading'] }}
        </h2>
    @endif

    <div class="bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200 rounded-xl p-6 max-w-md ml-auto">
        <div class="space-y-3">
            {{-- Subtotal --}}
            @if($block['data']['showSubtotal'] ?? true)
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-slate-600">{{ __('Subtotal') }}</span>
                    <span class="text-base font-medium text-slate-800">
                        {{ number_format($subtotal, 2, ',', '.') }}
                        <span class="text-xs text-slate-500 ml-1">{{ $currency }}</span>
                    </span>
                </div>
            @endif

            {{-- Discount --}}
            @if(($block['data']['showDiscount'] ?? true) && $discountPercent > 0)
                <div class="flex items-center justify-between py-2 border-t border-dashed border-slate-200">
                    <span class="text-sm text-slate-600">
                        {{ __('Discount') }}
                        <span class="text-xs text-green-600 ml-1">(-{{ $discountPercent }}%)</span>
                    </span>
                    <span class="text-base font-medium text-green-600">
                        -{{ number_format($discountAmount, 2, ',', '.') }}
                        <span class="text-xs text-slate-500 ml-1">{{ $currency }}</span>
                    </span>
                </div>

                {{-- Net Total --}}
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-slate-600">{{ __('Net Total') }}</span>
                    <span class="text-base font-medium text-slate-800">
                        {{ number_format($netTotal, 2, ',', '.') }}
                        <span class="text-xs text-slate-500 ml-1">{{ $currency }}</span>
                    </span>
                </div>
            @endif

            {{-- VAT --}}
            @if($block['data']['showVAT'] ?? true)
                <div class="flex items-center justify-between py-2 border-t border-dashed border-slate-200">
                    <span class="text-sm text-slate-600">
                        {{ __('VAT') }}
                        <span class="text-xs text-slate-400 ml-1">({{ $vatPercent }}%)</span>
                    </span>
                    <span class="text-base font-medium text-slate-700">
                        +{{ number_format($vatAmount, 2, ',', '.') }}
                        <span class="text-xs text-slate-500 ml-1">{{ $currency }}</span>
                    </span>
                </div>
            @endif

            {{-- Grand Total --}}
            @if($block['data']['showGrandTotal'] ?? true)
                <div class="flex items-center justify-between py-3 border-t-2 border-slate-300 mt-2">
                    <span class="text-base font-semibold text-slate-800">{{ __('Grand Total') }}</span>
                    <span class="text-xl font-bold text-blue-600">
                        {{ number_format($grandTotal, 2, ',', '.') }}
                        <span class="text-sm font-medium text-slate-600 ml-1">{{ $currency }}</span>
                    </span>
                </div>
            @endif
        </div>

        {{-- Services Count Note --}}
        <div class="mt-4 pt-3 border-t border-slate-200 flex items-center gap-2 text-xs text-slate-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span>{{ count($items) }} {{ __('service(s) included') }}</span>
        </div>
    </div>
</div>
