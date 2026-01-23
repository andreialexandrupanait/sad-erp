@props([
    'clients' => [],
    'selected' => null,
    'name' => 'client_id',
    'placeholder' => null,
    'searchPlaceholder' => null,
    'allowEmpty' => true,
    'emptyLabel' => null,
    'disabled' => false,
    'required' => false,
    'clientStatuses' => [],
])

@php
    $placeholder = $placeholder ?? __('Select a client...');
    $searchPlaceholder = $searchPlaceholder ?? __('Search clients...');
    $emptyLabel = $emptyLabel ?? __('-- No Client --');

    // Ensure clientStatuses is available for the slide-over form
    if (empty($clientStatuses) || (is_object($clientStatuses) && $clientStatuses->isEmpty())) {
        $clientStatuses = \App\Models\SettingOption::clientStatuses()->get();
    }

    // Convert clients to array format for JS
    $clientsArray = collect($clients)->map(function($client) {
        return [
            'value' => $client->id ?? $client['id'] ?? '',
            'label' => $client->name ?? $client['name'] ?? '',
            'company_name' => $client->company_name ?? $client['company_name'] ?? '',
            'email' => $client->email ?? $client['email'] ?? '',
            'created_from_temp' => $client->created_from_temp ?? $client['created_from_temp'] ?? false,
        ];
    })->values()->all();

    // Unique ID for this component instance
    $componentId = 'client-select-' . uniqid();
@endphp

<div
    x-data="clientSelect({
        clients: {{ Js::from($clientsArray) }},
        selected: {{ Js::from($selected) }},
        name: '{{ $name }}',
        allowEmpty: {{ $allowEmpty ? 'true' : 'false' }},
        placeholder: '{{ $placeholder }}',
        emptyLabel: '{{ $emptyLabel }}',
        createUrl: '{{ route('clients.store') }}',
        csrfToken: '{{ csrf_token() }}',
        componentId: '{{ $componentId }}'
    })"
    class="relative"
    id="{{ $componentId }}"
    {{ $disabled ? 'data-disabled' : '' }}
