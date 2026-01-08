<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Offer Response Confirmed') }} - {{ $offer->offer_number }}</title>
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

        <div class="status-banner" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%);">
            <span class="status-icon">✓</span>
            <span>{{ __('Response Confirmed') }}</span>
        </div>

        <div class="content">
            <h1>{{ __('We have received your response') }}</h1>

            <p class="message">
                {{ __('Thank you for taking the time to review our offer. We understand this offer may not meet your current needs.') }}
            </p>

            <div class="details-box">
                <div class="detail-row">
                    <span class="detail-label">{{ __('Offer Number') }}</span>
                    <span class="detail-value">{{ $offer->offer_number }}</span>
                </div>
                @if($offer->title)
                <div class="detail-row">
                    <span class="detail-label">{{ __('Project') }}</span>
                    <span class="detail-value">{{ $offer->title }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">{{ __('Response Date') }}</span>
                    <span class="detail-value">{{ $offer->rejected_at?->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i') }}</span>
                </div>
            </div>

            <div class="info-box">
                <p><strong>{{ __('Changed your mind?') }}</strong></p>
                <p style="margin-top: 8px;">{{ __('If your circumstances change or you would like to discuss alternative options, we would be happy to help. Feel free to contact us at any time.') }}</p>
            </div>

            {{-- Contact Info --}}
            <div style="text-align: center; margin-top: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
                <p style="color: #64748b; font-size: 14px; margin-bottom: 8px;">{{ __('Contact us') }}</p>
                @if($organization->email ?? false)
                    <a href="mailto:{{ $organization->email }}" style="color: #3b82f6; text-decoration: none; font-weight: 500;">{{ $organization->email }}</a>
                @endif
                @if($organization->phone ?? false)
                    <span style="color: #cbd5e1; margin: 0 8px;">|</span>
                    <a href="tel:{{ $organization->phone }}" style="color: #3b82f6; text-decoration: none; font-weight: 500;">{{ $organization->phone }}</a>
                @endif
            </div>
        </div>

        @include('emails.partials.footer')
    </div>
</body>
</html>
