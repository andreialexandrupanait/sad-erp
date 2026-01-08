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
        @php
            $formatMoney = function($moneyArr) {
                if (empty($moneyArr)) return '';
                $parts = [];
                foreach ($moneyArr as $currency => $amount) {
                    $parts[] = number_format($amount, 2, ',', '.') . ' ' . $currency;
                }
                return implode(' + ', $parts);
            };
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <x-widgets.metrics.stat-card
                :title="__('Draft')"
                :value="$stats['draft']"
                :subtitle="$formatMoney($stats['money']['draft'] ?? [])"
                color="blue"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>

            <x-widgets.metrics.stat-card
                :title="__('Sent')"
                :value="$stats['sent']"
                :subtitle="$formatMoney($stats['money']['sent'] ?? [])"
                color="blue"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>

            <x-widgets.metrics.stat-card
                :title="__('Viewed')"
                :value="$stats['viewed']"
                :subtitle="$formatMoney($stats['money']['viewed'] ?? [])"
                color="purple"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>

            <x-widgets.metrics.stat-card
                :title="__('Accepted')"
                :value="$stats['accepted']"
                :subtitle="$formatMoney($stats['money']['accepted'] ?? [])"
                color="green"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>

            <x-widgets.metrics.stat-card
                :title="__('Rejected')"
                :value="$stats['rejected']"
                :subtitle="$formatMoney($stats['money']['rejected'] ?? [])"
                color="red"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>

            <x-widgets.metrics.stat-card
                :title="__('Expiring Soon')"
                :value="$stats['expiring_soon']"
                :subtitle="__('next 7 days')"
                color="orange"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>
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
                <div class="flex flex-col sm:flex-row gap-3">
                    {{-- Search --}}
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <x-ui.input
                                type="text"
                                x-model="filters.q"
                                @input.debounce.300ms="fetchOffers()"
                                placeholder="{{ __('Search by offer number, client, title...') }}"
                                class="pl-10"
                            />
                        </div>
                    </div>

                    {{-- Client Filter --}}
                    <div class="w-full sm:w-52">
                        <x-ui.select x-model="filters.client_id" @change="fetchOffers()">
                            <option value="">{{ __('All Clients') }}</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->display_name }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    {{-- Status Filter --}}
                    <div class="w-full sm:w-40">
                        <x-ui.select x-model="filters.status" @change="fetchOffers()">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="draft">{{ __('Draft') }}</option>
                            <option value="sent">{{ __('Sent') }}</option>
                            <option value="viewed">{{ __('Viewed') }}</option>
                            <option value="accepted">{{ __('Accepted') }}</option>
                            <option value="rejected">{{ __('Rejected') }}</option>
                            <option value="expired">{{ __('Expired') }}</option>
                        </x-ui.select>
                    </div>

                    {{-- Search Button --}}
                    <div class="flex gap-2">
                        <x-ui.button type="button" variant="default" @click="fetchOffers()">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            {{ __('Search') }}
                        </x-ui.button>
                        <template x-if="filters.q || filters.client_id || filters.status">
                            <x-ui.button variant="outline" @click="clearFilters()">
                                {{ __('Clear') }}
                            </x-ui.button>
                        </template>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Bulk Actions Toolbar (Fixed at bottom) --}}
        <x-bulk-toolbar resource="oferte">
            <x-ui.button
                variant="outline"
                @click="bulkExport()"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('Export to CSV') }}
            </x-ui.button>
            <x-ui.button
                variant="destructive"
                @click="bulkDelete()"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('Delete Selected') }}
            </x-ui.button>
        </x-bulk-toolbar>

        {{-- Loading --}}
        <div x-show="loading" class="flex justify-center py-8">
            <x-ui.spinner size="lg" color="blue" />
        </div>

        {{-- Offers Table --}}
        <template x-if="!loading && offers.length > 0">
            <x-ui.card>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100">
                            <tr class="border-b border-slate-200">
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500 w-12">
                                    <x-bulk-checkbox x-model="selectAll" @change="toggleAll()" />
                                </th>
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Offer') }}</th>
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Client') }}</th>
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-right align-middle font-medium text-slate-500">{{ __('Total') }}</th>
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Valid Until') }}</th>
                                <th class="px-6 py-4 text-right align-middle font-medium text-slate-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <template x-for="offer in offers" :key="offer.id">
                                <tr class="hover:bg-slate-50" :class="{ 'bg-blue-50': isSelected(offer.id) }">
                                    <td class="px-6 py-4 w-12">
                                        <input type="checkbox"
                                               :checked="isSelected(offer.id)"
                                               @change="toggleItem(offer.id)"
                                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 cursor-pointer transition-colors">
                                    </td>
                                    <td class="px-6 py-4">
                                        <a :href="'/offers/' + offer.id" class="font-medium text-slate-900 hover:text-blue-600">
                                            <span x-text="offer.offer_number"></span>
                                        </a>
                                        <div class="text-sm text-slate-500" x-text="offer.title"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <template x-if="offer.client && !offer.client.is_temporary">
                                            <a :href="'/clients/' + offer.client?.slug" class="text-slate-900 hover:text-blue-600" x-text="offer.client?.name"></a>
                                        </template>
                                        <template x-if="offer.client && offer.client.is_temporary">
                                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-sm font-medium bg-amber-100 text-amber-700">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                <span x-text="offer.client?.name"></span>
                                            </span>
                                        </template>
                                        <template x-if="!offer.client">
                                            <span class="text-slate-400">—</span>
                                        </template>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
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
                                            {{-- Client modification badge --}}
                                            <span x-show="offer.client_modified_at"
                                                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"
                                                  :title="'{{ __('Client modified selections on') }} ' + offer.client_modified_at">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                                {{ __('Modified') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium">
                                        <span x-text="formatCurrency(offer.total, offer.currency)"></span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600" x-text="offer.valid_until"></td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            {{-- View --}}
                                            <a :href="'/offers/' + offer.id"
                                               class="inline-flex items-center text-slate-600 hover:text-slate-900 transition-colors"
                                               title="{{ __('View') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            {{-- Edit --}}
                                            <a :href="'/offers/' + offer.id + '/edit'"
                                               class="inline-flex items-center text-blue-600 hover:text-blue-900 transition-colors"
                                               title="{{ __('Edit') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            {{-- PDF Download --}}
                                            <a :href="'/offers/' + offer.id + '/pdf'"
                                               class="inline-flex items-center text-green-600 hover:text-green-900 transition-colors"
                                               title="{{ __('Download PDF') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </a>
                                            {{-- Print --}}
                                            <a :href="'/offers/' + offer.id + '/pdf?print=1'" target="_blank"
                                               class="inline-flex items-center text-purple-600 hover:text-purple-900 transition-colors"
                                               title="{{ __('Print') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                                </svg>
                                            </a>
                                            {{-- Send/Resend Email --}}
                                            <button x-show="offer.status !== 'rejected' && offer.status !== 'expired'"
                                                    @click="sendOffer(offer.id)"
                                                    class="inline-flex items-center text-green-600 hover:text-green-900 transition-colors"
                                                    :title="offer.status === 'draft' ? '{{ __('Send to Client') }}' : '{{ __('Resend') }}'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                            {{-- Delete --}}
                                            <button @click="deleteOffer(offer.id, offer.offer_number)"
                                                    class="inline-flex items-center text-red-600 hover:text-red-900 transition-colors"
                                                    title="{{ __('Delete') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
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
                client_id: '',
                page: 1
            },
            pagination: {},

            // Selection state (compatible with bulk-toolbar component)
            selectedIds: [],
            selectAll: false,
            isLoading: false,

            // Computed properties for bulk-toolbar
            get selectedCount() {
                return this.selectedIds.length;
            },

            get hasSelection() {
                return this.selectedIds.length > 0;
            },

            init() {
                this.fetchOffers();
            },

            // Selection methods
            isSelected(id) {
                return this.selectedIds.includes(id);
            },

            toggleItem(id) {
                const index = this.selectedIds.indexOf(id);
                if (index > -1) {
                    this.selectedIds.splice(index, 1);
                } else {
                    this.selectedIds.push(id);
                }
                this.updateSelectAllState();
            },

            toggleAll() {
                if (this.selectAll) {
                    this.selectedIds = this.offers.map(o => o.id);
                } else {
                    this.selectedIds = [];
                }
            },

            updateSelectAllState() {
                this.selectAll = this.offers.length > 0 && this.selectedIds.length === this.offers.length;
            },

            clearSelection() {
                this.selectedIds = [];
                this.selectAll = false;
            },

            clearFilters() {
                this.filters.q = '';
                this.filters.status = '';
                this.filters.client_id = '';
                this.filters.page = 1;
                this.fetchOffers();
            },

            async bulkExport() {
                if (this.selectedIds.length === 0) return;

                this.isLoading = true;

                try {
                    const response = await fetch('/offers/bulk-export', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ ids: this.selectedIds })
                    });

                    if (!response.ok) {
                        const error = await response.json().catch(() => ({ message: '{{ __('Export failed') }}' }));
                        throw new Error(error.message || '{{ __('Export failed') }}');
                    }

                    // Download the file
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;

                    // Get filename from Content-Disposition header or use default
                    const contentDisposition = response.headers.get('Content-Disposition');
                    let filename = 'offers_export.csv';
                    if (contentDisposition) {
                        const match = contentDisposition.match(/filename="?(.+)"?/i);
                        if (match) filename = match[1];
                    }

                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();

                    this.$dispatch('notify', {
                        type: 'success',
                        message: '{{ __('Offers exported successfully!') }}'
                    });
                    this.clearSelection();
                } catch (error) {
                    console.error('Error exporting offers:', error);
                    alert(error.message || '{{ __('Failed to export offers. Please try again.') }}');
                } finally {
                    this.isLoading = false;
                }
            },

            async bulkDelete() {
                if (this.selectedIds.length === 0) {
                    return;
                }

                const count = this.selectedIds.length;

                if (!confirm(`{{ __('Are you sure you want to delete') }} ${count} {{ __('offer(s)? This action cannot be undone.') }}`)) {
                    return;
                }

                this.isLoading = true;

                try {
                    // Ensure IDs are integers
                    const ids = this.selectedIds.map(id => parseInt(id, 10));
                    console.log('Sending bulk delete request with IDs:', ids);

                    const response = await fetch('/offers/bulk-delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids: ids })
                    });

                    const result = await response.json();

                    if (result.success || response.ok) {
                        // Check if any were actually deleted
                        if (result.deleted === 0 && result.skipped > 0) {
                            alert('{{ __('No offers were deleted. All selected offers have linked contracts and cannot be deleted.') }}');
                        } else {
                            this.$dispatch('notify', {
                                type: 'success',
                                message: result.message || `{{ __('Successfully deleted') }} ${count} {{ __('offer(s)') }}`
                            });
                        }

                        this.clearSelection();
                        await this.fetchOffers();
                    } else {
                        throw new Error(result.error || result.message || '{{ __('Failed to delete offers') }}');
                    }
                } catch (error) {
                    console.error('Error bulk deleting offers:', error);
                    alert(error.message || '{{ __('Failed to delete offers. Please try again.') }}');
                } finally {
                    this.isLoading = false;
                }
            },

            async fetchOffers() {
                this.loading = true;
                this.clearSelection(); // Clear selection when fetching new data
                try {
                    const params = new URLSearchParams();
                    if (this.filters.q) params.append('q', this.filters.q);
                    if (this.filters.status) params.append('status', this.filters.status);
                    if (this.filters.client_id) params.append('client_id', this.filters.client_id);
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
            },

            async sendOffer(offerId) {
                const offer = this.offers.find(o => o.id === offerId);
                if (!offer) return;

                const action = offer.status === 'draft' ? 'send' : 'resend';
                const actionText = offer.status === 'draft' ? '{{ __('send') }}' : '{{ __('resend') }}';

                if (!confirm(`{{ __('Are you sure you want to') }} ${actionText} {{ __('this offer to the client?') }}`)) {
                    return;
                }

                try {
                    const response = await fetch(`/offers/${offerId}/${action}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (result.success || response.ok) {
                        // Show success message
                        this.$dispatch('notify', {
                            type: 'success',
                            message: result.message || '{{ __('Offer sent successfully!') }}'
                        });

                        // Refresh the list to update status
                        await this.fetchOffers();
                    } else {
                        throw new Error(result.error || result.message || '{{ __('Failed to send offer') }}');
                    }
                } catch (error) {
                    console.error('Error sending offer:', error);
                    alert(error.message || '{{ __('Failed to send offer. Please try again.') }}');
                }
            },

            async deleteOffer(offerId, offerNumber) {
                if (!confirm(`{{ __('Are you sure you want to delete offer') }} ${offerNumber}? {{ __('This action cannot be undone.') }}`)) {
                    return;
                }

                try {
                    const response = await fetch(`/offers/${offerId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (result.success || response.ok) {
                        // Show success message
                        this.$dispatch('notify', {
                            type: 'success',
                            message: result.message || '{{ __('Offer deleted successfully!') }}'
                        });

                        // Refresh the list
                        await this.fetchOffers();
                    } else {
                        throw new Error(result.error || result.message || '{{ __('Failed to delete offer') }}');
                    }
                } catch (error) {
                    console.error('Error deleting offer:', error);
                    alert(error.message || '{{ __('Failed to delete offer. Please try again.') }}');
                }
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
