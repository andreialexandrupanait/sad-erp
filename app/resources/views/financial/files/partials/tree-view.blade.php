{{--
    Tree View Partial for Financial Files

    Fully independent tree navigation with Alpine.js state management.

    - Every node (year/month/category) has its own open state
    - Arrow clicks toggle expand/collapse
    - Label clicks navigate to page
    - Categories can be expanded to show files (lazy loaded via AJAX)
    - Active route only controls highlighting, NOT expand permissions
    - All nodes can be expanded/collapsed regardless of current page

    Required variables:
    - $availableYears: Collection of years
    - $allYearsSummary: Array with file counts per year/month/type
    - $year: Currently selected year (for highlighting)
    - $month: Currently selected month (nullable, for highlighting)
    - $category: Currently selected category (nullable, for highlighting)
--}}

<div class="p-4 space-y-1" x-data="fileTreeView({{ $year }}, {{ $month ?? 'null' }}, '{{ $category ?? '' }}')">
    @foreach($availableYears as $y)
        @php
            $yearTotal = collect($allYearsSummary[$y] ?? [])->sum('total');
            $isYearActive = ($year == $y && !$month);
        @endphp

        <!-- Year Node -->
        <div class="tree-node">
            <div class="flex items-center gap-1 w-full text-left px-2 py-1.5 rounded-lg hover:bg-slate-50 {{ $isYearActive ? 'bg-primary-50' : '' }}">
                <!-- Expand/Collapse Arrow - ONLY toggles, never navigates -->
                <button type="button"
                        class="tree-toggle p-0.5 hover:bg-slate-200 rounded transition-colors"
                        @click.stop.prevent="toggleYear({{ $y }})"
                        :aria-expanded="isYearOpen({{ $y }})"
                        aria-label="Expandeaza/Restrange {{ $y }}">
                    <svg class="w-4 h-4 text-slate-500 transition-transform duration-200"
                         :class="{ 'rotate-90': isYearOpen({{ $y }}) }"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- Year Link - Clicking navigates -->
                <a href="{{ route('financial.files.year', ['year' => $y]) }}"
                   class="flex items-center gap-2 flex-1 py-0.5">
                    <svg class="w-4 h-4 {{ $isYearActive ? 'text-primary-600' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    <span class="text-sm font-medium {{ $isYearActive ? 'text-primary-700' : 'text-slate-900' }}">{{ $y }}</span>
                </a>

                <!-- File Count Badge -->
                @if($yearTotal > 0)
                    <span class="text-xs text-slate-400 tabular-nums">{{ $yearTotal }}</span>
                @endif
            </div>

            <!-- Months Container - Controlled by Alpine state -->
            <div class="tree-children ml-4 mt-1 space-y-0.5"
                 x-show="isYearOpen({{ $y }})"
                 x-collapse>
                @for($m = 12; $m >= 1; $m--)
                    @php
                        $monthName = \Carbon\Carbon::create()->setMonth($m)->locale('ro')->isoFormat('MMMM');
                        $monthData = $allYearsSummary[$y][$m] ?? ['incasare' => 0, 'plata' => 0, 'extrase' => 0, 'general' => 0, 'total' => 0];
                        $monthTotal = $monthData['total'];
                        $isMonthActive = ($year == $y && $month == $m && !$category);
                    @endphp

                    @if($monthTotal > 0)
                        <!-- Month Node -->
                        <div class="tree-node">
                            <div class="flex items-center gap-1 w-full px-2 py-1 rounded-lg hover:bg-slate-50 {{ $isMonthActive ? 'bg-primary-50' : '' }}">
                                <!-- Expand/Collapse Arrow - ONLY toggles -->
                                <button type="button"
                                        class="tree-toggle p-0.5 hover:bg-slate-200 rounded transition-colors"
                                        @click.stop.prevent="toggleMonth({{ $y }}, {{ $m }})"
                                        :aria-expanded="isMonthOpen({{ $y }}, {{ $m }})">
                                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                                         :class="{ 'rotate-90': isMonthOpen({{ $y }}, {{ $m }}) }"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>

                                <!-- Month Link - Clicking navigates -->
                                <a href="{{ route('financial.files.month', ['year' => $y, 'month' => $m]) }}"
                                   class="flex items-center gap-2 flex-1 py-0.5">
                                    <svg class="w-4 h-4 {{ $isMonthActive ? 'text-primary-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                    </svg>
                                    <span class="text-sm {{ $isMonthActive ? 'font-medium text-primary-700' : 'text-slate-700' }}">{{ ucfirst($monthName) }}</span>
                                </a>

                                <span class="text-xs text-slate-400 tabular-nums">{{ $monthTotal }}</span>
                            </div>

                            <!-- Categories Container - Controlled by Alpine state -->
                            <div class="tree-children ml-5 mt-0.5 space-y-0.5"
                                 x-show="isMonthOpen({{ $y }}, {{ $m }})"
                                 x-collapse>

                                @php
                                    // Pre-define all Tailwind classes to ensure JIT compilation
                                    $catConfigs = [
                                        'incasare' => [
                                            'label' => 'Incasari',
                                            'count' => $monthData['incasare'],
                                            'hoverBg' => 'hover:bg-green-50',
                                            'activeBg' => 'bg-green-100',
                                            'buttonHoverBg' => 'hover:bg-green-100',
                                            'iconColor' => 'text-green-500',
                                            'svgColor' => 'text-green-600',
                                            'textActive' => 'font-medium text-green-800',
                                            'textNormal' => 'text-green-700',
                                            'badgeColor' => 'text-green-600',
                                        ],
                                        'plata' => [
                                            'label' => 'Plati',
                                            'count' => $monthData['plata'],
                                            'hoverBg' => 'hover:bg-red-50',
                                            'activeBg' => 'bg-red-100',
                                            'buttonHoverBg' => 'hover:bg-red-100',
                                            'iconColor' => 'text-red-500',
                                            'svgColor' => 'text-red-600',
                                            'textActive' => 'font-medium text-red-800',
                                            'textNormal' => 'text-red-700',
                                            'badgeColor' => 'text-red-600',
                                        ],
                                        'extrase' => [
                                            'label' => 'Extrase',
                                            'count' => $monthData['extrase'],
                                            'hoverBg' => 'hover:bg-blue-50',
                                            'activeBg' => 'bg-blue-100',
                                            'buttonHoverBg' => 'hover:bg-blue-100',
                                            'iconColor' => 'text-blue-500',
                                            'svgColor' => 'text-blue-600',
                                            'textActive' => 'font-medium text-blue-800',
                                            'textNormal' => 'text-blue-700',
                                            'badgeColor' => 'text-blue-600',
                                        ],
                                        'general' => [
                                            'label' => 'General',
                                            'count' => $monthData['general'],
                                            'hoverBg' => 'hover:bg-slate-50',
                                            'activeBg' => 'bg-slate-100',
                                            'buttonHoverBg' => 'hover:bg-slate-100',
                                            'iconColor' => 'text-slate-500',
                                            'svgColor' => 'text-slate-600',
                                            'textActive' => 'font-medium text-slate-800',
                                            'textNormal' => 'text-slate-700',
                                            'badgeColor' => 'text-slate-600',
                                        ],
                                    ];
                                @endphp

                                @foreach($catConfigs as $catKey => $catConfig)
                                    @if($catConfig['count'] > 0)
                                        @php
                                            $isActive = ($year == $y && $month == $m && $category == $catKey);
                                        @endphp

                                        <!-- Category Node (expandable) -->
                                        <div class="tree-node" x-data="categoryFiles({{ $y }}, {{ $m }}, '{{ $catKey }}')">
                                            <div class="flex items-center gap-1 w-full px-2 py-1 rounded-lg {{ $catConfig['hoverBg'] }} {{ $isActive ? $catConfig['activeBg'] : '' }}">
                                                <!-- Expand/Collapse Arrow -->
                                                <button type="button"
                                                        class="tree-toggle p-0.5 {{ $catConfig['buttonHoverBg'] }} rounded transition-colors"
                                                        @click.stop.prevent="toggle()">
                                                    <svg class="w-3 h-3 {{ $catConfig['iconColor'] }} transition-transform duration-200"
                                                         :class="{ 'rotate-90': open }"
                                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </button>

                                                <!-- Category Link -->
                                                <a href="{{ route('financial.files.category', ['year' => $y, 'month' => $m, 'category' => $catKey]) }}"
                                                   class="flex items-center gap-2 flex-1 py-0.5">
                                                    <svg class="w-4 h-4 {{ $catConfig['svgColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <span class="text-sm {{ $isActive ? $catConfig['textActive'] : $catConfig['textNormal'] }}">{{ $catConfig['label'] }}</span>
                                                </a>

                                                <span class="text-xs font-medium {{ $catConfig['badgeColor'] }} tabular-nums">{{ $catConfig['count'] }}</span>
                                            </div>

                                            <!-- Files List (lazy loaded) -->
                                            <div class="tree-children ml-5 mt-0.5 space-y-0.5"
                                                 x-show="open"
                                                 x-collapse>

                                                <!-- Loading State -->
                                                <div x-show="loading" class="px-2 py-1 text-xs text-slate-400">
                                                    <span class="inline-block animate-pulse">Se incarca...</span>
                                                </div>

                                                <!-- Files -->
                                                <template x-for="file in files" :key="file.id">
                                                    <a :href="file.show_url"
                                                       target="_blank"
                                                       class="flex items-center gap-2 px-2 py-1 rounded hover:bg-slate-100 transition-colors group">
                                                        <span class="text-sm flex-shrink-0" x-text="file.icon"></span>
                                                        <span class="text-xs text-slate-600 truncate flex-1" x-text="file.name" :title="file.full_name"></span>
                                                        <svg class="w-3 h-3 text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                        </svg>
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endfor
            </div>
        </div>
    @endforeach
