<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Offer Declined') }} - {{ $offer->offer_number }}</title>
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

        <div class="status-banner status-rejected">
            <span class="status-icon">✗</span>
            <span>{{ __('Offer Declined') }}</span>
        </div>

        <div class="content">
            <h1>{{ __('The client has declined your offer') }}</h1>

            <p class="message">
                {{ __('Unfortunately, the client has chosen not to accept this offer. Review the details below and consider following up.') }}
            </p>

            <div class="details-box">
                <div class="detail-row">
                    <span class="detail-label">{{ __('Offer Number') }}</span>
                    <span class="detail-value">{{ $offer->offer_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('Client') }}</span>
                    <span class="detail-value">{{ $offer->client?->display_name ?? $offer->temp_client_company ?? $offer->temp_client_name ?? '-' }}</span>
                </div>
                @if($offer->client?->email ?? $offer->temp_client_email)
                <div class="detail-row">
                    <span class="detail-label">{{ __('Client Email') }}</span>
                    <span class="detail-value">{{ $offer->client?->email ?? $offer->temp_client_email }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">{{ __('Declined At') }}</span>
                    <span class="detail-value">{{ $offer->rejected_at?->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i') }}</span>
                </div>
                @if($offer->rejection_reason)
                <div class="detail-row" style="flex-direction: column; align-items: flex-start;">
                    <span class="detail-label" style="margin-bottom: 8px;">{{ __('Reason Given') }}</span>
                    <span class="detail-value" style="background: #fef2f2; padding: 12px; border-radius: 8px; width: 100%; box-sizing: border-box;">{{ $offer->rejection_reason }}</span>
                </div>
                @endif
                <div class="total-box" style="background: #fef2f2;">
                    <div class="detail-row" style="border: none;">
                        <span class="detail-label">{{ __('Offer Value') }}</span>
                        <span class="detail-value">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                    </div>
                </div>
            </div>

            <div class="info-box warning">
                <p><strong>{{ __('Suggestion') }}:</strong> {{ __('Consider reaching out to the client to understand their concerns and potentially revise the offer.') }}</p>
            </div>

            <div class="btn-container">
                <a href="{{ route('offers.show', $offer) }}" class="btn btn-secondary">{{ __('View Offer Details') }}</a>
            </div>
        </div>

        @include('emails.partials.footer')
    </div>
</body>
</html>
