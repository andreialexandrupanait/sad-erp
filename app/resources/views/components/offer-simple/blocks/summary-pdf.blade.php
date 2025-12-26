{{-- Summary Block - PDF View --}}
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

<div style="margin-bottom: 30px;">
    @if(!empty($block['data']['heading']))
        <h2 style="font-size: 14pt; font-weight: bold; color: #1e293b; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 3px solid #16a34a; display: inline-block;">
            {{ $block['data']['heading'] }}
        </h2>
    @endif

    <table style="width: 300px; margin-left: auto; border-collapse: collapse; font-size: 10pt; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0; border-radius: 8px;">
        {{-- Subtotal --}}
        @if($block['data']['showSubtotal'] ?? true)
            <tr>
                <td style="padding: 12px 15px; color: #475569;">{{ __('Subtotal') }}</td>
                <td style="padding: 12px 15px; text-align: right; font-weight: 500; color: #1e293b;">
                    {{ number_format($subtotal, 2, ',', '.') }}
                    <span style="font-size: 9pt; color: #64748b; margin-left: 3px;">{{ $currency }}</span>
                </td>
            </tr>
        @endif

        {{-- Discount --}}
        @if(($block['data']['showDiscount'] ?? true) && $discountPercent > 0)
            <tr style="border-top: 1px dashed #e2e8f0;">
                <td style="padding: 12px 15px; color: #475569;">
                    {{ __('Discount') }}
                    <span style="font-size: 9pt; color: #16a34a; margin-left: 3px;">(-{{ $discountPercent }}%)</span>
                </td>
                <td style="padding: 12px 15px; text-align: right; font-weight: 500; color: #16a34a;">
                    -{{ number_format($discountAmount, 2, ',', '.') }}
                    <span style="font-size: 9pt; color: #64748b; margin-left: 3px;">{{ $currency }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 12px 15px; color: #475569;">{{ __('Net Total') }}</td>
                <td style="padding: 12px 15px; text-align: right; font-weight: 500; color: #1e293b;">
                    {{ number_format($netTotal, 2, ',', '.') }}
                    <span style="font-size: 9pt; color: #64748b; margin-left: 3px;">{{ $currency }}</span>
                </td>
            </tr>
        @endif

        {{-- VAT --}}
        @if($block['data']['showVAT'] ?? true)
            <tr style="border-top: 1px dashed #e2e8f0;">
                <td style="padding: 12px 15px; color: #475569;">
                    {{ __('VAT') }}
                    <span style="font-size: 9pt; color: #94a3b8; margin-left: 3px;">({{ $vatPercent }}%)</span>
                </td>
                <td style="padding: 12px 15px; text-align: right; font-weight: 500; color: #334155;">
                    +{{ number_format($vatAmount, 2, ',', '.') }}
                    <span style="font-size: 9pt; color: #64748b; margin-left: 3px;">{{ $currency }}</span>
                </td>
            </tr>
        @endif

        {{-- Grand Total --}}
        @if($block['data']['showGrandTotal'] ?? true)
            <tr style="border-top: 2px solid #cbd5e1;">
                <td style="padding: 15px; font-weight: 600; color: #1e293b; font-size: 11pt;">{{ __('Grand Total') }}</td>
                <td style="padding: 15px; text-align: right; font-weight: bold; color: #2563eb; font-size: 14pt;">
                    {{ number_format($grandTotal, 2, ',', '.') }}
                    <span style="font-size: 10pt; font-weight: 500; color: #475569; margin-left: 3px;">{{ $currency }}</span>
                </td>
            </tr>
        @endif
    </table>

    {{-- Services Count Note --}}
    <p style="text-align: right; font-size: 9pt; color: #64748b; margin-top: 10px;">
        {{ count($items) }} {{ __('service(s) included') }}
    </p>
</div>
