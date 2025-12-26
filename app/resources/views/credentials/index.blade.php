<x-app-layout>
    <x-slot name="pageTitle">{{ __('Credentials') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('credentials.create') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('New Credential') }}
        </x-ui.button>
    </x-slot>

    <div class="p-6 space-y-6" x-data="{
        ...credentialsSearch(),
        ...bulkSelection({
            idAttribute: 'data-credential-id',
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

        <!-- Search and Filter Form -->
        <x-ui.card>
            <x-ui.card-content>
                <form method="GET" action="{{ route('credentials.index') }}" x-ref="filterForm">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1">
                            <x-ui.label for="search">{{ __('Search') }}</x-ui.label>
                            <x-ui.input
                                type="text"
                                name="search"
                                id="search"
                                x-model="search"
                                @input.debounce.400ms="performSearch"
                                value="{{ request('search') }}"
                                placeholder="{{ __('Search credentials, sites...') }}"
                            />
                        </div>

                        <!-- Client Filter -->
                        <div class="w-full lg:w-64">
                            <x-ui.label for="client_id">{{ __('Client') }}</x-ui.label>
                            <x-ui.searchable-select
                                name="client_id"
                                :options="$clients"
                                :selected="request('client_id')"
                                :placeholder="__('All Clients')"
                                :emptyLabel="__('All Clients')"
                                onchange="this.form.submit()"
                            />
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-end gap-2">
                            <x-ui.button type="submit" variant="default">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                {{ __('Search') }}
                            </x-ui.button>
                            @if(request()->has('search') || request()->has('client_id'))
                                <x-ui.button type="button" variant="outline" @click="clearFilters">
                                    {{ __('Clear') }}
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Bulk Actions Toolbar -->
        <x-bulk-toolbar resource="credentials">
            <x-ui.button variant="outline" class="!bg-slate-800 !border-slate-600 !text-white hover:!bg-slate-700"
                @click="performBulkAction('export', '{{ route('credentials.bulk-export') }}', {
                    title: '{{ __('Export Credentials') }}',
                    message: '{{ __('Export selected credentials to CSV?') }}'
                })">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('Export CSV') }}
            </x-ui.button>

            <x-ui.button variant="destructive"
                @click="performBulkAction('delete', '{{ route('credentials.bulk-update') }}', {
                    title: '{{ __('Delete Credentials') }}',
                    message: '{{ __('Are you sure you want to delete the selected credentials? This cannot be undone.') }}'
                })">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('Delete Selected') }}
            </x-ui.button>
        </x-bulk-toolbar>

        <!-- Credentials Cards -->
        <div id="credentials-container">
            @include('credentials.partials.credentials-list')
        </div>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

    <script>
    async function fetchPassword(credentialId) {
        try {
            const response = await fetch(`/credentials/${credentialId}/password`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch password');
            }

            const data = await response.json();
            return data.password || '••••••••';
        } catch (error) {
            console.error('Error fetching password:', error);
            return '••••••••';
        }
    }

    function copyToClipboard(text, element) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('{{ __("Copied to clipboard") }}');
        });
    }

    function showToast(message, type = 'success') {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message, type }
        }));
    }

    function credentialsSearch() {
        return {
            search: '{{ request('search', '') }}',
            clientId: '{{ request('client_id', '') }}',
            loading: false,
            searchTimeout: null,

            init() {
                // Nothing needed
            },

            performSearch() {
                // Debounce and navigate
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.navigateWithFilters();
                }, 300);
            },

            navigateWithFilters() {
                this.loading = true;
                const url = new URL(window.location.origin + '{{ route('credentials.index') }}');
                if (this.search) url.searchParams.set('search', this.search);
                if (this.clientId) url.searchParams.set('client_id', this.clientId);
                window.location.href = url.toString();
            },

            clearFilters() {
                this.search = '';
                this.clientId = '';
                window.location.href = '{{ route('credentials.index') }}';
            }
        };
    }
    </script>
</x-app-layout>
