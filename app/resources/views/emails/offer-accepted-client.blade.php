<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Offer Confirmation') }} - {{ $offer->offer_number }}</title>
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
            <span>{{ __('Thank You for Your Order!') }}</span>
        </div>

        <div class="content">
            <h1>{{ __('Your acceptance has been confirmed') }}</h1>

            <p class="message">
                {{ __('Thank you for accepting our offer. We have received your confirmation and will begin working on your project shortly.') }}
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
                    <span class="detail-label">{{ __('Accepted On') }}</span>
                    <span class="detail-value">{{ $offer->accepted_at?->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i') }}</span>
                </div>
                <div class="total-box">
                    <div class="detail-row" style="border: none;">
                        <span class="detail-label">{{ __('Total Amount') }}</span>
                        <span class="detail-value">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                    </div>
                </div>
            </div>

            {{-- Services Summary --}}
            @if($offer->items && $offer->items->count() > 0)
                @php $selectedItems = $offer->items->where('is_selected', true); @endphp
                @if($selectedItems->count() > 0)
                <div class="services-list">
                    <div class="section-header">{{ __('Services Included') }}</div>
                    @foreach($selectedItems as $item)
                    <div class="service-item">
                        <span class="service-name">{{ $item->title }}</span>
                        <span class="service-price">{{ number_format($item->total_price, 2) }} {{ $offer->currency }}</span>
                    </div>
                    @endforeach
                    <div class="services-total">
                        <span class="services-total-label">{{ __('Total') }}</span>
                        <span class="services-total-value">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                    </div>
                </div>
                @endif
            @endif

            <div class="info-box success">
                <p><strong>{{ __('What happens next?') }}</strong></p>
                <p style="margin-top: 8px;">{{ __('Our team will contact you shortly to discuss the project timeline and next steps. If you have any questions in the meantime, feel free to reach out.') }}</p>
            </div>

            <div class="btn-container">
                <a href="{{ route('offers.public', $offer->public_token) }}" class="btn btn-primary">{{ __('View Your Offer') }}</a>
            </div>

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
