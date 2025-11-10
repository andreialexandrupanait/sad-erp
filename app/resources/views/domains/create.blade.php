<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ __('Add New Domain') }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">Register a new domain in the system</p>
            </div>
            <x-ui.button variant="outline" onclick="window.location.href='{{ route('domains.index') }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Domains
            </x-ui.button>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8">
        <x-ui.card>
            <x-ui.card-content>
                <form method="POST" action="{{ route('domains.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Domain Name -->
                        <div class="md:col-span-2">
                            <x-ui.label for="domain_name">
                                Domain Name <span class="text-red-500">*</span>
                            </x-ui.label>
                            <x-ui.input
                                id="domain_name"
                                type="text"
                                name="domain_name"
                                value="{{ old('domain_name') }}"
                                placeholder="example.com"
                                required
                            />
                            @error('domain_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Client -->
                        <div>
                            <x-ui.label for="client_id">
                                Client (Optional)
                            </x-ui.label>
                            <x-ui.select id="client_id" name="client_id">
                                <option value="">No Client</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->display_name }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                            @error('client_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Registrar -->
                        <div>
                            <x-ui.label for="registrar">
                                Registrar
                            </x-ui.label>
                            <x-ui.select id="registrar" name="registrar">
                                <option value="">Select registrar</option>
                                @foreach ($registrars as $key => $value)
                                    <option value="{{ $key }}" {{ old('registrar') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                            @error('registrar')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Registration Date -->
                        <div>
                            <x-ui.label for="registration_date">
                                Registration Date
                            </x-ui.label>
                            <x-ui.input
                                id="registration_date"
                                type="date"
                                name="registration_date"
                                value="{{ old('registration_date') }}"
                            />
                            @error('registration_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Expiry Date -->
                        <div>
                            <x-ui.label for="expiry_date">
                                Expiry Date <span class="text-red-500">*</span>
                            </x-ui.label>
                            <x-ui.input
                                id="expiry_date"
                                type="date"
                                name="expiry_date"
                                value="{{ old('expiry_date') }}"
                                required
                            />
                            @error('expiry_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Annual Cost -->
                        <div>
                            <x-ui.label for="annual_cost">
                                Annual Cost ($)
                            </x-ui.label>
                            <x-ui.input
                                id="annual_cost"
                                type="number"
                                step="0.01"
                                name="annual_cost"
                                value="{{ old('annual_cost') }}"
                                placeholder="15.00"
                            />
                            @error('annual_cost')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <x-ui.label for="status">
                                Status <span class="text-red-500">*</span>
                            </x-ui.label>
                            <x-ui.select id="status" name="status" required>
                                <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Expiring" {{ old('status') == 'Expiring' ? 'selected' : '' }}>Expiring</option>
                                <option value="Expired" {{ old('status') == 'Expired' ? 'selected' : '' }}>Expired</option>
                                <option value="Suspended" {{ old('status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                            </x-ui.select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Auto Renew -->
                        <div class="md:col-span-2">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input
                                        id="auto_renew"
                                        name="auto_renew"
                                        type="checkbox"
                                        value="1"
                                        {{ old('auto_renew') ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-2 focus:ring-slate-950 focus:ring-offset-2"
                                    >
                                </div>
                                <div class="ml-3">
                                    <x-ui.label for="auto_renew" class="font-medium">
                                        Auto-renew enabled
                                    </x-ui.label>
                                    <p class="text-sm text-slate-500">Domain will automatically renew before expiry</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <x-ui.label for="notes">
                                Notes
                            </x-ui.label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                placeholder="Additional information about this domain..."
                                class="flex min-h-[80px] w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8 gap-3">
                        <x-ui.button type="button" variant="outline" onclick="window.location.href='{{ route('domains.index') }}'">
                            Cancel
                        </x-ui.button>
                        <x-ui.button type="submit" variant="default">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Add Domain
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-app-layout>
