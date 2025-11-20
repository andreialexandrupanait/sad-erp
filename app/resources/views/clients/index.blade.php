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

    <div class="p-6 space-y-6" x-data>
        <!-- Success Message -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- Search and Filter Bar -->
        <x-ui.card>
            <x-ui.card-content>
                <form method="GET" action="{{ route('clients.index') }}">
                    <div class="flex flex-col sm:flex-row gap-3 items-center">
                        <!-- Search -->
                        <div class="flex-1 w-full">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <x-ui.input
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="{{ __('Search clients') }}"
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="w-full sm:w-48">
                            <x-ui.select name="status_id">
                                <option value="">{{ __('All Statuses') }}</option>
                                @foreach($clientStatuses as $status)
                                    <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <!-- Group by Status Checkbox (only for table view) -->
                        <div class="flex items-center">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="group_by_status" value="1"
                                       {{ request('group_by_status') ? 'checked' : '' }}
                                       onchange="this.form.submit()"
                                       class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                                <span class="text-sm text-slate-700">{{ __('Group by Status') }}</span>
                            </label>
                        </div>

                        <!-- View Mode Switcher (Icons) -->
                        <div class="flex gap-1 border border-slate-300 rounded-md p-1">
                            <a href="{{ route('clients.index', array_merge(request()->except('view'), ['view' => 'table'])) }}"
                                class="p-2 rounded transition-colors {{ $viewMode === 'table' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}"
                                title="{{ __('Table') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </a>
                            <a href="{{ route('clients.index', array_merge(request()->except('view'), ['view' => 'kanban'])) }}"
                                class="p-2 rounded transition-colors {{ $viewMode === 'kanban' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}"
                                title="{{ __('Kanban') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                            </a>
                            <a href="{{ route('clients.index', array_merge(request()->except('view'), ['view' => 'grid'])) }}"
                                class="p-2 rounded transition-colors {{ $viewMode === 'grid' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}"
                                title="{{ __('Grid') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                            </a>
                        </div>

                        <!-- Search/Clear Buttons -->
                        <div class="flex gap-2">
                            <x-ui.button type="submit" variant="default">
                                <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                <span class="hidden sm:inline">{{ __('Search') }}</span>
                            </x-ui.button>
                            @if(request('search') || request('status_id'))
                                <x-ui.button variant="outline" onclick="window.location.href='{{ route('clients.index') }}'">
                                    <span class="hidden sm:inline">{{ __('Clear') }}</span>
                                    <span class="sm:hidden">✕</span>
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Table View -->
        @if($viewMode === 'table')
            <x-ui.card>
                    @if($clients->isEmpty())
                        <div class="px-6 py-16 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No clients') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Get started by creating your first client') }}</p>
                        <div class="mt-6">
                            <x-ui.button variant="default" onclick="window.location.href='{{ route('clients.create') }}'">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('New Client') }}
                            </x-ui.button>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full caption-bottom text-sm">
                            <thead class="[&_tr]:border-b">
                                <tr class="border-b transition-colors hover:bg-slate-50/50">
                                    <x-ui.sortable-header column="name" label="{{ __('Client') }}" />
                                    <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">{{ __('Contact Person') }}</th>
                                    <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">{{ __('Contact') }}</th>
                                    <x-ui.sortable-header column="tax_id" label="{{ __('Tax ID') }}" />
                                    <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">{{ __('Status') }}</th>
                                    <x-ui.sortable-header column="total_incomes" label="{{ __('Total Incomes') }}" class="text-right" />
                                    <th class="h-12 px-4 text-right align-middle font-medium text-slate-500">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="[&_tr:last-child]:border-0">
                                @if($groupByStatus)
                                    {{-- Grouped by status view --}}
                                    @foreach($clientStatuses as $status)
                                        @php
                                            $statusClients = $clients->get($status->id, collect());
                                        @endphp
                                        @if($statusClients->count() > 0)
                                            {{-- Status group header --}}
                                            <tr class="bg-slate-50">
                                                <td colspan="7" class="px-4 py-3">
                                                    <div class="flex items-center gap-3">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                              style="background-color: {{ $status->color_background }}; color: {{ $status->color_text }};">
                                                            {{ $status->name }}
                                                        </span>
                                                        <span class="text-sm text-slate-600">{{ $statusClients->count() }} {{ __('clients') }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            {{-- Clients in this status --}}
                                            @foreach($statusClients as $client)
                                    <x-ui.table-row>
                                        <x-ui.table-cell>
                                            <div>
                                                <a href="{{ route('clients.show', $client) }}" class="text-sm font-semibold text-slate-900 hover:text-slate-600 transition-colors">
                                                    {{ $client->name }}
                                                </a>
                                                @if($client->company_name)
                                                    <div class="text-sm text-slate-500">{{ $client->company_name }}</div>
                                                @endif
                                            </div>
                                        </x-ui.table-cell>
                                        <x-ui.table-cell>
                                            <div class="text-sm text-slate-900">{{ $client->contact_person ?: '—' }}</div>
                                        </x-ui.table-cell>
                                        <x-ui.table-cell>
                                            @if($client->email)
                                                <div class="text-sm text-slate-900 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-1 group"
                                                     onclick="copyToClipboard('{{ $client->email }}', this)"
                                                     title="Click to copy">
                                                    <span>{{ $client->email }}</span>
                                                    <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            @else
                                                <div class="text-sm text-slate-900">—</div>
                                            @endif
                                            @if($client->phone)
                                                <div class="text-sm text-slate-500 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-1 group"
                                                     onclick="copyToClipboard('{{ $client->phone }}', this)"
                                                     title="Click to copy">
                                                    <span>{{ $client->phone }}</span>
                                                    <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </x-ui.table-cell>
                                        <x-ui.table-cell>
                                            <div class="text-sm text-slate-500">{{ $client->tax_id ?: '—' }}</div>
                                        </x-ui.table-cell>
                                        <x-ui.table-cell>
                                            <div x-data="{
                                                open: false,
                                                saving: false,
                                                statusId: {{ $client->status_id ?? 'null' }},
                                                currentStatus: {{ $client->status_id ?? 'null' }}
                                            }"
                                            @click.away="open = false"
                                            class="relative group">
                                                <!-- Display Mode -->
                                                <div x-show="!saving"
                                                     @click="open = !open"
                                                     class="inline-block"
                                                     title="{{ $client->status ? __('Click to change status') : __('Click to set status') }}">
                                                    <x-client-status-badge :status="$client->status" :editable="true" />
                                                </div>

                                                <!-- Saving State -->
                                                <div x-show="saving" x-cloak class="inline-flex items-center gap-2 px-3 py-1 text-xs text-blue-600">
                                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <span>{{ __('Saving...') }}</span>
                                                </div>

                                                <!-- ClickUp-Style Dropdown -->
                                                <div x-show="open"
                                                     x-cloak
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="transform opacity-0 scale-95"
                                                     x-transition:enter-end="transform opacity-100 scale-100"
                                                     x-transition:leave="transition ease-in duration-75"
                                                     x-transition:leave-start="transform opacity-100 scale-100"
                                                     x-transition:leave-end="transform opacity-0 scale-95"
                                                     class="absolute z-50 mt-1 w-56 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                                     style="left: 0;">
                                                    <div class="p-2 max-h-64 overflow-y-auto">
                                                        <div class="px-2 py-1.5 text-xs font-medium text-slate-500 uppercase tracking-wider">
                                                            {{ __('Change Status') }}
                                                        </div>
                                                        @foreach($clientStatuses as $status)
                                                            <button
                                                                type="button"
                                                                @click="
                                                                    open = false;
                                                                    saving = true;
                                                                    updateStatusInline('{{ $client->slug }}', {{ $status->id }}, $el, function(success) {
                                                                        saving = false;
                                                                        if (success) {
                                                                            statusId = {{ $status->id }};
                                                                            currentStatus = {{ $status->id }};
                                                                        }
                                                                    });
                                                                "
                                                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 rounded-md transition-colors {{ $client->status_id == $status->id ? 'bg-slate-50' : '' }}">
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                                      style="background-color: {{ $status->color_background }}; color: {{ $status->color_text }};">
                                                                    {{ $status->name }}
                                                                </span>
                                                                @if($client->status_id == $status->id)
                                                                    <svg class="ml-auto w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                    </svg>
                                                                @endif
                                                            </button>
                                                        @endforeach
                                                        @if($client->status_id)
                                                            <hr class="my-2 border-slate-200">
                                                            <button
                                                                type="button"
                                                                @click="
                                                                    open = false;
                                                                    saving = true;
                                                                    updateStatusInline('{{ $client->slug }}', null, $el, function(success) {
                                                                        saving = false;
                                                                        if (success) {
                                                                            statusId = null;
                                                                            currentStatus = null;
                                                                        }
                                                                    });
                                                                "
                                                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                </svg>
                                                                {{ __('Clear Status') }}
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </x-ui.table-cell>
                                        <x-ui.table-cell class="text-right">
                                            <div class="text-sm font-semibold text-slate-900">
                                                {{ number_format($client->total_incomes, 2) }} RON
                                            </div>
                                        </x-ui.table-cell>
                                        <x-ui.table-cell class="text-right">
                                            <x-table-actions
                                                :viewUrl="route('clients.show', $client)"
                                                :editUrl="route('clients.edit', $client)"
                                                :deleteAction="route('clients.destroy', $client)"
                                                :deleteConfirm="__('Are you sure you want to delete this client?')"
                                            />
                                        </x-ui.table-cell>
                                    </x-ui.table-row>
                                            @endforeach
                                        @endif
                                    @endforeach
                                @else
                                    {{-- Normal table view --}}
                                    @foreach($clients as $client)
                                        <x-ui.table-row>
                                            <x-ui.table-cell>
                                                <div>
                                                    <a href="{{ route('clients.show', $client) }}" class="text-sm font-semibold text-slate-900 hover:text-slate-600 transition-colors">
                                                        {{ $client->name }}
                                                    </a>
                                                    @if($client->company_name)
                                                        <div class="text-sm text-slate-500">{{ $client->company_name }}</div>
                                                    @endif
                                                </div>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>
                                                <div class="text-sm text-slate-900">{{ $client->contact_person ?: '—' }}</div>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>
                                                @if($client->email)
                                                    <div class="text-sm text-slate-900 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-1 group"
                                                         onclick="copyToClipboard('{{ $client->email }}', this)"
                                                         title="Click to copy">
                                                        <span>{{ $client->email }}</span>
                                                        <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                    </div>
                                                @else
                                                    <div class="text-sm text-slate-900">—</div>
                                                @endif
                                                @if($client->phone)
                                                    <div class="text-sm text-slate-500 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-1 group"
                                                         onclick="copyToClipboard('{{ $client->phone }}', this)"
                                                         title="Click to copy">
                                                        <span>{{ $client->phone }}</span>
                                                        <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>
                                                <div class="text-sm text-slate-500">{{ $client->tax_id ?: '—' }}</div>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell>
                                                <div x-data="{
                                                    open: false,
                                                    saving: false,
                                                    statusId: {{ $client->status_id ?? 'null' }},
                                                    currentStatus: {{ $client->status_id ?? 'null' }}
                                                }"
                                                @click.away="open = false"
                                                class="relative group">
                                                    <!-- Display Mode -->
                                                    <div x-show="!saving"
                                                         @click="open = !open"
                                                         class="inline-block"
                                                         title="{{ $client->status ? __('Click to change status') : __('Click to set status') }}">
                                                        <x-client-status-badge :status="$client->status" :editable="true" />
                                                    </div>

                                                    <!-- Saving State -->
                                                    <div x-show="saving" x-cloak class="inline-flex items-center gap-2 px-3 py-1 text-xs text-blue-600">
                                                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        <span>{{ __('Saving...') }}</span>
                                                    </div>

                                                    <!-- ClickUp-Style Dropdown -->
                                                    <div x-show="open"
                                                         x-cloak
                                                         x-transition:enter="transition ease-out duration-100"
                                                         x-transition:enter-start="transform opacity-0 scale-95"
                                                         x-transition:enter-end="transform opacity-100 scale-100"
                                                         x-transition:leave="transition ease-in duration-75"
                                                         x-transition:leave-start="transform opacity-100 scale-100"
                                                         x-transition:leave-end="transform opacity-0 scale-95"
                                                         class="absolute z-50 mt-1 w-56 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                                         style="left: 0;">
                                                        <div class="p-2 max-h-64 overflow-y-auto">
                                                            <div class="px-2 py-1.5 text-xs font-medium text-slate-500 uppercase tracking-wider">
                                                                {{ __('Change Status') }}
                                                            </div>
                                                            @foreach($clientStatuses as $status)
                                                                <button
                                                                    type="button"
                                                                    @click="
                                                                        open = false;
                                                                        saving = true;
                                                                        updateStatusInline('{{ $client->slug }}', {{ $status->id }}, $el, function(success) {
                                                                            saving = false;
                                                                            if (success) {
                                                                                statusId = {{ $status->id }};
                                                                                currentStatus = {{ $status->id }};
                                                                            }
                                                                        });
                                                                    "
                                                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 rounded-md transition-colors {{ $client->status_id == $status->id ? 'bg-slate-50' : '' }}">
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                                          style="background-color: {{ $status->color_background }}; color: {{ $status->color_text }};">
                                                                        {{ $status->name }}
                                                                    </span>
                                                                    @if($client->status_id == $status->id)
                                                                        <svg class="ml-auto w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    @endif
                                                                </button>
                                                            @endforeach
                                                            @if($client->status_id)
                                                                <hr class="my-2 border-slate-200">
                                                                <button
                                                                    type="button"
                                                                    @click="
                                                                        open = false;
                                                                        saving = true;
                                                                        updateStatusInline('{{ $client->slug }}', null, $el, function(success) {
                                                                            saving = false;
                                                                            if (success) {
                                                                                statusId = null;
                                                                                currentStatus = null;
                                                                            }
                                                                        });
                                                                    "
                                                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                    </svg>
                                                                    {{ __('Clear Status') }}
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell class="text-right">
                                                <div class="text-sm font-semibold text-slate-900">
                                                    {{ number_format($client->total_incomes, 2) }} RON
                                                </div>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell class="text-right">
                                                <x-table-actions
                                                    :viewUrl="route('clients.show', $client)"
                                                    :editUrl="route('clients.edit', $client)"
                                                    :deleteAction="route('clients.destroy', $client)"
                                                    :deleteConfirm="__('Are you sure you want to delete this client?')"
                                                />
                                            </x-ui.table-cell>
                                        </x-ui.table-row>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if(!$groupByStatus && $clients->hasPages())
                        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                            {{ $clients->links() }}
                        </div>
                    @endif
                @endif
            </x-ui.card>
        @endif

        <!-- Kanban View -->
        @if($viewMode === 'kanban')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" x-data="kanbanBoard()">
                @foreach($clientStatuses as $status)
                    <x-ui.card>
                        <x-ui.card-header>
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-slate-900">{{ $status->name }}</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $status->color_background }}; color: {{ $status->color_text }};">
                                    {{ $clients->get($status->id, collect())->count() }}
                                </span>
                            </div>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <div class="space-y-2 kanban-column" data-status-id="{{ $status->id }}"
                                 @dragover="dragOver($event)"
                                 @drop="drop($event, {{ $status->id }})">
                                @foreach($clients->get($status->id, collect()) as $client)
                                    <div class="kanban-card cursor-move p-3 bg-slate-50 hover:bg-slate-100 rounded-lg transition border border-transparent hover:border-slate-300"
                                         data-client-id="{{ $client->id }}"
                                         draggable="true"
                                         @dragstart="dragStart($event)"
                                         @dragend="dragEnd($event)">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <div class="font-medium text-slate-900">{{ $client->name }}</div>
                                                @if($client->company_name)
                                                    <div class="text-sm text-slate-500">{{ $client->company_name }}</div>
                                                @endif
                                            </div>
                                            <a href="{{ route('clients.show', $client) }}" class="text-slate-400 hover:text-slate-600" onclick="event.stopPropagation()">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                        </div>

                                        @if($client->email || $client->phone)
                                            <div class="mb-2 space-y-1 text-xs">
                                                @if($client->email)
                                                    <div class="text-slate-600 cursor-pointer hover:text-blue-600 transition-colors flex items-center gap-1"
                                                         onclick="event.stopPropagation(); copyToClipboard('{{ $client->email }}', this)"
                                                         title="Click to copy">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                        </svg>
                                                        <span class="truncate">{{ $client->email }}</span>
                                                    </div>
                                                @endif
                                                @if($client->phone)
                                                    <div class="text-slate-600 cursor-pointer hover:text-blue-600 transition-colors flex items-center gap-1"
                                                         onclick="event.stopPropagation(); copyToClipboard('{{ $client->phone }}', this)"
                                                         title="Click to copy">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                        </svg>
                                                        <span>{{ $client->phone }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="text-sm font-semibold text-slate-900 pt-2 border-t border-slate-200">
                                            {{ number_format($client->total_incomes, 2) }} RON
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                @endforeach
            </div>

            <script>
                function kanbanBoard() {
                    return {
                        draggedElement: null,

                        dragStart(event) {
                            this.draggedElement = event.currentTarget;
                            event.currentTarget.classList.add('opacity-50');
                        },

                        dragEnd(event) {
                            event.currentTarget.classList.remove('opacity-50');
                        },

                        dragOver(event) {
                            event.preventDefault();
                        },

                        drop(event, newStatusId) {
                            event.preventDefault();

                            if (!this.draggedElement) return;

                            const clientId = this.draggedElement.dataset.clientId;
                            const oldColumn = this.draggedElement.closest('.kanban-column');
                            const oldStatusId = oldColumn ? oldColumn.dataset.statusId : null;
                            const targetColumn = event.currentTarget;

                            // Only proceed if status changed
                            if (oldStatusId && oldStatusId !== newStatusId.toString()) {
                                // Update status via AJAX
                                fetch(`/clients/${clientId}/status`, {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({ status_id: newStatusId })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Move the card to the new column
                                        targetColumn.appendChild(this.draggedElement);
                                        this.showNotification('Status actualizat cu succes!');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error updating status:', error);
                                    this.showNotification('Eroare la actualizare', 'error');
                                });
                            }

                            this.draggedElement = null;
                        },

                        showNotification(message, type = 'success') {
                            const notification = document.createElement('div');
                            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
                            notification.textContent = message;
                            document.body.appendChild(notification);

                            setTimeout(() => {
                                notification.remove();
                            }, 3000);
                        }
                    }
                }
            </script>
        @endif

        <!-- Grid View -->
        @if($viewMode === 'grid')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($clients as $client)
                    <x-ui.card class="hover:shadow-lg transition-shadow">
                        <x-ui.card-content>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $client->name }}</h3>
                                <x-client-status-badge :status="$client->status" />
                            </div>
                            @if($client->company_name)
                                <p class="text-sm text-slate-600 mb-2">{{ $client->company_name }}</p>
                            @endif

                            @if($client->email || $client->phone)
                                <div class="mt-2 space-y-1">
                                    @if($client->email)
                                        <div class="text-sm text-slate-700 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-1 group"
                                             onclick="copyToClipboard('{{ $client->email }}', this)"
                                             title="Click to copy email">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <span>{{ $client->email }}</span>
                                        </div>
                                    @endif
                                    @if($client->phone)
                                        <div class="text-sm text-slate-700 cursor-pointer hover:text-blue-600 transition-colors inline-flex items-center gap-1 group"
                                             onclick="copyToClipboard('{{ $client->phone }}', this)"
                                             title="Click to copy phone">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            <span>{{ $client->phone }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-3 pt-3 border-t border-slate-200">
                                <div class="text-xs text-slate-500 uppercase mb-1">{{ __('Total Incomes') }}</div>
                                <div class="text-lg font-semibold text-slate-900">
                                    {{ number_format($client->total_incomes, 2) }} RON
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-4 border-t border-slate-200 mt-4">
                                <a href="{{ route('clients.show', $client) }}" class="text-sm text-slate-900 hover:text-slate-600 font-medium transition-colors">
                                    View Details →
                                </a>
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                @empty
                    <div class="col-span-3 text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">No clients</h3>
                        <p class="mt-1 text-sm text-slate-500">Get started by creating your first client.</p>
                        <div class="mt-6">
                            <x-ui.button variant="default" onclick="window.location.href='{{ route('clients.create') }}'">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                New Client
                            </x-ui.button>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($clients->hasPages())
                <div class="mt-6">
                    {{ $clients->links() }}
                </div>
            @endif
        @endif
    </div>

    <script>
        function copyToClipboard(text, element) {
            navigator.clipboard.writeText(text).then(function() {
                // Create checkmark feedback
                const originalHTML = element.innerHTML;
                element.innerHTML = '<span class="flex items-center gap-1"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!</span>';

                // Reset after 2 seconds
                setTimeout(function() {
                    element.innerHTML = originalHTML;
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy text: ', err);
            });
        }

        /**
         * Update client status with inline feedback (no page reload)
         */
        function updateStatusInline(clientSlug, statusId, selectElement, callback) {
            fetch(`/clients/${clientSlug}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status_id: statusId || null })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success feedback
                    const row = selectElement.closest('tr') || selectElement.closest('[x-data]');
                    if (row) {
                        row.classList.add('bg-green-50');
                        setTimeout(() => {
                            row.classList.remove('bg-green-50');
                        }, 1500);
                    }

                    // Reload page to update badge colors and counts
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);

                    callback(true);
                } else {
                    throw new Error(data.message || 'Failed to update status');
                }
            })
            .catch(error => {
                console.error('Error updating status:', error);

                // Show error feedback
                const errorDiv = document.createElement('div');
                errorDiv.className = 'fixed top-4 right-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg shadow-lg z-50';
                errorDiv.innerHTML = `
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>${error.message || '{{ __("Error updating status. Please try again.") }}'}</span>
                    </div>
                `;
                document.body.appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 3000);

                callback(false);
            });
        }

        /**
         * Legacy function for compatibility (redirects to new inline function)
         * @param {string} clientSlug - The client's slug (not ID)
         */
        function updateStatus(clientSlug, statusId) {
            updateStatusInline(clientSlug, statusId, document.body, function(success) {
                if (success) {
                    window.location.reload();
                }
            });
        }
    </script>

</x-app-layout>
