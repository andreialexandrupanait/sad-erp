<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Domenii (Domain Management)') }}
            </h2>
            <a href="{{ route('domains.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add New Domain
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 dark:text-gray-400 text-sm">Total Domains</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-red-200 dark:border-red-800">
                    <div class="text-red-600 dark:text-red-400 text-sm font-medium">Expired</div>
                    <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $stats['expired'] }}</div>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-yellow-200 dark:border-yellow-800">
                    <div class="text-yellow-600 dark:text-yellow-400 text-sm font-medium">Expiring Soon</div>
                    <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ $stats['expiring_soon'] }}</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-green-200 dark:border-green-800">
                    <div class="text-green-600 dark:text-green-400 text-sm font-medium">Valid</div>
                    <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $stats['valid'] }}</div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-blue-200 dark:border-blue-800">
                    <div class="text-blue-600 dark:text-blue-400 text-sm font-medium">Annual Cost</div>
                    <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">${{ number_format($stats['total_annual_cost'], 2) }}</div>
                </div>
            </div>

            <!-- Search and Filter Form -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('domains.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <!-- Search -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="Domain, client, registrar..."
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            </div>

                            <!-- Client Filter -->
                            <div>
                                <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client</label>
                                <select name="client_id" id="client_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">All Clients</option>
                                    <option value="none" {{ request('client_id') == 'none' ? 'selected' : '' }}>No Client</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Registrar Filter -->
                            <div>
                                <label for="registrar" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Registrar</label>
                                <select name="registrar" id="registrar" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">All Registrars</option>
                                    @foreach ($registrars as $key => $value)
                                        <option value="{{ $key }}" {{ request('registrar') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Expiry Status Filter -->
                            <div>
                                <label for="expiry_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Status</label>
                                <select name="expiry_status" id="expiry_status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">All Status</option>
                                    <option value="expired" {{ request('expiry_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="expiring" {{ request('expiry_status') == 'expiring' ? 'selected' : '' }}>Expiring Soon (30 days)</option>
                                    <option value="valid" {{ request('expiry_status') == 'valid' ? 'selected' : '' }}>Valid</option>
                                </select>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-end gap-2">
                                <button type="submit" class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Search
                                </button>
                                @if ($activeFilters > 0)
                                    <a href="{{ route('domains.index') }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded flex items-center">
                                        Clear ({{ $activeFilters }})
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Domains Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($domains->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <a href="{{ route('domains.index', array_merge(request()->all(), ['sort' => 'domain_name', 'dir' => request('sort') == 'domain_name' && request('dir') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:underline">
                                                Domain Name
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Client
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <a href="{{ route('domains.index', array_merge(request()->all(), ['sort' => 'registrar', 'dir' => request('sort') == 'registrar' && request('dir') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:underline">
                                                Registrar
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <a href="{{ route('domains.index', array_merge(request()->all(), ['sort' => 'expiry_date', 'dir' => request('sort') == 'expiry_date' && request('dir') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:underline">
                                                Expiry Date
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Cost
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($domains as $domain)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $domain->domain_name }}
                                                </div>
                                                @if ($domain->auto_renew)
                                                    <span class="text-xs text-blue-600 dark:text-blue-400">Auto-renew</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($domain->client)
                                                    <a href="{{ route('clients.show', $domain->client) }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                        {{ $domain->client->display_name }}
                                                    </a>
                                                @else
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $domain->registrar ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $domain->expiry_date->format('M d, Y') }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $domain->expiry_text }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($domain->expiry_status === 'Expired')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                                        🚨 Expired
                                                    </span>
                                                @elseif ($domain->expiry_status === 'Expiring')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                                                        ⚠️ Expiring
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                                        ✅ Valid
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $domain->annual_cost ? '$' . number_format($domain->annual_cost, 2) : '-' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('domains.show', $domain) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">View</a>
                                                <a href="{{ route('domains.edit', $domain) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Edit</a>
                                                <form action="{{ route('domains.destroy', $domain) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                        onclick="return confirm('Are you sure you want to delete this domain?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $domains->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No domains found.</p>
                            <a href="{{ route('domains.create') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Add Your First Domain
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
