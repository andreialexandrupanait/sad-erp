@props([
    'categories' => [],
    'showStatusFilters' => true,
    'defaultExpanded' => true,
])

<div 
    x-data="filterBar({ defaultExpanded: {{ $defaultExpanded ? 'true' : 'false' }} })"
    class="filter-bar bg-white rounded-lg border border-slate-200 mb-4 overflow-hidden"
>
    {{-- Filter header with toggle --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span class="text-sm font-medium text-slate-700">{{ __('Filters') }}</span>
            </div>
            
            {{-- Active filters count --}}
            <span 
                x-show="activeFilterCount > 0"
                x-cloak
                class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full"
                x-text="activeFilterCount + ' {{ __('active') }}'"
            ></span>
        </div>
        
        <div class="flex items-center gap-3">
            {{-- Clear all button --}}
            <button 
                type="button"
                x-show="activeFilterCount > 0"
                x-cloak
                @click="clearAllFilters()"
                class="text-xs text-slate-500 hover:text-slate-700 font-medium"
            >
                {{ __('Clear all') }}
            </button>
            
            {{-- Toggle expand/collapse --}}
            <button 
                type="button"
                @click="expanded = !expanded"
                class="p-1 text-slate-400 hover:text-slate-600 rounded transition-colors"
            >
                <svg 
                    class="w-5 h-5 transition-transform duration-200" 
                    :class="{ 'rotate-180': expanded }"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
    </div>
    
    {{-- Filter controls --}}
    <div 
        x-show="expanded" 
        x-collapse
        class="p-4"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Search text --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide">
                    {{ __('Search') }}
                </label>
                <div class="relative">
                    <input 
                        type="text"
                        x-model.debounce.300ms="filters.search"
                        placeholder="{{ __('Description, amount...') }}"
                        class="w-full h-9 pl-9 pr-3 text-sm border border-slate-200 rounded-md
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none
                               placeholder:text-slate-400"
                    >
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button 
                        x-show="filters.search"
                        @click="filters.search = ''"
                        type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            {{-- Date range --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide">
                    {{ __('Date range') }}
                </label>
                <div class="flex gap-2">
                    <input 
                        type="date" 
                        x-model="filters.dateFrom"
                        class="flex-1 h-9 px-2 text-sm border border-slate-200 rounded-md
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                    >
                    <span class="flex items-center text-slate-400">–</span>
                    <input 
                        type="date" 
                        x-model="filters.dateTo"
                        class="flex-1 h-9 px-2 text-sm border border-slate-200 rounded-md
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                    >
                </div>
            </div>
            
            {{-- Category filter --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide">
                    {{ __('Category') }}
                </label>
                <x-category-combobox 
                    :categories="$categories"
                    name="filter_category"
                    x-model="filters.categoryId"
                    :allow-empty="true"
                    placeholder="{{ __('All categories') }}"
                    width="100%"
                />
            </div>
            
            {{-- Amount range --}}
            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide">
                    {{ __('Amount') }}
                </label>
                <div class="flex gap-2">
                    <input 
                        type="number" 
                        x-model.number="filters.amountMin"
                        placeholder="{{ __('Min') }}"
                        step="0.01"
                        class="flex-1 h-9 px-2 text-sm border border-slate-200 rounded-md
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none
                               placeholder:text-slate-400"
                    >
                    <span class="flex items-center text-slate-400">–</span>
                    <input 
                        type="number" 
                        x-model.number="filters.amountMax"
                        placeholder="{{ __('Max') }}"
                        step="0.01"
                        class="flex-1 h-9 px-2 text-sm border border-slate-200 rounded-md
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none
                               placeholder:text-slate-400"
                    >
                </div>
            </div>
        </div>
        
        {{-- Status quick filters --}}
        @if($showStatusFilters)
        <div class="mt-4 pt-4 border-t border-slate-100">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('Status') }}:</span>
                <div class="flex flex-wrap gap-2">
                    <button 
                        type="button"
                        @click="toggleStatusFilter('new')"
                        :class="filters.status.includes('new') 
                            ? 'bg-emerald-100 text-emerald-700 border-emerald-300' 
                            : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full border transition-colors"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="filters.status.includes('new') ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                        {{ __('New') }}
                        <span x-show="statusCounts.new > 0" class="text-[10px] opacity-70" x-text="'(' + statusCounts.new + ')'"></span>
                    </button>
                    
                    <button 
                        type="button"
                        @click="toggleStatusFilter('imported')"
                        :class="filters.status.includes('imported') 
                            ? 'bg-blue-100 text-blue-700 border-blue-300' 
                            : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full border transition-colors"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="filters.status.includes('imported') ? 'bg-blue-500' : 'bg-slate-400'"></span>
                        {{ __('Imported') }}
                        <span x-show="statusCounts.imported > 0" class="text-[10px] opacity-70" x-text="'(' + statusCounts.imported + ')'"></span>
                    </button>
                    
                    <button 
                        type="button"
                        @click="toggleStatusFilter('duplicate')"
                        :class="filters.status.includes('duplicate') 
                            ? 'bg-amber-100 text-amber-700 border-amber-300' 
                            : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full border transition-colors"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="filters.status.includes('duplicate') ? 'bg-amber-500' : 'bg-slate-400'"></span>
                        {{ __('Duplicate') }}
                        <span x-show="statusCounts.duplicate > 0" class="text-[10px] opacity-70" x-text="'(' + statusCounts.duplicate + ')'"></span>
                    </button>
                    
                    <button 
                        type="button"
                        @click="toggleStatusFilter('skipped')"
                        :class="filters.status.includes('skipped') 
                            ? 'bg-slate-200 text-slate-700 border-slate-400' 
                            : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full border transition-colors"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="filters.status.includes('skipped') ? 'bg-slate-500' : 'bg-slate-400'"></span>
                        {{ __('Skipped') }}
                        <span x-show="statusCounts.skipped > 0" class="text-[10px] opacity-70" x-text="'(' + statusCounts.skipped + ')'"></span>
                    </button>
                </div>
            </div>
        </div>
        @endif
        
        {{-- Results summary --}}
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
            <p class="text-sm text-slate-600">
                <span class="font-medium" x-text="filteredCount"></span> 
                {{ __('of') }} 
                <span x-text="totalCount"></span> 
                {{ __('transactions') }}
            </p>
            
            {{-- Quick presets --}}
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">{{ __('Quick:') }}</span>
                <button 
                    type="button"
                    @click="applyPreset('uncategorized')"
                    class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                >
                    {{ __('Uncategorized') }}
                </button>
                <span class="text-slate-300">|</span>
                <button 
                    type="button"
                    @click="applyPreset('high-value')"
                    class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                >
                    {{ __('High value') }}
                </button>
            </div>
        </div>
    </div>
</div>
