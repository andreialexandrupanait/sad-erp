{{--
    Header Block - Full Company + Client Info
    Used in both admin builder and public view

    Props:
    - $mode: 'admin' (editable, uses Alpine) or 'public' (read-only, uses Blade)
    - $organization: Organization model
    - $bankAccounts: Collection of bank accounts
    - $offer: Offer model (for public mode)
    - $headerData: Array of header data (for public mode)
    - $client: Client model (for public mode)
--}}
@props([
    'mode' => 'admin',
    'offer' => null,
    'headerData' => [],
    'client' => null,
])

<div class="offer-header-block">
    {{-- Top Bar: Date info + Logo --}}
    <div class="bg-slate-900 text-white px-8 py-4">
        <div class="flex justify-between items-center">
            {{-- Left: Date and Validity --}}
            <div class="flex items-center gap-6 text-sm">
                @if($mode === 'public')
                    <div>
                        <span class="text-slate-400">{{ __('Date') }}:</span>
                        <span class="ml-1 font-medium">{{ $offer->created_at->format('d.m.Y') }}</span>
                    </div>
                    @if($offer->valid_until)
                        <div>
                            <span class="text-slate-400">{{ __('Valid until') }}:</span>
                            <span class="ml-1 font-medium {{ $offer->valid_until->isPast() ? 'text-red-400' : '' }}">
                                {{ $offer->valid_until->format('d.m.Y') }}
                            </span>
                        </div>
                    @endif
                @else
                    <div>
                        <span class="text-slate-400">{{ __('Date') }}:</span>
                        <span class="ml-1 font-medium" x-text="formatDate(new Date())"></span>
                    </div>
                    <div>
                        <span class="text-slate-400">{{ __('Valid until') }}:</span>
                        <span class="ml-1 font-medium" x-text="formatDate(offer.valid_until)"></span>
                    </div>
                @endif
            </div>
            {{-- Right: Logo --}}
            <div>
                @if($organization->logo ?? false)
                    <img src="{{ Storage::url($organization->logo) }}" alt="{{ $organization->name }}" class="h-10 w-auto object-contain brightness-0 invert">
                @else
                    <span class="text-xl font-bold tracking-tight">{{ $organization->name ?? config('app.name') }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Intro Section + Company Contact Details --}}
    <div class="bg-slate-800 text-white px-8 py-6">
        <div class="flex gap-8">
            {{-- Left: Intro Text --}}
            <div class="w-1/2 space-y-2">
                @if($mode === 'public')
                    <h3 class="text-xl font-bold whitespace-pre-wrap">{{ $headerData['introTitle'] ?? __('Your business partner for digital solutions.') }}</h3>
                    <p class="text-sm text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $headerData['introText'] ?? __('We deliver high-quality services tailored to your specific needs.') }}</p>
                @else
                    <textarea x-show="!previewMode" x-model="headerData.introTitle"
                              placeholder="{{ __('Your business partner for digital solutions.') }}"
                              x-ref="introTitleTextarea"
                              x-effect="$nextTick(() => { if($refs.introTitleTextarea) { $refs.introTitleTextarea.style.height = 'auto'; $refs.introTitleTextarea.style.height = $refs.introTitleTextarea.scrollHeight + 'px'; } })"
                              x-on:input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                              class="w-full text-xl font-bold bg-transparent text-white border-none p-0 focus:ring-0 placeholder:text-slate-400 resize-none overflow-hidden min-h-[28px]"></textarea>
                    <h3 x-show="previewMode" class="text-xl font-bold whitespace-pre-wrap" x-text="headerData.introTitle || '{{ __('Your business partner for digital solutions.') }}'"></h3>

                    <textarea x-show="!previewMode" x-model="headerData.introText"
                              placeholder="{{ __('We deliver high-quality services tailored to your specific needs.') }}"
                              x-ref="introTextTextarea"
                              x-effect="$nextTick(() => { if($refs.introTextTextarea) { $refs.introTextTextarea.style.height = 'auto'; $refs.introTextTextarea.style.height = $refs.introTextTextarea.scrollHeight + 'px'; } })"
                              x-on:input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                              class="w-full text-sm bg-transparent text-slate-300 border-none p-0 focus:ring-0 placeholder:text-slate-500 resize-none overflow-hidden leading-relaxed min-h-[40px]"></textarea>
                    <p x-show="previewMode" class="text-sm text-slate-300 leading-relaxed whitespace-pre-wrap" x-text="headerData.introText || '{{ __('We deliver high-quality services tailored to your specific needs.') }}'"></p>
                @endif
            </div>

            {{-- Right: Company Contact Details --}}
            <div class="w-1/2 text-xs space-y-1.5 border-l border-slate-700 pl-6">
                @if($organization->email ?? false)
                    <p class="flex items-start gap-2">
                        <span class="text-slate-500 w-20 flex-shrink-0">{{ __('Email') }}:</span>
                        <span class="text-slate-200">{{ $organization->email }}</span>
                    </p>
                @endif
                @if($organization->phone ?? false)
                    <p class="flex items-start gap-2">
                        <span class="text-slate-500 w-20 flex-shrink-0">{{ __('Telefon') }}:</span>
                        <span class="text-slate-200">{{ $organization->phone }}</span>
                    </p>
                @endif
                @if($organization->address ?? false)
                    <p class="flex items-start gap-2">
                        <span class="text-slate-500 w-20 flex-shrink-0">{{ __('Adresă') }}:</span>
                        <span class="text-slate-200">{{ $organization->address }}</span>
                    </p>
                @endif
                @php
                    $cif = $organization->tax_id ?? ($organization->settings['vat_id'] ?? null);
                    $regCom = $organization->settings['trade_registry'] ?? null;
                @endphp
                @if($cif)
                    <p class="flex items-start gap-2">
                        <span class="text-slate-500 w-20 flex-shrink-0">{{ __('CIF') }}:</span>
                        <span class="text-slate-200">{{ $cif }}</span>
                    </p>
                @endif
                @if($regCom)
                    <p class="flex items-start gap-2">
                        <span class="text-slate-500 w-20 flex-shrink-0">{{ __('Reg. Com.') }}:</span>
                        <span class="text-slate-200">{{ $regCom }}</span>
                    </p>
                @endif
                @if(isset($bankAccounts) && $bankAccounts->count() > 0)
                    <div class="pt-2 mt-2 border-t border-slate-700 space-y-1">
                        @foreach($bankAccounts as $account)
                            @php
                                $bankName = $account['bank'] ?? '';
                                $words = preg_split('/\s+/', trim($bankName));
                                $bankAbbr = '';
                                foreach ($words as $word) {
                                    if (strlen($word) > 0) $bankAbbr .= strtoupper($word[0]);
                                }
                            @endphp
                            <p class="flex items-start gap-2">
                                <span class="text-slate-500 w-20 flex-shrink-0">{{ $bankAbbr }} {{ $account['currency'] ?? 'RON' }}:</span>
                                <span class="text-slate-200 font-mono">{{ $account['iban'] ?? '' }}</span>
                            </p>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Client Info + Offer Title --}}
    <div class="px-8 py-6 bg-white {{ $mode === 'public' ? 'border-b border-slate-200' : '' }}">
        <div class="flex justify-between items-start">
            {{-- Left: Client Details --}}
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-widest mb-2">{{ __('Proposal for') }}</p>
                @if($mode === 'public')
                    @if($client)
                        <p class="text-lg font-semibold text-slate-900">{{ $client->company_name ?? $client->name }}</p>
                        @if($client->contact_person)
                            <p class="text-sm text-slate-600">{{ __('Attn') }}: {{ $client->contact_person }}</p>
                        @endif
                        @if($client->email)
                            <p class="text-sm text-slate-500">{{ $client->email }}</p>
                        @endif
                        @if($client->phone)
                            <p class="text-sm text-slate-500">{{ __('Tel') }}: {{ $client->phone }}</p>
                        @endif
                        @if($client->address)
                            <p class="text-sm text-slate-500">{{ $client->address }}</p>
                        @endif
                        @if($client->tax_id)
                            <p class="text-sm text-slate-500">CUI: {{ $client->tax_id }}</p>
                        @endif
                        @if($client->registration_number)
                            <p class="text-sm text-slate-500">{{ $client->registration_number }}</p>
                        @endif
                    @elseif($offer->temp_client_name || $offer->temp_client_company)
                        <p class="text-lg font-semibold text-slate-900">{{ $offer->temp_client_company ?: $offer->temp_client_name }}</p>
                        @if($offer->temp_client_company && $offer->temp_client_name)
                            <p class="text-sm text-slate-600">{{ __('Attn') }}: {{ $offer->temp_client_name }}</p>
                        @endif
                        @if($offer->temp_client_email)
                            <p class="text-sm text-slate-500">{{ $offer->temp_client_email }}</p>
                        @endif
                        @if($offer->temp_client_phone)
                            <p class="text-sm text-slate-500">{{ __('Tel') }}: {{ $offer->temp_client_phone }}</p>
                        @endif
                        @if($offer->temp_client_address)
                            <p class="text-sm text-slate-500">{{ $offer->temp_client_address }}</p>
                        @endif
                        @if($offer->temp_client_tax_id)
                            <p class="text-sm text-slate-500">CUI: {{ $offer->temp_client_tax_id }}</p>
                        @endif
                        @if($offer->temp_client_registration_number)
                            <p class="text-sm text-slate-500">{{ $offer->temp_client_registration_number }}</p>
                        @endif
                    @else
                        <p class="text-slate-400 italic">{{ __('Customer') }}</p>
                    @endif
                @else
                    <template x-if="offer.client_id === 'new'">
                        <div class="space-y-0.5">
                            <p class="text-lg font-semibold text-slate-900" x-text="newClient.company_name || '{{ __('New Client') }}'"></p>
                            <p x-show="newClient.contact_person" class="text-sm text-slate-600" x-text="'Attn: ' + newClient.contact_person"></p>
                            <p x-show="newClient.email" class="text-sm text-slate-500" x-text="newClient.email"></p>
                        </div>
                    </template>
                    <template x-if="offer.client_id && offer.client_id !== 'new'">
                        <div class="space-y-0.5">
                            <p class="text-lg font-semibold text-slate-900" x-text="selectedClient?.company_name || selectedClient?.name || '{{ __('Select a client') }}'"></p>
                            <p x-show="selectedClient?.contact_person" class="text-sm text-slate-600" x-text="'Attn: ' + selectedClient?.contact_person"></p>
                            <p x-show="selectedClient?.email" class="text-sm text-slate-500" x-text="selectedClient?.email"></p>
                        </div>
                    </template>
                    <template x-if="!offer.client_id">
                        <p class="text-slate-400 italic">{{ __('Select a client') }}</p>
                    </template>
                @endif
            </div>

            {{-- Right: Offer Number & Title --}}
            <div class="text-right">
                <p class="text-xs font-medium text-slate-400 uppercase tracking-widest mb-2">{{ __('Offer') }}</p>
                @if($mode === 'public')
                    <p class="text-xl font-bold text-slate-900">{{ $offer->offer_number }}</p>
                    @if($offer->title)
                        <p class="text-sm text-slate-500 mt-1">{{ $offer->title }}</p>
                    @endif
                @else
                    <p class="text-xl font-bold text-slate-900" x-text="offer.offer_number || '{{ __('New Offer') }}'"></p>
                    <p class="text-sm text-slate-500 mt-1" x-text="offer.title"></p>
                @endif
            </div>
        </div>
    </div>
</div>
