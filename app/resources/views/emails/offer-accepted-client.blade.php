<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Offer Confirmation') }} - {{ $offer->offer_number }}</title>
    @include('emails.partials.styles')
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; background-color: #f8fafc; -webkit-font-smoothing: antialiased;">
    <div style="max-width: 600px; margin: 32px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 4px 12px rgba(0, 0, 0, 0.05);">
        {{-- Header with Logo --}}
        <div style="text-align: center; padding: 32px 40px 24px;">
            @if($organization->logo ?? false)
                <img src="{{ $message->embed(storage_path('app/public/' . $organization->logo)) }}" alt="{{ $organization->name }}" style="max-height: 50px; max-width: 200px;">
            @else
                <div style="font-size: 22px; font-weight: 700; color: #1e40af; letter-spacing: -0.5px;">{{ $organization->name ?? config('app.name') }}</div>
            @endif
        </div>

        {{-- Status Badge --}}
        <div style="text-align: center; padding: 0 40px 24px;">
            <div style="display: inline-block; padding: 10px 20px; border-radius: 100px; font-size: 13px; font-weight: 600; letter-spacing: 0.3px; background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                <span style="font-size: 16px; line-height: 1; margin-right: 8px;">✓</span>
                <span>{{ __('Thank You for Your Order!') }}</span>
            </div>
        </div>

        {{-- Main Content --}}
        <div style="padding: 8px 40px 40px;">
            {{-- Headline --}}
            <h1 style="color: #0f172a; font-size: 24px; font-weight: 700; margin: 0 0 12px 0; line-height: 1.3; letter-spacing: -0.3px;">{{ __('Your acceptance has been confirmed') }}</h1>

            {{-- Confirmation Message --}}
            <p style="font-size: 15px; color: #64748b; margin: 0 0 28px 0; line-height: 1.7;">
                {{ __('Thank you for accepting our offer. We have received your confirmation and will begin working on your project shortly.') }}
            </p>

            {{-- Offer Details Card --}}
            <div style="background-color: #ffffff; border-radius: 12px; margin-bottom: 28px; border: 1px solid #e2e8f0; overflow: hidden;">
                {{-- Offer Number --}}
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 14px; font-weight: 500;">{{ __('Offer Number') }}</span>
                    <span style="font-weight: 600; font-size: 14px; color: #0f172a; text-align: right;">{{ $offer->offer_number }}</span>
                </div>
                {{-- Project --}}
                @if($offer->title)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 14px; font-weight: 500;">{{ __('Project') }}</span>
                    <span style="font-weight: 600; font-size: 14px; color: #0f172a; text-align: right;">{{ $offer->title }}</span>
                </div>
                @endif
                {{-- Accepted On --}}
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 14px; font-weight: 500;">{{ __('Accepted On') }}</span>
                    <span style="font-weight: 600; font-size: 14px; color: #0f172a; text-align: right;">{{ $offer->accepted_at?->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i') }}</span>
                </div>
                {{-- Total Amount Section --}}
                <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px;">
                        <span style="color: #475569; font-size: 14px; font-weight: 600;">{{ __('Total Amount') }}</span>
                        <span style="color: #0f172a; font-size: 22px; font-weight: 700; letter-spacing: -0.3px;">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                    </div>
                </div>
            </div>

            {{-- Services Summary --}}
            @if($offer->items && $offer->items->count() > 0)
                @php $selectedItems = $offer->items->where('is_selected', true); @endphp
                @if($selectedItems->count() > 0)
                <div style="margin: 0 0 28px 0; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
                    {{-- Section Header --}}
                    <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; padding: 16px 20px 12px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">{{ __('Services Included') }}</div>
                    {{-- Service Items --}}
                    @foreach($selectedItems as $index => $item)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: {{ $loop->last ? 'none' : '1px solid #f1f5f9' }}; font-size: 14px;">
                        <span style="color: #334155; font-weight: 500; flex: 1; padding-right: 16px; line-height: 1.4;">{{ $item->title }}</span>
                        <span style="font-weight: 600; color: #0f172a; white-space: nowrap; font-size: 14px;">{{ number_format($item->total_price, 2) }} {{ $offer->currency }}</span>
                    </div>
                    @endforeach
                    {{-- Services Total --}}
                    <div style="padding: 16px 20px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 600; color: #334155; font-size: 14px;">{{ __('Total') }}</span>
                        <span style="font-weight: 700; color: #0f172a; font-size: 16px;">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                    </div>
                </div>
                @endif
            @endif

            {{-- What happens next? Info Box --}}
            <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 16px 20px; margin: 0 0 24px 0;">
                <p style="margin: 0; font-size: 14px; color: #065f46; line-height: 1.6;"><strong>{{ __('What happens next?') }}</strong></p>
                <p style="margin: 8px 0 0 0; font-size: 14px; color: #065f46; line-height: 1.6;">{{ __('Our team will contact you shortly to discuss the project timeline and next steps. If you have any questions in the meantime, feel free to reach out.') }}</p>
            </div>

            {{-- Primary CTA Button --}}
            <div style="text-align: center; margin: 32px 0;">
                <a href="{{ route('offers.public', $offer->public_token) }}" style="display: inline-block; text-decoration: none; padding: 14px 36px; border-radius: 10px; font-weight: 600; font-size: 15px; background-color: #2563eb; color: #ffffff; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);">{{ __('View Your Offer') }}</a>
            </div>

            {{-- Contact Information --}}
            <div style="text-align: center; padding-top: 28px; border-top: 1px solid #e2e8f0; margin-top: 8px;">
                <p style="color: #94a3b8; font-size: 13px; margin: 0 0 8px 0;">{{ __('Questions? Contact us at') }}</p>
                <div>
                    @if($organization->email ?? false)
                        <a href="mailto:{{ $organization->email }}" style="color: #2563eb; text-decoration: none; font-weight: 500; font-size: 14px;">{{ $organization->email }}</a>
                    @endif
                    @if(($organization->email ?? false) && ($organization->phone ?? false))
                        <span style="color: #e2e8f0; margin: 0 12px;">|</span>
                    @endif
                    @if($organization->phone ?? false)
                        <a href="tel:{{ $organization->phone }}" style="color: #2563eb; text-decoration: none; font-weight: 500; font-size: 14px;">{{ $organization->phone }}</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div style="background-color: #f8fafc; padding: 24px 40px; text-align: center; border-top: 1px solid #e2e8f0;">
            <p style="margin: 6px 0; font-size: 12px; color: #94a3b8;">{{ __('This email was sent automatically by') }} <a href="{{ config('app.url') }}" style="color: #64748b; text-decoration: none;">{{ $organization->name ?? config('app.name') }}</a></p>
            @if($organization->email ?? false)
                <p style="margin: 6px 0; font-size: 12px; color: #94a3b8;">{{ __('Questions?') }} <a href="mailto:{{ $organization->email }}" style="color: #64748b; text-decoration: none;">{{ $organization->email }}</a></p>
            @endif
            <p style="margin: 6px 0; font-size: 12px; color: #94a3b8;">&copy; {{ date('Y') }} {{ $organization->name ?? config('app.name') }}. {{ __('All rights reserved.') }}</p>
        </div>
    </div>
</body>
</html>
