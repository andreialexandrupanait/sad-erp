<x-app-layout>
    <x-slot name="pageTitle">{{ __('Contracts') }}</x-slot>

    <div class="p-6 space-y-6"
         x-data="contractsPage({ stats: @js($stats) })"
         x-init="init()">

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <x-ui.card>
                <x-ui.card-content class="p-4">
                    <div class="text-2xl font-bold text-green-600" x-text="stats.active"></div>
                    <div class="text-sm text-slate-500">{{ __('Active') }}</div>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-content class="p-4">
                    <div class="text-2xl font-bold text-blue-600" x-text="stats.completed"></div>
                    <div class="text-sm text-slate-500">{{ __('Completed') }}</div>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-content class="p-4">
                    <div class="text-2xl font-bold text-red-600" x-text="stats.terminated"></div>
                    <div class="text-sm text-slate-500">{{ __('Terminated') }}</div>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-content class="p-4">
                    <div class="text-2xl font-bold text-yellow-600" x-text="stats.expiring_soon"></div>
                    <div class="text-sm text-slate-500">{{ __('Expiring Soon') }}</div>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-content class="p-4">
                    <div class="text-2xl font-bold text-slate-900" x-text="formatCurrency(stats.total_active_value)"></div>
                    <div class="text-sm text-slate-500">{{ __('Active Value') }}</div>
                </x-ui.card-content>
            </x-ui.card>
        </div>

        {{-- Messages --}}
        @if (session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="destructive">{{ session('error') }}</x-ui.alert>
        @endif

        {{-- Search and Filters --}}
        <x-ui.card>
            <x-ui.card-content>
                <div class="flex flex-col sm:flex-row gap-3 items-center">
                    <div class="flex-1 w-full">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text"
                                   x-model="filters.q"
                                   @input.debounce.300ms="search($event.target.value)"
                                   placeholder="{{ __('Search contracts...') }}"
                                   class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-md text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Status Filter Pills --}}
        <div class="flex flex-wrap gap-2">
            <button @click="setStatusFilter('')"
                    :class="!filters.status ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('All') }}
            </button>
            <button @click="setStatusFilter('active')"
                    :class="filters.status === 'active' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-700 hover:bg-green-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('Active') }}
            </button>
            <button @click="setStatusFilter('completed')"
                    :class="filters.status === 'completed' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700 hover:bg-blue-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('Completed') }}
            </button>
            <button @click="setStatusFilter('terminated')"
                    :class="filters.status === 'terminated' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-700 hover:bg-red-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('Terminated') }}
            </button>
        </div>

        {{-- Loading --}}
        <div x-show="loading" class="flex justify-center py-8">
            <x-ui.spinner size="lg" color="blue" />
        </div>

        {{-- Contracts Table --}}
        <template x-if="!loading && contracts.length > 0">
            <x-ui.card>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Contract') }}</th>
                                <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Client') }}</th>
                                <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-right font-medium text-slate-600">{{ __('Value') }}</th>
                                <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Period') }}</th>
                                <th class="px-6 py-4 text-right font-medium text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <template x-for="contract in contracts" :key="contract.id">
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <a :href="'/contracts/' + contract.id" class="font-medium text-slate-900 hover:text-blue-600">
                                            <span x-text="contract.contract_number"></span>
                                        </a>
                                        <div class="text-sm text-slate-500" x-text="contract.title"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a x-show="contract.client" :href="'/clients/' + contract.client?.slug" class="text-slate-900 hover:text-blue-600" x-text="contract.client?.name"></a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-green-100 text-green-700': contract.status === 'active',
                                                  'bg-blue-100 text-blue-700': contract.status === 'completed',
                                                  'bg-red-100 text-red-700': contract.status === 'terminated',
                                                  'bg-yellow-100 text-yellow-700': contract.status === 'expired',
                                                  'bg-slate-100 text-slate-700': contract.status === 'draft'
                                              }"
                                              x-text="contract.status_label">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium">
                                        <span x-text="formatCurrency(contract.total_value, contract.currency)"></span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">
                                        <span x-text="contract.start_date"></span>
                                        <span x-show="contract.end_date"> - <span x-text="contract.end_date"></span></span>
                                        <span x-show="!contract.end_date" class="text-slate-400">{{ __('Indefinite') }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a :href="'/contracts/' + contract.id"
                                           class="p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded inline-flex"
                                           title="{{ __('View') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div x-show="pagination.last_page > 1" class="px-4 py-3 border-t border-slate-200 flex items-center justify-between">
                    <div class="text-sm text-slate-600">
                        {{ __('Showing') }} <span x-text="pagination.from || 0"></span> - <span x-text="pagination.to || 0"></span> {{ __('of') }} <span x-text="pagination.total || 0"></span>
                    </div>
                    <div class="flex gap-1">
                        <button @click="goToPage(filters.page - 1)" :disabled="filters.page === 1"
                                class="px-3 py-1 text-sm border rounded hover:bg-slate-50 disabled:opacity-50">{{ __('Prev') }}</button>
                        <button @click="goToPage(filters.page + 1)" :disabled="filters.page === pagination.last_page"
                                class="px-3 py-1 text-sm border rounded hover:bg-slate-50 disabled:opacity-50">{{ __('Next') }}</button>
                    </div>
                </div>
            </x-ui.card>
        </template>

        {{-- Empty State --}}
        <template x-if="!loading && contracts.length === 0">
            <x-ui.card>
                <div class="px-6 py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No contracts found') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Contracts are created when offers are accepted.') }}</p>
                </div>
            </x-ui.card>
        </template>
    </div>

    @push('scripts')
    <script>
    function contractsPage(config) {
        return {
            contracts: [],
            stats: config.stats || {},
            loading: true,
            filters: {
                q: '',
                status: '',
                page: 1
            },
            pagination: {},

            init() {
                this.fetchContracts();
            },

            async fetchContracts() {
                this.loading = true;
                try {
                    const params = new URLSearchParams();
                    if (this.filters.q) params.append('q', this.filters.q);
                    if (this.filters.status) params.append('status', this.filters.status);
                    params.append('page', this.filters.page);

                    const response = await fetch(`/contracts?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();
                    this.contracts = data.contracts || [];
                    this.pagination = data.pagination || {};
                    this.stats = data.stats || this.stats;
                } catch (error) {
                    console.error('Error fetching contracts:', error);
                } finally {
                    this.loading = false;
                }
            },

            search(value) {
                this.filters.q = value;
                this.filters.page = 1;
                this.fetchContracts();
            },

            setStatusFilter(status) {
                this.filters.status = status;
                this.filters.page = 1;
                this.fetchContracts();
            },

            goToPage(page) {
                if (page < 1 || page > this.pagination.last_page) return;
                this.filters.page = page;
                this.fetchContracts();
            },

            formatCurrency(amount, currency = 'RON') {
                return new Intl.NumberFormat('ro-RO', {
                    style: 'currency',
                    currency: currency
                }).format(amount || 0);
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
