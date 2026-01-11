@props([
    'selected' => 'current_year',
    'widgetId' => 'widget',
])

@php
    $periods = [
        'current_month' => __('Luna curentă'),
        'last_month' => __('Luna trecută'),
        'current_year' => __('Anul curent'),
        'last_year' => __('Anul trecut'),
        'last_30_days' => __('Ultimele 30 zile'),
        'last_12_months' => __('Ultimele 12 luni'),
        'custom' => __('Altă perioadă'),
    ];
@endphp

<div
    x-data="{
        open: false,
        period: '{{ $selected }}',
        customFrom: '',
        customTo: '',
        periodLabel: '{{ $periods[$selected] ?? $periods['current_year'] }}',
        dateRange: '',
        periods: {{ Js::from($periods) }},
        dropdownStyle: {},

        init() {
            this.customFrom = new Date().toISOString().split('T')[0].replace(/-/g, '-');
            this.customTo = new Date().toISOString().split('T')[0].replace(/-/g, '-');
            this.updateDateRange();
        },

        positionDropdown() {
            const button = this.$refs.triggerButton;
            if (!button) return;
            const rect = button.getBoundingClientRect();
            this.dropdownStyle = {
                position: 'fixed',
                top: (rect.bottom + 8) + 'px',
                left: rect.left + 'px',
                zIndex: 9999
            };
        },

        selectPeriod(key) {
            this.period = key;
            this.periodLabel = this.periods[key];
            if (key !== 'custom') {
                this.open = false;
                this.$dispatch('period-changed-{{ $widgetId }}', {
                    period: this.period,
                    from: null,
                    to: null
                });
            }
            this.updateDateRange();
        },

        applyCustom() {
            if (this.customFrom && this.customTo) {
                this.open = false;
                this.$dispatch('period-changed-{{ $widgetId }}', {
                    period: 'custom',
                    from: this.customFrom,
                    to: this.customTo
                });
            }
        },

        updateDateRange() {
            const now = new Date();
            let from, to;

            switch(this.period) {
                case 'current_month':
                    from = new Date(now.getFullYear(), now.getMonth(), 1);
                    to = now;
                    break;
                case 'last_month':
                    from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    to = new Date(now.getFullYear(), now.getMonth(), 0);
                    break;
                case 'current_year':
                    from = new Date(now.getFullYear(), 0, 1);
                    to = now;
                    break;
                case 'last_year':
                    from = new Date(now.getFullYear() - 1, 0, 1);
                    to = new Date(now.getFullYear() - 1, 11, 31);
                    break;
                case 'last_30_days':
                    from = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
                    to = now;
                    break;
                case 'last_12_months':
                    from = new Date(now.getFullYear(), now.getMonth() - 12, now.getDate());
                    to = now;
                    break;
                case 'custom':
                    if (this.customFrom && this.customTo) {
                        from = new Date(this.customFrom);
                        to = new Date(this.customTo);
                    } else {
                        from = new Date(now.getFullYear(), 0, 1);
                        to = now;
                    }
                    break;
                default:
                    from = new Date(now.getFullYear(), 0, 1);
                    to = now;
            }

            const formatDate = (d) => {
                return d.toLocaleDateString('ro-RO', { day: 'numeric', month: 'short', year: 'numeric' });
            };

            this.dateRange = formatDate(from) + ' - ' + formatDate(to);
        }
    }"
    class="relative"
    @click.outside="open = false"
>
    {{-- Trigger Button --}}
    <button
        type="button"
        x-ref="triggerButton"
        @click="positionDropdown(); open = !open"
        class="flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 transition-colors"
    >
        <span x-text="periodLabel" class="font-medium"></span>
        <svg class="w-4 h-4 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
        <span class="text-xs text-slate-400 hidden sm:inline" x-text="dateRange"></span>
    </button>

    {{-- Dropdown Panel (fixed position to escape container) --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :style="dropdownStyle"
        class="w-72 bg-white rounded-lg shadow-xl border border-slate-200"
        x-cloak
    >
        <div class="p-3">
            {{-- Period Options --}}
            <div class="space-y-1">
                <template x-for="(label, key) in periods" :key="key">
                    <label
                        class="flex items-center gap-3 px-3 py-2 rounded-md cursor-pointer hover:bg-slate-50 transition-colors"
                        :class="period === key && 'bg-slate-100'"
                    >
                        <input
                            type="radio"
                            :name="'period-' + '{{ $widgetId }}'"
                            :value="key"
                            x-model="period"
                            @change="selectPeriod(key)"
                            class="w-4 h-4 text-slate-900 border-slate-300 focus:ring-slate-500"
                        >
                        <span class="text-sm text-slate-700" x-text="label"></span>
                    </label>
                </template>
            </div>

            {{-- Custom Date Range --}}
            <div
                x-show="period === 'custom'"
                x-transition
                class="mt-3 pt-3 border-t border-slate-200"
            >
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('De la') }}</label>
                        <input
                            type="date"
                            x-model="customFrom"
                            @change="updateDateRange()"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-md focus:ring-2 focus:ring-slate-500 focus:border-slate-500"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Pana la') }}</label>
                        <input
                            type="date"
                            x-model="customTo"
                            @change="updateDateRange()"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-md focus:ring-2 focus:ring-slate-500 focus:border-slate-500"
                        >
                    </div>
                </div>
                <button
                    type="button"
                    @click="applyCustom()"
                    class="mt-3 w-full px-4 py-2 text-sm font-medium text-white bg-slate-900 rounded-md hover:bg-slate-800 transition-colors"
                >
                    {{ __('Aplică') }}
                </button>
            </div>
        </div>
    </div>
</div>
