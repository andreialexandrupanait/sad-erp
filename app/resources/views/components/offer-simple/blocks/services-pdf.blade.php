{{-- Services Block - PDF View --}}
<div style="margin-bottom: 30px;">
    @if(!empty($block['data']['heading']))
        <h2 style="font-size: 14pt; font-weight: bold; color: #1e293b; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 3px solid #16a34a; display: inline-block;">
            {{ $block['data']['heading'] }}
        </h2>
    @endif

    @if(!empty($items) && count($items) > 0)
        <table style="width: 100%; border-collapse: collapse; font-size: 10pt;">
            {{-- Table Header --}}
            <thead>
                <tr style="background-color: #f1f5f9;">
                    <th style="padding: 10px 12px; text-align: left; font-weight: 600; color: #475569; border-bottom: 2px solid #e2e8f0; width: 45%;">
                        {{ __('Service') }}
                    </th>
                    <th style="padding: 10px 12px; text-align: center; font-weight: 600; color: #475569; border-bottom: 2px solid #e2e8f0; width: 15%;">
                        {{ __('Quantity') }}
                    </th>
                    @if($block['data']['showPrices'] ?? true)
                        <th style="padding: 10px 12px; text-align: right; font-weight: 600; color: #475569; border-bottom: 2px solid #e2e8f0; width: 18%;">
                            {{ __('Unit Price') }}
                        </th>
                        <th style="padding: 10px 12px; text-align: right; font-weight: 600; color: #475569; border-bottom: 2px solid #e2e8f0; width: 22%;">
                            {{ __('Total') }}
                        </th>
                    @endif
                </tr>
            </thead>

            {{-- Table Body --}}
            <tbody>
                @foreach($items as $index => $item)
                    <tr style="{{ $index % 2 === 1 ? 'background-color: #f8fafc;' : '' }}">
                        {{-- Service Title --}}
                        <td style="padding: 12px; border-bottom: 1px solid #e2e8f0; vertical-align: middle;">
                            <div style="font-weight: 500; color: #1e293b;">{{ $item['title'] ?? '' }}</div>
                        </td>

                        {{-- Quantity --}}
                        <td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: center; vertical-align: middle;">
                            <span style="color: #334155;">{{ $item['quantity'] ?? 1 }}</span>
                            <span style="color: #94a3b8; font-size: 9pt; margin-left: 2px;">{{ $item['unit'] ?? 'buc' }}</span>
                        </td>

                        @if($block['data']['showPrices'] ?? true)
                            {{-- Unit Price --}}
                            <td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: right; vertical-align: top;">
                                <span style="color: #334155;">{{ number_format($item['unit_price'] ?? 0, 2, ',', '.') }}</span>
                                <span style="color: #94a3b8; font-size: 9pt; margin-left: 2px;">{{ $offer->currency ?? 'EUR' }}</span>
                            </td>

                            {{-- Total --}}
                            <td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: right; vertical-align: top;">
                                <span style="font-weight: 600; color: #1e293b;">{{ number_format($item['total'] ?? 0, 2, ',', '.') }}</span>
                                <span style="color: #64748b; font-size: 9pt; margin-left: 2px;">{{ $offer->currency ?? 'EUR' }}</span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="padding: 30px; text-align: center; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
            <p style="color: #64748b; font-size: 10pt; margin: 0;">{{ __('No services listed') }}</p>
        </div>
    @endif
</div>
