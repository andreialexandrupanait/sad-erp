@props(['isTemplate' => false])

<div>
    @if($isTemplate)
        {{-- Template Header - Static placeholders --}}
        <div class="bg-slate-800 text-white px-8 py-6">
            <div class="flex justify-between items-start">
                <div class="space-y-1">
                    <p class="text-sm text-slate-300">{{ __('Date') }}: <span class="text-white">DD.MM.YYYY</span></p>
                    <p class="text-lg font-medium">{{ __('Service proposal for') }}: <span class="text-slate-300">{{ __('Client Name') }}</span></p>
                    <p class="text-sm text-slate-300">{{ __('Valid until') }}: <span class="text-white">DD.MM.YYYY</span></p>
                </div>
                <div class="flex items-center">
                    @if($organization->logo ?? false)
                        <img src="{{ Storage::url($organization->logo) }}" alt="{{ $organization->name }}" class="h-14 w-auto object-contain brightness-0 invert">
                    @else
                        <span class="text-2xl font-bold">{{ $organization->name ?? config('app.name') }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="bg-slate-700 text-white px-8 py-6">
            <div class="flex gap-8">
                <div class="flex-1 space-y-2">
                    <input type="text" x-model="block.data.introTitle"
                           placeholder="{{ __('Your business partner for digital solutions.') }}"
                           class="w-full text-lg font-semibold bg-slate-600/50 text-white border border-slate-500 rounded px-3 py-2 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 placeholder:text-slate-400">
                    <textarea x-model="block.data.introText"
                              placeholder="{{ __('We deliver high-quality services tailored to your specific needs.') }}"
                              rows="3"
                              class="w-full text-sm bg-slate-600/50 text-slate-200 border border-slate-500 rounded px-3 py-2 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 placeholder:text-slate-400 resize-none leading-relaxed"></textarea>
                </div>
                <div class="w-72 text-sm space-y-1">
                    <p><span class="text-slate-400">{{ __('Email') }}:</span> {{ $organization->email ?? 'email@company.com' }}</p>
                    <p><span class="text-slate-400">{{ __('Phone') }}:</span> {{ $organization->phone ?? '0700 000 000' }}</p>
                </div>
            </div>
        </div>
        <div class="px-8 py-4 bg-white border-b border-slate-200">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">{{ __('Proposal for') }}</p>
                    <p class="font-semibold text-slate-900">{{ __('Client Company Name') }}</p>
                </div>
                <p class="text-2xl font-bold text-slate-900">OFR-YYYY-XXX</p>
            </div>
        </div>
    @else
        {{-- Offer Header - Dynamic data --}}
        {{-- Top Bar: Date info + Logo --}}
        <div class="bg-slate-900 text-white px-10 py-5">
            <div class="flex justify-between items-center">
                {{-- Left: Date and Validity --}}
                <div class="flex items-center gap-6 text-sm">
                    <div>
                        <span class="text-slate-400">{{ __('Date') }}:</span>
                        <span class="ml-1 font-medium" x-text="formatDate(new Date())"></span>
                    </div>
                    <div>
                        <span class="text-slate-400">{{ __('Valid until') }}:</span>
                        <span class="ml-1 font-medium" x-text="formatDate(offer.valid_until)"></span>
                    </div>
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

        {{-- Intro Section + Contact Details --}}
        <div class="bg-slate-800 text-white px-10 py-6">
            <div class="flex gap-10">
                {{-- Left: Intro Text (50%) --}}
                <div class="w-1/2">
                    <h2 class="text-xl font-semibold mb-3 leading-tight" x-text="block.data.introTitle || '{{ __('Your business partner for digital solutions.') }}'"></h2>
                    <p class="text-sm text-slate-300 leading-relaxed" x-text="block.data.introText || '{{ __('We deliver high-quality services tailored to your specific needs. Our team is dedicated to helping you achieve your business goals.') }}'"></p>
                </div>
                {{-- Right: Contact Details (50%) --}}
                <div class="w-1/2 text-sm space-y-1.5 border-l border-slate-700 pl-8">
                    @if($organization->email ?? false)
                        <p class="flex items-start gap-2">
                            <span class="text-slate-500 w-16 flex-shrink-0">{{ __('Email') }}:</span>
                            <span class="text-slate-200">{{ $organization->email }}</span>
                        </p>
                    @endif
                    @if($organization->phone ?? false)
                        <p class="flex items-start gap-2">
                            <span class="text-slate-500 w-16 flex-shrink-0">{{ __('Phone') }}:</span>
                            <span class="text-slate-200">{{ $organization->phone }}</span>
                        </p>
                    @endif
                    @if($organization->address ?? false)
                        <p class="flex items-start gap-2">
                            <span class="text-slate-500 w-16 flex-shrink-0">{{ __('Address') }}:</span>
                            <span class="text-slate-200">{{ $organization->address }}</span>
                        </p>
                    @endif
                    @if($organization->registration_number ?? false)
                        <p class="flex items-start gap-2">
                            <span class="text-slate-500 w-16 flex-shrink-0">{{ __('CUI') }}:</span>
                            <span class="text-slate-200">{{ $organization->registration_number }}</span>
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
                                    <span class="text-slate-500 w-16 flex-shrink-0">{{ $bankAbbr }} {{ $account['currency'] ?? 'RON' }}:</span>
                                    <span class="text-slate-200 font-mono text-xs">{{ $account['iban'] ?? '' }}</span>
                                </p>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Client Info + Offer Number --}}
        <div class="px-10 py-6 bg-white border-b border-slate-100">
            <div class="flex justify-between items-start">
                {{-- Client Details --}}
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-widest mb-2">{{ __('Proposal for') }}</p>
                    <template x-if="offer.client_id === 'new'">
                        <div class="space-y-0.5">
                            <p class="text-lg font-semibold text-slate-900" x-text="newClient.company_name || '{{ __('New Client') }}'"></p>
                            <p x-show="newClient.contact_person" class="text-sm text-slate-600" x-text="'Attn: ' + newClient.contact_person"></p>
                            <p x-show="newClient.address" class="text-sm text-slate-500" x-text="newClient.address"></p>
                            <p x-show="newClient.tax_id" class="text-sm text-slate-500">{{ __('Tax ID') }}: <span x-text="newClient.tax_id"></span></p>
                        </div>
                    </template>
                    <template x-if="offer.client_id && offer.client_id !== 'new'">
                        <div class="space-y-0.5">
                            <p class="text-lg font-semibold text-slate-900" x-text="selectedClient.company || selectedClient.name || '{{ __('Select a client') }}'"></p>
                            <p x-show="selectedClient.contact" class="text-sm text-slate-600" x-text="'Attn: ' + selectedClient.contact"></p>
                            <p x-show="selectedClient.address" class="text-sm text-slate-500" x-text="selectedClient.address"></p>
                            <p x-show="selectedClient.tax" class="text-sm text-slate-500">{{ __('Tax ID') }}: <span x-text="selectedClient.tax"></span></p>
                        </div>
                    </template>
                    <template x-if="!offer.client_id">
                        <p class="text-slate-400 italic">{{ __('Select a client') }}</p>
                    </template>
                </div>
                {{-- Offer Number --}}
                <div class="text-right">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-widest mb-2">{{ __('Offer Number') }}</p>
                    <p class="text-2xl font-bold text-slate-900 tracking-tight">OFR-<span x-text="new Date().getFullYear()"></span>-XXX</p>
                </div>
            </div>
        </div>
    @endif
</div>
