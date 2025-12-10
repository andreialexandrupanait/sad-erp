<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Offer :number', ['number' => $offer->offer_number]) }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2563eb;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        h1 {
            color: #1f2937;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .offer-number {
            color: #6b7280;
            font-size: 14px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .message {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 30px;
        }
        .details-box {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #6b7280;
            font-size: 14px;
        }
        .detail-value {
            font-weight: 600;
            font-size: 14px;
        }
        .total-row {
            background-color: #2563eb;
            color: white;
            margin: -20px;
            margin-top: 20px;
            padding: 15px 20px;
            border-radius: 0 0 8px 8px;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: white !important;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
        }
        .btn:hover {
            background-color: #1d4ed8;
        }
        .btn-container {
            text-align: center;
        }
        .validity {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
        .signature {
            margin-top: 30px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
        </div>

        <h1>{{ __('New Offer') }}</h1>
        <p class="offer-number">{{ $offer->offer_number }}</p>

        <p class="greeting">
            {{ __('Dear :name,', ['name' => $offer->client?->display_name ?? __('Customer')]) }}
        </p>

        <p class="message">
            {{ __('We are pleased to send you our offer for your consideration. Please find the details below and click the button to view the complete offer.') }}
        </p>

        @if($offer->title)
            <p class="message">
                <strong>{{ __('Subject') }}:</strong> {{ $offer->title }}
            </p>
        @endif

        <div class="details-box">
            <div class="detail-row">
                <span class="detail-label">{{ __('Offer Number') }}</span>
                <span class="detail-value">{{ $offer->offer_number }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ __('Date') }}</span>
                <span class="detail-value">{{ $offer->offer_date?->format('d.m.Y') ?? now()->format('d.m.Y') }}</span>
            </div>
            @if($offer->valid_until)
                <div class="detail-row">
                    <span class="detail-label">{{ __('Valid Until') }}</span>
                    <span class="detail-value">{{ $offer->valid_until->format('d.m.Y') }}</span>
                </div>
            @endif
            <div class="total-row">
                <div class="detail-row" style="border: none; color: white;">
                    <span class="detail-label" style="color: rgba(255,255,255,0.8);">{{ __('Total') }}</span>
                    <span class="detail-value" style="font-size: 18px;">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                </div>
            </div>
        </div>

        @if($offer->valid_until && $offer->valid_until->isFuture())
            <div class="validity">
                {{ __('This offer is valid until :date', ['date' => $offer->valid_until->format('d.m.Y')]) }}
            </div>
        @endif

        <div class="btn-container">
            <a href="{{ $publicUrl }}" class="btn">{{ __('View Offer') }}</a>
        </div>

        <p class="message">
            {{ __('If you have any questions or would like to discuss this offer, please do not hesitate to contact us.') }}
        </p>

        <div class="signature">
            <p>{{ __('Best regards,') }}</p>
            <p><strong>{{ config('app.name') }}</strong></p>
        </div>

        <div class="footer">
            <p>{{ __('This email was sent automatically. Please do not reply directly to this email.') }}</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
        </div>
    </div>
</body>
</html>
