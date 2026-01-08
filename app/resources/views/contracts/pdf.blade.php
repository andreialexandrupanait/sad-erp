<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('Contract') }} {{ $contract->contract_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 25mm 25mm 25mm 25mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
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
            margin: 24px 0 12px 0;
            color: #000000;
            page-break-after: avoid;
        }

        h3 {
            font-size: 11pt;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #000000;
            page-break-after: avoid;
        }

        h4 {
            font-size: 11pt;
            font-weight: bold;
            margin: 16px 0 8px 0;
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
            margin: 12px 0 12px 0;
            padding-left: 30px;
        }

        li {
            margin: 6px 0;
            text-align: left;
            line-height: 1.6;
        }

        li ul, li ol {
            margin: 6px 0;
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
            border: 1px solid #000000;
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
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="contract-content">
            @if($contract->content)
                {!! $contract->pdf_content !!}
            @elseif(isset($content) && $content)
                {!! $content !!}
            @else
                <p style="text-align: center;">{{ __('No contract content available.') }}</p>
            @endif
        </div>
    </div>

    <div class="footer">
        {{ $contract->contract_number }} - {{ __('Page') }} <span class="page-number"></span>
    </div>
</body>
</html>
