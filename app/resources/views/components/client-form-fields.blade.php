@props([
    'client' => null,
    'statuses' => [],
    'prefix' => '',  // For nested forms, e.g., 'new_client_'
    'compact' => false,  // For slide-over modal (fewer fields, single column)
])

@php
    $namePrefix = $prefix ? $prefix : '';
    $idPrefix = $prefix ? str_replace(['[', ']'], ['_', ''], $prefix) : '';
@endphp

<div class="grid grid-cols-1 gap-x-6 gap-y-5 {{ $compact ? '' : 'sm:grid-cols-6' }}">
    <!-- Name (Required) -->
    <div class="{{ $compact ? '' : 'sm:col-span-3' }} field-wrapper">
        <x-ui.label for="{{ $idPrefix }}name">
            {{ __('Name') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="text"
                name="{{ $namePrefix }}name"
                id="{{ $idPrefix }}name"
                required
                placeholder="{{ __('Enter client name') }}"
                value="{{ old($namePrefix.'name', $client->name ?? '') }}"
            />
        </div>
        @error($namePrefix.'name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Company Name -->
    <div class="{{ $compact ? '' : 'sm:col-span-3' }} field-wrapper">
        <x-ui.label for="{{ $idPrefix }}company_name">{{ __('Company Name') }}</x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="text"
                name="{{ $namePrefix }}company_name"
                id="{{ $idPrefix }}company_name"
                placeholder="{{ __('Enter company name (optional)') }}"
                value="{{ old($namePrefix.'company_name', $client->company_name ?? '') }}"
            />
        </div>
        @error($namePrefix.'company_name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Tax ID (CUI) with Auto-fill -->
    <div class="{{ $compact ? '' : 'sm:col-span-3' }} field-wrapper" x-data="{ loading: false }">
        <x-ui.label for="{{ $idPrefix }}tax_id">{{ __('Tax ID (CUI)') }}</x-ui.label>
        <div class="mt-2 relative">
            <x-ui.input
                type="text"
                name="{{ $namePrefix }}tax_id"
                id="{{ $idPrefix }}tax_id"
                value="{{ old($namePrefix.'tax_id', $client->tax_id ?? '') }}"
                placeholder="{{ __('e.g., RO12345678') }}"
                @blur="
                    const cui = $event.target.value.replace(/[^0-9]/g, '');
                    if (cui.length >= 6 && !document.getElementById('{{ $idPrefix }}company_name').value) {
                        loading = true;
                        fetch(`https://api.openapi.ro/api/companies/${cui}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.found && data.cif) {
                                    if (!document.getElementById('{{ $idPrefix }}company_name').value) {
                                        document.getElementById('{{ $idPrefix }}company_name').value = data.denumire || '';
                                    }
                                    const regNum = document.getElementById('{{ $idPrefix }}registration_number');
                                    if (regNum) regNum.value = data.numar_reg_com || '';
                                    const addr = document.getElementById('{{ $idPrefix }}address');
                                    if (addr) addr.value = data.adresa || '';
                                    const vatPayer = document.getElementById('{{ $idPrefix }}vat_payer');
                                    if (vatPayer) vatPayer.checked = data.tva === 'DA';
                                }
                            })
                            .catch(err => console.error('Error fetching company data:', err))
                            .finally(() => loading = false);
                    }
                "
            />
            <div x-show="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                <svg class="animate-spin h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
        <p class="mt-1 text-xs text-slate-500">{{ __('Company details will be auto-filled from ANAF if available') }}</p>
        @if(!$compact)
            <p class="mt-1 text-xs text-slate-400 italic">{{ __('For clients without CUI, use "-" or leave empty') }}</p>
        @endif
        @error($namePrefix.'tax_id')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Registration Number -->
    <div class="{{ $compact ? '' : 'sm:col-span-3' }} field-wrapper">
        <x-ui.label for="{{ $idPrefix }}registration_number">{{ __('Registration Number') }}</x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="text"
                name="{{ $namePrefix }}registration_number"
                id="{{ $idPrefix }}registration_number"
                value="{{ old($namePrefix.'registration_number', $client->registration_number ?? '') }}"
                placeholder="{{ __('e.g., J40/1234/2020') }}"
            />
        </div>
        @error($namePrefix.'registration_number')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Contact Person -->
    <div class="{{ $compact ? '' : 'sm:col-span-3' }} field-wrapper">
        <x-ui.label for="{{ $idPrefix }}contact_person">{{ __('Contact Person') }}</x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="text"
                name="{{ $namePrefix }}contact_person"
                id="{{ $idPrefix }}contact_person"
                placeholder="{{ __('Enter contact person name (optional)') }}"
                value="{{ old($namePrefix.'contact_person', $client->contact_person ?? '') }}"
            />
        </div>
        @error($namePrefix.'contact_person')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Status -->
    <div class="{{ $compact ? '' : 'sm:col-span-3' }} field-wrapper">
        <x-ui.label for="{{ $idPrefix }}status_id">{{ __('Status') }}</x-ui.label>
        <div class="mt-2">
            <x-ui.nomenclature-select
                name="{{ $namePrefix }}status_id"
                category="client_statuses"
                :options="$statuses"
                :selected="old($namePrefix.'status_id', $client->status_id ?? '')"
                :placeholder="__('Select status')"
                :hasColors="true"
                :allowEmpty="true"
                valueKey="id"
            />
        </div>
        @error($namePrefix.'status_id')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Email -->
    <div class="{{ $compact ? '' : 'sm:col-span-3' }} field-wrapper">
        <x-ui.label for="{{ $idPrefix }}email">{{ __('Email') }}</x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="email"
                name="{{ $namePrefix }}email"
                id="{{ $idPrefix }}email"
                placeholder="{{ __('email@example.com') }}"
                value="{{ old($namePrefix.'email', $client->email ?? '') }}"
            />
        </div>
        @error($namePrefix.'email')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Phone -->
    <div class="{{ $compact ? '' : 'sm:col-span-3' }} field-wrapper">
        <x-ui.label for="{{ $idPrefix }}phone">{{ __('Phone') }}</x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="text"
                name="{{ $namePrefix }}phone"
                id="{{ $idPrefix }}phone"
                placeholder="{{ __('+40 XXX XXX XXX') }}"
                value="{{ old($namePrefix.'phone', $client->phone ?? '') }}"
            />
        </div>
        @error($namePrefix.'phone')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Address -->
    <div class="{{ $compact ? '' : 'sm:col-span-6' }} field-wrapper">
        <x-ui.label for="{{ $idPrefix }}address">{{ __('Address') }}</x-ui.label>
        <div class="mt-2">
            <x-ui.textarea
                name="{{ $namePrefix }}address"
                id="{{ $idPrefix }}address"
                rows="{{ $compact ? '2' : '3' }}"
                placeholder="{{ __('Enter full address (optional)') }}"
            >{{ old($namePrefix.'address', $client->address ?? '') }}</x-ui.textarea>
        </div>
        @error($namePrefix.'address')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- VAT Payer -->
    <div class="{{ $compact ? '' : 'sm:col-span-6' }} field-wrapper">
        <div class="flex items-start">
            <div class="flex h-6 items-center">
                <input
                    type="checkbox"
                    name="{{ $namePrefix }}vat_payer"
                    id="{{ $idPrefix }}vat_payer"
                    value="1"
                    {{ old($namePrefix.'vat_payer', $client->vat_payer ?? false) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-blue-500"
                >
            </div>
            <div class="ml-3 text-sm leading-6">
                <label for="{{ $idPrefix }}vat_payer" class="font-medium text-slate-900">{{ __('VAT Payer') }}</label>
                <p class="text-slate-500">{{ __('Check if this client is registered as a VAT payer') }}</p>
            </div>
        </div>
    </div>

    @if(!$compact)
    <!-- Notes (only in full form) -->
    <div class="sm:col-span-6 field-wrapper">
        <x-ui.label for="{{ $idPrefix }}notes">{{ __('Notes') }}</x-ui.label>
        <div class="mt-2">
            <x-ui.textarea
                name="{{ $namePrefix }}notes"
                id="{{ $idPrefix }}notes"
                rows="4"
                placeholder="{{ __('Additional notes about this client (optional)') }}"
            >{{ old($namePrefix.'notes', $client->notes ?? '') }}</x-ui.textarea>
        </div>
        @error($namePrefix.'notes')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    @endif
</div>