>
    {{-- Hidden input for form submission --}}
    <input type="hidden" :id="name" :name="name" :value="selectedValue || ''">

    {{-- Trigger Button --}}
    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        @keydown="onKeydown($event)"
        :disabled="{{ $disabled ? 'true' : 'false' }}"
        class="w-full h-10 px-3 pr-8 text-left text-sm border border-slate-200 rounded-md bg-white
               hover:border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none
               transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed
               flex items-center justify-between"
        :class="{ 'ring-2 ring-blue-500': open }"
    >
        <span
            x-text="selectedLabel || '{{ $placeholder }}'"
            :class="{ 'text-slate-500': !selectedValue }"
            class="truncate"
        ></span>
        <svg class="w-4 h-4 text-slate-400 flex-shrink-0 ml-2 transition-transform pointer-events-none" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown Panel --}}
    <div
        x-ref="dropdown"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="close()"
        @keydown.escape.prevent="close()"
        class="fixed z-[9999] bg-white border border-slate-200 rounded-lg shadow-xl overflow-hidden"
        style="min-width: 200px;"
    >
        {{-- Search Input --}}
        <div class="p-2 border-b border-slate-100">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    x-ref="searchInput"
                    x-model="searchQuery"
                    @keydown="onKeydown($event)"
                    placeholder="{{ $searchPlaceholder }}"
                    class="w-full h-9 pl-9 pr-3 text-sm border border-slate-200 rounded-md bg-slate-50
                           focus:bg-white focus:border-slate-400 focus:ring-1 focus:ring-slate-400 focus:outline-none"
                >
            </div>
        </div>

        {{-- Options List --}}
        <div x-ref="optionsList" class="max-h-56 overflow-y-auto py-1">
            {{-- Add New Client Option --}}
            <div
                @click="openSlideOver()"
                @mouseenter="highlightedIndex = -2"
                class="px-3 py-2 text-sm cursor-pointer hover:bg-emerald-50 flex items-center gap-2 border-b border-slate-100"
                :class="{ 'bg-emerald-50': highlightedIndex === -2 }"
            >
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="text-emerald-700 font-medium">{{ __('Add new client') }}</span>
            </div>

            {{-- Empty option --}}
            @if($allowEmpty)
            <div
                @click="clear(); close()"
                @mouseenter="highlightedIndex = -1"
                class="px-3 py-2 text-sm text-slate-500 cursor-pointer hover:bg-slate-50 flex items-center justify-between"
                :class="{ 'bg-slate-100': highlightedIndex === -1 }"
            >
                <span class="italic">{{ $emptyLabel }}</span>
                <svg x-show="!selectedValue" class="w-4 h-4 text-slate-900" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            @endif

            {{-- Client Options --}}
            <template x-for="(client, index) in filteredClients" :key="client.value">
                <div
                    @click="select(client)"
                    @mouseenter="highlightedIndex = index"
                    class="px-3 py-2 cursor-pointer transition-colors"
                    :class="{
                        'bg-slate-100': highlightedIndex === index,
                        'bg-slate-50': isSelected(client),
                        'hover:bg-slate-50': highlightedIndex !== index && !isSelected(client),
                        'bg-amber-50': client.created_from_temp && highlightedIndex !== index && !isSelected(client)
                    }"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm font-medium" :class="client.created_from_temp ? 'text-amber-800' : 'text-slate-700'" x-text="client.label + (client.created_from_temp ? ' (temp)' : '')"></span>
                            <span x-show="client.company_name" class="text-xs text-slate-500 ml-1" x-text="'(' + client.company_name + ')'"></span>
                        </div>
                        <svg x-show="isSelected(client)" class="w-4 h-4 text-slate-900 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </template>

            {{-- No results --}}
            <div x-show="filteredClients.length === 0 && searchQuery.length > 0" class="px-3 py-6 text-center">
                <p class="text-sm text-slate-500 mb-2">{{ __('No clients found') }}</p>
                <button
                    type="button"
                    @click="openSlideOver()"
                    class="text-sm text-emerald-600 hover:text-emerald-700 font-medium"
                >
                    {{ __('Create new client') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Slide-Over Modal for Creating New Client --}}
    <template x-teleport="body">
        <div
            x-show="slideOverOpen"
            x-cloak
            class="fixed inset-0 z-[10000] overflow-hidden"
            aria-labelledby="slide-over-title-{{ $componentId }}"
            role="dialog"
            aria-modal="true"
        >
            {{-- Backdrop --}}
            <div
                x-show="slideOverOpen"
                x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="closeSlideOver()"
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
            ></div>

            {{-- Slide-Over Panel --}}
            <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div
                    x-show="slideOverOpen"
                    x-transition:enter="transform transition ease-in-out duration-300"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-300"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                    class="w-screen max-w-lg"
                >
                    <div class="flex h-full flex-col bg-white shadow-xl">
                        {{-- Header --}}
                        <div class="bg-slate-50 px-4 py-6 sm:px-6 border-b border-slate-200 flex-shrink-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h2 id="slide-over-title-{{ $componentId }}" class="text-lg font-semibold text-slate-900">
                                        {{ __('Add New Client') }}
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ __('Create a new client and automatically select it.') }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="closeSlideOver()"
                                    class="rounded-md text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500"
                                >
                                    <span class="sr-only">{{ __('Close') }}</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Form --}}
                        <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6" :data-form-id="componentId">
                            {{-- Error Display --}}
                            <div x-show="formErrors.length > 0" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex gap-2">
                                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <template x-for="error in formErrors" :key="error">
                                            <p class="text-sm text-red-700" x-text="error"></p>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Include the actual client form fields component --}}
                            <x-client-form-fields
                                :client="null"
                                :statuses="$clientStatuses"
                                :prefix="$componentId . '_'"
                                :compact="true"
                            />
                        </div>

                        {{-- Footer --}}
                        <div class="flex-shrink-0 border-t border-slate-200 px-4 py-4 sm:px-6 bg-slate-50">
                            <div class="flex justify-end gap-3">
                                <button
                                    type="button"
                                    @click="closeSlideOver()"
                                    class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md
                                           hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2"
                                >
                                    {{ __('Cancel') }}
                                </button>
                                <button
                                    type="button"
                                    @click="createClient()"
                                    :disabled="saving"
                                    class="px-4 py-2 text-sm font-medium text-white bg-slate-900 rounded-md
                                           hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2
                                           disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                >
                                    <svg x-show="saving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="saving ? '{{ __('Creating...') }}' : '{{ __('Create Client') }}'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

