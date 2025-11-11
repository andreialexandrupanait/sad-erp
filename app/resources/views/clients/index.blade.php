<x-app-layout>
    <x-slot name="pageTitle">Clienți</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" @click="$dispatch('open-slide-panel', 'client-create')">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Client nou
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
                                    @foreach($clientStatuses as $status)
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
                            <x-ui.button variant="default" @click="$dispatch('open-slide-panel', 'client-create')">
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
                                    <x-ui.table-head>Contact Person</x-ui.table-head>
                                    <x-ui.table-head>Contact</x-ui.table-head>
                                    <x-ui.table-head>Tax ID</x-ui.table-head>
                                    <x-ui.table-head>Status</x-ui.table-head>
                                    <x-ui.table-head class="text-right">Total Incomes</x-ui.table-head>
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
                                            <div class="text-sm text-slate-900">{{ $client->contact_person ?: '—' }}</div>
                                        </x-ui.table-cell>
                                        <x-ui.table-cell>
                                            <div class="text-sm text-slate-900">{{ $client->email ?: '—' }}</div>
                                            @if($client->phone)
                                                <div class="text-sm text-slate-500">{{ $client->phone }}</div>
                                            @endif
                                        </x-ui.table-cell>
                                        <x-ui.table-cell>
                                            <div class="text-sm text-slate-500">{{ $client->tax_id ?: '—' }}</div>
                                        </x-ui.table-cell>
                                        <x-ui.table-cell>
                                            <x-client-status-badge :status="$client->status" />
                                        </x-ui.table-cell>
                                        <x-ui.table-cell class="text-right">
                                            <div class="text-sm font-semibold text-slate-900">
                                                {{ number_format($client->total_incomes, 2) }} RON
                                            </div>
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
                                                    @click="$dispatch('open-slide-panel', 'client-edit-{{ $client->id }}')"
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
                @foreach($clientStatuses as $status)
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
                            <x-ui.button variant="default" @click="$dispatch('open-slide-panel', 'client-create')">
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

    <!-- Create Client Slide Panel -->
    <x-slide-panel name="client-create" :show="false" maxWidth="2xl">
        <!-- Header -->
        <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
            <h2 class="text-2xl font-bold text-slate-900">New Client</h2>
            <button type="button" @click="$dispatch('close-slide-panel', 'client-create')" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto px-8 py-6">

            <form id="client-create-form" x-data="{
                loading: false,
                async submit(event) {
                    event.preventDefault();
                    this.loading = true;

                    const formData = new FormData(event.target);

                    try {
                        const response = await fetch('{{ route('clients.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (response.ok) {
                            $dispatch('close-slide-panel', 'client-create');
                            $dispatch('toast', { message: 'Client created successfully!', type: 'success' });
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            // Handle validation errors
                            if (data.errors) {
                                Object.keys(data.errors).forEach(key => {
                                    const errorElement = document.querySelector(`#client-create-form [name='${key}']`).closest('.field-wrapper');
                                    if (errorElement) {
                                        const existingError = errorElement.querySelector('.error-message');
                                        if (existingError) existingError.remove();

                                        const errorDiv = document.createElement('p');
                                        errorDiv.className = 'error-message mt-2 text-sm text-red-600';
                                        errorDiv.textContent = data.errors[key][0];
                                        errorElement.appendChild(errorDiv);
                                    }
                                });
                            }
                        }
                    } catch (error) {
                        $dispatch('toast', { message: 'An error occurred. Please try again.', type: 'error' });
                    } finally {
                        this.loading = false;
                    }
                }
            }" @submit="submit">
                @csrf

                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                        <!-- Name (Required) -->
                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="name">Name <span class="text-red-500">*</span></x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="text" name="name" id="name" required />
                            </div>
                        </div>

                        <!-- Company Name -->
                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="company_name">Company Name</x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="text" name="company_name" id="company_name" />
                            </div>
                        </div>

                        <!-- Tax ID (CUI) with Auto-fill -->
                        <div class="sm:col-span-3 field-wrapper" x-data="{ cuiLoading: false }">
                            <x-ui.label for="tax_id">Tax ID (CUI)</x-ui.label>
                            <div class="mt-2 relative">
                                <x-ui.input
                                    type="text"
                                    name="tax_id"
                                    id="tax_id"
                                    placeholder="e.g., RO12345678"
                                    @blur="if ($event.target.value && !document.getElementById('company_name').value) {
                                        cuiLoading = true;
                                        fetch(`https://api.openapi.ro/api/companies/${$event.target.value.replace('RO', '')}`)
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.found && data.cif) {
                                                    if (!document.getElementById('company_name').value) {
                                                        document.getElementById('company_name').value = data.denumire || '';
                                                    }
                                                    document.getElementById('registration_number').value = data.numar_reg_com || '';
                                                    document.getElementById('address').value = data.adresa || '';
                                                    document.getElementById('vat_payer').checked = data.tva === 'DA';
                                                }
                                            })
                                            .catch(err => console.error('Error fetching company data:', err))
                                            .finally(() => cuiLoading = false);
                                    }"
                                />
                                <div x-show="cuiLoading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <svg class="animate-spin h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Company details will be auto-filled from ANAF if available</p>
                        </div>

                        <!-- Registration Number -->
                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="registration_number">Registration Number</x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="text" name="registration_number" id="registration_number" placeholder="e.g., J40/1234/2020" />
                            </div>
                        </div>

                        <!-- Contact Person -->
                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="contact_person">Contact Person</x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="text" name="contact_person" id="contact_person" />
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="status_id">Status</x-ui.label>
                            <div class="mt-2">
                                <x-ui.select name="status_id" id="status_id">
                                    <option value="">Select Status</option>
                                    @foreach($clientStatuses as $status)
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="email">Email</x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="email" name="email" id="email" />
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="phone">Phone</x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="text" name="phone" id="phone" />
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="sm:col-span-6 field-wrapper">
                            <x-ui.label for="address">Address</x-ui.label>
                            <div class="mt-2">
                                <x-ui.textarea name="address" id="address" rows="3"></x-ui.textarea>
                            </div>
                        </div>

                        <!-- VAT Payer -->
                        <div class="sm:col-span-6 field-wrapper">
                            <div class="flex items-start">
                                <div class="flex h-6 items-center">
                                    <input type="checkbox" name="vat_payer" id="vat_payer" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="vat_payer" class="font-medium text-slate-900">VAT Payer</label>
                                    <p class="text-slate-500">Check if this client is registered as a VAT payer</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="sm:col-span-6 field-wrapper">
                            <x-ui.label for="notes">Notes</x-ui.label>
                            <div class="mt-2">
                                <x-ui.textarea name="notes" id="notes" rows="4"></x-ui.textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
            <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel', 'client-create')">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="default" form="client-create-form">
                Create Client
            </x-ui.button>
        </div>
    </x-slide-panel>

    <!-- Edit Client Slide Panels (one for each client) -->
    @foreach($viewMode === 'table' && !$clients->isEmpty() ? $clients : [] as $client)
        <x-slide-panel name="client-edit-{{ $client->id }}" :show="false" maxWidth="2xl">
            <!-- Header -->
            <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
                <h2 class="text-2xl font-bold text-slate-900">Edit Client</h2>
                <button type="button" @click="$dispatch('close-slide-panel', 'client-edit-{{ $client->id }}')" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto px-8 py-6">

                <form id="client-edit-form-{{ $client->id }}" x-data="{
                    loading: false,
                    async submit(event) {
                        event.preventDefault();
                        this.loading = true;

                        const formData = new FormData(event.target);

                        try {
                            const response = await fetch('{{ route('clients.update', $client) }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: formData
                            });

                            const data = await response.json();

                            if (response.ok) {
                                $dispatch('close-slide-panel', 'client-edit-{{ $client->id }}');
                                $dispatch('toast', { message: 'Client updated successfully!', type: 'success' });
                                setTimeout(() => window.location.reload(), 500);
                            } else {
                                // Handle validation errors
                                if (data.errors) {
                                    Object.keys(data.errors).forEach(key => {
                                        const formId = 'client-edit-form-{{ $client->id }}';
                                        const errorElement = document.querySelector(`#${formId} [name='${key}']`).closest('.field-wrapper');
                                        if (errorElement) {
                                            const existingError = errorElement.querySelector('.error-message');
                                            if (existingError) existingError.remove();

                                            const errorDiv = document.createElement('p');
                                            errorDiv.className = 'error-message mt-2 text-sm text-red-600';
                                            errorDiv.textContent = data.errors[key][0];
                                            errorElement.appendChild(errorDiv);
                                        }
                                    });
                                }
                            }
                        } catch (error) {
                            $dispatch('toast', { message: 'An error occurred. Please try again.', type: 'error' });
                        } finally {
                            this.loading = false;
                        }
                    }
                }" @submit="submit">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                            <!-- Name (Required) -->
                            <div class="sm:col-span-3 field-wrapper">
                                <x-ui.label for="name-{{ $client->id }}">Name <span class="text-red-500">*</span></x-ui.label>
                                <div class="mt-2">
                                    <x-ui.input type="text" name="name" id="name-{{ $client->id }}" value="{{ $client->name }}" required />
                                </div>
                            </div>

                            <!-- Company Name -->
                            <div class="sm:col-span-3 field-wrapper">
                                <x-ui.label for="company_name-{{ $client->id }}">Company Name</x-ui.label>
                                <div class="mt-2">
                                    <x-ui.input type="text" name="company_name" id="company_name-{{ $client->id }}" value="{{ $client->company_name }}" />
                                </div>
                            </div>

                            <!-- Tax ID -->
                            <div class="sm:col-span-3 field-wrapper">
                                <x-ui.label for="tax_id-{{ $client->id }}">Tax ID (CUI)</x-ui.label>
                                <div class="mt-2">
                                    <x-ui.input type="text" name="tax_id" id="tax_id-{{ $client->id }}" value="{{ $client->tax_id }}" placeholder="e.g., RO12345678" />
                                </div>
                            </div>

                            <!-- Registration Number -->
                            <div class="sm:col-span-3 field-wrapper">
                                <x-ui.label for="registration_number-{{ $client->id }}">Registration Number</x-ui.label>
                                <div class="mt-2">
                                    <x-ui.input type="text" name="registration_number" id="registration_number-{{ $client->id }}" value="{{ $client->registration_number }}" placeholder="e.g., J40/1234/2020" />
                                </div>
                            </div>

                            <!-- Contact Person -->
                            <div class="sm:col-span-3 field-wrapper">
                                <x-ui.label for="contact_person-{{ $client->id }}">Contact Person</x-ui.label>
                                <div class="mt-2">
                                    <x-ui.input type="text" name="contact_person" id="contact_person-{{ $client->id }}" value="{{ $client->contact_person }}" />
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="sm:col-span-3 field-wrapper">
                                <x-ui.label for="status_id-{{ $client->id }}">Status</x-ui.label>
                                <div class="mt-2">
                                    <x-ui.select name="status_id" id="status_id-{{ $client->id }}">
                                        <option value="">Select Status</option>
                                        @foreach($clientStatuses as $status)
                                            <option value="{{ $status->id }}" {{ $client->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="sm:col-span-3 field-wrapper">
                                <x-ui.label for="email-{{ $client->id }}">Email</x-ui.label>
                                <div class="mt-2">
                                    <x-ui.input type="email" name="email" id="email-{{ $client->id }}" value="{{ $client->email }}" />
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="sm:col-span-3 field-wrapper">
                                <x-ui.label for="phone-{{ $client->id }}">Phone</x-ui.label>
                                <div class="mt-2">
                                    <x-ui.input type="text" name="phone" id="phone-{{ $client->id }}" value="{{ $client->phone }}" />
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="sm:col-span-6 field-wrapper">
                                <x-ui.label for="address-{{ $client->id }}">Address</x-ui.label>
                                <div class="mt-2">
                                    <x-ui.textarea name="address" id="address-{{ $client->id }}" rows="3">{{ $client->address }}</x-ui.textarea>
                                </div>
                            </div>

                            <!-- VAT Payer -->
                            <div class="sm:col-span-6 field-wrapper">
                                <div class="flex items-start">
                                    <div class="flex h-6 items-center">
                                        <input type="checkbox" name="vat_payer" id="vat_payer-{{ $client->id }}" value="1" {{ $client->vat_payer ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                    </div>
                                    <div class="ml-3 text-sm leading-6">
                                        <label for="vat_payer-{{ $client->id }}" class="font-medium text-slate-900">VAT Payer</label>
                                        <p class="text-slate-500">Check if this client is registered as a VAT payer</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="sm:col-span-6 field-wrapper">
                                <x-ui.label for="notes-{{ $client->id }}">Notes</x-ui.label>
                                <div class="mt-2">
                                    <x-ui.textarea name="notes" id="notes-{{ $client->id }}" rows="4">{{ $client->notes }}</x-ui.textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
                <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel', 'client-edit-{{ $client->id }}')">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="default" form="client-edit-form-{{ $client->id }}">
                    Update Client
                </x-ui.button>
            </div>
        </x-slide-panel>
    @endforeach
</x-app-layout>
