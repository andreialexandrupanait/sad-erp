{{-- Acceptance Block - PDF View --}}
<div style="margin-bottom: 30px; page-break-inside: avoid;">
    @if(!empty($block['data']['heading']))
        <h2 style="font-size: 14pt; font-weight: bold; color: #1e293b; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 3px solid #16a34a; display: inline-block;">
            {{ $block['data']['heading'] }}
        </h2>
    @endif

    <div style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px;">
        {{-- Paragraph/Terms --}}
        <p style="font-size: 10pt; color: #475569; line-height: 1.6; margin-bottom: 20px;">
            {{ $block['data']['paragraph'] ?? __('By accepting this offer, you agree to the terms and conditions outlined above.') }}
        </p>

        {{-- Status Display --}}
        @if($offer->status === 'accepted')
            <div style="background-color: #dcfce7; border: 1px solid #bbf7d0; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                <p style="font-weight: 600; color: #166534; margin: 0 0 5px 0; font-size: 11pt;">
                    ✓ {{ __('Offer Accepted') }}
                </p>
                @if($offer->accepted_at)
                    <p style="font-size: 9pt; color: #16a34a; margin: 0;">
                        {{ __('Accepted on') }} {{ $offer->accepted_at->format('d.m.Y H:i') }}
                    </p>
                @endif
            </div>
        @elseif($offer->status === 'rejected')
            <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                <p style="font-weight: 600; color: #991b1b; margin: 0 0 5px 0; font-size: 11pt;">
                    ✗ {{ __('Offer Declined') }}
                </p>
                @if($offer->rejected_at)
                    <p style="font-size: 9pt; color: #dc2626; margin: 0;">
                        {{ __('Declined on') }} {{ $offer->rejected_at->format('d.m.Y H:i') }}
                    </p>
                @endif
            </div>
        @else
            {{-- Signature Lines for Pending Offers --}}
            <div style="margin-top: 30px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 45%; padding: 10px; vertical-align: bottom;">
                            <p style="font-size: 9pt; color: #64748b; margin: 0 0 30px 0;">{{ __('Client Signature') }}:</p>
                            <div style="border-bottom: 1px solid #94a3b8; height: 1px;"></div>
                        </td>
                        <td style="width: 10%;"></td>
                        <td style="width: 45%; padding: 10px; vertical-align: bottom;">
                            <p style="font-size: 9pt; color: #64748b; margin: 0 0 30px 0;">{{ __('Date') }}:</p>
                            <div style="border-bottom: 1px solid #94a3b8; height: 1px;"></div>
                        </td>
                    </tr>
                </table>
            </div>
        @endif

        {{-- Info Note --}}
        <p style="font-size: 8pt; color: #94a3b8; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
            {{ __('For electronic acceptance, a verification code is sent via email. All actions are logged for compliance.') }}
        </p>
    </div>
</div>
