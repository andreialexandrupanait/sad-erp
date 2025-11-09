<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Domain: {{$domain->domain_name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('domains.edit', $domain) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</a>
                <a href="{{ route('domains.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Domain Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Domain Name</label>
                            <p class="mt-1 text-lg font-mono text-gray-900 dark:text-gray-100">{{ $domain->domain_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Expiry Status</label>
                            <p class="mt-1">
                                @if ($domain->expiry_status === 'Expired')
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                        🚨 {{ $domain->expiry_text }}
                                    </span>
                                @elseif ($domain->expiry_status === 'Expiring')
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                                        ⚠️ {{ $domain->expiry_text }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                        ✅ {{ $domain->expiry_text }}
                                    </span>
                                @endif
                            </p>
                        </div>

                        @if ($domain->client)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Client</label>
                                <p class="mt-1">
                                    <a href="{{ route('clients.show', $domain->client) }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $domain->client->display_name }}
                                    </a>
                                </p>
                            </div>
                        @endif

                        @if ($domain->registrar)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Registrar</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $domain->registrar }}</p>
                            </div>
                        @endif

                        @if ($domain->registration_date)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Registration Date</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $domain->registration_date->format('M d, Y') }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Expiry Date</label>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $domain->expiry_date->format('M d, Y') }}</p>
                        </div>

                        @if ($domain->annual_cost)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Annual Cost</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">${{ number_format($domain->annual_cost, 2) }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Auto-Renew</label>
                            <p class="mt-1">
                                @if ($domain->auto_renew)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Enabled</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Disabled</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $domain->status }}</p>
                        </div>

                        @if ($domain->notes)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Notes</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $domain->notes }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Created</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $domain->created_at->format('M d, Y H:i') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $domain->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
