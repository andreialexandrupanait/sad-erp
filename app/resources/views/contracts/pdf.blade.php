<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('Contract') }} {{ $contract->contract_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #333;
        }
        .container {
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1f2937;
        }
        .contract-title {
            font-size: 20pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .contract-number {
            font-size: 12pt;
            color: #6b7280;
        }
        .parties {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .party {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 15px;
        }
        .party:first-child {
            padding-left: 0;
        }
        .party:last-child {
            padding-right: 0;
        }
        .party-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .party-content {
            font-size: 10pt;
        }
        .party-content p {
            margin-bottom: 3px;
        }
        .contract-details {
            background-color: #f9fafb;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        .details-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .details-row:last-child {
            margin-bottom: 0;
        }
        .details-label {
            display: table-cell;
            width: 30%;
            font-weight: bold;
            color: #6b7280;
        }
        .details-value {
            display: table-cell;
            width: 70%;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .section-content {
            font-size: 10pt;
            text-align: justify;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #1f2937;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
        }
        .items-table td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            font-size: 10pt;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .totals-box {
            width: 300px;
            margin-left: auto;
            border: 2px solid #1f2937;
            margin-bottom: 30px;
        }
        .totals-row {
            display: table;
            width: 100%;
            border-bottom: 1px solid #e5e7eb;
        }
        .totals-row:last-child {
            border-bottom: none;
            background-color: #1f2937;
            color: white;
        }
        .totals-label, .totals-value {
            display: table-cell;
            padding: 10px;
            font-size: 10pt;
        }
        .totals-label {
            text-align: left;
        }
        .totals-value {
            text-align: right;
            font-weight: bold;
        }
        .signatures {
            display: table;
            width: 100%;
            margin-top: 50px;
        }
        .signature-block {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 20px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 10px;
        }
        .signature-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .signature-title {
            font-size: 9pt;
            color: #6b7280;
        }
        .template-content {
            margin-top: 30px;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 30px;
            right: 30px;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .page-number:after {
            content: counter(page);
        }
        .annexes-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        .annex-item {
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f9fafb;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="contract-title">{{ __('CONTRACT') }}</div>
            <div class="contract-number">{{ $contract->contract_number }}</div>
            @if($contract->title)
                <div style="margin-top: 10px; font-style: italic;">{{ $contract->title }}</div>
            @endif
        </div>

        <div class="parties">
            <div class="party">
                <div class="party-title">{{ __('Provider') }}</div>
                <div class="party-content">
                    <p><strong>{{ config('app.name') }}</strong></p>
                    <p>{{ config('mail.from.address') }}</p>
                </div>
            </div>
            <div class="party">
                <div class="party-title">{{ __('Client') }}</div>
                <div class="party-content">
                    @if($contract->client)
                        <p><strong>{{ $contract->client->display_name }}</strong></p>
                        @if($contract->client->company_name)
                            <p>{{ $contract->client->company_name }}</p>
                        @endif
                        @if($contract->client->full_address)
                            <p>{{ $contract->client->full_address }}</p>
                        @endif
                        @if($contract->client->fiscal_code)
                            <p>{{ __('Fiscal Code') }}: {{ $contract->client->fiscal_code }}</p>
                        @endif
                        @if($contract->client->registration_number)
                            <p>{{ __('Reg. No.') }}: {{ $contract->client->registration_number }}</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="contract-details">
            <div class="details-row">
                <span class="details-label">{{ __('Contract Number') }}:</span>
                <span class="details-value">{{ $contract->contract_number }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">{{ __('Start Date') }}:</span>
                <span class="details-value">{{ $contract->start_date?->format('d.m.Y') ?? '-' }}</span>
            </div>
            @if($contract->end_date)
                <div class="details-row">
                    <span class="details-label">{{ __('End Date') }}:</span>
                    <span class="details-value">{{ $contract->end_date->format('d.m.Y') }}</span>
                </div>
            @endif
            <div class="details-row">
                <span class="details-label">{{ __('Contract Value') }}:</span>
                <span class="details-value"><strong>{{ number_format($contract->total_value, 2) }} {{ $contract->currency }}</strong></span>
            </div>
            @if($contract->auto_renew)
                <div class="details-row">
                    <span class="details-label">{{ __('Auto Renewal') }}:</span>
                    <span class="details-value">{{ __('Yes') }}</span>
                </div>
            @endif
        </div>

        @if($contract->description)
            <div class="section">
                <div class="section-title">{{ __('Description') }}</div>
                <div class="section-content">{!! nl2br(e($contract->description)) !!}</div>
            </div>
        @endif

        @if($contract->offer && $contract->offer->items->count() > 0)
            <div class="section">
                <div class="section-title">{{ __('Services / Products') }}</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 45%;">{{ __('Description') }}</th>
                            <th style="width: 10%;" class="text-center">{{ __('Qty') }}</th>
                            <th style="width: 15%;" class="text-right">{{ __('Unit Price') }}</th>
                            <th style="width: 10%;" class="text-right">{{ __('Tax') }}</th>
                            <th style="width: 15%;" class="text-right">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contract->offer->items as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                    @if($item->description)
                                        <br><small style="color: #6b7280;">{{ $item->description }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-right">{{ number_format($item->tax_rate ?? 0, 0) }}%</td>
                                <td class="text-right">{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="totals-box">
            <div class="totals-row">
                <span class="totals-label">{{ __('Total Contract Value') }}</span>
                <span class="totals-value">{{ number_format($contract->total_value, 2) }} {{ $contract->currency }}</span>
            </div>
        </div>

        @if($contract->annexes && $contract->annexes->count() > 0)
            <div class="annexes-section">
                <div class="section-title">{{ __('Contract Annexes') }}</div>
                @foreach($contract->annexes as $annex)
                    <div class="annex-item">
                        <strong>{{ $annex->annex_code }}</strong> - {{ $annex->title }}
                        <br>
                        <small>
                            {{ __('Value') }}: {{ number_format($annex->value, 2) }} {{ $annex->currency }}
                            | {{ __('Effective') }}: {{ $annex->effective_date?->format('d.m.Y') ?? '-' }}
                        </small>
                    </div>
                @endforeach
            </div>
        @endif

        @if($content)
            <div class="template-content">
                {!! $content !!}
            </div>
        @endif

        <div class="signatures">
            <div class="signature-block">
                <div class="signature-name">{{ config('app.name') }}</div>
                <div class="signature-title">{{ __('Provider') }}</div>
                <div class="signature-line">
                    {{ __('Signature & Stamp') }}
                </div>
            </div>
            <div class="signature-block">
                <div class="signature-name">{{ $contract->client?->display_name ?? '' }}</div>
                <div class="signature-title">{{ __('Client') }}</div>
                <div class="signature-line">
                    {{ __('Signature & Stamp') }}
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        {{ $contract->contract_number }} | {{ __('Page') }} <span class="page-number"></span> | {{ __('Generated on :date', ['date' => now()->format('d.m.Y H:i')]) }}
    </div>
</body>
</html>
