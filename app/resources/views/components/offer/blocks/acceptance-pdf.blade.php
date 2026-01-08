{{-- Acceptance Block - PDF View --}}
{{-- Variables: block, offer, variables, client --}}

<div style="margin-bottom: 30px;">
    {{-- Section Title --}}
    <h2 style="font-size: 14pt; font-weight: bold; color: #1e293b; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 3px solid #9333ea;">
        {{ $block['data']['title'] ?? __('Offer Acceptance') }}
    </h2>

    {{-- Acceptance Panel --}}
    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #86efac; border-radius: 12px; padding: 25px;">
        {{-- Acceptance Text --}}
        <p style="font-size: 10pt; color: #334155; line-height: 1.6; margin-bottom: 20px;">
            {{ $block['data']['acceptanceText'] ?? __('By approving this offer, I confirm that I have read and agree to the services and conditions described above.') }}
        </p>

        {{-- Client Info + Signature Area --}}
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; vertical-align: bottom;">
                    @if($block['data']['showClientInfo'] ?? true)
                        <p style="font-size: 9pt; color: #64748b; margin-bottom: 5px;">
                            <span style="font-weight: 600;">{{ __('Client') }}:</span>
                            {{ $variables['client.company'] ?? $variables['client.name'] ?? '_______________' }}
                        </p>
                    @endif
                    @if($block['data']['showDate'] ?? true)
                        <p style="font-size: 9pt; color: #64748b; margin-bottom: 0;">
                            <span style="font-weight: 600;">{{ __('Date') }}:</span>
                            {{ $variables['offer.date'] ?? now()->format('d.m.Y') }}
                        </p>
                    @endif
                </td>
                <td style="width: 50%; text-align: right; vertical-align: bottom;">
                    {{-- Signature Line --}}
                    <div style="display: inline-block; text-align: center;">
                        <div style="border-bottom: 1px solid #64748b; width: 200px; margin-bottom: 5px; height: 40px;"></div>
                        <p style="font-size: 8pt; color: #94a3b8; margin: 0;">{{ __('Signature') }}</p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Digital Acceptance Notice (for online offers) --}}
        @if(isset($offer) && $offer->acceptance_token)
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #86efac;">
                <p style="font-size: 8pt; color: #64748b; text-align: center; margin: 0;">
                    {{ __('This offer can be accepted online at:') }}
                    <span style="color: #16a34a; font-weight: 500;">{{ $variables['offer.acceptance_url'] ?? url('/offers/view/' . $offer->acceptance_token) }}</span>
                </p>
            </div>
        @endif
    </div>

    {{-- Legal Notice --}}
    <p style="font-size: 8pt; color: #94a3b8; text-align: center; margin-top: 15px; font-style: italic;">
        {{ __('This offer is valid until') }} {{ $variables['offer.valid_until'] ?? __('the date specified above') }}.
        {{ __('After this date, terms and pricing may be subject to change.') }}
    </p>
</div>