@once
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('clientSelect', (config = {}) => ({
        clients: config.clients || [],
        name: config.name || 'client_id',
        allowEmpty: config.allowEmpty !== false,
        placeholder: config.placeholder || 'Select a client...',
        emptyLabel: config.emptyLabel || '-- No Client --',
        createUrl: config.createUrl || '',
        csrfToken: config.csrfToken || '',
        componentId: config.componentId || '',

        selectedValue: config.selected || null,
        selectedLabel: '',
        searchQuery: '',
        open: false,
        highlightedIndex: -1,

        // Slide-over state
        slideOverOpen: false,
        saving: false,
        formErrors: [],

        init() {
            if (this.selectedValue !== null) {
                this.selectedValue = String(this.selectedValue);
            }
            this.updateSelectedLabel();

            window.addEventListener('scroll', () => {
                if (this.open) this.positionDropdown();
            }, true);
            window.addEventListener('resize', () => {
                if (this.open) this.positionDropdown();
            });
        },

        updateSelectedLabel() {
            if (this.selectedValue) {
                const client = this.clients.find(c => String(c.value) === String(this.selectedValue));
                this.selectedLabel = client ? client.label : '';
            } else {
                this.selectedLabel = '';
            }
        },

        get filteredClients() {
            if (!this.searchQuery.trim()) return this.clients;
            const q = this.searchQuery.toLowerCase().trim();
            return this.clients.filter(c =>
                c.label.toLowerCase().includes(q) ||
                (c.company_name && c.company_name.toLowerCase().includes(q)) ||
                (c.email && c.email.toLowerCase().includes(q))
            );
        },

        toggle() {
            this.open ? this.close() : this.openDropdown();
        },

        openDropdown() {
            this.open = true;
            this.searchQuery = '';
            this.highlightedIndex = this.selectedValue
                ? this.filteredClients.findIndex(c => String(c.value) === String(this.selectedValue))
                : 0;
            this.$nextTick(() => {
                this.positionDropdown();
                if (this.$refs.searchInput) this.$refs.searchInput.focus();
            });
        },

        close() {
            this.open = false;
            this.searchQuery = '';
            this.highlightedIndex = -1;
        },

        positionDropdown() {
            const trigger = this.$refs.trigger;
            const dropdown = this.$refs.dropdown;
            if (!trigger || !dropdown) return;

            const rect = trigger.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            const viewportWidth = window.innerWidth;
            const spaceBelow = viewportHeight - rect.bottom;
            const dropdownHeight = dropdown.offsetHeight || 300;

            let top;
            if (spaceBelow >= dropdownHeight || spaceBelow >= rect.top) {
                top = rect.bottom + 4;
            } else {
                top = rect.top - dropdownHeight - 4;
            }

            let left = rect.left;
            const dropdownWidth = Math.max(dropdown.offsetWidth, rect.width);
            if (left + dropdownWidth > viewportWidth - 8) {
                left = viewportWidth - dropdownWidth - 8;
            }
            if (left < 8) left = 8;

            dropdown.style.top = top + 'px';
            dropdown.style.left = left + 'px';
            dropdown.style.width = rect.width + 'px';
        },

        select(client) {
            this.selectedValue = String(client.value);
            this.selectedLabel = client.label;
            this.close();

            // Dispatch change event for any listeners
            this.$dispatch('client-selected', { id: client.value, name: client.label });
        },

        clear() {
            this.selectedValue = null;
            this.selectedLabel = '';
            this.$dispatch('client-selected', { id: null, name: null });
        },

        isSelected(client) {
            return String(client.value) === String(this.selectedValue);
        },

        onKeydown(e) {
            if (!this.open) {
                if (['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(e.key)) {
                    e.preventDefault();
                    this.openDropdown();
                }
                return;
            }

            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.highlightedIndex = Math.min(this.highlightedIndex + 1, this.filteredClients.length - 1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.highlightedIndex = Math.max(this.highlightedIndex - 1, this.allowEmpty ? -2 : -1);
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (this.highlightedIndex === -2) {
                        this.openSlideOver();
                    } else if (this.highlightedIndex === -1 && this.allowEmpty) {
                        this.clear();
                        this.close();
                    } else if (this.highlightedIndex >= 0 && this.filteredClients[this.highlightedIndex]) {
                        this.select(this.filteredClients[this.highlightedIndex]);
                    }
                    break;
                case 'Escape':
                    e.preventDefault();
                    this.close();
                    break;
            }
        },

        // Slide-over methods
        openSlideOver() {
            this.close();
            this.slideOverOpen = true;
            this.formErrors = [];
            this.$nextTick(() => {
                // Focus first input using container query
                const container = document.querySelector(`[data-form-id="${this.componentId}"]`);
                if (container) {
                    const firstInput = container.querySelector('input[type="text"]');
                    if (firstInput) firstInput.focus();
                }
            });
        },

        closeSlideOver() {
            this.slideOverOpen = false;
            this.formErrors = [];
            this.saving = false;
        },

        getFormData() {
            // Find the form container by data attribute (works with teleported content)
            const container = document.querySelector(`[data-form-id="${this.componentId}"]`);
            if (!container) return {};

            const prefix = this.componentId + '_';
            const fields = ['name', 'company_name', 'tax_id', 'registration_number', 'contact_person', 'status_id', 'email', 'phone', 'address', 'vat_payer'];
            const data = {};

            fields.forEach(field => {
                // Try by name attribute first (more reliable), then by ID
                let input = container.querySelector(`[name="${prefix}${field}"]`);
                if (!input) {
                    input = container.querySelector(`#${prefix}${field}`);
                }
                if (input) {
                    if (input.type === 'checkbox') {
                        data[field] = input.checked ? '1' : '0';
                    } else {
                        data[field] = input.value;
                    }
                }
            });

            return data;
        },

        clearFormFields() {
            const container = document.querySelector(`[data-form-id="${this.componentId}"]`);
            if (!container) return;

            const prefix = this.componentId + '_';
            const fields = ['name', 'company_name', 'tax_id', 'registration_number', 'contact_person', 'status_id', 'email', 'phone', 'address', 'vat_payer', 'notes'];

            fields.forEach(field => {
                let input = container.querySelector(`[name="${prefix}${field}"]`);
                if (!input) {
                    input = container.querySelector(`#${prefix}${field}`);
                }
                if (input) {
                    if (input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                }
            });
        },

        async createClient() {
            if (this.saving) return;

            // Debug: check what we're working with
            const container = document.querySelector(`[data-form-id="${this.componentId}"]`);
            console.log('componentId:', this.componentId);
            console.log('Container found:', container);
            if (container) {
                console.log('All inputs in container:', container.querySelectorAll('input'));
                const nameInput = container.querySelector('input[type="text"]');
                console.log('First text input:', nameInput);
                console.log('First text input value:', nameInput ? nameInput.value : 'N/A');
                console.log('First text input name:', nameInput ? nameInput.name : 'N/A');
            }

            const formData = this.getFormData();
            console.log('Form data collected:', formData);

            // Basic validation
            this.formErrors = [];

            if (!formData.name || !formData.name.trim()) {
                this.formErrors.push('{{ __("Please enter a client name.") }}');
                return;
            }

            this.saving = true;

            try {
                const response = await fetch(this.createUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (!response.ok) {
                    // Handle validation errors
                    if (data.errors) {
                        this.formErrors = [];
                        for (const [field, messages] of Object.entries(data.errors)) {
                            this.formErrors.push(messages[0]);
                        }
                    } else if (data.message) {
                        this.formErrors = [data.message];
                    } else {
                        this.formErrors = ['{{ __("An error occurred. Please try again.") }}'];
                    }
                    return;
                }

                // Success - add new client to list and select it
                const newClientData = {
                    value: data.client.id,
                    label: data.client.name,
                    company_name: data.client.company_name || '',
                    email: data.client.email || ''
                };

                // Add to beginning of list
                this.clients.unshift(newClientData);

                // Select the new client
                this.selectedValue = String(data.client.id);
                this.selectedLabel = data.client.name;

                // Clear form and close slide-over
                this.clearFormFields();
                this.closeSlideOver();

                // Dispatch events
                this.$dispatch('client-created', { client: data.client });
                this.$dispatch('client-selected', { id: data.client.id, name: data.client.name });

            } catch (error) {
                console.error('Error creating client:', error);
                this.formErrors = ['{{ __("An error occurred. Please try again.") }}'];
            } finally {
                this.saving = false;
            }
        }
    }));
});
</script>
@endpush
@endonce
