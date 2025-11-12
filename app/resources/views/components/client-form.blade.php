@props(['client' => null, 'statuses' => [], 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-ui.card-content>
            <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <!-- Name (Required) -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="name">
                        Name <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="name"
                            id="name"
                            required
                            placeholder="Enter client name"
                            value="{{ old('name', $client->name ?? '') }}"
                        />
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Company Name -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="company_name">Company Name</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="company_name"
                            id="company_name"
                            placeholder="Enter company name (optional)"
                            value="{{ old('company_name', $client->company_name ?? '') }}"
                        />
                    </div>
                    @error('company_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tax ID (CUI) with Auto-fill -->
                <div class="sm:col-span-3 field-wrapper" x-data="{ loading: false }">
                    <x-ui.label for="tax_id">Tax ID (CUI)</x-ui.label>
                    <div class="mt-2 relative">
                        <x-ui.input
                            type="text"
                            name="tax_id"
                            id="tax_id"
                            value="{{ old('tax_id', $client->tax_id ?? '') }}"
                            placeholder="e.g., RO12345678"
                            @blur="if ($event.target.value && !document.getElementById('company_name').value) {
                                loading = true;
                                fetch(`https://api.openapi.ro/api/companies/${$event.target.value.replace('RO', '')}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.found && data.cif) {
                                            if (!document.getElementById('company_name').value) {
                                                document.getElementById('company_name').value = data.denumire || '';
                                            }
                                            document.getElementById('registration_number').value = data.numar_reg_com || '';
                                            document.getElementById('address').value = data.adresa || '';
                                            document.getElementById('vat_payer').checked = data.tva === 'DA';
                                        }
                                    })
                                    .catch(err => console.error('Error fetching company data:', err))
                                    .finally(() => loading = false);
                            }"
                        />
                        <div x-show="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                            <svg class="animate-spin h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Company details will be auto-filled from ANAF if available</p>
                    @error('tax_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Registration Number -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="registration_number">Registration Number</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="registration_number"
                            id="registration_number"
                            value="{{ old('registration_number', $client->registration_number ?? '') }}"
                            placeholder="e.g., J40/1234/2020"
                        />
                    </div>
                    @error('registration_number')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Person -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="contact_person">Contact Person</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="contact_person"
                            id="contact_person"
                            placeholder="Enter contact person name (optional)"
                            value="{{ old('contact_person', $client->contact_person ?? '') }}"
                        />
                    </div>
                    @error('contact_person')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="status_id">Status</x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="status_id" id="status_id">
                            <option value="">Select status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ old('status_id', $client->status_id ?? '') == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('status_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="email">Email</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="email"
                            name="email"
                            id="email"
                            placeholder="email@example.com"
                            value="{{ old('email', $client->email ?? '') }}"
                        />
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="phone">Phone</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="phone"
                            id="phone"
                            placeholder="+40 XXX XXX XXX"
                            value="{{ old('phone', $client->phone ?? '') }}"
                        />
                    </div>
                    @error('phone')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="address">Address</x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea name="address" id="address" rows="3" placeholder="Enter full address (optional)">{{ old('address', $client->address ?? '') }}</x-ui.textarea>
                    </div>
                    @error('address')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- VAT Payer -->
                <div class="sm:col-span-6 field-wrapper">
                    <div class="flex items-start">
                        <div class="flex h-6 items-center">
                            <input
                                type="checkbox"
                                name="vat_payer"
                                id="vat_payer"
                                value="1"
                                {{ old('vat_payer', $client->vat_payer ?? false) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                            >
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label for="vat_payer" class="font-medium text-slate-900">VAT Payer</label>
                            <p class="text-slate-500">Check if this client is registered as a VAT payer</p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="notes">Notes</x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea name="notes" id="notes" rows="4" placeholder="Additional notes about this client (optional)">{{ old('notes', $client->notes ?? '') }}</x-ui.textarea>
                    </div>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-6 border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('clients.index') }}'">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $client ? 'Update Client' : 'Create Client' }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
