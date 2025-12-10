<x-app-layout>
    <x-slot name="pageTitle">{{ __('Offers') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('offers.create') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('New Offer') }}
        </x-ui.button>
    </x-slot>

    <div class="p-6 space-y-6"
         x-data="offersPage({ stats: @js($stats) })"
         x-init="init()">

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <x-ui.card>
                <x-ui.card-content class="p-4">
                    <div class="text-2xl font-bold text-slate-600" x-text="stats.draft"></div>
                    <div class="text-sm text-slate-500">{{ __('Draft') }}</div>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-content class="p-4">
                    <div class="text-2xl font-bold text-blue-600" x-text="stats.sent"></div>
                    <div class="text-sm text-slate-500">{{ __('Sent') }}</div>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-content class="p-4">
                    <div class="text-2xl font-bold text-purple-600" x-text="stats.viewed"></div>
                    <div class="text-sm text-slate-500">{{ __('Viewed') }}</div>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-content class="p-4">
                    <div class="text-2xl font-bold text-green-600" x-text="stats.accepted"></div>
                    <div class="text-sm text-slate-500">{{ __('Accepted') }}</div>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-content class="p-4">
                    <div class="text-2xl font-bold text-red-600" x-text="stats.rejected"></div>
                    <div class="text-sm text-slate-500">{{ __('Rejected') }}</div>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-content class="p-4">
                    <div class="text-2xl font-bold text-yellow-600" x-text="stats.expiring_soon"></div>
                    <div class="text-sm text-slate-500">{{ __('Expiring Soon') }}</div>
                </x-ui.card-content>
            </x-ui.card>
        </div>

        {{-- Success/Error Messages --}}
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
                                   placeholder="{{ __('Search offers...') }}"
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
            <button @click="setStatusFilter('draft')"
                    :class="filters.status === 'draft' ? 'bg-slate-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('Draft') }}
            </button>
            <button @click="setStatusFilter('sent')"
                    :class="filters.status === 'sent' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700 hover:bg-blue-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('Sent') }}
            </button>
            <button @click="setStatusFilter('viewed')"
                    :class="filters.status === 'viewed' ? 'bg-purple-600 text-white' : 'bg-purple-100 text-purple-700 hover:bg-purple-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('Viewed') }}
            </button>
            <button @click="setStatusFilter('accepted')"
                    :class="filters.status === 'accepted' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-700 hover:bg-green-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('Accepted') }}
            </button>
            <button @click="setStatusFilter('rejected')"
                    :class="filters.status === 'rejected' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-700 hover:bg-red-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('Rejected') }}
            </button>
        </div>

        {{-- Loading --}}
        <div x-show="loading" class="flex justify-center py-8">
            <x-ui.spinner size="lg" color="blue" />
        </div>

        {{-- Offers Table --}}
        <template x-if="!loading && offers.length > 0">
            <x-ui.card>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Offer') }}</th>
                                <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Client') }}</th>
                                <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-right font-medium text-slate-600">{{ __('Total') }}</th>
                                <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Valid Until') }}</th>
                                <th class="px-6 py-4 text-right font-medium text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <template x-for="offer in offers" :key="offer.id">
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <a :href="'/offers/' + offer.id" class="font-medium text-slate-900 hover:text-blue-600">
                                            <span x-text="offer.offer_number"></span>
                                        </a>
                                        <div class="text-sm text-slate-500" x-text="offer.title"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a x-show="offer.client" :href="'/clients/' + offer.client?.slug" class="text-slate-900 hover:text-blue-600" x-text="offer.client?.name"></a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-slate-100 text-slate-700': offer.status === 'draft',
                                                  'bg-blue-100 text-blue-700': offer.status === 'sent',
                                                  'bg-purple-100 text-purple-700': offer.status === 'viewed',
                                                  'bg-green-100 text-green-700': offer.status === 'accepted',
                                                  'bg-red-100 text-red-700': offer.status === 'rejected',
                                                  'bg-yellow-100 text-yellow-700': offer.status === 'expired'
                                              }"
                                              x-text="offer.status_label">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium">
                                        <span x-text="formatCurrency(offer.total, offer.currency)"></span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600" x-text="offer.valid_until"></td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a :href="'/offers/' + offer.id"
                                               class="p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded"
                                               title="{{ __('View') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a x-show="offer.status === 'draft'"
                                               :href="'/offers/' + offer.id + '/edit'"
                                               class="p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded"
                                               title="{{ __('Edit') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        </div>
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
        <template x-if="!loading && offers.length === 0">
            <x-ui.card>
                <div class="px-6 py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No offers found') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Get started by creating your first offer.') }}</p>
                    <div class="mt-6">
                        <x-ui.button variant="default" onclick="window.location.href='{{ route('offers.create') }}'">
                            {{ __('New Offer') }}
                        </x-ui.button>
                    </div>
                </div>
            </x-ui.card>
        </template>
    </div>

    @push('scripts')
    <script>
    function offersPage(config) {
        return {
            offers: [],
            stats: config.stats || {},
            loading: true,
            filters: {
                q: '',
                status: '',
                page: 1
            },
            pagination: {},

            init() {
                this.fetchOffers();
            },

            async fetchOffers() {
                this.loading = true;
                try {
                    const params = new URLSearchParams();
                    if (this.filters.q) params.append('q', this.filters.q);
                    if (this.filters.status) params.append('status', this.filters.status);
                    params.append('page', this.filters.page);

                    const response = await fetch(`/offers?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();
                    this.offers = data.offers || [];
                    this.pagination = data.pagination || {};
                    this.stats = data.stats || this.stats;
                } catch (error) {
                    console.error('Error fetching offers:', error);
                } finally {
                    this.loading = false;
                }
            },

            search(value) {
                this.filters.q = value;
                this.filters.page = 1;
                this.fetchOffers();
            },

            setStatusFilter(status) {
                this.filters.status = status;
                this.filters.page = 1;
                this.fetchOffers();
            },

            goToPage(page) {
                if (page < 1 || page > this.pagination.last_page) return;
                this.filters.page = page;
                this.fetchOffers();
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
