<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Offer Viewed') }} - {{ $offer->offer_number }}</title>
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

        <div class="status-banner status-viewed">
            <span class="status-icon">👁️</span>
            <span>{{ __('Offer Viewed') }}</span>
        </div>

        <div class="content">
            <h1>{{ __('Your offer has been viewed') }}</h1>

            <p class="message">
                {{ __('Good news! The client has viewed your offer. Here are the details:') }}
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
                <div class="detail-row">
                    <span class="detail-label">{{ __('Total') }}</span>
                    <span class="detail-value highlight">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('Viewed At') }}</span>
                    <span class="detail-value">{{ now()->format('d.m.Y H:i') }}</span>
                </div>
                @if($offer->valid_until)
                <div class="detail-row">
                    <span class="detail-label">{{ __('Valid Until') }}</span>
                    <span class="detail-value {{ $offer->valid_until->isPast() ? 'text-danger' : '' }}">{{ $offer->valid_until->format('d.m.Y') }}</span>
                </div>
                @endif
            </div>

            <div class="btn-container">
                <a href="{{ route('offers.show', $offer) }}" class="btn btn-primary">{{ __('View Offer Details') }}</a>
            </div>
        </div>

        @include('emails.partials.footer')
    </div>
</body>
</html>
