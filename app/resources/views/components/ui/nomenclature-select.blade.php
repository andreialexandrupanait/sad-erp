@props([
    'options' => [],
    'selected' => null,
    'name' => 'option',
    'category' => '',
    'placeholder' => null,
    'searchPlaceholder' => null,
    'allowEmpty' => false,
    'emptyLabel' => null,
    'disabled' => false,
    'required' => false,
    'hasColors' => false,
    'valueKey' => 'value',  // Use 'id' for FK-based selections (e.g., client status_id)
])

@php
    $placeholder = $placeholder ?? __('Select an option...');
    $searchPlaceholder = $searchPlaceholder ?? __('Search...');
    $emptyLabel = $emptyLabel ?? __('-- None --');

    // Convert options to array format for JS (include id for edit/delete)
    // Use valueKey to determine which field to use as the value (default: 'value', can be 'id')
    $optionsArray = collect($options)->map(function($option) use ($valueKey) {
        $id = $option->id ?? $option['id'] ?? null;
        $optionValue = $option->value ?? $option['value'] ?? '';
        $label = $option->label ?? $option['label'] ?? ($option->name ?? $option['name'] ?? '');

        return [
            'id' => $id,
            'value' => $valueKey === 'id' ? $id : $optionValue,
            'label' => $label,
            'color' => $option->color_class ?? $option['color_class'] ?? null,
        ];
    })->values()->all();

    // Unique ID for this component instance
    $componentId = 'nom-sel-' . uniqid();
@endphp

