<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add New Domain') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('domains.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Domain Name -->
                            <div class="md:col-span-2">
                                <label for="domain_name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Domain Name <span class="text-red-500">*</span>
                                </label>
                                <input id="domain_name" type="text" name="domain_name" value="{{ old('domain_name') }}" required
                                    placeholder="example.com"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                @error('domain_name')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Client -->
                            <div>
                                <label for="client_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Client (Optional)
                                </label>
                                <select id="client_id" name="client_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">No Client</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Registrar -->
                            <div>
                                <label for="registrar" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Registrar
                                </label>
                                <select id="registrar" name="registrar" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">Select registrar</option>
                                    @foreach ($registrars as $key => $value)
                                        <option value="{{ $key }}" {{ old('registrar') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('registrar')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Registration Date -->
                            <div>
                                <label for="registration_date" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Registration Date
                                </label>
                                <input id="registration_date" type="date" name="registration_date" value="{{ old('registration_date') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                @error('registration_date')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Expiry Date -->
                            <div>
                                <label for="expiry_date" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Expiry Date <span class="text-red-500">*</span>
                                </label>
                                <input id="expiry_date" type="date" name="expiry_date" value="{{ old('expiry_date') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                @error('expiry_date')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Annual Cost -->
                            <div>
                                <label for="annual_cost" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Annual Cost ($)
                                </label>
                                <input id="annual_cost" type="number" step="0.01" name="annual_cost" value="{{ old('annual_cost') }}"
                                    placeholder="15.00"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                @error('annual_cost')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select id="status" name="status" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Expiring" {{ old('status') == 'Expiring' ? 'selected' : '' }}>Expiring</option>
                                    <option value="Expired" {{ old('status') == 'Expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="Suspended" {{ old('status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                                @error('status')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Auto Renew -->
                            <div class="md:col-span-2 flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="auto_renew" name="auto_renew" type="checkbox" value="1" {{ old('auto_renew') ? 'checked' : '' }}
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded dark:border-gray-700 dark:bg-gray-900">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="auto_renew" class="font-medium text-gray-700 dark:text-gray-300">
                                        Auto-renew enabled
                                    </label>
                                    <p class="text-gray-500 dark:text-gray-400">Domain will automatically renew before expiry</p>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label for="notes" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Notes
                                </label>
                                <textarea id="notes" name="notes" rows="3"
                                    placeholder="Additional information about this domain..."
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 gap-4">
                            <a href="{{ route('domains.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Add Domain
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
