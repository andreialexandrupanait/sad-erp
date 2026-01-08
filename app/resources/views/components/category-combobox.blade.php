@props([
    'categories',
    'selected' => null,
    'name' => 'category_id',
    'placeholder' => null,
    'allowEmpty' => true,
    'allowCreate' => true,
    'width' => '200px',
    'disabled' => false,
])

@php
    $placeholder = $placeholder ?? __('Select category...');
    // Ensure categories is a plain array for JS
    $categoriesArray = collect($categories)->map(function($cat) {
        return [
            'id' => $cat->id,
            'label' => $cat->label,
            'value' => $cat->value ?? $cat->label,
            'children' => $cat->children ? $cat->children->map(function($child) {
                return [
                    'id' => $child->id,
                    'label' => $child->label,
                    'value' => $child->value ?? $child->label,
                ];
            })->values()->all() : []
        ];
    })->values()->all();
@endphp

<div
    x-data="categoryCombobox({
        categories: {{ Js::from($categoriesArray) }},
        selected: {{ Js::from($selected) }},
        name: '{{ $name }}',
        allowEmpty: {{ $allowEmpty ? 'true' : 'false' }},
        allowCreate: {{ $allowCreate ? 'true' : 'false' }},
        placeholder: '{{ $placeholder }}'
    })"
    x-init="init()"
    @category-created.window="onCategoryCreated($event)"
    class="relative"
    style="width: {{ $width }};"
    {{ $disabled ? 'data-disabled' : '' }}
>
    {{-- Hidden input for form submission --}}
    <input type="hidden" :name="name" :value="selectedId || ''">

    {{-- Trigger Button --}}
    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        @keydown="onKeydown($event)"
        :disabled="{{ $disabled ? 'true' : 'false' }}"
        class="w-full h-9 px-3 pr-8 text-left text-sm border border-slate-200 rounded-lg bg-white
               hover:border-slate-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none
               transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed
               flex items-center justify-between"
        :class="{ 'border-blue-500 ring-1 ring-blue-500': open }"
    >
        <span
            x-text="selectedLabel || '{{ $placeholder }}'"
            :class="{ 'text-slate-400': !selectedId }"
            class="truncate"
        ></span>
        <svg class="w-4 h-4 text-slate-400 flex-shrink-0 ml-2 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        class="fixed z-50 w-72 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden"
        style="min-width: 280px;"
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
                    @input="onSearch($event.target.value)"
                    @keydown="onKeydown($event)"
                    placeholder="{{ __('Search categories...') }}"
                    class="w-full h-9 pl-9 pr-8 text-sm border border-slate-200 rounded-lg bg-slate-50
                           focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                >
                <button
                    type="button"
                    x-show="searchQuery"
                    @click="searchQuery = ''; $refs.searchInput.focus()"
                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600 rounded"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Options List --}}
        <div x-ref="optionsList" class="max-h-56 overflow-y-auto py-1" x-show="!showCreateForm">
            {{-- Empty option --}}
            @if($allowEmpty)
            <div
                @click="clear(); close()"
                @mouseenter="highlightedIndex = -1"
                :data-highlighted="highlightedIndex === -1"
                class="px-3 py-2 text-sm text-slate-500 cursor-pointer hover:bg-slate-50 flex items-center justify-between"
                :class="{ 'bg-slate-100': highlightedIndex === -1 }"
            >
                <span class="italic">-- {{ __('No category') }} --</span>
                <svg x-show="!selectedId" class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            @endif

            {{-- Category options --}}
            <template x-for="(item, index) in filteredList" :key="item.id">
                <div
                    @click="select(item)"
                    @mouseenter="highlightedIndex = index"
                    :data-highlighted="isHighlighted(index)"
                    class="px-3 py-2 cursor-pointer transition-colors"
                    :class="{
                        'bg-blue-50': isHighlighted(index),
                        'bg-blue-100': isSelected(item),
                        'hover:bg-slate-50': !isHighlighted(index) && !isSelected(item),
                        'pl-6': item.depth === 1
                    }"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm" :class="{ 'font-semibold text-slate-800': item.isParent, 'text-slate-600': !item.isParent }">
                            <span x-show="item.isParent" class="text-slate-400 mr-1">■</span>
                            <span x-show="!item.isParent" class="text-slate-300 mr-1">└</span>
                            <span x-text="item.label"></span>
                        </span>
                        <svg x-show="isSelected(item)" class="w-4 h-4 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </template>

            {{-- No results --}}
            <div x-show="filteredList.length === 0 && searchQuery.length > 0" class="px-3 py-6 text-center">
                <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-slate-500">{{ __('No categories found') }}</p>
            </div>
        </div>

        {{-- Create Form --}}
        <div x-show="showCreateForm" x-cloak class="p-3 border-t border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-semibold text-slate-800">
                    <span x-show="createMode === 'category'">{{ __('New Category') }}</span>
                    <span x-show="createMode === 'subcategory'">{{ __('New Subcategory') }}</span>
                </span>
                <button type="button" @click="showCreateForm = false" class="p-1 text-slate-400 hover:text-slate-600 rounded">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Parent selector (for subcategory) --}}
            <div x-show="createMode === 'subcategory'" class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Parent Category') }}</label>
                <select x-model="selectedParentId" class="w-full h-9 px-3 text-sm border border-slate-200 rounded-lg bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">{{ __('Select parent...') }}</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.label"></option>
                    </template>
                </select>
            </div>

            {{-- Name input --}}
            <div class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Category Name') }}</label>
                <input
                    type="text"
                    x-ref="newCategoryInput"
                    x-model="newCategoryName"
                    @keydown.enter.prevent="createCategory()"
                    placeholder="{{ __('Enter name...') }}"
                    class="w-full h-9 px-3 text-sm border border-slate-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
            </div>

            {{-- Submit button --}}
            <button
                type="button"
                @click="createCategory()"
                :disabled="newCategoryName.trim().length < 2 || saving || (createMode === 'subcategory' && !selectedParentId)"
                class="w-full h-9 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg
                       disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2"
            >
                <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-show="!saving">{{ __('Create') }}</span>
                <span x-show="saving">{{ __('Creating...') }}</span>
            </button>
        </div>

        {{-- Footer Actions --}}
        @if($allowCreate)
        <div x-show="!showCreateForm" class="border-t border-slate-100 p-2 flex gap-2">
            <button
                type="button"
                @click="showCreate('category')"
                class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-slate-600
                       hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('Category') }}
            </button>
            <button
                type="button"
                @click="showCreate('subcategory')"
                class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-slate-600
                       hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('Subcategory') }}
            </button>
        </div>
        @endif
    </div>
</div>
