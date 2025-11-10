<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ __('Clients') }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">Manage your client relationships and track business information</p>
            </div>
            <div class="flex items-center gap-3">
                <x-ui.button variant="outline" onclick="window.location.href='{{ route('clients.import.form') }}'">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Import
                </x-ui.button>
                <x-ui.button variant="outline" onclick="window.location.href='{{ route('clients.export', request()->query()) }}'">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export
                </x-ui.button>
                <x-ui.button variant="default" onclick="window.location.href='{{ route('clients.create') }}'">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Client
                </x-ui.button>
            </div>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8 space-y-6">
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
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <!-- Search -->
                            <div class="flex-1">
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
                                        placeholder="Search by name, company, tax ID, email..."
                                        class="pl-10"
                                    />
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div class="w-full sm:w-48">
                                <x-ui.select name="status_id">
                                    <option value="">All Statuses</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </x-ui.select>
                            </div>

                            <!-- Buttons -->
                            <div class="flex gap-2">
                                <x-ui.button type="submit" variant="default">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                    </svg>
                                    Search
                                </x-ui.button>
                                @if(request('search') || request('status_id'))
                                    <x-ui.button variant="outline" onclick="window.location.href='{{ route('clients.index') }}'">
                                        Clear
                                    </x-ui.button>
                                @endif
                            </div>
                        </div>

                        <!-- View Mode Switcher -->
                        <div class="flex justify-end">
                            <div class="inline-flex gap-1 border border-slate-300 rounded-md p-1">
                                <a href="{{ route('clients.index', array_merge(request()->all(), ['view' => 'table'])) }}"
                                    class="px-3 py-1 text-sm rounded {{ $viewMode === 'table' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                                    Table
                                </a>
                                <a href="{{ route('clients.index', array_merge(request()->all(), ['view' => 'kanban'])) }}"
                                    class="px-3 py-1 text-sm rounded {{ $viewMode === 'kanban' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                                    Kanban
                                </a>
                                <a href="{{ route('clients.index', array_merge(request()->all(), ['view' => 'grid'])) }}"
                                    class="px-3 py-1 text-sm rounded {{ $viewMode === 'grid' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                                    Grid
                                </a>
                            </div>
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
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full caption-bottom text-sm">
                            <thead class="[&_tr]:border-b">
                                <tr class="border-b transition-colors hover:bg-slate-50/50">
                                    <x-ui.table-head>Client</x-ui.table-head>
                                    <x-ui.table-head>Contact</x-ui.table-head>
                                    <x-ui.table-head>Tax ID</x-ui.table-head>
                                    <x-ui.table-head>Status</x-ui.table-head>
                                    <x-ui.table-head class="text-right">Actions</x-ui.table-head>
                                </tr>
                            </thead>
                            <tbody class="[&_tr:last-child]:border-0">
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
                                            <div class="text-sm text-slate-900">{{ $client->email }}</div>
                                            <div class="text-sm text-slate-500">{{ $client->phone }}</div>
                                        </x-ui.table-cell>
                                        <x-ui.table-cell>
                                            <div class="text-sm text-slate-500">{{ $client->tax_id ?: '—' }}</div>
                                        </x-ui.table-cell>
                                        <x-ui.table-cell>
                                            <x-client-status-badge :status="$client->status" />
                                        </x-ui.table-cell>
                                        <x-ui.table-cell class="text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <x-ui.button
                                                    variant="secondary"
                                                    size="sm"
                                                    onclick="window.location.href='{{ route('clients.show', $client) }}'"
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
                                                    onclick="window.location.href='{{ route('clients.edit', $client) }}'"
                                                >
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    Edit
                                                </x-ui.button>
                                                <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this client?');">
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

                    @if($clients->hasPages())
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
                @foreach($statuses as $status)
                    <x-ui.card>
                        <x-ui.card-header>
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-slate-900">{{ $status->name }}</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $status->color_background }}; color: {{ $status->color_text }};">
                                    {{ $clients->get($status->id)->count() ?? 0 }}
                                </span>
                            </div>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <div class="space-y-2 kanban-column" data-status-id="{{ $status->id }}">
                                @foreach($clients->get($status->id, collect()) as $client)
                                    <div class="kanban-card cursor-move p-3 bg-slate-50 hover:bg-slate-100 rounded-lg transition border border-transparent hover:border-slate-300"
                                         data-client-id="{{ $client->id }}"
                                         draggable="true"
                                         @dragstart="dragStart($event)"
                                         @dragend="dragEnd($event)"
                                         @dragover.prevent
                                         @drop="drop($event, {{ $status->id }})">
                                        <div class="flex items-start justify-between">
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
                            this.draggedElement = event.target;
                            event.target.classList.add('opacity-50');
                        },

                        dragEnd(event) {
                            event.target.classList.remove('opacity-50');
                        },

                        drop(event, newStatusId) {
                            event.preventDefault();

                            if (!this.draggedElement) return;

                            const clientId = this.draggedElement.dataset.clientId;
                            const oldStatusId = this.draggedElement.closest('.kanban-column').dataset.statusId;

                            if (oldStatusId !== newStatusId.toString()) {
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
                                        // Move the card visually
                                        const targetColumn = event.target.closest('.kanban-column');
                                        if (targetColumn) {
                                            targetColumn.appendChild(this.draggedElement);
                                        }

                                        // Show success notification
                                        this.showNotification('Client status updated successfully!');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error updating status:', error);
                                    this.showNotification('Error updating status', 'error');
                                });
                            }

                            this.draggedElement = null;
                        },

                        showNotification(message, type = 'success') {
                            // Simple notification - you can enhance this with a toast library
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
                            <div class="flex items-center justify-between pt-4 border-t border-slate-200">
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
</x-app-layout>
