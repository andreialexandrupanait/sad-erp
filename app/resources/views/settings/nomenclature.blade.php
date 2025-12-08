<x-app-layout>
    <x-slot name="pageTitle">{{ __('Settings') }}</x-slot>

    @push('head')
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    @endpush

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto" x-data="{
            showForm: false,
            editingId: null,
            isSubmitting: false,
            formData: {
                id: null,
                label: '',
                value: '',
                color: '#3b82f6',
                parent_id: ''
            },

            // Bulk selection
            selectedIds: [],
            selectAll: false,
            isLoading: false,

            get selectedCount() {
                return this.selectedIds.length;
            },

            get hasSelection() {
                return this.selectedIds.length > 0;
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
                    this.selectAllVisible();
                } else {
                    this.selectedIds = [];
                }
            },

            selectAllVisible() {
                const rows = document.querySelectorAll('[data-option-id]');
                this.selectedIds = Array.from(rows).map(row => parseInt(row.dataset.optionId));
            },

            updateSelectAllState() {
                const rows = document.querySelectorAll('[data-option-id]');
                const totalRows = rows.length;
                this.selectAll = totalRows > 0 && this.selectedIds.length === totalRows;
            },

            clearSelection() {
                this.selectedIds = [];
                this.selectAll = false;
            },

            async bulkDelete() {
                if (this.selectedIds.length === 0) return;

                const confirmMsg = `{{ __('settings.confirm_delete_items', ['count' => ':count']) }}`.replace(':count', this.selectedIds.length);
                if (!confirm(confirmMsg)) return;

                this.isLoading = true;

                try {
                    const response = await fetch('{{ route('settings.nomenclature.bulk-delete') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            ids: this.selectedIds
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || '{{ __('settings.error_deleting') }}');
                    }
                } catch (error) {
                    console.error('Bulk delete error:', error);
                    alert('{{ __('settings.error_occurred') }}');
                } finally {
                    this.isLoading = false;
                }
            },

            slugify(text) {
                return text.toString().toLowerCase().trim()
                    .replace(/\s+/g, '-')
                    .replace(/[^\w\-]+/g, '')
                    .replace(/\-\-+/g, '-')
                    .replace(/^-+/, '')
                    .replace(/-+$/, '');
            },

            autoGenerateValue() {
                if (!this.formData.id) {
                    this.formData.value = this.slugify(this.formData.label);
                }
            },

            openAddForm(parentId = null) {
                this.editingId = null;
                this.formData = {
                    id: null,
                    label: '',
                    value: '',
                    color: '#3b82f6',
                    parent_id: parentId || ''
                };
                this.showForm = true;
            },

            openEditForm(id, label, color, parentId, isHierarchical = false) {
                if (isHierarchical) {
                    this.showForm = false;
                    this.editingId = id;
                } else {
                    this.editingId = null;
                    this.showForm = true;
                }
                this.formData = {
                    id: id,
                    label: label,
                    value: this.slugify(label),
                    color: color || '#3b82f6',
                    parent_id: parentId || ''
                };
            },

            closeForm() {
                this.showForm = false;
                this.editingId = null;
                this.formData = {
                    id: null,
                    label: '',
                    value: '',
                    color: '#3b82f6',
                    parent_id: ''
                };
            },

            async saveOption() {
                this.isSubmitting = true;

                const data = {
                    category: '{{ $category }}',
                    label: this.formData.label,
                    value: this.formData.value,
                    color: this.formData.color,
                    parent_id: this.formData.parent_id || null
                };

                const url = this.formData.id
                    ? '/settings/nomenclature/' + this.formData.id
                    : '/settings/nomenclature';
                const method = this.formData.id ? 'PATCH' : 'POST';

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const responseData = await response.json();

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        const errorMsg = responseData.message || '{{ __('settings.error_saving') }}';
                        alert(errorMsg);
                        console.error('Error response:', responseData);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('{{ __('settings.error_saving') }}');
                } finally {
                    this.isSubmitting = false;
                }
            },

            async deleteOption(optionId) {
                if (!confirm('{{ __('settings.confirm_delete_option') }}')) return;

                try {
                    const response = await fetch('/settings/nomenclature/' + optionId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const responseData = await response.json();

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        const errorMsg = responseData.message || '{{ __('settings.error_deleting') }}';
                        alert(errorMsg);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('{{ __('settings.error_deleting') }}');
                }
            }
        }">
            <div class="p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">{{ $title }}</h2>
                        <p class="text-sm text-slate-500 mt-1">{{ $data->count() }} {{ isset($isHierarchical) && $isHierarchical ? __('settings.categories') : __('settings.options') }}</p>
                    </div>
                    <button @click="openAddForm()" class="px-4 py-2 text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('settings.' . (isset($isHierarchical) && $isHierarchical ? 'add_category' : 'add_option')) }}</span>
                    </button>
                </div>

                <!-- Bulk Actions Toolbar -->
                <div
                    x-show="hasSelection"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] max-w-4xl w-full mx-auto px-4"
                    x-cloak
                >
                    <div class="bg-slate-900 text-white rounded-xl shadow-2xl border border-slate-700 px-6 py-4">
                        <div class="flex items-center justify-between gap-4 flex-wrap">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-sm font-bold">
                                    <span x-text="selectedCount"></span>
                                </div>
                                <span class="text-sm font-medium">
                                    <span x-text="selectedCount"></span>
                                    <span x-text="selectedCount === 1 ? '{{ __('settings.element_selected') }}' : '{{ __('settings.elements_selected') }}'"></span>
                                </span>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <button
                                    @click="bulkDelete()"
                                    :disabled="isLoading"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-500 rounded-lg transition-colors disabled:opacity-50"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    {{ __('settings.delete_selected') }}
                                </button>

                                <button
                                    @click="clearSelection()"
                                    class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors"
                                    :disabled="isLoading"
                                >
                                    {{ __('settings.cancel_action') }}
                                </button>
                            </div>
                        </div>

                        <div
                            x-show="isLoading"
                            class="absolute inset-0 bg-slate-900/80 rounded-xl flex items-center justify-center"
                        >
                            <svg class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                    <div class="p-6">
                        <!-- Add Form (Compact) -->
                        <div x-show="showForm" x-cloak class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <form @submit.prevent="saveOption()">
                                @if(isset($isHierarchical) && $isHierarchical)
                                    <!-- Parent Category Selection -->
                                    <div class="mb-2">
                                        <select x-model="formData.parent_id" class="w-full px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">{{ __('settings.main_category') }}</option>
                                            @foreach($data as $parentOption)
                                                <option value="{{ $parentOption->id }}">{{ $parentOption->label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="flex items-center gap-2">
                                    <!-- Name -->
                                    <input type="text" x-model="formData.label" @input="autoGenerateValue()" required
                                           class="flex-1 px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="{{ __('settings.name') }}">

                                    @if($hasColors)
                                        <!-- Color (only for parent categories) -->
                                        <template x-if="!formData.parent_id || formData.parent_id === ''">
                                            <input type="color" x-model="formData.color" class="w-12 h-8 border border-slate-300 rounded cursor-pointer">
                                        </template>
                                        <template x-if="formData.parent_id && formData.parent_id !== ''">
                                            <div class="w-12"></div>
                                        </template>
                                    @endif

                                    <!-- Actions -->
                                    <button type="submit" :disabled="isSubmitting" class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition-colors disabled:opacity-50 whitespace-nowrap">
                                        <span x-show="!isSubmitting">{{ __('settings.save') }}</span>
                                        <span x-show="isSubmitting">...</span>
                                    </button>
                                    <button type="button" @click="closeForm()" class="px-3 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded transition-colors">
                                        {{ __('settings.cancel_action') }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        @if(isset($isHierarchical) && $isHierarchical)
                            {{-- HIERARCHICAL VIEW - Card Based --}}
                            <div class="space-y-4">
                                @forelse($data as $parent)
                                    {{-- Parent Category Card --}}
                                    <div class="rounded-lg border border-slate-200 overflow-hidden" data-option-id="{{ $parent->id }}">
                                        {{-- Parent Header --}}
                                        <div class="flex items-center gap-3 px-4 py-3 bg-slate-50 border-l-4" style="border-left-color: {{ $parent->color_class ?? '#3b82f6' }}">
                                            <input type="checkbox"
                                                   :checked="selectedIds.includes({{ $parent->id }})"
                                                   @change="toggleItem({{ $parent->id }})"
                                                   class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                                            <div class="flex-1 flex items-center gap-2">
                                                <span class="font-semibold text-slate-900">{{ $parent->label }}</span>
                                                @if($parent->children->count() > 0)
                                                    <span class="px-2 py-0.5 text-xs font-medium bg-slate-200 text-slate-600 rounded-full">
                                                        {{ $parent->children->count() }}
                                                    </span>
                                                @endif
                                            </div>

                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $parent->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $parent->is_active ? __('settings.active') : __('settings.inactive') }}
                                            </span>

                                            <div class="flex items-center gap-2">
                                                @if($hasColors)
                                                    <div class="w-7 h-7 rounded border border-slate-300 flex-shrink-0 cursor-pointer hover:ring-2 hover:ring-blue-300 transition-all"
                                                         style="background-color: {{ $parent->color_class ?? '#3b82f6' }}"
                                                         @click="openEditForm({{ $parent->id }}, '{{ addslashes($parent->label) }}', '{{ $parent->color_class ?? '#3b82f6' }}', {{ $parent->parent_id ?? 'null' }}, true)"
                                                         title="{{ __('settings.change_color') }}"></div>
                                                @endif
                                                <div class="flex items-center gap-1 border-l border-slate-200 pl-2">
                                                    <button @click="openAddForm({{ $parent->id }})" class="p-1.5 text-blue-600 hover:text-blue-700 hover:bg-blue-100 rounded" title="{{ __('settings.add_subcategory') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                    </button>
                                                    <button @click="openEditForm({{ $parent->id }}, '{{ addslashes($parent->label) }}', '{{ $parent->color_class ?? '#3b82f6' }}', {{ $parent->parent_id ?? 'null' }}, true)"
                                                            class="p-1.5 text-slate-500 hover:text-slate-700 hover:bg-slate-200 rounded" title="{{ __('settings.edit') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                    <button @click="deleteOption({{ $parent->id }})" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-100 rounded" title="{{ __('settings.delete') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Inline Edit Form for Parent --}}
                                        <div x-show="editingId === {{ $parent->id }}" x-cloak class="px-4 py-3 bg-amber-50 border-t border-amber-200">
                                            <form @submit.prevent="saveOption()" class="flex items-center gap-2">
                                                <select x-model="formData.parent_id" class="px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500">
                                                    <option value="">{{ __('settings.main') }}</option>
                                                    @foreach($data as $parentOption)
                                                        @if($parentOption->id != $parent->id)
                                                            <option value="{{ $parentOption->id }}">{{ $parentOption->label }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <input type="text" x-model="formData.label" @input="autoGenerateValue()" required
                                                       class="flex-1 px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500"
                                                       placeholder="{{ __('settings.name') }}">
                                                @if($hasColors)
                                                    <input type="color" x-show="!formData.parent_id || formData.parent_id === ''" x-model="formData.color" class="w-10 h-8 border border-slate-300 rounded cursor-pointer">
                                                @endif
                                                <button type="submit" :disabled="isSubmitting" class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded transition-colors disabled:opacity-50">{{ __('settings.save') }}</button>
                                                <button type="button" @click="closeForm()" class="px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded transition-colors">{{ __('settings.cancel_action') }}</button>
                                            </form>
                                        </div>

                                        {{-- Children List --}}
                                        @if($parent->children->count() > 0)
                                            <div class="divide-y divide-slate-100">
                                                @foreach($parent->children as $child)
                                                    <div class="flex items-center gap-3 px-4 py-2.5 pl-10 hover:bg-slate-50 transition-colors" data-option-id="{{ $child->id }}">
                                                        <input type="checkbox"
                                                               :checked="selectedIds.includes({{ $child->id }})"
                                                               @change="toggleItem({{ $child->id }})"
                                                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                                                        <span class="flex-1 text-sm text-slate-600">{{ $child->label }}</span>

                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $child->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                            {{ $child->is_active ? __('settings.active') : __('settings.inactive') }}
                                                        </span>

                                                        <div class="flex items-center gap-2">
                                                            @if($hasColors)
                                                                <div class="w-7 h-7 flex-shrink-0"></div>
                                                            @endif
                                                            <div class="flex items-center gap-1 border-l border-slate-200 pl-2">
                                                                <div class="w-7 h-7"></div>
                                                                <button @click="openEditForm({{ $child->id }}, '{{ addslashes($child->label) }}', '{{ $child->color_class ?? '#3b82f6' }}', {{ $child->parent_id ?? 'null' }}, true)"
                                                                        class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-200 rounded" title="{{ __('settings.edit') }}">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                    </svg>
                                                                </button>
                                                                <button @click="deleteOption({{ $child->id }})" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-100 rounded" title="{{ __('settings.delete') }}">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Inline Edit Form for Child --}}
                                                    <div x-show="editingId === {{ $child->id }}" x-cloak class="px-4 py-2.5 pl-10 bg-amber-50 border-t border-amber-200">
                                                        <form @submit.prevent="saveOption()" class="flex items-center gap-2">
                                                            <select x-model="formData.parent_id" class="px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500">
                                                                <option value="">{{ __('settings.main') }}</option>
                                                                @foreach($data as $parentOption)
                                                                    <option value="{{ $parentOption->id }}">{{ $parentOption->label }}</option>
                                                                @endforeach
                                                            </select>
                                                            <input type="text" x-model="formData.label" @input="autoGenerateValue()" required
                                                                   class="flex-1 px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500"
                                                                   placeholder="{{ __('settings.name') }}">
                                                            <button type="submit" :disabled="isSubmitting" class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded transition-colors disabled:opacity-50">{{ __('settings.save') }}</button>
                                                            <button type="button" @click="closeForm()" class="px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded transition-colors">{{ __('settings.cancel_action') }}</button>
                                                        </form>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="py-12 text-center text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                        </svg>
                                        <p>{{ __('settings.no_categories') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        @else
                            {{-- NON-HIERARCHICAL VIEW - Table Based --}}
                            <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th class="text-left py-3 px-4 w-10">
                                            <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                                   class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        </th>
                                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase">{{ __('settings.name') }}</th>
                                        @if($hasColors)
                                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase w-20">{{ __('settings.color') }}</th>
                                        @endif
                                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase w-20">{{ __('settings.status') }}</th>
                                        <th class="text-right py-3 px-4 text-xs font-semibold text-slate-600 uppercase w-32">{{ __('settings.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                        {{-- NON-HIERARCHICAL VIEW --}}
                                        @forelse($data as $option)
                                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors"
                                                data-option-id="{{ $option->id }}">
                                                <td class="py-3 px-4">
                                                    <input type="checkbox"
                                                           :checked="selectedIds.includes({{ $option->id }})"
                                                           @change="toggleItem({{ $option->id }})"
                                                           class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                </td>
                                                <td class="py-3 px-4 text-sm text-slate-900">{{ $option->label }}</td>
                                                @if($hasColors)
                                                    <td class="py-3 px-4">
                                                        @if($option->color_class)
                                                            <div class="w-8 h-8 rounded border border-slate-300" style="background-color: {{ $option->color_class }}"></div>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td class="py-3 px-4">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $option->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $option->is_active ? __('settings.active') : __('settings.inactive') }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 text-right">
                                                    <div class="flex justify-end gap-1">
                                                        <button @click="openEditForm({{ $option->id }}, '{{ addslashes($option->label) }}', '{{ $option->color_class ?? '#3b82f6' }}', null, true)"
                                                                class="p-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded" title="{{ __('settings.edit') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </button>
                                                        <button @click="deleteOption({{ $option->id }})" class="p-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded" title="{{ __('settings.delete') }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            {{-- Inline Edit Form --}}
                                            <tr x-show="editingId === {{ $option->id }}" x-cloak class="bg-yellow-50 border-l-4 border-l-yellow-400">
                                                <td colspan="{{ $hasColors ? '5' : '4' }}" class="py-2 px-4">
                                                    <form @submit.prevent="saveOption()" class="flex items-center gap-2">
                                                        <!-- Name -->
                                                        <input type="text" x-model="formData.label" @input="autoGenerateValue()" required
                                                               class="flex-1 px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500"
                                                               placeholder="{{ __('settings.name') }}">

                                                        @if($hasColors)
                                                            <!-- Color -->
                                                            <input type="color" x-model="formData.color" class="w-12 h-8 border border-slate-300 rounded cursor-pointer">
                                                        @endif

                                                        <!-- Actions -->
                                                        <button type="submit" :disabled="isSubmitting" class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded transition-colors disabled:opacity-50 whitespace-nowrap">
                                                            {{ __('settings.save') }}
                                                        </button>
                                                        <button type="button" @click="closeForm()" class="px-3 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded transition-colors">
                                                            {{ __('settings.cancel_action') }}
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $hasColors ? '5' : '4' }}" class="py-8 text-center text-slate-500">
                                                    {{ __('settings.no_options') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                </tbody>
                            </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
