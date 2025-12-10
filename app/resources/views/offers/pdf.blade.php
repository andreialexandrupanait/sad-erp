<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('Offer') }} {{ $offer->offer_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 20px;
        }

        /* Header block styles */
        .header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header-row {
            display: table;
            width: 100%;
        }
        .client-info, .company-info {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        .client-box {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 5px;
        }
        .client-label {
            font-size: 9pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .client-name {
            font-size: 12pt;
            font-weight: bold;
            color: #1e293b;
        }
        .company-info {
            text-align: right;
            padding-left: 20px;
        }
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 9pt;
            color: #64748b;
        }
        .offer-number-box {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }
        .offer-number {
            font-size: 16pt;
            font-weight: bold;
            color: #1e293b;
        }
        .offer-dates {
            font-size: 9pt;
            color: #64748b;
            margin-top: 5px;
        }

        /* Items/services table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 10px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            color: #475569;
        }
        .items-table td {
            border: 1px solid #e2e8f0;
            padding: 10px;
            font-size: 10pt;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .item-title {
            font-weight: bold;
            color: #1e293b;
        }
        .item-description {
            font-size: 9pt;
            color: #64748b;
            margin-top: 3px;
        }
        .currency-badge {
            font-size: 8pt;
            color: #d97706;
            background-color: #fef3c7;
            padding: 2px 5px;
            border-radius: 3px;
            margin-left: 5px;
        }

        /* Summary/totals */
        .summary-section {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 15px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table th {
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            color: #64748b;
        }
        .summary-table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            font-size: 9pt;
        }
        .totals {
            width: 300px;
            margin-left: auto;
            margin-top: 15px;
        }
        .totals-row {
            display: table;
            width: 100%;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals-row.total {
            border-bottom: 2px solid #1e293b;
        }
        .totals-label, .totals-value {
            display: table-cell;
            padding: 8px;
            font-size: 10pt;
        }
        .totals-label {
            text-align: left;
            color: #64748b;
        }
        .totals-value {
            text-align: right;
            font-weight: bold;
        }
        .totals-row.discount .totals-value {
            color: #dc2626;
        }
        .totals-row.total .totals-label,
        .totals-row.total .totals-value {
            font-size: 12pt;
            color: #1e293b;
        }

        /* Content/text block */
        .content-block {
            margin-bottom: 20px;
        }
        .content-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .content-body {
            font-size: 10pt;
            color: #475569;
        }

        /* Terms block */
        .terms-section {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
        }
        .terms-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 10px;
        }
        .terms-content {
            font-size: 9pt;
            color: #64748b;
        }

        /* Signature block */
        .signature-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .signature-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 10px;
        }
        .signature-note {
            font-size: 9pt;
            color: #64748b;
            margin-bottom: 20px;
        }
        .signature-boxes {
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            padding-right: 20px;
        }
        .signature-box:last-child {
            padding-right: 0;
            padding-left: 20px;
        }
        .signature-line {
            border-bottom: 2px solid #cbd5e1;
            height: 40px;
            margin-bottom: 5px;
        }
        .signature-label {
            font-size: 9pt;
            color: #64748b;
        }

        /* Table block (custom tables) */
        .custom-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .custom-table th {
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            color: #475569;
        }
        .custom-table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            font-size: 9pt;
        }
        .table-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 10px;
        }

        /* Quote block */
        .quote-block {
            border-left: 4px solid #3b82f6;
            background-color: #eff6ff;
            padding: 15px;
            border-radius: 0 5px 5px 0;
            margin-bottom: 20px;
        }
        .quote-content {
            font-style: italic;
            color: #1e40af;
            font-size: 10pt;
        }
        .quote-author {
            font-size: 9pt;
            color: #3b82f6;
            margin-top: 10px;
        }

        /* Columns block */
        .columns-block {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }
        .column:last-child {
            padding-right: 0;
            padding-left: 15px;
        }
        .column-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .column-content {
            font-size: 9pt;
            color: #64748b;
        }

        /* Page break */
        .page-break {
            page-break-after: always;
        }

        /* Divider */
        .divider {
            border-top: 2px solid #e2e8f0;
            margin: 20px 0;
        }

        /* Spacer */
        .spacer {
            height: 20px;
        }

        /* Image block */
        .image-block {
            text-align: center;
            margin-bottom: 20px;
        }
        .image-block img {
            max-width: 100%;
            height: auto;
        }
        .image-caption {
            font-size: 9pt;
            color: #64748b;
            margin-top: 5px;
        }

        /* Block style classes */
        .mt-none { margin-top: 0; }
        .mt-sm { margin-top: 10px; }
        .mt-md { margin-top: 20px; }
        .mt-lg { margin-top: 40px; }
        .mt-xl { margin-top: 60px; }

        .mb-none { margin-bottom: 0; }
        .mb-sm { margin-bottom: 10px; }
        .mb-md { margin-bottom: 20px; }
        .mb-lg { margin-bottom: 40px; }
        .mb-xl { margin-bottom: 60px; }

        .bg-slate { background-color: #f8fafc; }
        .bg-blue { background-color: #eff6ff; }
        .bg-green { background-color: #f0fdf4; }
        .bg-amber { background-color: #fffbeb; }

        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .border-subtle { border: 1px solid #e2e8f0; border-radius: 5px; }
        .border-prominent { border: 2px solid #94a3b8; border-radius: 5px; }

        .p-none { padding: 0; }
        .p-sm { padding: 10px; }
        .p-md { padding: 20px; }
        .p-lg { padding: 30px; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        @php
            $blocks = $offer->blocks ?? [];
            $items = $offer->items;
            $organization = $offer->organization;
            $client = $offer->client;
        @endphp

        @if(empty($blocks))
            {{-- Fallback: Legacy layout when no blocks defined --}}
            <div class="header">
                <div class="header-row">
                    <div class="client-info">
                        <div class="client-box">
                            <div class="client-label">{{ __('TO') }}:</div>
                            @if($client)
                                <div class="client-name">{{ $client->display_name }}</div>
                                @if($client->contact_person)
                                    <p>{{ __('Attn') }}: {{ $client->contact_person }}</p>
                                @endif
                                @if($client->address)
                                    <p>{{ $client->address }}</p>
                                @endif
                                @if($client->tax_id)
                                    <p>{{ __('Tax ID') }}: {{ $client->tax_id }}</p>
                                @endif
                                @if($client->email)
                                    <p>{{ $client->email }}</p>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="company-info">
                        <div class="company-name">{{ $organization?->name ?? config('app.name') }}</div>
                        <div class="company-details">
                            @if($organization?->address)
                                <p>{{ $organization->address }}</p>
                            @endif
                            @if($organization?->tax_id)
                                <p>{{ __('Tax ID') }}: {{ $organization->tax_id }}</p>
                            @endif
                            @if($organization?->phone)
                                <p>{{ $organization->phone }}</p>
                            @endif
                            @if($organization?->email)
                                <p>{{ $organization->email }}</p>
                            @endif
                        </div>
                        <div class="offer-number-box">
                            <div class="offer-number">{{ $offer->offer_number }}</div>
                            <div class="offer-dates">
                                <p>{{ __('Date') }}: {{ $offer->created_at?->format('d.m.Y') ?? now()->format('d.m.Y') }}</p>
                                @if($offer->valid_until)
                                    <p>{{ __('Valid until') }}: {{ $offer->valid_until->format('d.m.Y') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Services table --}}
            @if($items->isNotEmpty())
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 45%;">{{ __('Description') }}</th>
                            <th style="width: 10%;" class="text-center">{{ __('Qty') }}</th>
                            <th style="width: 10%;" class="text-center">{{ __('Unit') }}</th>
                            <th style="width: 15%;" class="text-right">{{ __('Unit Price') }}</th>
                            <th style="width: 15%;" class="text-right">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <div class="item-title">{{ $item->title }}</div>
                                    @if($item->description)
                                        <div class="item-description">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-center">{{ __($item->unit ?? 'pcs') }}</td>
                                <td class="text-right">
                                    {{ number_format($item->unit_price, 2) }}
                                    @if($item->original_currency && $item->original_currency !== $offer->currency)
                                        <span class="currency-badge">{{ $item->original_currency }}</span>
                                    @endif
                                </td>
                                <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Totals --}}
            <div class="totals">
                <div class="totals-row">
                    <span class="totals-label">{{ __('Subtotal') }}</span>
                    <span class="totals-value">{{ number_format($offer->subtotal, 2) }} {{ $offer->currency }}</span>
                </div>
                @if($offer->discount_percent > 0)
                    <div class="totals-row discount">
                        <span class="totals-label">{{ __('Discount') }} ({{ $offer->discount_percent }}%)</span>
                        <span class="totals-value">-{{ number_format($offer->discount_amount, 2) }} {{ $offer->currency }}</span>
                    </div>
                @endif
                <div class="totals-row total">
                    <span class="totals-label">{{ __('Total') }}</span>
                    <span class="totals-value">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                </div>
            </div>

        @else
            {{-- Blocks-based layout --}}
            @foreach($blocks as $block)
                @if($block['visible'] ?? true)
                    @php
                        $style = $block['data']['style'] ?? [];
                        $styleClasses = [];
                        if (!empty($style['marginTop'])) $styleClasses[] = 'mt-' . $style['marginTop'];
                        if (!empty($style['marginBottom'])) $styleClasses[] = 'mb-' . $style['marginBottom'];
                        if (!empty($style['background']) && $style['background'] !== 'white') {
                            $bgMap = ['slate-50' => 'bg-slate', 'blue-50' => 'bg-blue', 'green-50' => 'bg-green', 'amber-50' => 'bg-amber'];
                            $styleClasses[] = $bgMap[$style['background']] ?? '';
                        }
                        if (!empty($style['textAlign'])) $styleClasses[] = 'text-' . $style['textAlign'];
                        if (!empty($style['border'])) $styleClasses[] = 'border-' . $style['border'];
                        if (!empty($style['padding'])) $styleClasses[] = 'p-' . $style['padding'];
                        $styleClass = implode(' ', $styleClasses);
                    @endphp

                    @switch($block['type'])
                        @case('header')
                            <div class="header {{ $styleClass }}">
                                <div class="header-row">
                                    <div class="client-info">
                                        <div class="client-box">
                                            <div class="client-label">{{ __('TO') }}:</div>
                                            @if($client)
                                                <div class="client-name">{{ $client->display_name }}</div>
                                                @if($client->contact_person)
                                                    <p>{{ __('Attn') }}: {{ $client->contact_person }}</p>
                                                @endif
                                                @if($client->address)
                                                    <p>{{ $client->address }}</p>
                                                @endif
                                                @if($client->tax_id)
                                                    <p>{{ __('Tax ID') }}: {{ $client->tax_id }}</p>
                                                @endif
                                                @if($client->email)
                                                    <p>{{ $client->email }}</p>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    <div class="company-info">
                                        <div class="company-name">{{ $organization?->name ?? config('app.name') }}</div>
                                        <div class="company-details">
                                            @if($organization?->address)
                                                <p>{{ $organization->address }}</p>
                                            @endif
                                            @if($organization?->tax_id)
                                                <p>{{ __('Tax ID') }}: {{ $organization->tax_id }}</p>
                                            @endif
                                            @if($organization?->phone)
                                                <p>{{ $organization->phone }}</p>
                                            @endif
                                            @if($organization?->email)
                                                <p>{{ $organization->email }}</p>
                                            @endif
                                        </div>
                                        <div class="offer-number-box">
                                            <div class="offer-number">{{ $offer->offer_number }}</div>
                                            <div class="offer-dates">
                                                <p>{{ __('Date') }}: {{ $offer->created_at?->format('d.m.Y') ?? now()->format('d.m.Y') }}</p>
                                                @if($offer->valid_until)
                                                    <p>{{ __('Valid until') }}: {{ $offer->valid_until->format('d.m.Y') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @break

                        @case('services')
                            @if($items->isNotEmpty())
                                <div class="{{ $styleClass }}">
                                    <h3 style="font-size: 12pt; font-weight: bold; color: #1e293b; margin-bottom: 15px;">{{ __('Proposed Services') }}</h3>
                                    <table class="items-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%;">#</th>
                                                <th style="width: 45%;">{{ __('Description') }}</th>
                                                <th style="width: 10%;" class="text-center">{{ __('Qty') }}</th>
                                                <th style="width: 10%;" class="text-center">{{ __('Unit') }}</th>
                                                <th style="width: 15%;" class="text-right">{{ __('Unit Price') }}</th>
                                                <th style="width: 15%;" class="text-right">{{ __('Total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $index => $item)
                                                <tr>
                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="item-title">{{ $item->title }}</div>
                                                        @if($item->description)
                                                            <div class="item-description">{{ $item->description }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                                    <td class="text-center">{{ __($item->unit ?? 'pcs') }}</td>
                                                    <td class="text-right">
                                                        {{ number_format($item->unit_price, 2) }}
                                                        @if($item->original_currency && $item->original_currency !== $offer->currency)
                                                            <span class="currency-badge">{{ $item->original_currency }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                            @break

                        @case('summary')
                            <div class="summary-section {{ $styleClass }}">
                                <div class="summary-title">{{ __('Investment Summary') }}</div>
                                <table class="summary-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50%;">{{ __('Service') }}</th>
                                            <th style="width: 15%;" class="text-right">{{ __('Qty') }}</th>
                                            <th style="width: 15%;" class="text-right">{{ __('Unit Price') }}</th>
                                            <th style="width: 20%;" class="text-right">{{ __('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            <tr>
                                                <td>{{ $item->title }}</td>
                                                <td class="text-right">{{ number_format($item->quantity, 2) }} {{ __($item->unit ?? 'pcs') }}</td>
                                                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                                <td class="text-right" style="font-weight: bold;">{{ number_format($item->total_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="totals">
                                    <div class="totals-row">
                                        <span class="totals-label">{{ __('Subtotal') }}</span>
                                        <span class="totals-value">{{ number_format($offer->subtotal, 2) }} {{ $offer->currency }}</span>
                                    </div>
                                    @if($offer->discount_percent > 0)
                                        <div class="totals-row discount">
                                            <span class="totals-label">{{ __('Discount') }} ({{ $offer->discount_percent }}%)</span>
                                            <span class="totals-value">-{{ number_format($offer->discount_amount, 2) }} {{ $offer->currency }}</span>
                                        </div>
                                    @endif
                                    <div class="totals-row total">
                                        <span class="totals-label">{{ __('TOTAL') }}</span>
                                        <span class="totals-value">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                                    </div>
                                </div>
                            </div>
                            @break

                        @case('content')
                            <div class="content-block {{ $styleClass }}">
                                @if(!empty($block['data']['title']))
                                    <div class="content-title">{{ $block['data']['title'] }}</div>
                                @endif
                                @if(!empty($block['data']['content']))
                                    <div class="content-body">{!! $block['data']['content'] !!}</div>
                                @endif
                            </div>
                            @break

                        @case('terms')
                            @if(!empty($block['data']['content']))
                                <div class="terms-section {{ $styleClass }}">
                                    <div class="terms-title">{{ __('Terms and Conditions') }}</div>
                                    <div class="terms-content">{!! nl2br(e($block['data']['content'])) !!}</div>
                                </div>
                            @endif
                            @break

                        @case('signature')
                            <div class="signature-section {{ $styleClass }}">
                                <div class="signature-title">{{ __('Offer Acceptance') }}</div>
                                <div class="signature-note">
                                    {{ __('By signing below, I confirm that I have read and agree to the services and conditions described in this offer.') }}
                                </div>
                                <div class="signature-boxes">
                                    <div class="signature-box">
                                        <div class="signature-line"></div>
                                        <div class="signature-label">{{ __('Client Signature') }}</div>
                                        <div class="signature-label">{{ __('Date') }}: _______________</div>
                                    </div>
                                    <div class="signature-box">
                                        <div class="signature-line"></div>
                                        <div class="signature-label">{{ __('Provider Signature') }}</div>
                                        <div class="signature-label">{{ __('Date') }}: _______________</div>
                                    </div>
                                </div>
                            </div>
                            @break

                        @case('table')
                            <div class="{{ $styleClass }}">
                                @if(!empty($block['data']['title']))
                                    <div class="table-title">{{ $block['data']['title'] }}</div>
                                @endif
                                @if(!empty($block['data']['columns']) && !empty($block['data']['rows']))
                                    <table class="custom-table">
                                        <thead>
                                            <tr>
                                                @foreach($block['data']['columns'] as $col)
                                                    <th>{{ $col }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($block['data']['rows'] as $row)
                                                <tr>
                                                    @foreach($row as $cell)
                                                        <td>{{ $cell }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                            @break

                        @case('quote')
                            <div class="quote-block {{ $styleClass }}">
                                @if(!empty($block['data']['content']))
                                    <div class="quote-content">{{ $block['data']['content'] }}</div>
                                @endif
                                @if(!empty($block['data']['author']))
                                    <div class="quote-author">{{ $block['data']['author'] }}</div>
                                @endif
                            </div>
                            @break

                        @case('columns')
                            <div class="columns-block {{ $styleClass }}">
                                <div class="column">
                                    @if(!empty($block['data']['leftTitle']))
                                        <div class="column-title">{{ $block['data']['leftTitle'] }}</div>
                                    @endif
                                    @if(!empty($block['data']['leftContent']))
                                        <div class="column-content">{!! nl2br(e($block['data']['leftContent'])) !!}</div>
                                    @endif
                                </div>
                                <div class="column">
                                    @if(!empty($block['data']['rightTitle']))
                                        <div class="column-title">{{ $block['data']['rightTitle'] }}</div>
                                    @endif
                                    @if(!empty($block['data']['rightContent']))
                                        <div class="column-content">{!! nl2br(e($block['data']['rightContent'])) !!}</div>
                                    @endif
                                </div>
                            </div>
                            @break

                        @case('page_break')
                            <div class="page-break"></div>
                            @break

                        @case('divider')
                            <hr class="divider {{ $styleClass }}">
                            @break

                        @case('spacer')
                            <div class="spacer {{ $styleClass }}"></div>
                            @break

                        @case('image')
                            @if(!empty($block['data']['src']))
                                <div class="image-block {{ $styleClass }}">
                                    <img src="{{ $block['data']['src'] }}" alt="{{ $block['data']['alt'] ?? '' }}">
                                    @if(!empty($block['data']['caption']))
                                        <div class="image-caption">{{ $block['data']['caption'] }}</div>
                                    @endif
                                </div>
                            @endif
                            @break

                    @endswitch
                @endif
            @endforeach
        @endif
    </div>

    <div class="footer">
        {{ __('Generated on :date', ['date' => now()->format('d.m.Y H:i')]) }} | {{ $organization?->name ?? config('app.name') }}
    </div>
</body>
</html>
