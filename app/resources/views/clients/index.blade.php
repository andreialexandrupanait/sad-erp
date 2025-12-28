<x-app-layout>
    <x-slot name="pageTitle">{{ __('Clients') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('clients.create') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('New Client') }}
        </x-ui.button>
    </x-slot>

    {{-- Main Container with Alpine.js --}}
    @php
        $statusData = $statuses->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'slug' => $s->slug,
            'color_background' => $s->color_background,
            'color_text' => $s->color_text
        ])->values()->all();
    @endphp
    <div class="p-6 space-y-6"
         x-data="clientsPage({ statuses: @js($statusData), filters: @js($initialFilters) })"
         x-init="init()">

        {{-- Loading Overlay --}}
        <div x-show="loading && initialLoad"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-white/90 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="flex flex-col items-center gap-3">
                <x-ui.spinner size="lg" color="blue" />
                <span class="text-sm text-slate-500">{{ __('Loading...') }}</span>
            </div>
        </div>

        {{-- Success Message --}}
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        {{-- Search and Controls Bar --}}
        <x-ui.card>
            <x-ui.card-content>
                <div class="flex flex-col sm:flex-row gap-3 items-center">
                    {{-- Search Input --}}
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
                                   @keydown.escape="filters.q = ''; search('')"
                                   placeholder="{{ __('Search clients...') }}"
                                   class="block w-full pl-10 pr-10 py-2 border border-slate-300 rounded-md text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            {{-- Clear search button --}}
                            <button x-show="filters.q"
                                    @click="filters.q = ''; search('')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Group Toggle (Table view only) --}}
                    <button x-show="ui.viewMode === 'table'"
                            @click="toggleGrouped()"
                            :class="ui.grouped ? 'bg-blue-100 text-blue-700 border-blue-300' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors border"
                            title="{{ __('Group by Status') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        {{ __('Group') }}
                    </button>

                    {{-- View Mode Switcher --}}
                    <div class="flex gap-1 border border-slate-300 rounded-md p-1">
                        <button @click="setViewMode('table')"
                                :class="ui.viewMode === 'table' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                                class="p-2 rounded transition-colors"
                                title="{{ __('Table') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                        <button @click="setViewMode('kanban')"
                                :class="ui.viewMode === 'kanban' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                                class="p-2 rounded transition-colors"
                                title="{{ __('Kanban') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                        </button>
                        <button @click="setViewMode('grid')"
                                :class="ui.viewMode === 'grid' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                                class="p-2 rounded transition-colors"
                                title="{{ __('Grid') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Bulk Actions Toolbar (Fixed Bottom Bar) --}}
        <x-bulk-toolbar resource="clienți">
            {{-- Bulk Status Update Dropdown --}}
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <x-ui.button variant="outline" class="!bg-slate-800 !border-slate-600 !text-white hover:!bg-slate-700" @click="open = !open">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    {{ __('Update Status') }}
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </x-ui.button>

                <div x-show="open"
                     x-transition
                     class="absolute bottom-full mb-2 left-0 w-56 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50"
                     style="display: none;">
                    <template x-for="status in statuses" :key="status.id">
                        <button type="button"
                                @click="bulkUpdateStatus(status.id); open = false"
                                class="w-full px-3 py-2 text-left text-sm text-slate-900 hover:bg-slate-50 flex items-center gap-2 transition-colors">
                            <span class="w-3 h-3 rounded-full flex-shrink-0" :style="'background-color: ' + status.color_background"></span>
                            <span class="font-medium" x-text="status.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <x-ui.button variant="outline" class="!bg-slate-800 !border-slate-600 !text-white hover:!bg-slate-700" @click="bulkExport()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('Export') }}
            </x-ui.button>

            <x-ui.button variant="destructive" @click="bulkDelete()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('Delete') }}
            </x-ui.button>
        </x-bulk-toolbar>

        {{-- Status Filter Pills --}}
        <div x-show="ui.viewMode === 'table'" class="flex flex-wrap gap-2">
            {{-- All Status Pill --}}
            <button @click="clearStatusFilter()"
                    :class="filters.status.length === 0 ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('All') }}
                <span :class="filters.status.length === 0 ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600'"
                      class="inline-flex items-center justify-center px-1.5 py-0.5 text-xs rounded-full"
                      x-text="statusCounts.total || 0">
                </span>
            </button>

            {{-- Status Pills --}}
            <template x-for="status in statuses" :key="status.id">
                <button @click="toggleStatus(status.slug)"
                        :class="filters.status.includes(status.slug) ? '' : 'opacity-60 hover:opacity-100'"
                        :style="'background-color: ' + status.color_background + '; color: ' + status.color_text"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-all">
                    <span x-text="status.name"></span>
                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-xs rounded-full"
                          :style="'background-color: ' + status.color_text + '20'"
                          x-text="getStatusCount(status.id)">
                    </span>
                </button>
            </template>
        </div>

        {{-- Loading Indicator (for subsequent loads) --}}
        <div x-show="loading && !initialLoad"
             class="flex justify-center py-4">
            <x-ui.spinner size="md" color="blue" />
        </div>

        {{-- TABLE VIEW --}}
        <template x-if="ui.viewMode === 'table'">
            <div>
                {{-- Empty State --}}
                <template x-if="!loading && clients.length === 0">
                    <x-ui.card>
                        <div class="px-6 py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No clients found') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                <span x-show="filters.q || filters.status.length">{{ __('Try adjusting your search or filter') }}</span>
                                <span x-show="!filters.q && !filters.status.length">{{ __('Get started by creating your first client') }}</span>
                            </p>
                            <div class="mt-6" x-show="!filters.q && !filters.status.length">
                                <x-ui.button variant="default" onclick="window.location.href='{{ route('clients.create') }}'">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('New Client') }}
                                </x-ui.button>
                            </div>
                        </div>
                    </x-ui.card>
                </template>

                {{-- Grouped View --}}
                <template x-if="!loading && clients.length > 0 && ui.grouped">
                    <div class="space-y-6">
                        <template x-for="status in statuses" :key="status.id">
                            <div x-show="getClientsForStatus(status.id).length > 0" class="status-group">
                                {{-- Status Header --}}
                                <div class="flex items-center gap-3 mb-3 cursor-pointer select-none"
                                     @click="toggleGroupCollapse(status.id)">
                                    <button type="button" class="p-1 hover:bg-slate-100 rounded transition-colors">
                                        <svg class="w-4 h-4 text-slate-500 transition-transform duration-200"
                                             :class="{ '-rotate-90': isGroupCollapsed(status.id) }"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                          :style="'background-color: ' + status.color_background + '; color: ' + status.color_text"
                                          x-text="status.name">
                                    </span>
                                    <span class="text-sm text-slate-500">
                                        <span x-text="getClientsForStatus(status.id).length"></span> {{ __('app.clients') }}
                                    </span>
                                </div>

                                {{-- Status Group Table --}}
                                <div x-show="!isGroupCollapsed(status.id)" class="rounded-[10px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                                    <div class="overflow-x-auto">
                                        <table class="w-full caption-bottom text-sm">
                                            <thead class="bg-slate-100">
                                                <tr class="border-b border-slate-200">
                                                    <th class="px-6 py-4 text-left align-middle font-medium text-slate-600 w-12">
                                                        <x-bulk-checkbox x-model="selectAll" @change="toggleSelectAll()" />
                                                    </th>
                                                    <th @click="setSort('name')" class="px-6 py-4 text-left align-middle font-medium text-slate-600 cursor-pointer hover:text-slate-900">
                                                        <div class="flex items-center gap-1">
                                                            {{ __('Client') }}
                                                            <span x-show="sortColumn === 'name'" class="text-blue-600">
                                                                <svg x-show="sortDirection === 'asc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                                                <svg x-show="sortDirection === 'desc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                            </span>
                                                        </div>
                                                    </th>
                                                    <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Contact') }}</th>
                                                    <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Status') }}</th>
                                                    <th @click="setSort('revenue')" class="px-6 py-4 text-right align-middle font-medium text-slate-600 cursor-pointer hover:text-slate-900">
                                                        <div class="flex items-center justify-end gap-1">
                                                            {{ __('Revenue') }}
                                                            <span x-show="sortColumn === 'revenue' || sortColumn === 'total_incomes'" class="text-blue-600">
                                                                <svg x-show="sortDirection === 'asc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                                                <svg x-show="sortDirection === 'desc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                            </span>
                                                        </div>
                                                    </th>
                                                    <th class="px-6 py-4 text-right align-middle font-medium text-slate-500 w-24">{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="[&_tr:last-child]:border-0">
                                                <template x-for="client in getClientsForStatus(status.id)" :key="client.id">
                                                    <tr class="border-b border-slate-200 hover:bg-slate-50/50 transition-colors">
                                                        <td class="px-6 py-4 align-middle w-12">
                                                            <input type="checkbox"
                                                                   :checked="isSelected(client.id)"
                                                                   @change="toggleItem(client.id)"
                                                                   class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 cursor-pointer transition-colors">
                                                        </td>
                                                        <td class="px-6 py-4 align-middle">
                                                            <div>
                                                                <a :href="'/clients/' + (client.slug || client.id)"
                                                                   class="text-sm font-semibold text-slate-900 hover:text-slate-600 transition-colors"
                                                                   x-text="client.name"></a>
                                                                <div x-show="client.contact_person" class="text-sm text-slate-500" x-text="client.contact_person"></div>
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4 align-middle">
                                                            <div x-show="client.email"
                                                                 @click="copyToClipboard(client.email, $event)"
                                                                 class="text-sm text-slate-900 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-1 group"
                                                                 title="Click to copy">
                                                                <span x-text="client.email"></span>
                                                                <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                                </svg>
                                                            </div>
                                                            <div x-show="!client.email" class="text-sm text-slate-500">—</div>
                                                            <div x-show="client.phone"
                                                                 @click="copyToClipboard(client.phone, $event)"
                                                                 class="text-sm text-slate-500 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-1 group"
                                                                 title="Click to copy">
                                                                <span x-text="client.phone"></span>
                                                                <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                                </svg>
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4 align-middle">
                                                            {{-- Status Dropdown --}}
                                                            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                                                                <button type="button"
                                                                        @click="open = !open"
                                                                        :disabled="savingStatus[client.id]"
                                                                        :class="{ 'opacity-50': savingStatus[client.id] }"
                                                                        class="cursor-pointer transition-all hover:scale-105 active:scale-95">
                                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
                                                                          :style="client.status ? 'background-color: ' + client.status.color_background + '; color: ' + client.status.color_text : 'background-color: #e2e8f0; color: #475569'">
                                                                        <span x-show="savingStatus[client.id]" class="mr-1">
                                                                            <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                                            </svg>
                                                                        </span>
                                                                        <span x-text="client.status ? client.status.name : '{{ __('No Status') }}'"></span>
                                                                        <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                        </svg>
                                                                    </span>
                                                                </button>

                                                                <div x-show="open"
                                                                     x-transition
                                                                     class="absolute z-50 mt-1 left-0 w-40 bg-white rounded-lg shadow-lg border border-slate-200 py-1"
                                                                     style="display: none;">
                                                                    <template x-for="s in statuses" :key="s.id">
                                                                        <button type="button"
                                                                                @click="updateClientStatus(client, s.id); open = false"
                                                                                class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2 transition-colors"
                                                                                :class="{ 'bg-slate-100': client.status_id === s.id }">
                                                                            <span class="w-3 h-3 rounded-full flex-shrink-0" :style="'background-color: ' + s.color_background"></span>
                                                                            <span x-text="s.name"></span>
                                                                            <svg x-show="client.status_id === s.id" class="w-4 h-4 ml-auto text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                            </svg>
                                                                        </button>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4 align-middle text-right">
                                                            <div class="text-sm font-semibold text-slate-900" x-text="formatCurrency(client.total_incomes)"></div>
                                                            <div x-show="client.invoices_count > 0" class="text-xs text-slate-500">
                                                                <span x-text="client.invoices_count"></span> {{ __('invoices') }}
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4 align-middle text-right">
                                                            <div class="flex items-center justify-end gap-1">
                                                                <a :href="'/clients/' + (client.slug || client.id)"
                                                                   class="p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded transition-colors"
                                                                   title="{{ __('View') }}">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                                    </svg>
                                                                </a>
                                                                <a :href="'/clients/' + (client.slug || client.id) + '/edit'"
                                                                   class="p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded transition-colors"
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
                                </div>
                            </div>
                        </template>

                        {{-- No Status Group --}}
                        <div x-show="getClientsWithoutStatus().length > 0" class="status-group">
                            <div class="flex items-center gap-3 mb-3 cursor-pointer select-none"
                                 @click="toggleGroupCollapse(null)">
                                <button type="button" class="p-1 hover:bg-slate-100 rounded transition-colors">
                                    <svg class="w-4 h-4 text-slate-500 transition-transform duration-200"
                                         :class="{ '-rotate-90': isGroupCollapsed(null) }"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-200 text-slate-600">
                                    {{ __('No Status') }}
                                </span>
                                <span class="text-sm text-slate-500">
                                    <span x-text="getClientsWithoutStatus().length"></span> {{ __('app.clients') }}
                                </span>
                            </div>

                            <div x-show="!isGroupCollapsed(null)" class="rounded-[10px] border border-slate-200 bg-white shadow-sm overflow-hidden">
                                {{-- Same table structure as above for clients without status --}}
                                <div class="overflow-x-auto">
                                    <table class="w-full caption-bottom text-sm">
                                        {{-- Similar table content --}}
                                        <thead class="bg-slate-100">
                                            <tr class="border-b border-slate-200">
                                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500 w-12">
                                                    <x-bulk-checkbox x-model="selectAll" @change="toggleSelectAll()" />
                                                </th>
                                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Client') }}</th>
                                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Contact') }}</th>
                                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Status') }}</th>
                                                <th class="px-6 py-4 text-right align-middle font-medium text-slate-500">{{ __('Revenue') }}</th>
                                                <th class="px-6 py-4 text-right align-middle font-medium text-slate-500 w-24">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="[&_tr:last-child]:border-0">
                                            <template x-for="client in getClientsWithoutStatus()" :key="client.id">
                                                <tr class="border-b border-slate-200 hover:bg-slate-50/50 transition-colors">
                                                    <td class="px-6 py-4 align-middle">
                                                        <input type="checkbox"
                                                               :checked="isSelected(client.id)"
                                                               @change="toggleItem(client.id)"
                                                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 cursor-pointer transition-colors">
                                                    </td>
                                                    <td class="px-6 py-4 align-middle">
                                                        <a :href="'/clients/' + (client.slug || client.id)" class="text-sm font-semibold text-slate-900 hover:text-slate-600" x-text="client.name"></a>
                                                        <div x-show="client.contact_person" class="text-sm text-slate-500" x-text="client.contact_person"></div>
                                                    </td>
                                                    <td class="px-6 py-4 align-middle">
                                                        <div x-show="client.email" class="text-sm text-slate-900" x-text="client.email"></div>
                                                        <div x-show="!client.email" class="text-sm text-slate-500">—</div>
                                                        <div x-show="client.phone" class="text-sm text-slate-500" x-text="client.phone"></div>
                                                    </td>
                                                    <td class="px-6 py-4 align-middle">
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-200 text-slate-600">{{ __('No Status') }}</span>
                                                    </td>
                                                    <td class="px-6 py-4 align-middle text-right">
                                                        <div class="text-sm font-semibold text-slate-900" x-text="formatCurrency(client.total_incomes)"></div>
                                                    </td>
                                                    <td class="px-6 py-4 align-middle text-right">
                                                        <a :href="'/clients/' + (client.slug || client.id)" class="p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                        </a>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Flat Table View --}}
                <template x-if="!loading && clients.length > 0 && !ui.grouped">
                    <x-ui.card>
                        <div class="overflow-x-auto">
                            <table class="w-full caption-bottom text-sm">
                                <thead class="bg-slate-100">
                                    <tr class="border-b border-slate-200">
                                        <th class="px-6 py-4 text-left align-middle font-medium text-slate-500 w-12">
                                            <x-bulk-checkbox x-model="selectAll" @change="toggleSelectAll()" />
                                        </th>
                                        <th @click="setSort('name')" class="px-6 py-4 text-left align-middle font-medium text-slate-600 cursor-pointer hover:text-slate-900">
                                            <div class="flex items-center gap-1">
                                                {{ __('Client') }}
                                                <span x-show="sortColumn === 'name'" class="text-blue-600">
                                                    <svg x-show="sortDirection === 'asc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                                    <svg x-show="sortDirection === 'desc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Contact') }}</th>
                                        <th @click="setSort('status')" class="px-6 py-4 text-left align-middle font-medium text-slate-600 cursor-pointer hover:text-slate-900">
                                            <div class="flex items-center gap-1">
                                                {{ __('Status') }}
                                                <span x-show="sortColumn === 'status' || sortColumn === 'status_id'" class="text-blue-600">
                                                    <svg x-show="sortDirection === 'asc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                                    <svg x-show="sortDirection === 'desc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </span>
                                            </div>
                                        </th>
                                        <th @click="setSort('revenue')" class="px-6 py-4 text-right align-middle font-medium text-slate-600 cursor-pointer hover:text-slate-900">
                                            <div class="flex items-center justify-end gap-1">
                                                {{ __('Revenue') }}
                                                <span x-show="sortColumn === 'revenue' || sortColumn === 'total_incomes'" class="text-blue-600">
                                                    <svg x-show="sortDirection === 'asc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                                    <svg x-show="sortDirection === 'desc'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-right align-middle font-medium text-slate-500 w-24">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="[&_tr:last-child]:border-0">
                                    <template x-for="client in clients" :key="client.id">
                                        <tr class="border-b border-slate-200 hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4 align-middle">
                                                <input type="checkbox"
                                                       :checked="isSelected(client.id)"
                                                       @change="toggleItem(client.id)"
                                                       class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 cursor-pointer transition-colors">
                                            </td>
                                            <td class="px-6 py-4 align-middle">
                                                <div>
                                                    <a :href="'/clients/' + (client.slug || client.id)"
                                                       class="text-sm font-semibold text-slate-900 hover:text-slate-600 transition-colors"
                                                       x-text="client.name"></a>
                                                    <div x-show="client.contact_person" class="text-sm text-slate-500" x-text="client.contact_person"></div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 align-middle">
                                                <div x-show="client.email"
                                                     @click="copyToClipboard(client.email, $event)"
                                                     class="text-sm text-slate-900 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-1 group"
                                                     title="Click to copy">
                                                    <span x-text="client.email"></span>
                                                    <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <div x-show="!client.email" class="text-sm text-slate-500">—</div>
                                                <div x-show="client.phone"
                                                     @click="copyToClipboard(client.phone, $event)"
                                                     class="text-sm text-slate-500 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-1 group"
                                                     title="Click to copy">
                                                    <span x-text="client.phone"></span>
                                                    <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 align-middle">
                                                {{-- Status Dropdown --}}
                                                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                                                    <button type="button"
                                                            @click="open = !open"
                                                            :disabled="savingStatus[client.id]"
                                                            :class="{ 'opacity-50': savingStatus[client.id] }"
                                                            class="cursor-pointer transition-all hover:scale-105 active:scale-95">
                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
                                                              :style="client.status ? 'background-color: ' + client.status.color_background + '; color: ' + client.status.color_text : 'background-color: #e2e8f0; color: #475569'">
                                                            <span x-show="savingStatus[client.id]" class="mr-1">
                                                                <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                                </svg>
                                                            </span>
                                                            <span x-text="client.status ? client.status.name : '{{ __('No Status') }}'"></span>
                                                            <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                            </svg>
                                                        </span>
                                                    </button>

                                                    <div x-show="open"
                                                         x-transition
                                                         class="absolute z-50 mt-1 left-0 w-40 bg-white rounded-lg shadow-lg border border-slate-200 py-1"
                                                         style="display: none;">
                                                        <template x-for="s in statuses" :key="s.id">
                                                            <button type="button"
                                                                    @click="updateClientStatus(client, s.id); open = false"
                                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2 transition-colors"
                                                                    :class="{ 'bg-slate-100': client.status_id === s.id }">
                                                                <span class="w-3 h-3 rounded-full flex-shrink-0" :style="'background-color: ' + s.color_background"></span>
                                                                <span x-text="s.name"></span>
                                                                <svg x-show="client.status_id === s.id" class="w-4 h-4 ml-auto text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                </svg>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 align-middle text-right">
                                                <div class="text-sm font-semibold text-slate-900" x-text="formatCurrency(client.total_incomes)"></div>
                                                <div x-show="client.invoices_count > 0" class="text-xs text-slate-500">
                                                    <span x-text="client.invoices_count"></span> {{ __('invoices') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 align-middle text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    <a :href="'/clients/' + (client.slug || client.id)"
                                                       class="p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded transition-colors"
                                                       title="{{ __('View') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                    </a>
                                                    <a :href="'/clients/' + (client.slug || client.id) + '/edit'"
                                                       class="p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded transition-colors"
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
                        <div x-show="pagination.last_page > 1" class="bg-slate-100 px-6 py-4 border-t border-slate-200 flex items-center justify-between flex-wrap gap-4">
                            <div class="flex items-center gap-2 text-sm text-slate-600">
                                <span>{{ __('Per page:') }}</span>
                                <select @change="setPerPage($event.target.value)"
                                        :value="ui.perPage"
                                        class="border-slate-300 rounded-md text-sm py-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-sm text-slate-600">
                                    {{ __('Showing') }} <span x-text="pagination.from || 0"></span> - <span x-text="pagination.to || 0"></span> {{ __('of') }} <span x-text="pagination.total || 0"></span>
                                </span>

                                <div class="flex gap-1">
                                    <button @click="goToPage(filters.page - 1)"
                                            :disabled="filters.page === 1"
                                            :class="{ 'opacity-50 cursor-not-allowed': filters.page === 1 }"
                                            class="px-3 py-1 text-sm border border-slate-300 rounded-md hover:bg-slate-50">
                                        {{ __('Prev') }}
                                    </button>

                                    <template x-for="p in pages" :key="p">
                                        <button @click="goToPage(p)"
                                                :class="p === filters.page ? 'bg-slate-900 text-white' : 'hover:bg-slate-50'"
                                                :disabled="p === '...'"
                                                class="px-3 py-1 text-sm border border-slate-300 rounded-md"
                                                x-text="p">
                                        </button>
                                    </template>

                                    <button @click="goToPage(filters.page + 1)"
                                            :disabled="filters.page === pagination.last_page"
                                            :class="{ 'opacity-50 cursor-not-allowed': filters.page === pagination.last_page }"
                                            class="px-3 py-1 text-sm border border-slate-300 rounded-md hover:bg-slate-50">
                                        {{ __('Next') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                </template>
            </div>
        </template>

        {{-- KANBAN VIEW --}}
        <template x-if="ui.viewMode === 'kanban'">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="status in statuses" :key="status.id">
                    <x-ui.card>
                        <x-ui.card-header>
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-slate-900" x-text="status.name"></h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :style="'background-color: ' + status.color_background + '; color: ' + status.color_text"
                                      x-text="getClientsForStatus(status.id).length">
                                </span>
                            </div>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <div class="space-y-2">
                                <template x-for="client in getClientsForStatus(status.id)" :key="client.id">
                                    <div class="p-3 bg-slate-50 hover:bg-slate-100 rounded-lg transition border border-transparent hover:border-slate-300">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <a :href="'/clients/' + (client.slug || client.id)"
                                                   class="font-medium text-slate-900 hover:text-slate-600"
                                                   x-text="client.name"></a>
                                                <div x-show="client.company_name" class="text-sm text-slate-500" x-text="client.company_name"></div>
                                            </div>
                                            <a :href="'/clients/' + (client.slug || client.id)"
                                               class="text-slate-400 hover:text-slate-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                        </div>
                                        <div class="text-sm font-semibold text-slate-900 pt-2 border-t border-slate-200"
                                             x-text="formatCurrency(client.total_incomes)">
                                        </div>
                                    </div>
                                </template>
                                <div x-show="getClientsForStatus(status.id).length === 0"
                                     class="text-center py-4 text-sm text-slate-500">
                                    {{ __('No clients') }}
                                </div>
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                </template>
            </div>
        </template>

        {{-- GRID VIEW --}}
        <template x-if="ui.viewMode === 'grid'">
            <div>
                <div x-show="clients.length === 0 && !loading" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No clients found') }}</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="client in clients" :key="client.id">
                        <x-ui.card class="hover:shadow-lg transition-shadow">
                            <x-ui.card-content>
                                <div class="flex items-center justify-between mb-4">
                                    <a :href="'/clients/' + (client.slug || client.id)"
                                       class="text-lg font-semibold text-slate-900 hover:text-slate-600"
                                       x-text="client.name"></a>
                                    <span x-show="client.status"
                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :style="'background-color: ' + client.status.color_background + '; color: ' + client.status.color_text"
                                          x-text="client.status?.name">
                                    </span>
                                </div>
                                <p x-show="client.company_name" class="text-sm text-slate-600 mb-2" x-text="client.company_name"></p>
                                <div class="mt-3 pt-3 border-t border-slate-200">
                                    <div class="text-xs text-slate-500 uppercase mb-1">{{ __('Total Incomes') }}</div>
                                    <div class="text-lg font-semibold text-slate-900" x-text="formatCurrency(client.total_incomes)"></div>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-slate-200 mt-4">
                                    <a :href="'/clients/' + (client.slug || client.id)"
                                       class="text-sm text-slate-900 hover:text-slate-600 font-medium transition-colors">
                                        {{ __('View Details') }} →
                                    </a>
                                </div>
                            </x-ui.card-content>
                        </x-ui.card>
                    </template>
                </div>

                {{-- Grid Pagination --}}
                <div x-show="pagination.last_page > 1" class="mt-6 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <span>{{ __('Per page:') }}</span>
                        <select @change="setPerPage($event.target.value)" :value="ui.perPage"
                                class="border-slate-300 rounded-md text-sm py-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="flex gap-1">
                        <button @click="goToPage(filters.page - 1)" :disabled="filters.page === 1"
                                class="px-3 py-1 text-sm border border-slate-300 rounded-md hover:bg-slate-50">{{ __('Prev') }}</button>
                        <template x-for="p in pages" :key="p">
                            <button @click="goToPage(p)" :class="p === filters.page ? 'bg-slate-900 text-white' : 'hover:bg-slate-50'"
                                    class="px-3 py-1 text-sm border border-slate-300 rounded-md" x-text="p"></button>
                        </template>
                        <button @click="goToPage(filters.page + 1)" :disabled="filters.page === pagination.last_page"
                                class="px-3 py-1 text-sm border border-slate-300 rounded-md hover:bg-slate-50">{{ __('Next') }}</button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Toast Notification Container --}}
        <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
    </div>

    {{-- Confirm Dialog Component --}}
    <x-ui.confirm-dialog />
</x-app-layout>
