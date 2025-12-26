{{-- Services Block - PDF View --}}
{{-- Variables: block, offer, variables, items --}}

<div style="margin-bottom: 30px;">
    {{-- Section Title --}}
    <h2 style="font-size: 14pt; font-weight: bold; color: #1e293b; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 3px solid #2563eb;">
        {{ $block['data']['title'] ?? __('Proposed Services') }}
    </h2>

    {{-- Zone 1: Selected Services Table --}}
    @if(isset($items) && count($items) > 0)
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
                <tr style="background-color: #f8fafc;">
                    <th style="text-align: left; padding: 10px 12px; font-size: 9pt; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                        {{ __('Service') }}
                    </th>
                    @if($block['data']['showPrices'] ?? true)
                        <th style="text-align: center; padding: 10px 12px; font-size: 9pt; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; width: 80px;">
                            {{ __('Qty') }}
                        </th>
                        <th style="text-align: right; padding: 10px 12px; font-size: 9pt; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; width: 100px;">
                            {{ __('Unit Price') }}
                        </th>
                        <th style="text-align: right; padding: 10px 12px; font-size: 9pt; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; width: 100px;">
                            {{ __('Total') }}
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                    <tr style="{{ $index % 2 === 0 ? '' : 'background-color: #f8fafc;' }}">
                        <td style="padding: 12px; border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                            <p style="font-size: 11pt; font-weight: 600; color: #1e293b; margin: 0 0 4px 0;">
                                {{ $item['title'] ?? __('Untitled Service') }}
                            </p>
                            @if(($block['data']['showDescriptions'] ?? true) && !empty($item['description']))
                                <p style="font-size: 9pt; color: #64748b; margin: 0; line-height: 1.4;">
                                    {{ $item['description'] }}
                                </p>
                            @endif
                        </td>
                        @if($block['data']['showPrices'] ?? true)
                            <td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: center; vertical-align: middle;">
                                <span style="font-size: 10pt; color: #475569;">
                                    {{ number_format($item['quantity'] ?? 1, 2) }} {{ $item['unit'] ?? 'buc' }}
                                </span>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: right; vertical-align: middle;">
                                <span style="font-size: 10pt; color: #475569;">
                                    {{ number_format($item['unit_price'] ?? 0, 2) }} {{ $item['currency'] ?? 'RON' }}
                                </span>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: right; vertical-align: middle;">
                                <span style="font-size: 11pt; font-weight: 600; color: #1e293b;">
                                    {{ number_format($item['total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0)), 2) }} {{ $item['currency'] ?? 'RON' }}
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 10pt; color: #94a3b8; font-style: italic; padding: 20px; text-align: center; background-color: #f8fafc; border-radius: 8px;">
            {{ __('No services included in this offer.') }}
        </p>
    @endif

    {{-- Zone 2: Optional Services (Upsell) --}}
    @if(isset($block['data']['optionalServices']) && count($block['data']['optionalServices']) > 0)
        <div style="margin-top: 25px; margin-bottom: 25px;">
            <h3 style="font-size: 12pt; font-weight: 600; color: #d97706; margin-bottom: 15px; display: flex; align-items: center;">
                <span style="display: inline-block; width: 20px; height: 20px; background-color: #fef3c7; border-radius: 50%; text-align: center; line-height: 20px; margin-right: 8px; font-size: 14pt;">+</span>
                {{ __('Optional Services') }}
            </h3>
            <table style="width: 100%; border-collapse: separate; border-spacing: 10px;">
                <tr>
                    @foreach($block['data']['optionalServices'] as $index => $optService)
                        <td style="width: 50%; vertical-align: top; padding: 15px; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fcd34d; border-radius: 8px;">
                            <p style="font-size: 11pt; font-weight: 600; color: #1e293b; margin: 0 0 8px 0;">
                                {{ $optService['title'] ?? __('Optional Service') }}
                            </p>
                            @if(!empty($optService['description']))
                                <p style="font-size: 9pt; color: #64748b; margin: 0 0 10px 0; line-height: 1.4;">
                                    {{ $optService['description'] }}
                                </p>
                            @endif
                            <p style="font-size: 12pt; font-weight: bold; color: #d97706; margin: 0;">
                                {{ number_format($optService['unit_price'] ?? 0, 2) }} {{ $optService['currency'] ?? 'RON' }}
                            </p>
                        </td>
                        @if(($index + 1) % 2 === 0 && $index < count($block['data']['optionalServices']) - 1)
                            </tr><tr>
                        @endif
                    @endforeach
                </tr>
            </table>
        </div>
    @endif

    {{-- Zone 3: Notes / Precizări --}}
    @if(!empty($block['data']['notes']))
        <div style="margin-top: 25px; padding: 20px; background-color: #f8fafc; border-left: 4px solid #3b82f6; border-radius: 0 8px 8px 0;">
            <h4 style="font-size: 11pt; font-weight: 600; color: #475569; margin: 0 0 10px 0;">
                {{ $block['data']['notesTitle'] ?? __('Notes') }}
            </h4>
            <div style="font-size: 10pt; color: #64748b; line-height: 1.6;">
                {!! $block['data']['notes'] !!}
            </div>
        </div>
    @endif
</div>
