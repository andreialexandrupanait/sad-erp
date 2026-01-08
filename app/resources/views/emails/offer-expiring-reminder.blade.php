<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Offer Expiring Reminder') }}</title>
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

        <div class="status-banner status-sent" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
            <span class="status-icon">⏰</span>
            <span>{{ __('Expiring Soon') }}</span>
        </div>

        <div class="content">
            <h1>{{ __('Your offer expires soon') }}</h1>

            <p class="greeting">
                {{ __('Dear :name,', ['name' => $offer->client?->display_name ?? $offer->temp_client_name ?? __('Customer')]) }}
            </p>

            <p class="message">
                @if($daysRemaining <= 1)
                    {{ __('This is a reminder that the following offer will expire tomorrow. Please review and respond if you wish to proceed.') }}
                @else
                    {{ __('This is a reminder that the following offer will expire in :days days. Please review and respond if you wish to proceed.', ['days' => $daysRemaining]) }}
                @endif
            </p>

            @if($offer->title)
            <div class="info-box">
                <p><strong>{{ __('Subject') }}:</strong> {{ $offer->title }}</p>
            </div>
            @endif

            <div class="details-box">
                <div class="detail-row">
                    <span class="detail-label">{{ __('Offer Number') }}</span>
                    <span class="detail-value">{{ $offer->offer_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('Valid Until') }}</span>
                    <span class="detail-value" style="color: #f59e0b; font-weight: bold;">{{ $offer->valid_until->format('d.m.Y') }}</span>
                </div>
                <div class="total-box">
                    <div class="detail-row" style="border: none;">
                        <span class="detail-label">{{ __('Total') }}</span>
                        <span class="detail-value" style="font-size: 18px;">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                    </div>
                </div>
            </div>

            <div class="info-box warning" style="background-color: #fef3c7; border-color: #f59e0b;">
                <p style="color: #92400e;">
                    @if($daysRemaining <= 1)
                        {{ __('This offer will expire tomorrow!') }}
                    @else
                        {{ __('Only :days days remaining to accept this offer.', ['days' => $daysRemaining]) }}
                    @endif
                </p>
            </div>

            <div class="btn-container">
                <a href="{{ $publicUrl }}" class="btn btn-primary" style="background: linear-gradient(135deg, #f59e0b, #d97706);">{{ __('Review Offer') }}</a>
            </div>

            <p class="message" style="margin-top: 24px;">
                {{ __('If you have any questions or need more time, please contact us to discuss your options.') }}
            </p>

            {{-- Contact Info --}}
            <div style="text-align: center; margin-top: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
                <p style="color: #64748b; font-size: 14px; margin-bottom: 8px;">{{ __('Questions? Contact us at') }}</p>
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
