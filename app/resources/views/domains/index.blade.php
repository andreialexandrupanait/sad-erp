<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ __('Domenii (Domain Management)') }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">Manage domain registrations and track expiry dates</p>
            </div>
            <x-ui.button variant="default" onclick="window.location.href='{{ route('domains.create') }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add New Domain
            </x-ui.button>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8 space-y-6">
        <!-- Success Messages -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- Statistics Cards -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-5">
            <!-- Total Domains - Featured -->
            <div class="rounded-lg border border-slate-200 bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-300">Total Domains</p>
                            <p class="mt-2 text-3xl font-bold">{{ $stats['total'] }}</p>
                            <p class="mt-1 text-xs text-slate-400">domains managed</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-white/10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expired -->
            <div class="rounded-lg border border-red-200 bg-red-50 shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-red-600">Expired</p>
                            <p class="mt-2 text-2xl font-bold text-red-700">{{ $stats['expired'] }}</p>
                            <p class="mt-1 text-xs text-red-600">domains</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-red-100">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expiring Soon -->
            <div class="rounded-lg border border-yellow-200 bg-yellow-50 shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-yellow-600">Expiring Soon</p>
                            <p class="mt-2 text-2xl font-bold text-yellow-700">{{ $stats['expiring_soon'] }}</p>
                            <p class="mt-1 text-xs text-yellow-600">next 30 days</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valid -->
            <div class="rounded-lg border border-green-200 bg-green-50 shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-green-600">Valid</p>
                            <p class="mt-2 text-2xl font-bold text-green-700">{{ $stats['valid'] }}</p>
                            <p class="mt-1 text-xs text-green-600">domains</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Annual Cost -->
            <div class="rounded-lg border border-blue-200 bg-blue-50 shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-blue-600">Annual Cost</p>
                            <p class="mt-2 text-2xl font-bold text-blue-700">${{ number_format($stats['total_annual_cost'], 2) }}</p>
                            <p class="mt-1 text-xs text-blue-600">per year</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <x-ui.card>
            <x-ui.card-content>
                <form method="GET" action="{{ route('domains.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                        <!-- Search -->
                        <div>
                            <x-ui.label for="search">Search</x-ui.label>
                            <x-ui.input
                                type="text"
                                name="search"
                                id="search"
                                value="{{ request('search') }}"
                                placeholder="Domain, client, registrar..."
                            />
                        </div>

                        <!-- Client Filter -->
                        <div>
                            <x-ui.label for="client_id">Client</x-ui.label>
                            <x-ui.select name="client_id" id="client_id">
                                <option value="">All Clients</option>
                                <option value="none" {{ request('client_id') == 'none' ? 'selected' : '' }}>No Client</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->display_name }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <!-- Registrar Filter -->
                        <div>
                            <x-ui.label for="registrar">Registrar</x-ui.label>
                            <x-ui.select name="registrar" id="registrar">
                                <option value="">All Registrars</option>
                                @foreach ($registrars as $key => $value)
                                    <option value="{{ $key }}" {{ request('registrar') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <!-- Expiry Status Filter -->
                        <div>
                            <x-ui.label for="expiry_status">Expiry Status</x-ui.label>
                            <x-ui.select name="expiry_status" id="expiry_status">
                                <option value="">All Status</option>
                                <option value="expired" {{ request('expiry_status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="expiring" {{ request('expiry_status') == 'expiring' ? 'selected' : '' }}>Expiring Soon (30 days)</option>
                                <option value="valid" {{ request('expiry_status') == 'valid' ? 'selected' : '' }}>Valid</option>
                            </x-ui.select>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-end gap-2">
                            <x-ui.button type="submit" variant="default" class="flex-1">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                Search
                            </x-ui.button>
                            @if ($activeFilters > 0)
                                <x-ui.button variant="outline" onclick="window.location.href='{{ route('domains.index') }}'">
                                    Clear ({{ $activeFilters }})
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Domains Table -->
        <x-ui.card>
            @if ($domains->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr class="border-b transition-colors hover:bg-slate-50/50">
                                <x-ui.table-head>
                                    <a href="{{ route('domains.index', array_merge(request()->all(), ['sort' => 'domain_name', 'dir' => request('sort') == 'domain_name' && request('dir') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:underline">
                                        Domain Name
                                    </a>
                                </x-ui.table-head>
                                <x-ui.table-head>Client</x-ui.table-head>
                                <x-ui.table-head>
                                    <a href="{{ route('domains.index', array_merge(request()->all(), ['sort' => 'registrar', 'dir' => request('sort') == 'registrar' && request('dir') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:underline">
                                        Registrar
                                    </a>
                                </x-ui.table-head>
                                <x-ui.table-head>
                                    <a href="{{ route('domains.index', array_merge(request()->all(), ['sort' => 'expiry_date', 'dir' => request('sort') == 'expiry_date' && request('dir') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:underline">
                                        Expiry Date
                                    </a>
                                </x-ui.table-head>
                                <x-ui.table-head>Status</x-ui.table-head>
                                <x-ui.table-head>Cost</x-ui.table-head>
                                <x-ui.table-head class="text-right">Actions</x-ui.table-head>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            @foreach ($domains as $domain)
                                <x-ui.table-row>
                                    <x-ui.table-cell>
                                        <div class="font-medium text-slate-900">
                                            {{ $domain->domain_name }}
                                        </div>
                                        @if ($domain->auto_renew)
                                            <x-ui.badge variant="info" class="mt-1">Auto-renew</x-ui.badge>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        @if ($domain->client)
                                            <a href="{{ route('clients.show', $domain->client) }}" class="text-sm text-slate-600 hover:text-slate-900 transition-colors">
                                                {{ $domain->client->display_name }}
                                            </a>
                                        @else
                                            <span class="text-sm text-slate-400">-</span>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm text-slate-700">
                                            {{ $domain->registrar ?? '-' }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm font-medium text-slate-900">
                                            {{ $domain->expiry_date->format('M d, Y') }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ $domain->expiry_text }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        @if ($domain->expiry_status === 'Expired')
                                            <x-ui.badge variant="destructive">Expired</x-ui.badge>
                                        @elseif ($domain->expiry_status === 'Expiring')
                                            <x-ui.badge variant="warning">Expiring</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="success">Valid</x-ui.badge>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm text-slate-700">
                                            {{ $domain->annual_cost ? '$' . number_format($domain->annual_cost, 2) : '-' }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-ui.button
                                                variant="secondary"
                                                size="sm"
                                                onclick="window.location.href='{{ route('domains.show', $domain) }}'"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View
                                            </x-ui.button>
                                            <x-ui.button
                                                variant="outline"
                                                size="sm"
                                                onclick="window.location.href='{{ route('domains.edit', $domain) }}'"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </x-ui.button>
                                            <form action="{{ route('domains.destroy', $domain) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this domain?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.button type="submit" variant="destructive" size="sm">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Delete
                                                </x-ui.button>
                                            </form>
                                        </div>
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($domains->hasPages())
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                        {{ $domains->links() }}
                    </div>
                @endif
            @else
                <div class="px-6 py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">No domains found</h3>
                    <p class="mt-1 text-sm text-slate-500">Get started by adding your first domain.</p>
                    <div class="mt-6">
                        <x-ui.button variant="default" onclick="window.location.href='{{ route('domains.create') }}'">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Your First Domain
                        </x-ui.button>
                    </div>
                </div>
            @endif
        </x-ui.card>
    </div>
</x-app-layout>
