<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Domain: {{ $domain->domain_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('domains.update', $domain) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="domain_name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Domain Name <span class="text-red-500">*</span></label>
                                <input id="domain_name" type="text" name="domain_name" value="{{ old('domain_name', $domain->domain_name) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            </div>

                            <div>
                                <label for="client_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Client</label>
                                <select id="client_id" name="client_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">No Client</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id', $domain->client_id) == $client->id ? 'selected' : '' }}>{{ $client->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="registrar" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Registrar</label>
                                <select id="registrar" name="registrar" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">Select registrar</option>
                                    @foreach ($registrars as $key => $value)
                                        <option value="{{ $key }}" {{ old('registrar', $domain->registrar) == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="registration_date" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Registration Date</label>
                                <input id="registration_date" type="date" name="registration_date" value="{{ old('registration_date', $domain->registration_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            </div>

                            <div>
                                <label for="expiry_date" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Expiry Date <span class="text-red-500">*</span></label>
                                <input id="expiry_date" type="date" name="expiry_date" value="{{ old('expiry_date', $domain->expiry_date->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            </div>

                            <div>
                                <label for="annual_cost" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Annual Cost ($)</label>
                                <input id="annual_cost" type="number" step="0.01" name="annual_cost" value="{{ old('annual_cost', $domain->annual_cost) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            </div>

                            <div>
                                <label for="status" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Status <span class="text-red-500">*</span></label>
                                <select id="status" name="status" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="Active" {{ old('status', $domain->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Expiring" {{ old('status', $domain->status) == 'Expiring' ? 'selected' : '' }}>Expiring</option>
                                    <option value="Expired" {{ old('status', $domain->status) == 'Expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="Suspended" {{ old('status', $domain->status) == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 flex items-start">
                                <input id="auto_renew" name="auto_renew" type="checkbox" value="1" {{ old('auto_renew', $domain->auto_renew) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                                <label for="auto_renew" class="ml-3 font-medium text-gray-700 dark:text-gray-300">Auto-renew enabled</label>
                            </div>

                            <div class="md:col-span-2">
                                <label for="notes" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Notes</label>
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ old('notes', $domain->notes) }}</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 gap-4">
                            <a href="{{ route('domains.show', $domain) }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update Domain</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
