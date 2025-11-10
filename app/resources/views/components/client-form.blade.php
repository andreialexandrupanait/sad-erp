@props(['client' => null, 'statuses' => [], 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-ui.card-content>
            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <!-- Name (Required) -->
                <div class="sm:col-span-3">
                    <x-ui.label for="name">
                        Name <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="name"
                            id="name"
                            required
                            value="{{ old('name', $client->name ?? '') }}"
                        />
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Company Name -->
                <div class="sm:col-span-3">
                    <x-ui.label for="company_name">Company Name</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="company_name"
                            id="company_name"
                            value="{{ old('company_name', $client->company_name ?? '') }}"
                        />
                    </div>
                    @error('company_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tax ID (CUI) -->
                <div class="sm:col-span-3">
                    <x-ui.label for="tax_id">Tax ID (CUI)</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="tax_id"
                            id="tax_id"
                            value="{{ old('tax_id', $client->tax_id ?? '') }}"
                            placeholder="e.g., RO12345678"
                        />
                    </div>
                    @error('tax_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Registration Number -->
                <div class="sm:col-span-3">
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
                <div class="sm:col-span-3">
                    <x-ui.label for="contact_person">Contact Person</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="contact_person"
                            id="contact_person"
                            value="{{ old('contact_person', $client->contact_person ?? '') }}"
                        />
                    </div>
                    @error('contact_person')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="sm:col-span-3">
                    <x-ui.label for="status_id">Status</x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="status_id" id="status_id">
                            <option value="">Select Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}"
                                    {{ old('status_id', $client->status_id ?? '') == $status->id ? 'selected' : '' }}>
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
                <div class="sm:col-span-3">
                    <x-ui.label for="email">Email</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email', $client->email ?? '') }}"
                        />
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div class="sm:col-span-3">
                    <x-ui.label for="phone">Phone</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="phone"
                            id="phone"
                            value="{{ old('phone', $client->phone ?? '') }}"
                        />
                    </div>
                    @error('phone')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="sm:col-span-6">
                    <x-ui.label for="address">Address</x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea name="address" id="address" rows="3">{{ old('address', $client->address ?? '') }}</x-ui.textarea>
                    </div>
                    @error('address')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- VAT Payer -->
                <div class="sm:col-span-6">
                    <div class="flex items-start">
                        <div class="flex h-6 items-center">
                            <input type="checkbox" name="vat_payer" id="vat_payer" value="1"
                                {{ old('vat_payer', $client->vat_payer ?? false) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label for="vat_payer" class="font-medium text-slate-900">VAT Payer</label>
                            <p class="text-slate-500">Check if this client is registered as a VAT payer</p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="sm:col-span-6">
                    <x-ui.label for="notes">Notes</x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea name="notes" id="notes" rows="4">{{ old('notes', $client->notes ?? '') }}</x-ui.textarea>
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
