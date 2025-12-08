<x-app-layout>
    <x-slot name="pageTitle">{{ __('Domains') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('domains.create') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('Add Domain') }}
        </x-ui.button>
    </x-slot>

    <div class="p-6 space-y-6" x-data="{
        ...bulkSelection({
            idAttribute: 'data-domain-id',
            rowSelector: '[data-selectable]'
        })
    }">
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
                            <p class="text-sm font-medium text-slate-300">{{ __('Total Domains') }}</p>
                            <p class="mt-2 text-3xl font-bold">{{ $stats['total'] }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ __('domains managed') }}</p>
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
                            <p class="text-sm font-medium text-red-600">{{ __('Expired') }}</p>
                            <p class="mt-2 text-2xl font-bold text-red-700">{{ $stats['expired'] }}</p>
                            <p class="mt-1 text-xs text-red-600">{{ __('domains') }}</p>
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
                            <p class="text-sm font-medium text-yellow-600">{{ __('Expiring Soon') }}</p>
                            <p class="mt-2 text-2xl font-bold text-yellow-700">{{ $stats['expiring_soon'] }}</p>
                            <p class="mt-1 text-xs text-yellow-600">{{ __('next 30 days') }}</p>
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
                            <p class="text-sm font-medium text-green-600">{{ __('Valid') }}</p>
                            <p class="mt-2 text-2xl font-bold text-green-700">{{ $stats['valid'] }}</p>
                            <p class="mt-1 text-xs text-green-600">{{ __('domains') }}</p>
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
                            <p class="text-sm font-medium text-blue-600">{{ __('Annual Cost') }}</p>
                            <p class="mt-2 text-2xl font-bold text-blue-700">${{ number_format($stats['total_annual_cost'], 2) }}</p>
                            <p class="mt-1 text-xs text-blue-600">{{ __('per year') }}</p>
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
                            <x-ui.label for="search">{{ __('Search') }}</x-ui.label>
                            <x-ui.input
                                type="text"
                                name="search"
                                id="search"
                                value="{{ request('search') }}"
                                placeholder="{{ __('Search domains') }}"
                            />
                        </div>

                        <!-- Client Filter -->
                        <div>
                            <x-ui.label for="client_id">{{ __('Client') }}</x-ui.label>
                            <x-ui.searchable-select
                                name="client_id"
                                :options="$clients"
                                :selected="request('client_id')"
                                :placeholder="__('All Clients')"
                                :emptyLabel="__('All Clients')"
                                :prependOptions="[['value' => 'none', 'label' => __('No Client')]]"
                            />
                        </div>

                        <!-- Registrar Filter -->
                        <div>
                            <x-ui.label for="registrar">{{ __('Registrar') }}</x-ui.label>
                            <x-ui.select name="registrar" id="registrar">
                                <option value="">{{ __('All Registrars') }}</option>
                                @foreach ($registrars as $key => $value)
                                    <option value="{{ $key }}" {{ request('registrar') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <!-- Expiry Status Filter -->
                        <div>
                            <x-ui.label for="expiry_status">{{ __('Expiry Status') }}</x-ui.label>
                            <x-ui.select name="expiry_status" id="expiry_status">
                                <option value="">{{ __('All Expiry Statuses') }}</option>
                                <option value="expired" {{ request('expiry_status') == 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                                <option value="expiring" {{ request('expiry_status') == 'expiring' ? 'selected' : '' }}>{{ __('Expiring Soon') }} (30 {{ __('days') }})</option>
                                <option value="valid" {{ request('expiry_status') == 'valid' ? 'selected' : '' }}>{{ __('Valid') }}</option>
                            </x-ui.select>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-end gap-2">
                            <x-ui.button type="submit" variant="default" class="flex-1">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                {{ __('Search') }}
                            </x-ui.button>
                            @if ($activeFilters > 0)
                                <x-ui.button variant="outline" onclick="window.location.href='{{ route('domains.index') }}'">
                                    {{ __('Clear') }} ({{ $activeFilters }})
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Bulk Actions Toolbar -->
        <x-bulk-toolbar>
            <x-ui.button
                variant="outline"
                @click="performBulkAction('toggle-auto-renew', '{{ route('domains.bulk-auto-renew') }}', {
                    confirmTitle: '{{ __('Toggle Auto-Renew') }}',
                    confirmMessage: '{{ __('Are you sure you want to toggle auto-renew for the selected domains?') }}',
                    successMessage: '{{ __('Auto-renew toggled successfully!') }}'
                })"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ __('Toggle Auto-Renew') }}
            </x-ui.button>
            <x-ui.button
                variant="outline"
                @click="performBulkAction('export', '{{ route('domains.bulk-export') }}', {
                    confirmTitle: '{{ __('Export Domains') }}',
                    confirmMessage: '{{ __('Export selected domains to CSV?') }}',
                    successMessage: '{{ __('Domains exported successfully!') }}'
                })"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('Export to CSV') }}
            </x-ui.button>
            <x-ui.button
                variant="destructive"
                @click="performBulkAction('delete', '{{ route('domains.bulk-update') }}', {
                    confirmTitle: '{{ __('Delete Domains') }}',
                    confirmMessage: '{{ __('Are you sure you want to delete the selected domains? This action cannot be undone.') }}',
                    successMessage: '{{ __('Domains deleted successfully!') }}'
                })"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('Delete Selected') }}
            </x-ui.button>
        </x-bulk-toolbar>

        <!-- Domains Table -->
        <x-ui.card>
            @if ($domains->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr class="border-b transition-colors hover:bg-slate-50/50">
                                <th class="h-12 px-4 text-left align-middle font-medium text-slate-500 w-12">
                                    <x-bulk-checkbox x-model="selectAll" @change="toggleAll" />
                                </th>
                                <x-ui.sortable-header column="domain_name" label="{{ __('Domain Name') }}" />
                                <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">{{ __('Client') }}</th>
                                <x-ui.sortable-header column="registrar" label="{{ __('Registrar') }}" />
                                <x-ui.sortable-header column="expiry_date" label="{{ __('Expiry Date') }}" />
                                <x-ui.sortable-header column="annual_cost" label="{{ __('Cost') }}" class="text-right" />
                                <th class="h-12 px-4 text-right align-middle font-medium text-slate-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            @foreach ($domains as $domain)
                                <x-ui.table-row data-selectable data-domain-id="{{ $domain->id }}">
                                    <x-ui.table-cell>
                                        <x-bulk-checkbox
                                            
                                            @change="toggleItem({{ $domain->id }})"
                                            x-bind:checked="selectedIds.includes({{ $domain->id }})"
                                        />
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="font-medium text-slate-900">
                                            {{ $domain->domain_name }}
                                        </div>
                                        @if ($domain->auto_renew)
                                            <x-ui.badge variant="info" class="mt-1">{{ __('Auto-renew enabled') }}</x-ui.badge>
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
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $domain->expiry_status === 'Expired' ? 'bg-red-100 text-red-700' : ($domain->expiry_status === 'Expiring' ? 'bg-orange-50 text-orange-600' : 'bg-green-50 text-green-600') }}">
                                            {{ $domain->expiry_text }}
                                        </span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="text-right">
                                        <div class="text-sm text-slate-700">
                                            {{ $domain->annual_cost ? '$' . number_format($domain->annual_cost, 2) : '-' }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="text-right">
                                        <x-table-actions
                                            :viewUrl="route('domains.show', $domain)"
                                            :editUrl="route('domains.edit', $domain)"
                                            :deleteAction="route('domains.destroy', $domain)"
                                            :deleteConfirm="__('Are you sure you want to delete this domain?')"
                                        />
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
                    <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No domains') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Get started by creating your first domain') }}</p>
                    <div class="mt-6">
                        <x-ui.button variant="default" onclick="window.location.href='{{ route('domains.create') }}'">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Add Domain') }}
                        </x-ui.button>
                    </div>
                </div>
            @endif
        </x-ui.card>
    </div>

    <!-- Toast Notifications -->
    <x-toast />
</x-app-layout>
