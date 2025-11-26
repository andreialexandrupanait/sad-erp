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

    {{-- Pass statuses to Alpine.js for cycling --}}
    <div class="p-6 space-y-6"
         x-data="clientsTable()"
         x-init="init()"
         @status-updated.window="handleStatusUpdate($event.detail)">

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
                    <input type="hidden" name="status_id" value="{{ request('status_id') }}">
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

                        <!-- Group by Status Toggle (only for table view) -->
                        @if($viewMode === 'table')
                            <a href="{{ route('clients.index', array_merge(request()->except('group'), ['group' => request('group') ? '' : '1'])) }}"
                               onclick="toggleGroupPreference({{ request('group') ? 'false' : 'true' }})"
                               class="inline-flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request('group') ? 'bg-blue-100 text-blue-700 border border-blue-300' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                               title="{{ __('Group by Status') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                {{ __('Group') }}
                            </a>
                        @endif

                        <!-- View Mode Switcher -->
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
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

        <!-- Quick Status Filter Pills -->
        @if($viewMode === 'table')
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('clients.index', array_merge(request()->except('status_id'), [])) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ !request('status_id') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    {{ __('All') }}
                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-xs rounded-full {{ !request('status_id') ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }}"
                          x-text="totalCount">
                        {{ array_sum($statusCounts ?? []) }}
                    </span>
                </a>
                @foreach($clientStatuses as $status)
                    @php $count = $statusCounts[$status->id] ?? 0; @endphp
                    <a href="{{ route('clients.index', array_merge(request()->except('status_id'), ['status_id' => $status->id])) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition-colors {{ request('status_id') == $status->id ? '' : 'hover:opacity-80' }}"
                       style="{{ request('status_id') == $status->id ? 'background-color: ' . $status->color_background . '; color: ' . $status->color_text . ';' : 'background-color: ' . $status->color_background . '40; color: ' . $status->color_text . ';' }}">
                        {{ $status->name }}
                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-xs rounded-full"
                              style="background-color: {{ $status->color_text }}20;"
                              x-text="statusCounts[{{ $status->id }}] ?? 0">
                            {{ $count }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Table View -->
        @if($viewMode === 'table')
            @if($clients->isEmpty())
                <x-ui.card>
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
                </x-ui.card>
            @elseif(request('group'))
                {{-- GROUPED VIEW: Clients grouped by Status --}}
                @foreach($clientStatuses as $status)
                    @php
                        $statusClients = $clients->filter(function($c) use ($status) { return $c->status_id == $status->id; });
                    @endphp
                    @if($statusClients->count() > 0)
                        <div class="status-group mb-6" data-status-id="{{ $status->id }}"
                             x-data="{ collapsed: localStorage.getItem('client_group_{{ $status->id }}') === 'true' }"
                             x-init="$watch('collapsed', val => localStorage.setItem('client_group_{{ $status->id }}', val))">
                            {{-- Status Header with collapse toggle --}}
                            <div class="flex items-center gap-3 mb-3 cursor-pointer select-none" @click="collapsed = !collapsed">
                                <button type="button" class="p-1 hover:bg-slate-100 rounded transition-colors">
                                    <svg class="w-4 h-4 text-slate-500 transition-transform duration-200"
                                         :class="{ '-rotate-90': collapsed }"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                      style="background-color: {{ $status->color_background }}; color: {{ $status->color_text }};">
                                    {{ $status->name }}
                                </span>
                                <span class="text-sm text-slate-500">
                                    {{ $statusClients->count() }} {{ __('clients') }}
                                </span>
                            </div>

                            <x-ui.card x-show="!collapsed" x-collapse>
                                <div class="overflow-x-auto">
                                    <table class="w-full caption-bottom text-sm">
                                        <thead class="[&_tr]:border-b">
                                            <tr class="border-b transition-colors hover:bg-slate-50/50">
                                                <x-ui.sortable-header column="name" :label="__('Client')" />
                                                <th class="h-10 px-4 text-left align-middle font-medium text-slate-500">{{ __('Contact') }}</th>
                                                <x-ui.sortable-header column="status_id" :label="__('Status')" />
                                                <x-ui.sortable-header column="total_incomes" :label="__('Revenue')" class="text-right" />
                                                <th class="h-10 px-4 text-right align-middle font-medium text-slate-500 w-24">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="[&_tr:last-child]:border-0" id="status-group-{{ $status->id }}">
                                            @foreach($statusClients as $client)
                                                @include('clients._table_row', ['client' => $client])
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </x-ui.card>
                        </div>
                    @endif
                @endforeach

                {{-- Clients without status (in grouped view) --}}
                @php
                    $noStatusClients = $clients->filter(function($c) { return !$c->status_id; });
                @endphp
                @if($noStatusClients->count() > 0)
                    <div class="status-group mb-6" data-status-id="null"
                         x-data="{ collapsed: localStorage.getItem('client_group_null') === 'true' }"
                         x-init="$watch('collapsed', val => localStorage.setItem('client_group_null', val))">
                        {{-- Status Header with collapse toggle --}}
                        <div class="flex items-center gap-3 mb-3 cursor-pointer select-none" @click="collapsed = !collapsed">
                            <button type="button" class="p-1 hover:bg-slate-100 rounded transition-colors">
                                <svg class="w-4 h-4 text-slate-500 transition-transform duration-200"
                                     :class="{ '-rotate-90': collapsed }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-200 text-slate-600">
                                {{ __('No Status') }}
                            </span>
                            <span class="text-sm text-slate-500">{{ $noStatusClients->count() }} {{ __('clients') }}</span>
                        </div>

                        <x-ui.card x-show="!collapsed" x-collapse>
                            <div class="overflow-x-auto">
                                <table class="w-full caption-bottom text-sm">
                                    <thead class="[&_tr]:border-b">
                                        <tr class="border-b transition-colors hover:bg-slate-50/50">
                                            <x-ui.sortable-header column="name" :label="__('Client')" />
                                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500">{{ __('Contact') }}</th>
                                            <x-ui.sortable-header column="status_id" :label="__('Status')" />
                                            <x-ui.sortable-header column="total_incomes" :label="__('Revenue')" class="text-right" />
                                            <th class="h-10 px-4 text-right align-middle font-medium text-slate-500 w-24">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="[&_tr:last-child]:border-0" id="status-group-null">
                                        @foreach($noStatusClients as $client)
                                            @include('clients._table_row', ['client' => $client])
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </x-ui.card>
                    </div>
                @endif

                @if($clients->hasPages())
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 rounded-b-lg">
                        {{ $clients->links() }}
                    </div>
                @endif
            @else
                {{-- FLAT VIEW: Single table with all clients --}}
                <x-ui.card>
                    <div class="overflow-x-auto">
                        <table class="w-full caption-bottom text-sm">
                            <thead class="[&_tr]:border-b">
                                <tr class="border-b transition-colors hover:bg-slate-50/50">
                                    <x-ui.sortable-header column="name" :label="__('Client')" />
                                    <th class="h-10 px-4 text-left align-middle font-medium text-slate-500">{{ __('Contact') }}</th>
                                    <x-ui.sortable-header column="status_id" :label="__('Status')" />
                                    <x-ui.sortable-header column="total_incomes" :label="__('Revenue')" class="text-right" />
                                    <th class="h-10 px-4 text-right align-middle font-medium text-slate-500 w-24">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="[&_tr:last-child]:border-0">
                                @foreach($clients as $client)
                                    @include('clients._table_row', ['client' => $client])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($clients->hasPages())
                        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                            {{ $clients->links() }}
                        </div>
                    @endif
                </x-ui.card>
            @endif
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
                    </div>
                @endforelse
            </div>

            @if($clients->hasPages())
                <div class="mt-6">
                    {{ $clients->links() }}
                </div>
            @endif
        @endif

        <!-- Toast Notification Container -->
        <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
    </div>

    @php
        $statusesJson = $clientStatuses->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'color_background' => $s->color_background,
                'color_text' => $s->color_text
            ];
        })->values()->toArray();
    @endphp

    <script>
        // Status definitions from server
        const STATUSES = @json($statusesJson);

        function clientsTable() {
            return {
                saving: {},
                statusCounts: @json($statusCounts ?? []),
                totalCount: {{ array_sum($statusCounts ?? []) }},
                clients: {},

                init() {
                    // Build initial client->status mapping
                    document.querySelectorAll('.client-row').forEach(row => {
                        const slug = row.dataset.clientSlug;
                        const statusId = row.dataset.statusId === 'null' ? null : parseInt(row.dataset.statusId);
                        this.clients[slug] = statusId;
                    });
                },

                getNextStatus(currentStatusId) {
                    if (currentStatusId === null) {
                        return STATUSES[0];
                    }
                    const currentIndex = STATUSES.findIndex(s => s.id === currentStatusId);
                    const nextIndex = (currentIndex + 1) % STATUSES.length;
                    return STATUSES[nextIndex];
                },

                getStatusById(statusId) {
                    if (statusId === null) return null;
                    return STATUSES.find(s => s.id === statusId) || null;
                },

                getClientsForStatus(statusId) {
                    return Object.entries(this.clients)
                        .filter(([slug, sid]) => sid === statusId)
                        .map(([slug]) => slug);
                },

                async cycleStatus(clientSlug, currentStatusId) {
                    if (this.saving[clientSlug]) return;

                    const nextStatus = this.getNextStatus(currentStatusId);
                    const newStatusId = nextStatus.id;

                    this.saving[clientSlug] = true;

                    try {
                        const response = await fetch(`/clients/${clientSlug}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ status_id: newStatusId })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Update local state
                            const oldStatusId = this.clients[clientSlug];
                            this.clients[clientSlug] = newStatusId;

                            // Update status counts
                            if (oldStatusId !== null && this.statusCounts[oldStatusId]) {
                                this.statusCounts[oldStatusId]--;
                            }
                            if (!this.statusCounts[newStatusId]) {
                                this.statusCounts[newStatusId] = 0;
                            }
                            this.statusCounts[newStatusId]++;

                            // Update UI - move row to new group
                            this.moveClientToGroup(clientSlug, oldStatusId, newStatusId, nextStatus);

                            // Show success toast
                            this.showToast(`Status changed to "${nextStatus.name}"`, 'success');
                        } else {
                            throw new Error(data.message || 'Failed to update status');
                        }
                    } catch (error) {
                        console.error('Error updating status:', error);
                        this.showToast('Error updating status', 'error');
                    } finally {
                        this.saving[clientSlug] = false;
                    }
                },

                moveClientToGroup(clientSlug, oldStatusId, newStatusId, newStatus) {
                    const row = document.querySelector(`tr[data-client-slug="${clientSlug}"]`);
                    if (!row) return;

                    // Update the status badge
                    const badge = row.querySelector('.status-indicator');
                    if (badge) {
                        badge.style.backgroundColor = newStatus.color_background;
                        badge.style.color = newStatus.color_text;
                        badge.innerHTML = `
                            <span x-show="saving['${clientSlug}']" class="mr-1">
                                <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </span>
                            ${newStatus.name}
                        `;
                    }

                    // Update row data attribute
                    row.dataset.statusId = newStatusId;

                    // Find target group tbody
                    const targetTbody = document.getElementById(`status-group-${newStatusId}`);
                    if (targetTbody && targetTbody !== row.parentElement) {
                        // Animate the row
                        row.style.transition = 'all 0.3s ease';
                        row.style.backgroundColor = '#ecfdf5'; // green highlight

                        setTimeout(() => {
                            targetTbody.appendChild(row);
                            row.style.backgroundColor = '';
                        }, 300);
                    }

                    // Update button click handler
                    const button = row.querySelector('.status-badge');
                    if (button) {
                        button.setAttribute('@click', `cycleStatus('${clientSlug}', ${newStatusId})`);
                    }
                },

                handleStatusUpdate(detail) {
                    // Handle external status updates if needed
                },

                showToast(message, type = 'success') {
                    const container = document.getElementById('toast-container');
                    const toast = document.createElement('div');
                    toast.className = `px-4 py-3 rounded-lg shadow-lg text-white transform transition-all duration-300 ${
                        type === 'success' ? 'bg-green-500' : 'bg-red-500'
                    }`;
                    toast.innerHTML = `
                        <div class="flex items-center gap-2">
                            ${type === 'success'
                                ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                                : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
                            }
                            <span>${message}</span>
                        </div>
                    `;
                    container.appendChild(toast);

                    setTimeout(() => {
                        toast.style.opacity = '0';
                        setTimeout(() => toast.remove(), 300);
                    }, 2500);
                }
            };
        }

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

                    if (oldStatusId && oldStatusId !== newStatusId.toString()) {
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
                                targetColumn.appendChild(this.draggedElement);
                            }
                        });
                    }
                    this.draggedElement = null;
                }
            };
        }

        function copyToClipboard(text, element) {
            navigator.clipboard.writeText(text).then(function() {
                const originalHTML = element.innerHTML;
                element.innerHTML = '<span class="flex items-center gap-1"><svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!</span>';
                setTimeout(function() {
                    element.innerHTML = originalHTML;
                }, 2000);
            });
        }

        // Status dropdown component for each row
        function statusDropdown(statusId, bg, text, name, slug) {
            return {
                open: false,
                saving: false,
                currentStatusId: statusId,
                currentBg: bg,
                currentText: text,
                currentName: name,
                clientSlug: slug,

                async updateStatus(newStatusId, newStatusName, newStatusBg, newStatusText) {
                    if (this.saving || newStatusId === this.currentStatusId) {
                        this.open = false;
                        return;
                    }

                    const oldStatusId = this.currentStatusId;
                    this.saving = true;
                    this.open = false;

                    try {
                        const response = await fetch(`/clients/${this.clientSlug}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ status_id: newStatusId })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.currentStatusId = newStatusId;
                            this.currentName = newStatusName;
                            this.currentBg = newStatusBg;
                            this.currentText = newStatusText;

                            // Move row to new group if in grouped view
                            this.moveRowToGroup(oldStatusId, newStatusId);

                            showToastMessage(`Status changed to "${newStatusName}"`, 'success');
                        } else {
                            throw new Error(data.message || 'Failed to update status');
                        }
                    } catch (error) {
                        console.error('Error updating status:', error);
                        showToastMessage('Error updating status', 'error');
                    } finally {
                        this.saving = false;
                    }
                },

                moveRowToGroup(oldStatusId, newStatusId) {
                    // Check if we're in grouped view
                    const isGroupedView = window.location.search.includes('group=1');
                    if (!isGroupedView) return;

                    // Find the row by client slug
                    const row = document.querySelector(`tr[data-client-slug="${this.clientSlug}"]`);
                    if (!row) return;

                    // Update row's data attribute
                    row.dataset.statusId = newStatusId;

                    // Find target group tbody (for grouped view)
                    const targetTbody = document.getElementById(`status-group-${newStatusId}`);

                    if (targetTbody && targetTbody !== row.parentElement) {
                        // Target group exists, animate the move
                        row.style.transition = 'all 0.3s ease';
                        row.style.backgroundColor = '#ecfdf5'; // Light green highlight

                        setTimeout(() => {
                            targetTbody.appendChild(row);
                            setTimeout(() => {
                                row.style.backgroundColor = '';
                            }, 300);
                        }, 100);

                        // Update group counts in headers
                        this.updateGroupCounts(oldStatusId, newStatusId);
                    } else if (!targetTbody) {
                        // Target group doesn't exist (was empty), reload to show it
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }
                },

                updateGroupCounts(oldStatusId, newStatusId) {
                    // Update old group count
                    const oldGroup = document.querySelector(`.status-group[data-status-id="${oldStatusId}"]`);
                    if (oldGroup) {
                        const oldTbody = document.getElementById(`status-group-${oldStatusId}`);
                        const oldCount = oldTbody ? oldTbody.querySelectorAll('tr.client-row').length : 0;
                        const oldCountSpan = oldGroup.querySelector('.text-slate-500');
                        if (oldCountSpan) {
                            oldCountSpan.textContent = `${oldCount} {{ __('clients') }}`;
                        }
                        // Hide group if empty
                        if (oldCount === 0) {
                            oldGroup.style.display = 'none';
                        }
                    }

                    // Update new group count
                    const newGroup = document.querySelector(`.status-group[data-status-id="${newStatusId}"]`);
                    if (newGroup) {
                        const newTbody = document.getElementById(`status-group-${newStatusId}`);
                        const newCount = newTbody ? newTbody.querySelectorAll('tr.client-row').length : 0;
                        const newCountSpan = newGroup.querySelector('.text-slate-500');
                        if (newCountSpan) {
                            newCountSpan.textContent = `${newCount} {{ __('clients') }}`;
                        }
                        // Show group if it was hidden
                        newGroup.style.display = '';
                    }
                }
            };
        }

        function showToastMessage(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `px-4 py-3 rounded-lg shadow-lg text-white transform transition-all duration-300 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            toast.innerHTML = `
                <div class="flex items-center gap-2">
                    ${type === 'success'
                        ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                        : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
                    }
                    <span>${message}</span>
                </div>
            `;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        }

        // Save group preference to localStorage
        function toggleGroupPreference(enabled) {
            localStorage.setItem('clients_group_by_status', enabled ? '1' : '0');
        }

        // Save sort preference when sorting changes
        function saveSortPreference(column, direction) {
            localStorage.setItem('clients_sort_column', column);
            localStorage.setItem('clients_sort_direction', direction);
        }

        // On page load, check if we should redirect based on saved preferences
        // Default is grouped view (group=1) unless user explicitly disabled it
        (function() {
            const viewMode = '{{ $viewMode }}';
            const urlParams = new URLSearchParams(window.location.search);
            const hasGroupParam = urlParams.has('group');
            const hasSortParam = urlParams.has('sort');
            const savedGroupPreference = localStorage.getItem('clients_group_by_status');
            const savedSortColumn = localStorage.getItem('clients_sort_column');
            const savedSortDirection = localStorage.getItem('clients_sort_direction');

            let needsRedirect = false;
            const url = new URL(window.location.href);

            // Only apply preferences if we're in table view
            if (viewMode === 'table') {
                // Apply group preference if not explicitly set in URL
                if (!hasGroupParam && savedGroupPreference !== '0') {
                    url.searchParams.set('group', '1');
                    needsRedirect = true;
                }

                // Apply sort preference if not explicitly set in URL
                if (!hasSortParam && savedSortColumn) {
                    url.searchParams.set('sort', savedSortColumn);
                    url.searchParams.set('dir', savedSortDirection || 'asc');
                    needsRedirect = true;
                }

                if (needsRedirect) {
                    window.location.replace(url.toString());
                }
            }

            // Save current sort to localStorage when page loads with sort params
            if (hasSortParam) {
                const currentSort = urlParams.get('sort');
                const currentDir = urlParams.get('dir') || 'asc';
                saveSortPreference(currentSort, currentDir);
            }
        })();
    </script>
</x-app-layout>
