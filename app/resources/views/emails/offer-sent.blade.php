<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Offer :number', ['number' => $offer->offer_number]) }}</title>
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
            <div style="display: inline-block; padding: 10px 20px; border-radius: 100px; font-size: 13px; font-weight: 600; letter-spacing: 0.3px; background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
                <span style="font-size: 16px; line-height: 1; margin-right: 8px;">📄</span>
                <span>{{ __('New Offer') }}</span>
            </div>
        </div>

        {{-- Main Content --}}
        <div style="padding: 8px 40px 40px;">
            {{-- Headline --}}
            <h1 style="color: #0f172a; font-size: 24px; font-weight: 700; margin: 0 0 12px 0; line-height: 1.3; letter-spacing: -0.3px;">{{ __('You have received an offer') }}</h1>

            {{-- Greeting & Message --}}
            <p style="font-size: 15px; color: #475569; margin: 0 0 8px 0;">
                {{ __('Dear :name,', ['name' => $offer->client?->display_name ?? $offer->temp_client_name ?? __('Customer')]) }}
            </p>

            <p style="font-size: 15px; color: #64748b; margin: 0 0 28px 0; line-height: 1.7;">
                {{ __('We are pleased to send you our offer for your consideration. Please find the details below and click the button to view the complete offer.') }}
            </p>

            {{-- Subject/Project Title (if exists) --}}
            @if($offer->title)
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; margin: 0 0 24px 0;">
                <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.6;"><strong style="color: #334155; font-weight: 600;">{{ __('Subject') }}:</strong> {{ $offer->title }}</p>
            </div>
            @endif

            {{-- Offer Details Card --}}
            <div style="background-color: #ffffff; border-radius: 12px; margin-bottom: 28px; border: 1px solid #e2e8f0; overflow: hidden;">
                {{-- Offer Number --}}
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 14px; font-weight: 500;">{{ __('Offer Number') }}</span>
                    <span style="font-weight: 600; font-size: 14px; color: #0f172a; text-align: right;">{{ $offer->offer_number }}</span>
                </div>
                {{-- Date --}}
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 14px; font-weight: 500;">{{ __('Date') }}</span>
                    <span style="font-weight: 600; font-size: 14px; color: #0f172a; text-align: right;">{{ $offer->offer_date?->format('d.m.Y') ?? now()->format('d.m.Y') }}</span>
                </div>
                {{-- Valid Until --}}
                @if($offer->valid_until)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 14px; font-weight: 500;">{{ __('Valid Until') }}</span>
                    <span style="font-weight: 600; font-size: 14px; color: #0f172a; text-align: right;">{{ $offer->valid_until->format('d.m.Y') }}</span>
                </div>
                @endif
                {{-- Total Amount Section --}}
                <div style="background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px;">
                        <span style="color: #475569; font-size: 14px; font-weight: 600;">{{ __('Total') }}</span>
                        <span style="color: #0f172a; font-size: 22px; font-weight: 700; letter-spacing: -0.3px;">{{ number_format($offer->total, 2) }} {{ $offer->currency }}</span>
                    </div>
                </div>
            </div>

            {{-- Validity Warning --}}
            @if($offer->valid_until && $offer->valid_until->isFuture())
            <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 16px 20px; margin: 0 0 24px 0;">
                <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.6;">⏰ {{ __('This offer is valid until :date', ['date' => $offer->valid_until->format('d.m.Y')]) }}</p>
            </div>
            @endif

            {{-- Primary CTA Button --}}
            <div style="text-align: center; margin: 32px 0;">
                <a href="{{ $publicUrl }}" style="display: inline-block; text-decoration: none; padding: 14px 36px; border-radius: 10px; font-weight: 600; font-size: 15px; background-color: #2563eb; color: #ffffff; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);">{{ __('View Offer') }}</a>
            </div>

            {{-- Additional Message --}}
            <p style="font-size: 15px; color: #64748b; margin: 0; line-height: 1.7;">
                {{ __('If you have any questions or would like to discuss this offer, please do not hesitate to contact us.') }}
            </p>

            {{-- Contact Information --}}
            <div style="text-align: center; padding-top: 28px; border-top: 1px solid #e2e8f0; margin-top: 28px;">
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
