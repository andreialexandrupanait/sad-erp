@props([
    'options' => [],
    'selected' => null,
    'name' => 'select',
    'placeholder' => null,
    'searchPlaceholder' => null,
    'allowEmpty' => true,
    'emptyLabel' => null,
    'disabled' => false,
    'prependOptions' => [], // Custom options to add at the beginning (e.g., [['value' => 'none', 'label' => 'No Client']])
])

@php
    $placeholder = $placeholder ?? __('Select...');
    $searchPlaceholder = $searchPlaceholder ?? __('Search...');
    $emptyLabel = $emptyLabel ?? __('-- None --');

    // Convert options to array format for JS
    $optionsArray = collect($options)->map(function($option) {
        // Handle both object and array formats
        if (is_object($option)) {
            return [
                'value' => $option->id ?? $option->value ?? '',
                'label' => $option->display_name ?? $option->name ?? $option->label ?? '',
            ];
        }
        return [
            'value' => $option['value'] ?? $option['id'] ?? '',
            'label' => $option['label'] ?? $option['name'] ?? $option['display_name'] ?? '',
        ];
    })->values()->all();

    // Prepend custom options if provided
    if (!empty($prependOptions)) {
        $optionsArray = array_merge($prependOptions, $optionsArray);
    }
@endphp

<div
    x-data="searchableSelect({
        options: {{ Js::from($optionsArray) }},
        selected: {{ Js::from($selected) }},
        name: '{{ $name }}',
        allowEmpty: {{ $allowEmpty ? 'true' : 'false' }},
        placeholder: '{{ $placeholder }}',
        emptyLabel: '{{ $emptyLabel }}'
    })"
    class="relative"
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
               hover:border-slate-300 focus:border-slate-900 focus:ring-2 focus:ring-slate-950 focus:ring-offset-2 focus:outline-none
               transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed
               flex items-center justify-between"
        :class="{ 'border-slate-900 ring-2 ring-slate-950 ring-offset-2': open }"
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
            <template x-for="(option, index) in filteredOptions" :key="option.value">
                <div
                    @click="select(option)"
                    @mouseenter="highlightedIndex = index"
                    class="px-3 py-2 cursor-pointer transition-colors"
                    :class="{
                        'bg-slate-100': highlightedIndex === index,
                        'bg-slate-50': isSelected(option),
                        'hover:bg-slate-50': highlightedIndex !== index && !isSelected(option)
                    }"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-700" x-text="option.label"></span>
                        <svg x-show="isSelected(option)" class="w-4 h-4 text-slate-900 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </template>

            {{-- No results --}}
            <div x-show="filteredOptions.length === 0 && searchQuery.length > 0" class="px-3 py-6 text-center">
                <p class="text-sm text-slate-500">{{ __('No results found') }}</p>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('searchableSelect', (config = {}) => ({
        options: config.options || [],
        name: config.name || 'select',
        allowEmpty: config.allowEmpty !== false,
        placeholder: config.placeholder || 'Select...',
        emptyLabel: config.emptyLabel || '-- None --',

        selectedValue: config.selected || null,
        selectedLabel: '',
        searchQuery: '',
        open: false,
        highlightedIndex: -1,

        // Cached computed values for memoization
        _cachedFilteredOptions: null,
        _lastSearchQuery: null,

        // Store listener references for cleanup
        _scrollListener: null,
        _resizeListener: null,

        init() {
            // Convert selectedValue to string for comparison
            if (this.selectedValue !== null) {
                this.selectedValue = String(this.selectedValue);
            }
            this.updateSelectedLabel();

            // Reposition on scroll/resize - store references for cleanup
            this._scrollListener = () => {
                if (this.open) this.positionDropdown();
            };
            this._resizeListener = () => {
                if (this.open) this.positionDropdown();
            };

            window.addEventListener('scroll', this._scrollListener, true);
            window.addEventListener('resize', this._resizeListener);

            // Cleanup listeners when component is destroyed
            this.$cleanup = () => {
                window.removeEventListener('scroll', this._scrollListener, true);
                window.removeEventListener('resize', this._resizeListener);
            };
        },

        destroy() {
            if (this.$cleanup) this.$cleanup();
        },

        updateSelectedLabel() {
            if (this.selectedValue) {
                const option = this.options.find(o => String(o.value) === String(this.selectedValue));
                this.selectedLabel = option ? option.label : '';
            } else {
                this.selectedLabel = '';
            }
        },

        get filteredOptions() {
            // Return cached result if search query hasn't changed
            const currentQuery = this.searchQuery.trim();
            if (this._lastSearchQuery === currentQuery && this._cachedFilteredOptions !== null) {
                return this._cachedFilteredOptions;
            }

            this._lastSearchQuery = currentQuery;
            if (!currentQuery) {
                this._cachedFilteredOptions = this.options;
            } else {
                const q = currentQuery.toLowerCase();
                this._cachedFilteredOptions = this.options.filter(o => o.label.toLowerCase().includes(q));
            }
            return this._cachedFilteredOptions;
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
            this.close();
        },

        clear() {
            this.selectedValue = null;
            this.selectedLabel = '';
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
                    this.scrollToHighlighted();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.highlightedIndex = Math.max(this.highlightedIndex - 1, this.allowEmpty ? -1 : 0);
                    this.scrollToHighlighted();
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (this.highlightedIndex === -1 && this.allowEmpty) {
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

        scrollToHighlighted() {
            this.$nextTick(() => {
                const list = this.$refs.optionsList;
                const highlighted = list?.querySelector('[data-highlighted="true"]');
                if (highlighted) {
                    highlighted.scrollIntoView({ block: 'nearest' });
                }
            });
        }
    }));
});
</script>
@endpush
@endonce
