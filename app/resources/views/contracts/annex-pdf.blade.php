<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('Annex') }} {{ $annex->annex_code }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 15mm 15mm 15mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000000;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* Page wrapper - no padding needed, @page margins work with Chrome */
        .page-wrapper {
            padding: 0;
        }

        /* Typography - professional document styling */
        h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 20px 0;
            color: #000000;
            text-align: center;
            text-transform: uppercase;
            page-break-after: avoid;
        }

        h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 24px 0 4px 0;
            color: #000000;
            page-break-after: avoid;
        }

        h3 {
            font-size: 11pt;
            font-weight: bold;
            margin: 20px 0 4px 0;
            color: #000000;
            page-break-after: avoid;
        }

        h4 {
            font-size: 11pt;
            font-weight: 400;
            margin: 10px 0 2px 0;
            color: #000000;
            page-break-after: avoid;
        }

        /* Paragraphs */
        p {
            margin: 0 0 12px 0;
            text-align: justify;
            orphans: 3;
            widows: 3;
        }

        /* Quill editor alignment classes */
        .ql-align-center, p.ql-align-center {
            text-align: center !important;
        }

        .ql-align-right, p.ql-align-right {
            text-align: right !important;
        }

        .ql-align-justify, p.ql-align-justify {
            text-align: justify !important;
        }

        /* Quill indent classes */
        .ql-indent-1 { padding-left: 3em; }
        .ql-indent-2 { padding-left: 6em; }
        .ql-indent-3 { padding-left: 9em; }
        .ql-indent-4 { padding-left: 12em; }

        /* Lists */
        ul, ol {
            margin-top: 2px;
            margin-bottom: 2px;
            padding-left: 30px;
        }

        li {
            margin-top: 2px !important;
            margin-bottom: 2px !important;
            text-align: left;
            line-height: 1.4;
        }

        li:last-child {
            margin-bottom: 12px !important;
        }

        li p {
            margin: 0 !important;
            padding: 0;
        }

        li ul, li ol {
            margin-top: 2px;
            margin-bottom: 2px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            page-break-inside: avoid;
        }

        table th,
        table td {
            border: 1px solid rgba(0, 0, 0, 0);
            padding: 8px 10px;
            font-size: 10pt;
            text-align: left;
            vertical-align: top;
        }

        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        /* Text formatting */
        strong, b { font-weight: bold; }
        em, i { font-style: italic; }
        u { text-decoration: underline; }
        s, strike { text-decoration: line-through; }

        a {
            color: #000000;
            text-decoration: underline;
        }

        blockquote {
            border-left: 3px solid #666666;
            padding-left: 15px;
            margin: 16px 0 16px 20px;
            font-style: italic;
        }

        code {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 9pt;
        }

        pre {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 9pt;
            margin: 12px 0;
            padding: 10px;
            border: 1px solid #cccccc;
            background-color: #f8f8f8;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* Page break control */
        .page-break { page-break-after: always; }
        .avoid-break { page-break-inside: avoid; }
        .keep-together { page-break-inside: avoid; }

        /* Signatures */
        .signatures {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border: none;
        }

        .signature-table td {
            border: none;
            width: 50%;
            vertical-align: top;
            padding: 15px 25px;
        }

        .signature-block { text-align: center; }

        .signature-line {
            border-top: 1px solid #000000;
            margin-top: 60px;
            padding-top: 8px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 50px;
            right: 50px;
            text-align: center;
            font-size: 9pt;
            color: #666666;
        }

        .page-number:after {
            content: counter(page);
        }

        hr {
            border: none;
            border-top: 1px solid #000000;
            margin: 20px 0;
        }

        p:empty, p br:only-child {
            margin: 6px 0;
            min-height: 1em;
        }

        p strong:first-child {
            display: inline;
        }

        /* Fallback styling for hardcoded structure */
        .annex-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .annex-title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .annex-code {
            font-size: 12pt;
            margin-bottom: 10px;
        }

        .contract-ref {
            font-size: 10pt;
            color: #666666;
        }

        .parties-table {
            width: 100%;
            border: none;
            margin-bottom: 30px;
        }

        .parties-table td {
            border: none;
            width: 50%;
            vertical-align: top;
            padding: 15px;
        }

        .party-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }

        .annex-details {
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 30px;
        }

        .details-row {
            margin-bottom: 8px;
        }

        .details-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #333333;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid #333333;
        }

        .items-table td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            font-size: 10pt;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-box {
            width: 250px;
            margin-left: auto;
            margin-bottom: 30px;
            border: 1px solid #333333;
        }

        .totals-row {
            background-color: #333333;
            color: white;
            padding: 10px;
        }

        .totals-label {
            display: inline-block;
        }

        .totals-value {
            float: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Annex PDF -->
    <div class="page-wrapper">
        <div class="annex-content">
            @if(isset($content) && $content)
                {{-- Template-based content with variables replaced --}}
                {!! $content !!}
            @else
                {{-- Fallback: Hardcoded structure when no template content --}}
                <div class="annex-header">
                    <div class="annex-title">{{ __('CONTRACT ANNEX') }}</div>
                    <div class="annex-code">{{ $annex->annex_code }}</div>
                    @if($contract)
                        <div class="contract-ref">
                            {{ __('to Contract') }} {{ $contract->document_number }}
                            @if($contract->title)
                                - {{ $contract->title }}
                            @endif
                        </div>
                    @endif
                </div>

                <table class="parties-table">
                    <tr>
                        <td>
                            <div class="party-title">{{ __('Provider') }}</div>
                            <div class="party-content">
                                <p><strong>{{ config('app.name') }}</strong></p>
                                <p>{{ config('mail.from.address') }}</p>
                            </div>
                        </td>
                        <td>
                            <div class="party-title">{{ __('Client') }}</div>
                            <div class="party-content">
                                @if($contract?->client)
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
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="annex-details">
                    <div class="details-row">
                        <span class="details-label">{{ __('Annex Number') }}:</span>
                        <span class="details-value">{{ $annex->annex_number }}</span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">{{ __('Effective Date') }}:</span>
                        <span class="details-value">{{ $annex->effective_date?->format('d.m.Y') ?? now()->format('d.m.Y') }}</span>
                    </div>
                    @if($annex->title)
                        <div class="details-row">
                            <span class="details-label">{{ __('Title') }}:</span>
                            <span class="details-value">{{ $annex->title }}</span>
                        </div>
                    @endif
                    <div class="details-row">
                        <span class="details-label">{{ __('Annex Value') }}:</span>
                        <span class="details-value"><strong>{{ number_format($annex->additional_value, 2) }} {{ $annex->currency }}</strong></span>
                    </div>
                </div>

                @if($annex->description)
                    <div class="section">
                        <div class="section-title">{{ __('Description') }}</div>
                        <div class="section-content">{!! nl2br(e($annex->description)) !!}</div>
                    </div>
                @endif

                @if($annex->offer && $annex->offer->items->count() > 0)
                    <div class="section">
                        <div class="section-title">{{ __('Additional Services / Products') }}</div>
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
                                @foreach($annex->offer->items as $index => $item)
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
                        <span class="totals-label">{{ __('Annex Value') }}</span>
                        <span class="totals-value">{{ number_format($annex->additional_value, 2) }} {{ $annex->currency }}</span>
                    </div>
                </div>

                @if($contract)
                <div class="section">
                    <div style="text-align: center; font-style: italic; color: #666666;">
                        {{ __('This annex is an integral part of contract :number and has the same legal force.', ['number' => $contract->document_number]) }}
                    </div>
                </div>
                @endif

                <table class="signature-table">
                    <tr>
                        <td>
                            <div class="signature-block">
                                <div class="signature-name">{{ config('app.name') }}</div>
                                <div style="font-size: 9pt; color: #666666;">{{ __('Provider') }}</div>
                                <div class="signature-line">
                                    {{ __('Signature & Stamp') }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="signature-block">
                                <div class="signature-name">{{ $contract?->client?->display_name ?? '' }}</div>
                                <div style="font-size: 9pt; color: #666666;">{{ __('Client') }}</div>
                                <div class="signature-line">
                                    {{ __('Signature & Stamp') }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            @endif
        </div>
    </div>

    <div class="footer">
        {{ __('Page') }} <span class="page-number"></span>
    </div>
</body>
</html>
