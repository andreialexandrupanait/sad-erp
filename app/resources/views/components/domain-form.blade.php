@props(['domain' => null, 'clients' => [], 'registrars' => [], 'statuses' => [], 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-ui.card-content>
            <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <!-- Domain Name (Required) -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="domain_name">
                        Domain Name <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="domain_name"
                            id="domain_name"
                            required
                            value="{{ old('domain_name', $domain->domain_name ?? '') }}"
                            placeholder="example.com"
                        />
                    </div>
                    @error('domain_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Client -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="client_id">Client (Optional)</x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="client_id" id="client_id">
                            <option value="">No Client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $domain->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                    {{ $client->display_name }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('client_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Registrar -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="registrar">Registrar</x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="registrar" id="registrar">
                            <option value="">Select registrar</option>
                            @foreach($registrars as $registrar)
                                <option value="{{ $registrar->value }}" {{ old('registrar', $domain->registrar ?? '') == $registrar->value ? 'selected' : '' }}>
                                    {{ $registrar->label }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('registrar')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Registration Date -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="registration_date">Registration Date</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="date"
                            name="registration_date"
                            id="registration_date"
                            placeholder="YYYY-MM-DD"
                            value="{{ old('registration_date', $domain ? $domain->registration_date?->format('Y-m-d') : '') }}"
                        />
                    </div>
                    @error('registration_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expiry Date -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="expiry_date">
                        Expiry Date <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="date"
                            name="expiry_date"
                            id="expiry_date"
                            required
                            placeholder="YYYY-MM-DD"
                            value="{{ old('expiry_date', $domain ? $domain->expiry_date?->format('Y-m-d') : '') }}"
                        />
                    </div>
                    @error('expiry_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Annual Cost -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="annual_cost">Annual Cost ($)</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="number"
                            step="0.01"
                            name="annual_cost"
                            id="annual_cost"
                            value="{{ old('annual_cost', $domain->annual_cost ?? '') }}"
                            placeholder="15.00"
                        />
                    </div>
                    @error('annual_cost')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="status">
                        Status <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="status" id="status" required>
                            @php
                                $defaultStatus = $statuses->first()?->value ?? 'activ';
                            @endphp
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" {{ old('status', $domain->status ?? $defaultStatus) == $status->value ? 'selected' : '' }}>
                                    {{ $status->label }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Auto Renew -->
                <div class="sm:col-span-6 field-wrapper">
                    <div class="flex items-start">
                        <div class="flex h-6 items-center">
                            <input
                                type="checkbox"
                                name="auto_renew"
                                id="auto_renew"
                                value="1"
                                {{ old('auto_renew', $domain->auto_renew ?? false) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                            >
                        </div>
                        <div class="ml-3 text-sm leading-6">
                            <label for="auto_renew" class="font-medium text-slate-900">Auto-renew enabled</label>
                            <p class="text-slate-500">Domain will automatically renew before expiry</p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="notes">Notes</x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea name="notes" id="notes" rows="3" placeholder="Additional information about this domain...">{{ old('notes', $domain->notes ?? '') }}</x-ui.textarea>
                    </div>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-6 border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('domains.index') }}'">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $domain ? 'Update Domain' : 'Create Domain' }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
