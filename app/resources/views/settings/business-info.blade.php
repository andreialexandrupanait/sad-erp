<x-app-layout>
    <x-slot name="pageTitle">{{ __('Informații companie') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
                        <a href="{{ route('settings.business') }}" class="hover:text-slate-700">{{ __('Setări afacere') }}</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span>{{ __('Informații companie') }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ __('Informații companie') }}</h1>
                    <p class="text-slate-500 mt-1">{{ __('Gestionează datele companiei tale care apar pe oferte, contracte și facturi') }}</p>
                </div>

                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                @endif

                <form action="{{ route('settings.business-info.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Logo Section -->
                    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 bg-slate-100 border-b border-slate-200 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Logo companie') }}</h3>
                        </div>
                        <div class="p-6">
                        <div class="flex items-start gap-6">
                            <div class="flex-shrink-0">
                                <div id="logoPreview" class="w-32 h-32 bg-slate-100 rounded-lg flex items-center justify-center border-2 border-dashed border-slate-300 overflow-hidden">
                                    @if($organization->logo)
                                        <img src="{{ Storage::url($organization->logo) }}" alt="Logo" class="w-full h-full object-contain">
                                    @else
                                        <div class="text-center">
                                            <svg class="w-8 h-8 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-xs text-slate-400 mt-1">{{ __('Fără logo') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1">
                                <label class="block">
                                    <span class="sr-only">{{ __('Alege logo') }}</span>
                                    <input type="file" name="logo" accept="image/*"
                                           onchange="previewLogo(this)"
                                           class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                                </label>
                                <p class="text-xs text-slate-500 mt-2">{{ __('PNG, JPG sau SVG. Max 2MB. Recomandat: 400x400px') }}</p>
                                @if($organization->logo)
                                    <label class="flex items-center gap-2 mt-3">
                                        <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300">
                                        <span class="text-sm text-red-600">{{ __('Șterge logo-ul curent') }}</span>
                                    </label>
                                @endif
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Obligatoriu Section -->
                    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 bg-slate-100 border-b border-slate-200 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Date obligatorii') }}</h3>
                        </div>
                        <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Denumire firmă') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $organization->name) }}" required
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                @error('name')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('CIF') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="tax_id" value="{{ old('tax_id', $organization->tax_id) }}" required
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="RO12345678">
                                @error('tax_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('CIF intracomunitar') }}</label>
                                <input type="text" name="vat_id" value="{{ old('vat_id', $organization->settings['vat_id'] ?? '') }}"
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="RO12345678">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Reg. comerțului') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="trade_registry" value="{{ old('trade_registry', $organization->settings['trade_registry'] ?? '') }}" required
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="J40/1234/2020">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Capital social') }}</label>
                                <input type="text" name="share_capital" value="{{ old('share_capital', $organization->settings['share_capital'] ?? '') }}"
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="200 Lei">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Reprezentant legal') }}</label>
                                <input type="text" name="representative" value="{{ old('representative', $organization->settings['representative'] ?? '') }}"
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="{{ __('Nume și prenume reprezentant') }}">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Adresa') }} <span class="text-red-500">*</span></label>
                                <textarea name="address" rows="2" required
                                       class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="{{ __('Str., Nr., Bl., Sc., Ap.') }}">{{ old('address', $organization->address) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Localitate') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="city" value="{{ old('city', $organization->settings['city'] ?? '') }}" required
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="București">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Județ') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="county" value="{{ old('county', $organization->settings['county'] ?? '') }}" required
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="București">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Țara') }} <span class="text-red-500">*</span></label>
                                <select name="country" required class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                    <option value="Romania" {{ ($organization->settings['country'] ?? 'Romania') == 'Romania' ? 'selected' : '' }}>România</option>
                                    <option value="Bulgaria" {{ ($organization->settings['country'] ?? '') == 'Bulgaria' ? 'selected' : '' }}>Bulgaria</option>
                                    <option value="Hungary" {{ ($organization->settings['country'] ?? '') == 'Hungary' ? 'selected' : '' }}>Ungaria</option>
                                    <option value="Moldova" {{ ($organization->settings['country'] ?? '') == 'Moldova' ? 'selected' : '' }}>Republica Moldova</option>
                                    <option value="Other" {{ ($organization->settings['country'] ?? '') == 'Other' ? 'selected' : '' }}>Altă țară</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Plătitor TVA?') }}</label>
                                <div class="flex items-center gap-4 h-10">
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="vat_payer" value="1" {{ ($organization->settings['vat_payer'] ?? false) ? 'checked' : '' }} class="border-slate-300">
                                        <span class="text-sm text-slate-700">{{ __('Da') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="vat_payer" value="0" {{ !($organization->settings['vat_payer'] ?? false) ? 'checked' : '' }} class="border-slate-300">
                                        <span class="text-sm text-slate-700">{{ __('Nu') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Optional Section -->
                    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 bg-slate-100 border-b border-slate-200 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Date opționale') }}</h3>
                        </div>
                        <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Telefon') }}</label>
                                <input type="tel" name="phone" value="{{ old('phone', $organization->phone) }}"
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="+40 xxx xxx xxx">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Fax') }}</label>
                                <input type="tel" name="fax" value="{{ old('fax', $organization->settings['fax'] ?? '') }}"
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="+40 xxx xxx xxx">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Email') }}</label>
                                <input type="email" name="email" value="{{ old('email', $organization->email) }}"
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="office@company.com">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Adresa web') }}</label>
                                <input type="url" name="website" value="{{ old('website', $organization->settings['website'] ?? '') }}"
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="https://www.company.com">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Date adiționale') }}</label>
                                <textarea name="additional_info" rows="2"
                                          class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                          placeholder="{{ __('Informații suplimentare care apar pe documente...') }}">{{ old('additional_info', $organization->settings['additional_info'] ?? '') }}</textarea>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Setări documente Section -->
                    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 bg-slate-100 border-b border-slate-200 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Setări documente') }}</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Prefix oferte') }}</label>
                                    <input type="text" name="offer_prefix" value="{{ old('offer_prefix', $organization->settings['offer_prefix'] ?? 'OFR') }}"
                                           class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                           placeholder="OFR SAD">
                                    <p class="text-xs text-slate-500 mt-1">{{ __('Prefixul pentru numerele de oferte (ex: OFR SAD-2025-001)') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Prefix contracte') }}</label>
                                    <input type="text" name="contract_prefix" value="{{ old('contract_prefix', $organization->settings['contract_prefix'] ?? 'CTR') }}"
                                           class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                           placeholder="CTR SAD">
                                    <p class="text-xs text-slate-500 mt-1">{{ __('Prefixul pentru numerele de contracte') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conturi bancare Section -->
                    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 bg-slate-100 border-b border-slate-200 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Conturi bancare') }}</h3>
                        </div>
                        <div class="p-6">
                        <!-- Add new account form -->
                        <div class="flex flex-col md:flex-row gap-3 items-end mb-6">
                            <div class="w-full md:flex-[2]">
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('IBAN') }}</label>
                                <input type="text" id="new_iban"
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="RO49AAAA1B31007593840000">
                            </div>
                            <div class="w-full md:flex-[2]">
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Banca') }}</label>
                                <input type="text" id="new_bank"
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="Banca Transilvania">
                            </div>
                            <div class="w-full md:w-28">
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Moneda') }}</label>
                                <select id="new_currency" class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                    <option value="RON">RON</option>
                                    <option value="EUR">EUR</option>
                                    <option value="USD">USD</option>
                                </select>
                            </div>
                            <div class="w-full md:flex-[1.5]">
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Descriere') }}</label>
                                <input type="text" id="new_description"
                                       class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                       placeholder="{{ __('Cont principal') }}">
                            </div>
                            <div class="flex-shrink-0">
                                <button type="button" onclick="addBankAccount()" class="h-10 px-4 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 whitespace-nowrap">
                                    {{ __('Adaugă cont') }}
                                </button>
                            </div>
                        </div>

                        <!-- Bank accounts table -->
                        @php
                            $bankAccounts = $organization->settings['bank_accounts'] ?? [];
                            $bankAccounts = array_filter($bankAccounts, fn($a) => !empty($a['iban']));
                        @endphp

                        <div id="bankAccountsTable" class="{{ count($bankAccounts) ? '' : 'hidden' }}">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        <th class="pb-3 pr-4">{{ __('IBAN') }}</th>
                                        <th class="pb-3 pr-4">{{ __('Banca') }}</th>
                                        <th class="pb-3 pr-4 w-20">{{ __('Moneda') }}</th>
                                        <th class="pb-3 pr-4">{{ __('Descriere') }}</th>
                                        <th class="pb-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody id="bankAccountsBody">
                                    @foreach($bankAccounts as $index => $account)
                                    @if(!empty($account['iban']))
                                    <tr class="bank-account-row border-b border-slate-100 last:border-0">
                                        <td class="py-3 pr-4">
                                            <span class="text-sm text-blue-600">{{ $account['iban'] ?? '' }}</span>
                                            <input type="hidden" name="bank_accounts[{{ $index }}][iban]" value="{{ $account['iban'] ?? '' }}">
                                        </td>
                                        <td class="py-3 pr-4">
                                            <span class="text-sm text-slate-700">{{ $account['bank'] ?? '' }}</span>
                                            <input type="hidden" name="bank_accounts[{{ $index }}][bank]" value="{{ $account['bank'] ?? '' }}">
                                        </td>
                                        <td class="py-3 pr-4">
                                            <span class="text-sm text-slate-700">{{ $account['currency'] ?? 'RON' }}</span>
                                            <input type="hidden" name="bank_accounts[{{ $index }}][currency]" value="{{ $account['currency'] ?? 'RON' }}">
                                        </td>
                                        <td class="py-3 pr-4">
                                            <span class="text-sm text-slate-700">{{ $account['description'] ?? '' }}</span>
                                            <input type="hidden" name="bank_accounts[{{ $index }}][description]" value="{{ $account['description'] ?? '' }}">
                                        </td>
                                        <td class="py-3">
                                            <button type="button" onclick="removeBankAccount(this)" class="p-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div id="noAccountsMessage" class="{{ count($bankAccounts) ? 'hidden' : '' }} text-center py-6 text-sm text-slate-500">
                            {{ __('Nu ai adăugat încă niciun cont bancar.') }}
                        </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('settings.business') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">
                            {{ __('Anulează') }}
                        </a>
                        <button type="submit" class="px-6 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
                            {{ __('Salvează modificările') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewLogo(input) {
            const preview = document.getElementById('logoPreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Logo" class="w-full h-full object-contain">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        let bankAccountIndex = {{ count($bankAccounts) }};

        function addBankAccount() {
            const iban = document.getElementById('new_iban').value.trim();
            const bank = document.getElementById('new_bank').value.trim();
            const currency = document.getElementById('new_currency').value;
            const description = document.getElementById('new_description').value.trim();

            if (!iban) {
                alert('{{ __("Te rugăm să introduci un IBAN.") }}');
                return;
            }

            const tbody = document.getElementById('bankAccountsBody');
            const table = document.getElementById('bankAccountsTable');
            const noMessage = document.getElementById('noAccountsMessage');

            const row = document.createElement('tr');
            row.className = 'bank-account-row border-b border-slate-100 last:border-0';
            row.innerHTML = `
                <td class="py-3 pr-4">
                    <span class="text-sm text-blue-600">${iban}</span>
                    <input type="hidden" name="bank_accounts[${bankAccountIndex}][iban]" value="${iban}">
                </td>
                <td class="py-3 pr-4">
                    <span class="text-sm text-slate-700">${bank}</span>
                    <input type="hidden" name="bank_accounts[${bankAccountIndex}][bank]" value="${bank}">
                </td>
                <td class="py-3 pr-4">
                    <span class="text-sm text-slate-700">${currency}</span>
                    <input type="hidden" name="bank_accounts[${bankAccountIndex}][currency]" value="${currency}">
                </td>
                <td class="py-3 pr-4">
                    <span class="text-sm text-slate-700">${description}</span>
                    <input type="hidden" name="bank_accounts[${bankAccountIndex}][description]" value="${description}">
                </td>
                <td class="py-3">
                    <button type="button" onclick="removeBankAccount(this)" class="p-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </td>
            `;

            tbody.appendChild(row);
            table.classList.remove('hidden');
            noMessage.classList.add('hidden');
            bankAccountIndex++;

            // Clear inputs
            document.getElementById('new_iban').value = '';
            document.getElementById('new_bank').value = '';
            document.getElementById('new_currency').value = 'RON';
            document.getElementById('new_description').value = '';
        }

        function removeBankAccount(button) {
            const row = button.closest('tr');
            const tbody = document.getElementById('bankAccountsBody');
            row.remove();

            // Check if table is empty
            if (tbody.querySelectorAll('tr').length === 0) {
                document.getElementById('bankAccountsTable').classList.add('hidden');
                document.getElementById('noAccountsMessage').classList.remove('hidden');
            }
        }
    </script>
</x-app-layout>
