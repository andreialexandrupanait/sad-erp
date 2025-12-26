{{-- Header Block - PDF View --}}
{{-- Variables: block, offer, variables, organization, client, bankAccounts --}}

<div style="margin-bottom: 30px;">
    {{-- Top Section: Logo + Company Info + Dates --}}
    <table style="width: 100%; margin-bottom: 0;">
        <tr>
            <td style="width: 60%; vertical-align: top; padding: 20px; background-color: #1e293b;">
                {{-- Dates --}}
                <p style="font-size: 9pt; color: #94a3b8; margin-bottom: 5px;">
                    {{ __('Date') }}: <span style="color: #ffffff;">{{ $variables['offer.date'] ?? now()->format('d.m.Y') }}</span>
                </p>
                <p style="font-size: 11pt; color: #ffffff; margin-bottom: 5px;">
                    {{ __('Service proposal for') }}: <span style="color: #94a3b8;">{{ $variables['client.company'] ?: $variables['client.name'] }}</span>
                </p>
                <p style="font-size: 9pt; color: #94a3b8;">
                    {{ __('Valid until') }}: <span style="color: #ffffff;">{{ $variables['offer.valid_until'] ?? '' }}</span>
                </p>
            </td>
            <td style="width: 40%; vertical-align: middle; text-align: right; padding: 20px; background-color: #1e293b;">
                @if($organization->logo ?? false)
                    <img src="{{ public_path('storage/' . $organization->logo) }}" style="height: 50px; max-width: 180px;" alt="{{ $organization->name }}">
                @else
                    <span style="font-size: 18pt; font-weight: bold; color: #ffffff;">{{ $variables['company.name'] }}</span>
                @endif
            </td>
        </tr>
    </table>

    {{-- Middle Section: Intro Text + Contact Info --}}
    <table style="width: 100%; margin-bottom: 0;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding: 20px; background-color: #334155;">
                <p style="font-size: 12pt; font-weight: bold; color: #ffffff; margin-bottom: 10px;">
                    {{ $block['data']['introTitle'] ?? __('Your business partner for digital solutions.') }}
                </p>
                <p style="font-size: 9pt; color: #cbd5e1; line-height: 1.5;">
                    {{ $block['data']['introText'] ?? __('We deliver high-quality services tailored to your specific needs.') }}
                </p>
            </td>
            <td style="width: 50%; vertical-align: top; padding: 20px; background-color: #334155; border-left: 1px solid #475569;">
                <table style="width: 100%; font-size: 9pt;">
                    @if($organization->email ?? false)
                    <tr>
                        <td style="color: #94a3b8; width: 60px; padding: 2px 0;">{{ __('Email') }}:</td>
                        <td style="color: #e2e8f0; padding: 2px 0;">{{ $organization->email }}</td>
                    </tr>
                    @endif
                    @if($organization->phone ?? false)
                    <tr>
                        <td style="color: #94a3b8; padding: 2px 0;">{{ __('Phone') }}:</td>
                        <td style="color: #e2e8f0; padding: 2px 0;">{{ $organization->phone }}</td>
                    </tr>
                    @endif
                    @if($organization->address ?? false)
                    <tr>
                        <td style="color: #94a3b8; padding: 2px 0;">{{ __('Address') }}:</td>
                        <td style="color: #e2e8f0; padding: 2px 0;">{{ $organization->address }}</td>
                    </tr>
                    @endif
                    @if($organization->registration_number ?? false)
                    <tr>
                        <td style="color: #94a3b8; padding: 2px 0;">{{ __('CUI') }}:</td>
                        <td style="color: #e2e8f0; padding: 2px 0;">{{ $organization->registration_number }}</td>
                    </tr>
                    @endif
                </table>
                @if(isset($bankAccounts) && $bankAccounts->count() > 0)
                    <div style="border-top: 1px solid #475569; margin-top: 8px; padding-top: 8px;">
                        <table style="width: 100%; font-size: 8pt;">
                            @foreach($bankAccounts as $account)
                                @php
                                    $bankName = $account['bank'] ?? '';
                                    $words = preg_split('/\s+/', trim($bankName));
                                    $bankAbbr = '';
                                    foreach ($words as $word) {
                                        if (strlen($word) > 0) $bankAbbr .= strtoupper($word[0]);
                                    }
                                @endphp
                                <tr>
                                    <td style="color: #94a3b8; width: 60px; padding: 2px 0;">{{ $bankAbbr }} {{ $account['currency'] ?? 'RON' }}:</td>
                                    <td style="color: #e2e8f0; padding: 2px 0; font-family: monospace;">{{ $account['iban'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Bottom Section: Client Info + Offer Number --}}
    <table style="width: 100%; background-color: #ffffff; border-bottom: 1px solid #e2e8f0;">
        <tr>
            <td style="width: 60%; vertical-align: top; padding: 20px;">
                <p style="font-size: 8pt; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">
                    {{ __('Proposal for') }}
                </p>
                <p style="font-size: 14pt; font-weight: bold; color: #1e293b; margin-bottom: 3px;">
                    {{ $variables['client.company'] ?: $variables['client.name'] }}
                </p>
                @if($client?->contact_person)
                    <p style="font-size: 10pt; color: #64748b;">{{ __('Attn') }}: {{ $client->contact_person }}</p>
                @endif
                @if($client?->address)
                    <p style="font-size: 9pt; color: #94a3b8;">{{ $client->address }}</p>
                @endif
                @if($client?->tax_id)
                    <p style="font-size: 9pt; color: #94a3b8;">{{ __('Tax ID') }}: {{ $client->tax_id }}</p>
                @endif
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right; padding: 20px;">
                <p style="font-size: 8pt; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">
                    {{ __('Offer Number') }}
                </p>
                <p style="font-size: 20pt; font-weight: bold; color: #1e293b;">
                    {{ $variables['offer.number'] ?? 'OFR-' . date('Y') . '-XXX' }}
                </p>
            </td>
        </tr>
    </table>
</div>
