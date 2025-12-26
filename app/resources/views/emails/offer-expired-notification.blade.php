<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Offer Expired') }}</title>
    @include('emails.partials.styles')
</head>
<body>
    <div class="container">
        <div class="header">
            @if($organization->logo ?? false)
                <img src="{{ $message->embed(storage_path('app/public/' . $organization->logo)) }}" alt="{{ $organization->name }}" style="max-height: 50px; max-width: 200px;">
            @else
                <div class="logo">{{ $organization->name ?? config('app.name') }}</div>
            @endif
        </div>

        <div class="status-banner" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
            <span class="status-icon">⚠️</span>
            <span>{{ __('Offer Expired') }}</span>
        </div>

        <div class="content">
            <h1>{{ __('Offer has expired') }}</h1>

            <p class="message">
                {{ __('The following offer has passed its validity date and has been automatically marked as expired.') }}
            </p>

            <div class="details-box">
                <div class="detail-row">
                    <span class="detail-label">{{ __('Offer Number') }}</span>
                    <span class="detail-value">{{ $offer->offer_number }}</span>
                </div>
                @if($offer->title)
                <div class="detail-row">
                    <span class="detail-label">{{ __('Title') }}</span>
                    <span class="detail-value">{{ $offer->title }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">{{ __('Client') }}</span>
                    <span class="detail-value">{{ $offer->client?->display_name ?? $offer->temp_client_name ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('Valid Until') }}</span>
                    <span class="detail-value" style="color: #ef4444;">{{ $offer->valid_until->format('d.m.Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('Days Overdue') }}</span>
                    <span class="detail-value">{{ $offer->valid_until->diffInDays(now()) }}</span>
                </div>
                <div class="total-box">
                    <div class="detail-row" style="border: none;">
                        <span class="detail-label">{{ __('Total Value') }}</span>
                        <span class="detail-value" style="font-size: 18px;">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                    </div>
                </div>
            </div>

            <div class="info-box" style="background-color: #fef2f2; border-color: #ef4444;">
                <p style="color: #991b1b;">
                    {{ __('You may want to follow up with the client or create a new offer with an updated validity date.') }}
                </p>
            </div>

            <div class="btn-container">
                <a href="{{ route('offers.show', $offer) }}" class="btn btn-primary">{{ __('View Offer') }}</a>
            </div>
        </div>

        @include('emails.partials.footer')
    </div>
</body>
</html>
