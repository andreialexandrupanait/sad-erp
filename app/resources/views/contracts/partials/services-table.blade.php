{{-- Services Table for Contract Content --}}
<div style="margin: 20px 0;">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <thead>
            <tr style="background-color: #f8fafc;">
                <th style="border: 1px solid #e2e8f0; padding: 12px 8px; text-align: left; font-weight: 600;">{{ __('Service') }}</th>
                <th style="border: 1px solid #e2e8f0; padding: 12px 8px; text-align: center; font-weight: 600; width: 80px;">{{ __('Qty') }}</th>
                <th style="border: 1px solid #e2e8f0; padding: 12px 8px; text-align: right; font-weight: 600; width: 100px;">{{ __('Unit Price') }}</th>
                @if($showDiscount ?? false)
                <th style="border: 1px solid #e2e8f0; padding: 12px 8px; text-align: center; font-weight: 600; width: 80px;">{{ __('Discount') }}</th>
                @endif
                <th style="border: 1px solid #e2e8f0; padding: 12px 8px; text-align: right; font-weight: 600; width: 120px;">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td style="border: 1px solid #e2e8f0; padding: 10px 8px;">
                    <div style="font-weight: 500;">{{ $item->service?->name ?? $item->name ?? $item->title ?? __('Service') }}</div>
                </td>
                <td style="border: 1px solid #e2e8f0; padding: 10px 8px; text-align: center;">
                    {{ number_format($item->quantity, $item->quantity == intval($item->quantity) ? 0 : 2) }}
                    @if($item->unit && $item->unit !== 'unit')
                    <span style="font-size: 11px; color: #64748b;">{{ $item->unit }}</span>
                    @endif
                </td>
                <td style="border: 1px solid #e2e8f0; padding: 10px 8px; text-align: right;">
                    {{ number_format($item->unit_price, 2) }}
                </td>
                @if($showDiscount ?? false)
                <td style="border: 1px solid #e2e8f0; padding: 10px 8px; text-align: center;">
                    @if($item->discount_percent > 0)
                    -{{ number_format($item->discount_percent, 0) }}%
                    @else
                    -
                    @endif
                </td>
                @endif
                <td style="border: 1px solid #e2e8f0; padding: 10px 8px; text-align: right; font-weight: 500;">
                    {{ number_format($item->total_price, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            @if(isset($subtotal) && isset($discount) && $discount > 0)
            <tr>
                <td colspan="{{ ($showDiscount ?? false) ? 4 : 3 }}" style="border: 1px solid #e2e8f0; padding: 10px 8px; text-align: right;">
                    {{ __('Subtotal') }}:
                </td>
                <td style="border: 1px solid #e2e8f0; padding: 10px 8px; text-align: right;">
                    {{ number_format($subtotal, 2) }} {{ $currency }}
                </td>
            </tr>
            <tr>
                <td colspan="{{ ($showDiscount ?? false) ? 4 : 3 }}" style="border: 1px solid #e2e8f0; padding: 10px 8px; text-align: right; color: #dc2626;">
                    {{ __('Discount') }}:
                </td>
                <td style="border: 1px solid #e2e8f0; padding: 10px 8px; text-align: right; color: #dc2626;">
                    -{{ number_format($discount, 2) }} {{ $currency }}
                </td>
            </tr>
            @endif
            <tr style="background-color: #f1f5f9;">
                <td colspan="{{ ($showDiscount ?? false) ? 4 : 3 }}" style="border: 1px solid #e2e8f0; padding: 12px 8px; text-align: right; font-weight: 700;">
                    {{ __('TOTAL') }}:
                </td>
                <td style="border: 1px solid #e2e8f0; padding: 12px 8px; text-align: right; font-weight: 700; font-size: 16px;">
                    {{ number_format($total, 2) }} {{ $currency }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