<div
    x-data="{
        options: {{ Js::from($optionsArray) }},
        name: '{{ $name }}',
        category: '{{ $category }}',
        allowEmpty: {{ $allowEmpty ? 'true' : 'false' }},
        hasColors: {{ $hasColors ? 'true' : 'false' }},
        placeholder: '{{ addslashes($placeholder) }}',
        emptyLabel: '{{ addslashes($emptyLabel) }}',
        storeUrl: '{{ route('settings.nomenclature.store') }}',
        baseUrl: '{{ url('settings/nomenclature') }}',
        csrfToken: '{{ csrf_token() }}',
        componentId: '{{ $componentId }}',

        selectedValue: {{ Js::from($selected) }},
        selectedLabel: '',
        selectedColor: null,
        searchQuery: '',
        open: false,
        highlightedIndex: -1,

        slideOverOpen: false,
        saving: false,
        formErrors: [],
        newLabel: '',
        newColor: '#3b82f6',

        editingOption: null,
        isEditMode: false,

        init() {
            if (this.selectedValue !== null && this.selectedValue !== '') {
                this.selectedValue = String(this.selectedValue);
            } else {
                this.selectedValue = null;
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
                const option = this.options.find(o => String(o.value) === String(this.selectedValue));
                this.selectedLabel = option ? option.label : '';
                this.selectedColor = option ? option.color : null;
            } else {
                this.selectedLabel = '';
                this.selectedColor = null;
            }
        },

        get filteredOptions() {
            if (!this.searchQuery.trim()) return this.options;
            const q = this.searchQuery.toLowerCase().trim();
            return this.options.filter(o => o.label.toLowerCase().includes(q));
        },

        toggle() {
            this.open ? this.close() : this.openDropdown();
        },

        openDropdown() {
            this.open = true;
            this.searchQuery = '';
            this.highlightedIndex = this.selectedValue
                ? this.filteredOptions.findIndex(o => String(o.value) === String(this.selectedValue))
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

        select(option) {
            this.selectedValue = String(option.value);
            this.selectedLabel = option.label;
            this.selectedColor = option.color;
            this.close();
            this.$dispatch('nomenclature-selected', { value: option.value, label: option.label, color: option.color });
        },

        clear() {
            this.selectedValue = null;
            this.selectedLabel = '';
            this.selectedColor = null;
            this.$dispatch('nomenclature-selected', { value: null, label: null, color: null });
        },

        isSelected(option) {
            return String(option.value) === String(this.selectedValue);
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
                    this.highlightedIndex = Math.min(this.highlightedIndex + 1, this.filteredOptions.length - 1);
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
                    } else if (this.highlightedIndex >= 0 && this.filteredOptions[this.highlightedIndex]) {
                        this.select(this.filteredOptions[this.highlightedIndex]);
                    }
                    break;
                case 'Escape':
                    e.preventDefault();
                    this.close();
                    break;
            }
        },

        openSlideOver() {
            this.close();
            this.isEditMode = false;
            this.editingOption = null;
            this.slideOverOpen = true;
            this.formErrors = [];
            this.newLabel = '';
            this.newColor = '#3b82f6';
        },

        editOption(option) {
            this.close();
            this.isEditMode = true;
            this.editingOption = option;
            this.slideOverOpen = true;
            this.formErrors = [];
            this.newLabel = option.label;
            this.newColor = option.color || '#3b82f6';
        },

        closeSlideOver() {
            this.slideOverOpen = false;
            this.formErrors = [];
            this.saving = false;
            this.newLabel = '';
            this.newColor = '#3b82f6';
            this.isEditMode = false;
            this.editingOption = null;
        },

        async saveOption() {
            if (this.saving) return;

            this.formErrors = [];

            if (!this.newLabel || !this.newLabel.trim()) {
                this.formErrors.push('Please enter an option name.');
                return;
            }

            this.saving = true;

            const formData = {
                category: this.category,
                label: this.newLabel.trim(),
            };

            if (this.hasColors) {
                formData.color = this.newColor;
            }

            try {
                let url, method;
                if (this.isEditMode && this.editingOption) {
                    url = this.baseUrl + '/' + this.editingOption.id;
                    method = 'PATCH';
                } else {
                    url = this.storeUrl;
                    method = 'POST';
                }

                const response = await fetch(url, {
                    method: method,
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
                    if (data.errors) {
                        this.formErrors = [];
                        for (const [field, messages] of Object.entries(data.errors)) {
                            this.formErrors.push(messages[0]);
                        }
                    } else if (data.message) {
                        this.formErrors = [data.message];
                    } else {
                        this.formErrors = ['An error occurred. Please try again.'];
                    }
                    return;
                }

                if (this.isEditMode) {
                    const index = this.options.findIndex(o => o.id === this.editingOption.id);
                    if (index !== -1) {
                        this.options[index] = {
                            id: data.setting.id,
                            value: data.setting.value,
                            label: data.setting.label,
                            color: data.setting.color_class || null,
                        };
                    }
                    if (this.selectedValue === String(data.setting.value)) {
                        this.selectedLabel = data.setting.label;
                        this.selectedColor = data.setting.color_class || null;
                    }
                    this.$dispatch('nomenclature-updated', { option: data.setting });
                } else {
                    const newOptionData = {
                        id: data.setting.id,
                        value: data.setting.value,
                        label: data.setting.label,
                        color: data.setting.color_class || null,
                    };
                    this.options.unshift(newOptionData);
                    this.selectedValue = String(data.setting.value);
                    this.selectedLabel = data.setting.label;
                    this.selectedColor = data.setting.color_class || null;
                    this.$dispatch('nomenclature-created', { option: data.setting });
                    this.$dispatch('nomenclature-selected', { value: data.setting.value, label: data.setting.label, color: data.setting.color_class });
                }

                this.closeSlideOver();

            } catch (error) {
                console.error('Error saving option:', error);
                this.formErrors = ['An error occurred. Please try again.'];
            } finally {
                this.saving = false;
            }
        },

        async deleteOption(option) {
            if (!confirm('Delete ' + String.fromCharCode(34) + option.label + String.fromCharCode(34) + '?')) return;

            try {
                const response = await fetch(this.baseUrl + '/' + option.id, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    alert(data.message || 'Failed to delete option.');
                    return;
                }

                this.options = this.options.filter(o => o.id !== option.id);

                if (this.selectedValue === String(option.value)) {
                    this.selectedValue = null;
                    this.selectedLabel = '';
                    this.selectedColor = null;
                }

                this.$dispatch('nomenclature-deleted', { option: option });

            } catch (error) {
                console.error('Error deleting option:', error);
                alert('An error occurred. Please try again.');
            }
        }
    }"
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
               hover:border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none
               transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed
               flex items-center justify-between"
        :class="{ 'border-blue-500 ring-2 ring-blue-500 ring-offset-2': open }"
    >
        <span class="flex items-center gap-2 truncate">
            <span
                x-show="selectedColor"
                class="w-2 h-2 rounded-full flex-shrink-0"
                :style="'background-color: ' + selectedColor"
            ></span>
            <span
                x-text="selectedLabel || placeholder"
                :class="{ 'text-slate-500': !selectedValue }"
            ></span>
        </span>
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
            {{-- Add New Option --}}
            <div
                @click="openSlideOver()"
                @mouseenter="highlightedIndex = -2"
                class="px-3 py-2 text-sm cursor-pointer hover:bg-emerald-50 flex items-center gap-2 border-b border-slate-100"
                :class="{ 'bg-emerald-50': highlightedIndex === -2 }"
            >
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="text-emerald-700 font-medium">{{ __('Add new option') }}</span>
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

            {{-- Options --}}
            <template x-for="(option, index) in filteredOptions" :key="option.id || option.value">
                <div
                    @click="select(option)"
                    @mouseenter="highlightedIndex = index"
                    class="px-3 py-2 cursor-pointer transition-colors group"
                    :class="{
                        'bg-slate-100': highlightedIndex === index,
                        'bg-slate-50': isSelected(option),
                        'hover:bg-slate-50': highlightedIndex !== index && !isSelected(option)
                    }"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <span
                                x-show="option.color"
                                class="w-2 h-2 rounded-full flex-shrink-0"
                                :style="'background-color: ' + option.color"
                            ></span>
                            <span class="text-sm text-slate-700 truncate" x-text="option.label"></span>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            {{-- Checkmark for selected --}}
                            <svg x-show="isSelected(option)" class="w-4 h-4 text-slate-900 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{-- Divider (only show when checkmark is visible) --}}
                            <span x-show="isSelected(option)" class="text-slate-300 mx-0.5">|</span>
                            {{-- Edit/Delete buttons --}}
                            <button
                                type="button"
                                @click.stop="editOption(option)"
                                class="p-1 rounded hover:bg-slate-200 text-slate-400 hover:text-slate-600"
                                title="{{ __('Edit') }}"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button
                                type="button"
                                @click.stop="deleteOption(option)"
                                class="p-1 rounded hover:bg-red-100 text-slate-400 hover:text-red-600"
                                title="{{ __('Delete') }}"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- No results --}}
            <div x-show="filteredOptions.length === 0 && searchQuery.length > 0" class="px-3 py-6 text-center">
                <p class="text-sm text-slate-500 mb-2">{{ __('No options found') }}</p>
                <button
                    type="button"
                    @click="openSlideOver()"
                    class="text-sm text-emerald-600 hover:text-emerald-700 font-medium"
                >
                    {{ __('Create new option') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Slide-Over Modal for Creating/Editing Option --}}
    <template x-teleport="body">
        <div
            x-show="slideOverOpen"
            x-cloak
            class="fixed inset-0 z-[10000] overflow-hidden"
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
                    class="w-screen max-w-md"
                >
                    <div class="flex h-full flex-col bg-white shadow-xl">
                        {{-- Header --}}
                        <div class="bg-slate-50 px-4 py-6 sm:px-6 border-b border-slate-200 flex-shrink-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900" x-text="isEditMode ? '{{ __('Edit Option') }}' : '{{ __('Add New Option') }}'"></h2>
                                    <p class="mt-1 text-sm text-slate-500">
                                        <template x-if="isEditMode">
                                            <span>{{ __('Editing') }}: <strong x-text="editingOption?.label"></strong></span>
                                        </template>
                                        <template x-if="!isEditMode">
                                            <span>{{ __('Create a new option and automatically select it.') }}</span>
                                        </template>
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
                        <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
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

                            {{-- Name Field --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    {{ __('Name') }} <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    x-model="newLabel"
                                    @keydown.enter.prevent="saveOption()"
                                    class="w-full h-10 px-3 text-sm border border-slate-200 rounded-md
                                           focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                                    placeholder="{{ __('Enter option name...') }}"
                                >
                            </div>

                            {{-- Color Field --}}
                            @if($hasColors)
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    {{ __('Color') }}
                                </label>
                                <div class="flex items-center gap-3">
                                    <input
                                        type="color"
                                        x-model="newColor"
                                        class="w-12 h-10 border border-slate-200 rounded-md cursor-pointer"
                                    >
                                    <div
                                        class="flex-1 h-10 rounded-md border border-slate-200 flex items-center px-3"
                                        :style="'background-color: ' + newColor + '20'"
                                    >
                                        <span class="text-sm" :style="'color: ' + newColor" x-text="newLabel || '{{ __('Preview') }}'"></span>
                                    </div>
                                </div>
                            </div>
                            @endif
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
                                    @click="saveOption()"
                                    :disabled="saving"
                                    class="px-4 py-2 text-sm font-medium text-white bg-slate-900 rounded-md
                                           hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2
                                           disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                >
                                    <svg x-show="saving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="saving ? '{{ __('Saving...') }}' : (isEditMode ? '{{ __('Save') }}' : '{{ __('Create') }}')"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
