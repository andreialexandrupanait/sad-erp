<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Offer Accepted') }} - {{ $offer->offer_number }}</title>
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

        <div class="status-banner status-accepted">
            <span class="status-icon">✓</span>
            <span>{{ __('Offer Accepted') }}</span>
        </div>

        <div class="content">
            <h1>{{ __('Great news! Your offer has been accepted') }}</h1>

            <p class="message">
                {{ __('The client has accepted your offer. You can now proceed with the next steps.') }}
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
                    <span class="detail-label">{{ __('Accepted At') }}</span>
                    <span class="detail-value">{{ $offer->accepted_at?->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i') }}</span>
                </div>
                <div class="total-box">
                    <div class="detail-row" style="border: none;">
                        <span class="detail-label">{{ __('Total Value') }}</span>
                        <span class="detail-value">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                    </div>
                </div>
            </div>

            <div class="info-box success">
                <p><strong>{{ __('Next Steps') }}:</strong> {{ __('You can now convert this offer to a contract and start the project.') }}</p>
            </div>

            <div class="btn-container">
                <a href="{{ route('offers.show', $offer) }}" class="btn btn-primary">{{ __('View Offer & Create Contract') }}</a>
            </div>
        </div>

        @include('emails.partials.footer')
    </div>
</body>
</html>
