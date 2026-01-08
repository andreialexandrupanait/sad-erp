{{-- Summary Block - PDF View (Plutio Style with Columns) --}}
{{-- Variables: block, offer, variables, items --}}

<div style="margin-bottom: 30px;">
    {{-- Section Title --}}
    <h2 style="font-size: 14pt; font-weight: bold; color: #1e293b; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 3px solid #16a34a;">
        {{ $block['data']['title'] ?? __('Investment Summary') }}
    </h2>

    {{-- Summary Table with Columns: Service | Quantity | Unit Price | Total --}}
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0;">
        {{-- Table Header --}}
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th style="padding: 12px 15px; text-align: left; font-size: 9pt; font-weight: 600; color: #64748b; border-bottom: 2px solid #e2e8f0; width: 50%;">
                    {{ __('Service') }}
                </th>
                <th style="padding: 12px 15px; text-align: center; font-size: 9pt; font-weight: 600; color: #64748b; border-bottom: 2px solid #e2e8f0; width: 15%;">
                    {{ __('Quantity') }}
                </th>
                <th style="padding: 12px 15px; text-align: right; font-size: 9pt; font-weight: 600; color: #64748b; border-bottom: 2px solid #e2e8f0; width: 17%;">
                    {{ __('Unit Price') }}
                </th>
                <th style="padding: 12px 15px; text-align: right; font-size: 9pt; font-weight: 600; color: #64748b; border-bottom: 2px solid #e2e8f0; width: 18%;">
                    {{ __('Total') }}
                </th>
            </tr>
        </thead>
        <tbody>
            {{-- Services Rows --}}
            @if(isset($items) && count($items) > 0)
                @foreach($items as $index => $item)
                    <tr style="{{ $index % 2 === 0 ? 'background-color: #ffffff;' : 'background-color: #f8fafc;' }}">
                        {{-- Service Name --}}
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                            <span style="font-size: 10pt; color: #1e293b; font-weight: 500;">
                                {{ $item['title'] ?? __('Untitled Service') }}
                            </span>
                            @if(($block['data']['showDescriptions'] ?? false) && !empty($item['description']))
                                <p style="font-size: 9pt; color: #64748b; margin: 4px 0 0 0; line-height: 1.4;">
                                    {{ Str::limit($item['description'], 100) }}
                                </p>
                            @endif
                        </td>
                        {{-- Quantity --}}
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: center; vertical-align: top;">
                            <span style="font-size: 10pt; color: #334155;">
                                {{ number_format($item['quantity'] ?? 1, 2) }}
                            </span>
                            <span style="font-size: 9pt; color: #94a3b8; margin-left: 3px;">
                                {{ $item['unit'] ?? 'buc' }}
                            </span>
                        </td>
                        {{-- Unit Price --}}
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: right; vertical-align: top;">
                            <span style="font-size: 10pt; color: #334155;">
                                {{ number_format($item['unit_price'] ?? 0, 2) }} {{ $item['currency'] ?? ($offer->currency ?? 'RON') }}
                            </span>
                        </td>
                        {{-- Total --}}
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: right; vertical-align: top;">
                            <span style="font-size: 10pt; font-weight: 600; color: #1e293b;">
                                {{ number_format($item['total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0)), 2) }} {{ $item['currency'] ?? ($offer->currency ?? 'RON') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="padding: 20px; text-align: center; color: #94a3b8; font-style: italic;">
                        {{ __('No services included') }}
                    </td>
                </tr>
            @endif
        </tbody>

        {{-- Totals Footer --}}
        <tfoot>
            {{-- Subtotal --}}
            @if($block['data']['showSubtotal'] ?? true)
                @php
                    $subtotal = collect($items ?? [])->sum(function($item) {
                        return $item['total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0));
                    });
                @endphp
                <tr style="background-color: #f1f5f9;">
                    <td colspan="3" style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #e2e8f0;">
                        <span style="font-size: 10pt; color: #64748b;">{{ __('Subtotal') }}</span>
                    </td>
                    <td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #e2e8f0;">
                        <span style="font-size: 10pt; font-weight: 600; color: #1e293b;">
                            {{ number_format($subtotal, 2) }} {{ $offer->currency ?? 'RON' }}
                        </span>
                    </td>
                </tr>
            @endif

            {{-- Discount --}}
            @if(($block['data']['showDiscount'] ?? true) && ($offer->discount_percent ?? 0) > 0)
                @php
                    $subtotal = $subtotal ?? collect($items ?? [])->sum(function($item) {
                        return $item['total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0));
                    });
                    $discountAmount = $subtotal * (($offer->discount_percent ?? 0) / 100);
                @endphp
                <tr style="background-color: #f0fdf4;">
                    <td colspan="3" style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #e2e8f0;">
                        <span style="font-size: 10pt; color: #16a34a;">{{ __('Discount') }}</span>
                        <span style="font-size: 8pt; background-color: #dcfce7; color: #166534; padding: 2px 6px; border-radius: 10px; margin-left: 5px;">
                            {{ $offer->discount_percent }}%
                        </span>
                    </td>
                    <td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #e2e8f0;">
                        <span style="font-size: 10pt; font-weight: 600; color: #16a34a;">
                            -{{ number_format($discountAmount, 2) }} {{ $offer->currency ?? 'RON' }}
                        </span>
                    </td>
                </tr>
            @endif

            {{-- VAT --}}
            @if($block['data']['showVAT'] ?? false)
                @php
                    $subtotal = $subtotal ?? collect($items ?? [])->sum(function($item) {
                        return $item['total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0));
                    });
                    $discountAmount = $discountAmount ?? 0;
                    $vatPercent = $block['data']['vatPercent'] ?? 19;
                    $vatBase = $subtotal - $discountAmount;
                    $vatAmount = $vatBase * ($vatPercent / 100);
                @endphp
                <tr style="background-color: #f1f5f9;">
                    <td colspan="3" style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #e2e8f0;">
                        <span style="font-size: 10pt; color: #64748b;">{{ __('VAT') }} ({{ $vatPercent }}%)</span>
                    </td>
                    <td style="padding: 12px 15px; text-align: right; border-bottom: 1px solid #e2e8f0;">
                        <span style="font-size: 10pt; font-weight: 600; color: #1e293b;">
                            {{ number_format($vatAmount, 2) }} {{ $offer->currency ?? 'RON' }}
                        </span>
                    </td>
                </tr>
            @endif

            {{-- Grand Total --}}
            @if($block['data']['showGrandTotal'] ?? true)
                @php
                    $subtotal = $subtotal ?? collect($items ?? [])->sum(function($item) {
                        return $item['total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0));
                    });
                    $discountAmount = $discountAmount ?? 0;
                    $vatAmount = $vatAmount ?? 0;
                    $grandTotal = $subtotal - $discountAmount + $vatAmount;
                @endphp
                <tr style="background-color: #1e293b;">
                    <td colspan="3" style="padding: 15px; text-align: right;">
                        <span style="font-size: 12pt; font-weight: bold; color: #ffffff;">{{ __('Total') }}</span>
                    </td>
                    <td style="padding: 15px; text-align: right;">
                        <span style="font-size: 14pt; font-weight: bold; color: #ffffff;">
                            {{ number_format($grandTotal, 2) }} {{ $offer->currency ?? 'RON' }}
                        </span>
                    </td>
                </tr>
            @endif
        </tfoot>
    </table>
</div>
