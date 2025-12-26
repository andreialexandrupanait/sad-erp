<x-app-layout>
    <x-slot name="pageTitle">{{ __('Document Templates') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6 space-y-6"
                 x-data="templatesPage()"
                 x-init="init()">

                {{-- Header --}}
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
                    <a href="{{ route('settings.index') }}" class="hover:text-slate-700">{{ __('Settings') }}</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span>{{ __('Document Templates') }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">{{ __('Document Templates') }}</h1>
                        <p class="text-slate-500 mt-1">{{ __('Manage templates for offers and contracts') }}</p>
                    </div>
                    <div class="relative" x-data="{ open: false }">
                        <x-ui.button variant="default" @click="open = !open" class="flex items-center gap-2">
                            <svg class="-ml-1 mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('New Template') }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </x-ui.button>
                        <div x-show="open" @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 z-50">
                            <a href="{{ route('settings.document-templates.create') }}"
                               class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 rounded-t-lg">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                {{ __('Offer Template') }}
                            </a>
                            <a href="{{ route('settings.contract-templates.create') }}"
                               class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 rounded-b-lg border-t border-slate-100">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                {{ __('Contract Template') }}
                            </a>
                        </div>
                    </div>
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
                                        @input.debounce.300ms="fetchTemplates()"
                                        placeholder="{{ __('Search by name...') }}"
                                        class="pl-10"
                                    />
                                </div>
                            </div>

                            {{-- Type Filter --}}
                            <div class="w-full sm:w-40">
                                <x-ui.select x-model="filters.type" @change="fetchTemplates()">
                                    <option value="">{{ __('All Types') }}</option>
                                    <option value="offer">{{ __('Offer') }}</option>
                                    <option value="contract">{{ __('Contract') }}</option>
                                </x-ui.select>
                            </div>

                            {{-- Status Filter --}}
                            <div class="w-full sm:w-40">
                                <x-ui.select x-model="filters.is_active" @change="fetchTemplates()">
                                    <option value="">{{ __('All Statuses') }}</option>
                                    <option value="1">{{ __('Active') }}</option>
                                    <option value="0">{{ __('Inactive') }}</option>
                                </x-ui.select>
                            </div>

                            {{-- Search Button --}}
                            <div class="flex gap-2">
                                <x-ui.button type="button" variant="default" @click="fetchTemplates()">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                    </svg>
                                    {{ __('Search') }}
                                </x-ui.button>
                                <template x-if="filters.q || filters.type || filters.is_active !== ''">
                                    <x-ui.button variant="outline" @click="clearFilters()">
                                        {{ __('Clear') }}
                                    </x-ui.button>
                                </template>
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- Bulk Actions Toolbar --}}
                <x-bulk-toolbar resource="template-uri">
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

                {{-- Templates Table --}}
                <template x-if="!loading && templates.length > 0">
                    <x-ui.card>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-100">
                                    <tr class="border-b border-slate-200">
                                        <th class="px-6 py-4 text-left align-middle font-medium text-slate-500 w-12">
                                            <input type="checkbox"
                                                   x-model="selectAll"
                                                   @change="toggleAll()"
                                                   class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        </th>
                                        <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Name') }}</th>
                                        <th class="px-6 py-4 text-left align-middle font-medium text-slate-500 w-28">{{ __('Type') }}</th>
                                        <th class="px-6 py-4 text-center align-middle font-medium text-slate-500 w-28">{{ __('Status') }}</th>
                                        <th class="px-6 py-4 text-left align-middle font-medium text-slate-500 w-36">{{ __('Updated') }}</th>
                                        <th class="px-6 py-4 text-right align-middle font-medium text-slate-500 w-32">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    <template x-for="template in templates" :key="template.id">
                                        <tr class="hover:bg-slate-50" :class="{ 'bg-blue-50': isSelected(template.id) }">
                                            <td class="px-6 py-4 w-12">
                                                <input type="checkbox"
                                                       :checked="isSelected(template.id)"
                                                       @change="toggleItem(template.id)"
                                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium text-slate-900" x-text="template.name"></span>
                                                    <template x-if="template.is_default">
                                                        <span class="px-1.5 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded">{{ __('Default') }}</span>
                                                    </template>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-0.5 text-xs font-medium rounded"
                                                      :class="{
                                                          'bg-blue-100 text-blue-700': template.type === 'offer',
                                                          'bg-emerald-100 text-emerald-700': template.type === 'contract',
                                                          'bg-purple-100 text-purple-700': template.type === 'annex'
                                                      }"
                                                      x-text="template.type_label">
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <template x-if="template.is_active">
                                                    <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">{{ __('Active') }}</span>
                                                </template>
                                                <template x-if="!template.is_active">
                                                    <span class="px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-500 rounded-full">{{ __('Inactive') }}</span>
                                                </template>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600" x-text="formatDate(template.updated_at)"></td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end gap-3">
                                                    {{-- Edit --}}
                                                    <a :href="getEditUrl(template)"
                                                       class="inline-flex items-center text-blue-600 hover:text-blue-900 transition-colors"
                                                       title="{{ __('Edit') }}">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </a>
                                                    {{-- Duplicate --}}
                                                    <button @click="duplicateTemplate(template)"
                                                            class="inline-flex items-center text-slate-600 hover:text-slate-900 transition-colors"
                                                            title="{{ __('Duplicate') }}">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                    </button>
                                                    {{-- Delete --}}
                                                    <button @click="deleteTemplate(template)"
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
                    </x-ui.card>
                </template>

                {{-- Empty State --}}
                <template x-if="!loading && templates.length === 0">
                    <x-ui.card>
                        <div class="px-6 py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No templates') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Create your first template to get started.') }}</p>
                            <div class="mt-6">
                                <x-ui.button variant="default" onclick="window.location.href='{{ route('settings.document-templates.create') }}'">
                                    {{ __('New Template') }}
                                </x-ui.button>
                            </div>
                        </div>
                    </x-ui.card>
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function templatesPage() {
        return {
            templates: [],
            loading: true,
            filters: {
                q: '',
                type: '',
                is_active: ''
            },

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
                this.fetchTemplates();
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
                    this.selectedIds = this.templates.map(t => t.id);
                } else {
                    this.selectedIds = [];
                }
            },

            updateSelectAllState() {
                this.selectAll = this.templates.length > 0 && this.selectedIds.length === this.templates.length;
            },

            clearSelection() {
                this.selectedIds = [];
                this.selectAll = false;
            },

            clearFilters() {
                this.filters.q = '';
                this.filters.type = '';
                this.filters.is_active = '';
                this.fetchTemplates();
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                const date = new Date(dateString);
                return date.toLocaleDateString('ro-RO', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            },

            getEditUrl(template) {
                if (template.model_type === 'contract_template') {
                    return '/settings/contract-templates/' + template.id + '/edit';
                }
                // Offer template - use builder
                return '/settings/document-templates/' + template.id + '/builder';
            },

            getApiUrl(template, action = '') {
                const suffix = action ? '/' + action : '';
                if (template.model_type === 'contract_template') {
                    return '/settings/contract-templates/' + template.id + suffix;
                }
                return '/settings/document-templates/' + template.id + suffix;
            },

            async fetchTemplates() {
                this.loading = true;
                this.clearSelection();
                try {
                    const params = new URLSearchParams();
                    if (this.filters.q) params.append('q', this.filters.q);
                    if (this.filters.type) params.append('type', this.filters.type);
                    if (this.filters.is_active !== '') params.append('is_active', this.filters.is_active);

                    const response = await fetch(`/settings/document-templates?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();
                    this.templates = data.templates || [];
                } catch (error) {
                    console.error('Error fetching templates:', error);
                } finally {
                    this.loading = false;
                }
            },

            async duplicateTemplate(template) {
                try {
                    const url = this.getApiUrl(template, 'duplicate');
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        // Redirect to edit page or refresh list
                        await this.fetchTemplates();
                        this.$dispatch('notify', {
                            type: 'success',
                            message: '{{ __('Template duplicated successfully!') }}'
                        });
                    } else {
                        throw new Error('{{ __('Failed to duplicate template') }}');
                    }
                } catch (error) {
                    console.error('Error duplicating template:', error);
                    alert(error.message || '{{ __('Failed to duplicate template. Please try again.') }}');
                }
            },

            async deleteTemplate(template) {
                if (!confirm(`{{ __('Are you sure you want to delete template') }} "${template.name}"? {{ __('This action cannot be undone.') }}`)) {
                    return;
                }

                try {
                    const url = this.getApiUrl(template);
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        this.$dispatch('notify', {
                            type: 'success',
                            message: '{{ __('Template deleted successfully!') }}'
                        });
                        await this.fetchTemplates();
                    } else {
                        const result = await response.json();
                        throw new Error(result.error || result.message || '{{ __('Failed to delete template') }}');
                    }
                } catch (error) {
                    console.error('Error deleting template:', error);
                    alert(error.message || '{{ __('Failed to delete template. Please try again.') }}');
                }
            },

            async bulkDelete() {
                if (this.selectedIds.length === 0) return;

                const count = this.selectedIds.length;
                if (!confirm(`{{ __('Are you sure you want to delete') }} ${count} {{ __('template(s)? This action cannot be undone.') }}`)) {
                    return;
                }

                this.isLoading = true;

                try {
                    const response = await fetch('/settings/document-templates/bulk-delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids: this.selectedIds })
                    });

                    const result = await response.json();

                    if (result.success || response.ok) {
                        this.$dispatch('notify', {
                            type: 'success',
                            message: result.message || `{{ __('Successfully deleted') }} ${count} {{ __('template(s)') }}`
                        });

                        this.clearSelection();
                        await this.fetchTemplates();
                    } else {
                        throw new Error(result.error || result.message || '{{ __('Failed to delete templates') }}');
                    }
                } catch (error) {
                    console.error('Error bulk deleting templates:', error);
                    alert(error.message || '{{ __('Failed to delete templates. Please try again.') }}');
                } finally {
                    this.isLoading = false;
                }
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