</div>

<script>
/**
 * File Tree View Alpine.js Component
 *
 * Provides fully independent expand/collapse state for each node.
 * - All years can be expanded/collapsed regardless of current page
 * - All months can be expanded/collapsed regardless of current page
 * - Categories can be expanded to show files (lazy loaded)
 * - Active route only controls highlighting, NOT expand permissions
 * - State persists in localStorage across page navigations
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('fileTreeView', (activeYear, activeMonth, activeCategory) => ({
        // State storage - maps year/month to open state
        openYears: {},
        openMonths: {},

        // Active route info (for auto-expanding active branch)
        activeYear: activeYear,
        activeMonth: activeMonth,
        activeCategory: activeCategory,

        init() {
            // Load saved state from localStorage
            this.loadState();

            // Auto-expand the active branch (but don't collapse others)
            if (this.activeYear) {
                this.openYears[this.activeYear] = true;
            }
            if (this.activeYear && this.activeMonth) {
                this.openMonths[`${this.activeYear}-${this.activeMonth}`] = true;
            }

            // Save state after initialization
            this.saveState();
        },

        // Check if a year is expanded
        isYearOpen(year) {
            return this.openYears[year] === true;
        },

        // Check if a month is expanded
        isMonthOpen(year, month) {
            return this.openMonths[`${year}-${month}`] === true;
        },

        // Toggle year expand/collapse
        toggleYear(year) {
            this.openYears[year] = !this.openYears[year];
            this.saveState();
        },

        // Toggle month expand/collapse
        toggleMonth(year, month) {
            const key = `${year}-${month}`;
            this.openMonths[key] = !this.openMonths[key];
            this.saveState();
        },

        // Save state to localStorage
        saveState() {
            try {
                localStorage.setItem('fileTreeOpenYears', JSON.stringify(this.openYears));
                localStorage.setItem('fileTreeOpenMonths', JSON.stringify(this.openMonths));
            } catch (e) {
                console.warn('Could not save tree state:', e);
            }
        },

        // Load state from localStorage
        loadState() {
            try {
                const savedYears = localStorage.getItem('fileTreeOpenYears');
                const savedMonths = localStorage.getItem('fileTreeOpenMonths');

                if (savedYears) {
                    this.openYears = JSON.parse(savedYears);
                }
                if (savedMonths) {
                    this.openMonths = JSON.parse(savedMonths);
                }
            } catch (e) {
                console.warn('Could not load tree state:', e);
                this.openYears = {};
                this.openMonths = {};
            }
        }
    }));

    // Category files component - lazy loads files when expanded
    Alpine.data('categoryFiles', (year, month, category) => ({
        open: false,
        loading: false,
        loaded: false,
        files: [],

        async toggle() {
            this.open = !this.open;

            // Load files on first expand
            if (this.open && !this.loaded) {
                await this.loadFiles();
            }
        },

        async loadFiles() {
            this.loading = true;

            try {
                const response = await fetch(`/financial/files/api/${year}/${month}/${category}`);
                if (response.ok) {
                    const data = await response.json();
                    this.files = data.files;
                    this.loaded = true;
                }
            } catch (e) {
                console.error('Failed to load files:', e);
            } finally {
                this.loading = false;
            }
        }
    }));
});
</script>
