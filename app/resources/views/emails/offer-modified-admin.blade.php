<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Offer Modified by Client') }} - {{ $offer->offer_number }}</title>
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

        <div class="status-banner status-modified">
            <span class="status-icon">✎</span>
            <span>{{ __('Client Modified Selections') }}</span>
        </div>

        <div class="content">
            <h1>{{ __('The client has changed their service selections') }}</h1>

            <p class="message">
                {{ __('The client has modified their optional service selections on the offer. Please review the changes below.') }}
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
                    <span class="detail-label">{{ __('Modified At') }}</span>
                    <span class="detail-value">{{ now()->format('d.m.Y H:i') }}</span>
                </div>
                <div class="total-box" style="background: #faf5ff;">
                    <div class="detail-row" style="border: none;">
                        <span class="detail-label">{{ __('New Total') }}</span>
                        <span class="detail-value" style="color: #7c3aed;">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                    </div>
                </div>
            </div>

            @if(isset($changes) && count($changes) > 0)
            <div style="margin-top: 24px;">
                <h3 style="font-size: 14px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">{{ __('Changes Made') }}</h3>
                <div style="background: #f8fafc; border-radius: 8px; padding: 16px;">
                    @foreach($changes as $change)
                    <div style="display: flex; align-items: center; padding: 8px 0; {{ !$loop->last ? 'border-bottom: 1px solid #e2e8f0;' : '' }}">
                        @if($change['action'] === 'added')
                            <span style="color: #16a34a; margin-right: 8px;">+</span>
                            <span style="color: #334155;">{{ $change['item'] }}</span>
                            <span style="margin-left: auto; color: #16a34a; font-weight: 500;">+{{ number_format($change['amount'], 2) }} {{ $offer->currency }}</span>
                        @else
                            <span style="color: #dc2626; margin-right: 8px;">-</span>
                            <span style="color: #334155; text-decoration: line-through;">{{ $change['item'] }}</span>
                            <span style="margin-left: auto; color: #dc2626; font-weight: 500;">-{{ number_format($change['amount'], 2) }} {{ $offer->currency }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="info-box">
                <p><strong>{{ __('Note') }}:</strong> {{ __('The offer has not been accepted yet. The client is still reviewing and adjusting their selections.') }}</p>
            </div>

            <div class="btn-container">
                <a href="{{ route('offers.show', $offer) }}" class="btn btn-primary">{{ __('View Updated Offer') }}</a>
            </div>
        </div>

        @include('emails.partials.footer')
    </div>
</body>
</html>
