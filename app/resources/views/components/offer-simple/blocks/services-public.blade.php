{{-- Services Block - Public View --}}
<div class="mb-8">
    @if(!empty($block['data']['heading']))
        <h2 class="text-xl font-semibold text-slate-800 border-b-2 border-green-500 pb-2 mb-4 inline-block">
            {{ $block['data']['heading'] }}
        </h2>
    @endif

    @if(!empty($items) && count($items) > 0)
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            {{-- Table Header --}}
            <div class="grid grid-cols-12 gap-4 bg-slate-50 px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wide border-b border-slate-200">
                <div class="col-span-5">{{ __('Service') }}</div>
                <div class="col-span-2 text-center">{{ __('Quantity') }}</div>
                @if($block['data']['showPrices'] ?? true)
                    <div class="col-span-2 text-right">{{ __('Unit Price') }}</div>
                    <div class="col-span-3 text-right">{{ __('Total') }}</div>
                @endif
            </div>

            {{-- Service Rows --}}
            @foreach($items as $item)
                <div class="grid grid-cols-12 gap-4 px-4 py-4 border-b border-slate-100 last:border-b-0 hover:bg-slate-50/50 transition-colors items-center">
                    {{-- Service Title --}}
                    <div class="col-span-5">
                        <h4 class="text-sm font-medium text-slate-800">{{ $item['title'] ?? '' }}</h4>
                    </div>

                    {{-- Quantity --}}
                    <div class="col-span-2 text-center">
                        <span class="text-sm text-slate-700">{{ $item['quantity'] ?? 1 }}</span>
                        <span class="text-xs text-slate-400 ml-1">{{ $item['unit'] ?? 'buc' }}</span>
                    </div>

                    @if($block['data']['showPrices'] ?? true)
                        {{-- Unit Price --}}
                        <div class="col-span-2 text-right">
                            <span class="text-sm text-slate-700">
                                {{ number_format($item['unit_price'] ?? 0, 2, ',', '.') }}
                                <span class="text-xs text-slate-400">{{ $offer->currency ?? 'EUR' }}</span>
                            </span>
                        </div>

                        {{-- Total --}}
                        <div class="col-span-3 text-right">
                            <span class="text-sm font-semibold text-slate-800">
                                {{ number_format($item['total'] ?? 0, 2, ',', '.') }}
                                <span class="text-xs font-normal text-slate-500">{{ $offer->currency ?? 'EUR' }}</span>
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-8 text-center">
            <p class="text-sm text-slate-500">{{ __('No services listed') }}</p>
        </div>
    @endif
</div>
