<x-app-layout>
    <x-slot name="pageTitle">{{ __('Contracts') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('contracts.create') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('New Contract') }}
        </x-ui.button>
    </x-slot>

    <div class="p-4 md:p-6 space-y-4 md:space-y-6"
         x-data="contractsPage({ stats: @js($stats) })"
         x-init="init()"
         @keydown.escape.window="clearSelection()">

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4">
            <x-widgets.metrics.stat-card
                :title="__('Active')"
                :value="$stats['active'] ?? 0"
                color="green"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>

            <x-widgets.metrics.stat-card
                :title="__('Completed')"
                :value="$stats['completed'] ?? 0"
                color="blue"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>

            <x-widgets.metrics.stat-card
                :title="__('Terminated')"
                :value="$stats['terminated'] ?? 0"
                color="red"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>

            <x-widgets.metrics.stat-card
                :title="__('Expiră curând')"
                :value="$stats['expiring_soon'] ?? 0"
                :subtitle="__('next 30 days')"
                color="orange"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>

            {{-- Active Value - Multi-currency display --}}
            <div class="rounded-xl border bg-card text-card-foreground shadow-sm border-indigo-200 bg-indigo-50">
                <div class="p-4 flex flex-col">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-indigo-600">{{ __('Active Value') }}</p>
                        <div class="p-2 rounded-full bg-indigo-100">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2 space-y-1">
                        @php
                            $activeValues = $stats['total_active_value'] ?? [];
                            $hasValues = is_array($activeValues) && count($activeValues) > 0;
                        @endphp
                        @if($hasValues)
                            @foreach($activeValues as $currency => $value)
                                <div class="flex items-baseline justify-between">
                                    <span class="text-lg font-bold text-indigo-900">{{ number_format($value, 2, ',', '.') }}</span>
                                    <span class="text-sm font-medium text-indigo-600">{{ $currency }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="text-lg font-bold text-indigo-900">0,00 RON</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Messages --}}
        @if (session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="destructive">{{ session('error') }}</x-ui.alert>
        @endif

        {{-- Search and Filters --}}
        <x-ui.card>
            <x-ui.card-content>
                <div class="flex flex-col sm:flex-row gap-3 items-center">
                    <div class="flex-1 w-full">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text"
                                   x-model="filters.q"
                                   @input.debounce.300ms="search($event.target.value)"
                                   placeholder="{{ __('Search contracts...') }}"
                                   class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-md text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Status Filter Pills --}}
        <div class="flex flex-wrap gap-2">
            <button @click="setStatusFilter('')"
                    :class="!filters.status && !filters.hasAnnexes ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('All') }}
            </button>
            <button @click="setStatusFilter('active')"
                    :class="filters.status === 'active' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-700 hover:bg-green-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('Active') }}
            </button>
            <button @click="setStatusFilter('completed')"
                    :class="filters.status === 'completed' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700 hover:bg-blue-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('Completed') }}
            </button>
            <button @click="setStatusFilter('terminated')"
                    :class="filters.status === 'terminated' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-700 hover:bg-red-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors">
                {{ __('Terminated') }}
            </button>
            <span class="text-slate-300 self-center">|</span>
            <button @click="toggleHasAnnexesFilter()"
                    :class="filters.hasAnnexes ? 'bg-purple-600 text-white' : 'bg-purple-100 text-purple-700 hover:bg-purple-200'"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('Cu anexe') }}
            </button>
        </div>

        {{-- Bulk Actions Toolbar --}}
        <div x-show="selectedIds.length > 0"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] max-w-4xl w-full mx-auto px-4"
             x-cloak>
            <div class="bg-slate-900 text-white rounded-xl shadow-2xl border border-slate-700 px-6 py-4">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-sm font-bold">
                            <span x-text="selectedIds.length"></span>
                        </div>
                        <span class="text-sm font-medium">
                            <span x-text="selectedIds.length"></span>
                            <span x-text="selectedIds.length === 1 ? '{{ __('contract') }}' : '{{ __('contracts') }}'"></span>
                            {{ __('selected') }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <x-ui.button
                            variant="outline"
                            class="!bg-slate-800 !border-slate-600 !text-white hover:!bg-slate-700"
                            @click="bulkExport()"
                            x-bind:disabled="bulkLoading"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ __('Export CSV') }}
                        </x-ui.button>

                        <x-ui.button
                            variant="outline"
                            class="!bg-slate-800 !border-slate-600 !text-white hover:!bg-slate-700"
                            @click="bulkTerminate()"
                            x-bind:disabled="bulkLoading"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            {{ __('Close Contracts') }}
                        </x-ui.button>

                        <x-ui.button
                            variant="destructive"
                            @click="bulkDelete()"
                            x-bind:disabled="bulkLoading"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            {{ __('Delete') }}
                        </x-ui.button>

                        <button
                            @click="clearSelection()"
                            class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors"
                            x-bind:disabled="bulkLoading"
                        >
                            {{ __('Deselect') }}
                        </button>
                    </div>
                </div>

                <div x-show="bulkLoading"
                     class="absolute inset-0 bg-slate-900/80 rounded-xl flex items-center justify-center">
                    <svg class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Loading --}}
        <div x-show="loading" class="flex justify-center py-8">
            <x-ui.spinner size="lg" color="blue" />
        </div>

        {{-- Contracts Table --}}
        <template x-if="!loading && contracts.length > 0">
            <x-ui.card>
                {{-- Summary Box --}}
                <div class="px-6 py-4 bg-indigo-50 border-b border-indigo-100">
                    <div class="flex items-center justify-between flex-wrap gap-3 md:gap-4">
                        <p class="text-sm text-indigo-900">
                            <span class="font-semibold" x-text="pagination.total || contracts.length"></span>
                            <span x-text="(pagination.total || contracts.length) === 1 ? '{{ __("contract") }}' : '{{ __("contracte") }}'"></span> &middot;
                            <span class="font-semibold" x-text="stats.active || 0"></span>
                            <span x-text="(stats.active || 0) === 1 ? '{{ __("activ") }}' : '{{ __("active") }}'"></span> &middot;
                            <span x-text="formatActiveValues()"></span> {{ __('valoare totală activă') }}
                        </p>
                        <template x-if="hasAnyAnnexes()">
                            <div class="flex items-center gap-3 text-xs">
                                <button @click="expandAllAnnexes()"
                                        class="text-indigo-600 hover:text-indigo-800 hover:underline font-medium">
                                    {{ __('Extinde toate anexele') }}
                                </button>
                                <span class="text-indigo-300">|</span>
                                <button @click="collapseAllAnnexes()"
                                        class="text-indigo-600 hover:text-indigo-800 hover:underline font-medium">
                                    {{ __('Restrânge toate') }}
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left font-medium text-slate-600 w-12">
                                    <x-bulk-checkbox x-model="selectAll" @change="toggleSelectAll()" />
                                </th>
                                <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Contract') }}</th>
                                <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Client') }}</th>
                                <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-left font-medium text-slate-600">{{ __('Period') }}</th>
                                <th class="px-6 py-4 text-right font-medium text-slate-600">{{ __('Value') }}</th>
                                <th class="px-6 py-4 text-center font-medium text-slate-600">{{ __('Anexe') }}</th>
                                <th class="px-6 py-4 text-right font-medium text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <template x-for="contract in contracts" :key="contract.id">
                            <tbody>
                                {{-- Contract Row --}}
                                <tr class="border-b border-slate-200 hover:bg-slate-50/50 transition-colors"
                                    :class="{
                                        'bg-blue-50 hover:bg-blue-50': selectedIds.includes(contract.id),
                                        'bg-amber-50/50': isExpiringSoon(contract)
                                    }">
                                    <td class="px-6 py-4">
                                        <input type="checkbox"
                                               :checked="selectedIds.includes(contract.id)"
                                               @change="toggleItem(contract.id)"
                                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 cursor-pointer transition-colors">
                                    </td>
                                    <td class="px-6 py-4">
                                        <a :href="'/contracts/' + contract.id" class="font-semibold text-slate-900 hover:text-indigo-600 transition-colors">
                                            <span x-text="contract.contract_number"></span>
                                        </a>
                                        <div class="text-sm text-slate-500 max-w-xs truncate" x-text="contract.title"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a x-show="contract.client" :href="'/clients/' + contract.client?.slug" class="text-slate-700 hover:text-indigo-600 transition-colors" x-text="contract.client?.name"></a>
                                        <span x-show="!contract.client" class="text-slate-400">-</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-green-100 text-green-700': contract.status === 'active',
                                                  'bg-blue-100 text-blue-700': contract.status === 'completed',
                                                  'bg-red-100 text-red-700': contract.status === 'terminated',
                                                  'bg-amber-100 text-amber-700': contract.status === 'expired',
                                                  'bg-slate-100 text-slate-700': contract.status === 'draft'
                                              }"
                                              x-text="contract.status_label">
                                        </span>
                                        <template x-if="isExpiringSoon(contract) && contract.status === 'active'">
                                            <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                                                <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ __('Expiră') }}
                                            </span>
                                        </template>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">
                                        <div class="text-sm">
                                            <span x-text="contract.start_date"></span>
                                            <template x-if="contract.end_date">
                                                <span>
                                                    <span class="text-slate-400 mx-1">&rarr;</span>
                                                    <span x-text="contract.end_date"></span>
                                                </span>
                                            </template>
                                            <span x-show="!contract.end_date" class="text-slate-400 italic">{{ __('Nedeterminat') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="font-semibold text-slate-900" x-text="formatCurrency(contract.total_value, contract.currency)"></div>
                                        <template x-if="contract.annexes && contract.annexes.length > 0 && !hasMixedCurrencyAnnexes(contract)">
                                            <div class="text-xs text-purple-600 font-medium">
                                                {{ __('Total') }}: <span x-text="formatCurrency(getTotalWithAnnexes(contract), contract.currency)"></span>
                                            </div>
                                        </template>
                                        <template x-if="contract.annexes && contract.annexes.length > 0 && hasMixedCurrencyAnnexes(contract)">
                                            <div class="text-xs text-amber-600 font-medium" title="{{ __('Acest contract are anexe în monede diferite') }}">
                                                <svg class="w-3 h-3 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                                {{ __('Multi-monedă') }}
                                            </div>
                                        </template>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <template x-if="contract.annexes && contract.annexes.length > 0">
                                            <button @click="toggleAnnexExpand(contract.id)"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-purple-700 bg-purple-100 rounded-full hover:bg-purple-200 transition-colors focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-1">
                                                <span x-text="contract.annexes.length"></span>
                                                <svg class="w-3 h-3 transition-transform duration-200"
                                                     :class="{ 'rotate-180': isAnnexExpanded(contract.id) }"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                        </template>
                                        <template x-if="!contract.annexes || contract.annexes.length === 0">
                                            <span class="text-slate-300">-</span>
                                        </template>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a :href="'/contracts/' + contract.id"
                                               class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors"
                                               title="{{ __('View') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a :href="'/contracts/' + contract.id + '/edit'"
                                               class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors"
                                               title="{{ __('Edit Content') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <a :href="'/contracts/' + contract.id + '/download'"
                                               class="p-1.5 text-slate-500 hover:text-green-600 hover:bg-green-50 rounded transition-colors"
                                               title="{{ __('Download PDF') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </a>
                                            <a :href="'/contracts/' + contract.id + '/download?print=1'" target="_blank"
                                               class="p-1.5 text-slate-500 hover:text-purple-600 hover:bg-purple-50 rounded transition-colors"
                                               title="{{ __('Print') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                                </svg>
                                            </a>
                                            <template x-if="contract.status === 'active'">
                                                <button @click="terminateContract(contract.id, contract.contract_number)"
                                                        class="p-1.5 text-slate-500 hover:text-orange-600 hover:bg-orange-50 rounded transition-colors"
                                                        title="{{ __('Close Contract') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </template>
                                            <template x-if="contract.status !== 'active'">
                                                <button @click="deleteContract(contract.id, contract.contract_number)"
                                                        class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                                                        title="{{ __('Delete') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Annex Rows (Collapsible) --}}
                                <template x-for="annex in (contract.annexes || [])" :key="'annex-' + annex.id">
                                    <tr x-show="isAnnexExpanded(contract.id)"
                                        x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0"
                                        class="bg-slate-50 border-b border-slate-100 hover:bg-slate-100/50 transition-colors"
                                        x-cloak>
                                        <td class="px-6 py-3"></td>
                                        <td class="px-6 py-3 pl-10">
                                            <div class="flex items-center gap-2">
                                                <span class="text-slate-400 text-sm">&#8627;</span>
                                                <a :href="'/contracts/' + contract.id + '/annexes/' + annex.id"
                                                   class="font-medium text-purple-600 hover:text-purple-800 transition-colors"
                                                   x-text="annex.annex_code"></a>
                                            </div>
                                            <div class="text-sm text-slate-500 ml-5 max-w-xs truncate" x-text="annex.title"></div>
                                        </td>
                                        <td class="px-6 py-3"></td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">
                                                {{ __('Anexă') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-slate-600">
                                            <div class="text-sm" x-text="annex.effective_date || '-'"></div>
                                        </td>
                                        <td class="px-6 py-3 text-right">
                                            <div class="font-semibold text-purple-700">
                                                +<span x-text="formatCurrency(annex.additional_value, annex.currency)"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 text-center"></td>
                                        <td class="px-6 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a :href="'/contracts/' + contract.id + '/annexes/' + annex.id"
                                                   class="p-1.5 text-slate-400 hover:text-purple-600 hover:bg-purple-50 rounded transition-colors"
                                                   title="{{ __('View') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                <a :href="'/contracts/' + contract.id + '/annexes/' + annex.id + '/download'"
                                                   class="p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded transition-colors"
                                                   title="{{ __('Download') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </template>
                    </table>
                </div>

                {{-- Pagination --}}
                <div x-show="pagination.last_page > 1" class="px-4 py-3 border-t border-slate-200 flex items-center justify-between">
                    <div class="text-sm text-slate-600">
                        {{ __('Afișare') }} <span x-text="pagination.from || 0"></span> - <span x-text="pagination.to || 0"></span> {{ __('din') }} <span x-text="pagination.total || 0"></span>
                    </div>
                    <div class="flex gap-1">
                        <button @click="goToPage(filters.page - 1)" :disabled="filters.page === 1"
                                class="px-3 py-1 text-sm border rounded hover:bg-slate-50 disabled:opacity-50">{{ __('Anterior') }}</button>
                        <button @click="goToPage(filters.page + 1)" :disabled="filters.page === pagination.last_page"
                                class="px-3 py-1 text-sm border rounded hover:bg-slate-50 disabled:opacity-50">{{ __('Următor') }}</button>
                    </div>
                </div>
            </x-ui.card>
        </template>

        {{-- Empty State --}}
        <template x-if="!loading && contracts.length === 0">
            <x-ui.card>
                <div class="px-6 py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No contracts found') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Contracts are created when offers are accepted.') }}</p>
                </div>
            </x-ui.card>
        </template>
    </div>

    <x-toast />

    @push('scripts')
    <script>
    function contractsPage(config) {
        return {
            contracts: [],
            stats: config.stats || {},
            loading: true,
            filters: {
                q: '',
                status: '',
                hasAnnexes: false,
                page: 1
            },
            pagination: {},
            selectedIds: [],
            selectAll: false,
            bulkLoading: false,
            expandedContracts: [],

            init() {
                this.fetchContracts();
            },

            async fetchContracts() {
                this.loading = true;
                try {
                    const params = new URLSearchParams();
                    if (this.filters.q) params.append('q', this.filters.q);
                    if (this.filters.status) params.append('status', this.filters.status);
                    if (this.filters.hasAnnexes) params.append('has_annexes', '1');
                    params.append('page', this.filters.page);

                    const response = await fetch(`/contracts?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();
                    this.contracts = data.contracts || [];
                    this.pagination = data.pagination || {};
                    this.stats = data.stats || this.stats;
                } catch (error) {
                    console.error('Error fetching contracts:', error);
                } finally {
                    this.loading = false;
                }
            },

            search(value) {
                this.filters.q = value;
                this.filters.page = 1;
                this.fetchContracts();
            },

            setStatusFilter(status) {
                this.filters.status = status;
                this.filters.page = 1;
                this.fetchContracts();
            },

            toggleHasAnnexesFilter() {
                this.filters.hasAnnexes = !this.filters.hasAnnexes;
                this.filters.page = 1;
                this.fetchContracts();
            },

            goToPage(page) {
                if (page < 1 || page > this.pagination.last_page) return;
                this.filters.page = page;
                this.fetchContracts();
            },

            formatCurrency(amount, currency = 'RON') {
                return new Intl.NumberFormat('ro-RO', {
                    style: 'currency',
                    currency: currency
                }).format(amount || 0);
            },

            formatActiveValues() {
                const values = this.stats.total_active_value;
                if (!values || typeof values !== 'object') {
                    return '0,00 RON';
                }
                const currencies = Object.keys(values);
                if (currencies.length === 0) {
                    return '0,00 RON';
                }
                return currencies.map(currency => {
                    return this.formatCurrency(values[currency], currency);
                }).join(' + ');
            },

            getTotalWithAnnexes(contract) {
                let total = parseFloat(contract.total_value) || 0;
                const contractCurrency = contract.currency || 'RON';
                if (contract.annexes && contract.annexes.length > 0) {
                    contract.annexes.forEach(annex => {
                        // Only sum annexes with the same currency
                        const annexCurrency = annex.currency || 'RON';
                        if (annexCurrency === contractCurrency) {
                            total += parseFloat(annex.additional_value) || 0;
                        }
                    });
                }
                return total;
            },

            hasMixedCurrencyAnnexes(contract) {
                if (!contract.annexes || contract.annexes.length === 0) return false;
                const contractCurrency = contract.currency || 'RON';
                return contract.annexes.some(annex => (annex.currency || 'RON') !== contractCurrency);
            },

            isExpiringSoon(contract) {
                if (!contract.end_date || contract.status !== 'active') return false;
                const endDate = new Date(contract.end_date_raw || contract.end_date);
                const today = new Date();
                const daysUntilExpiry = Math.ceil((endDate - today) / (1000 * 60 * 60 * 24));
                return daysUntilExpiry > 0 && daysUntilExpiry <= 30;
            },

            toggleAnnexExpand(contractId) {
                const index = this.expandedContracts.indexOf(contractId);
                if (index > -1) {
                    this.expandedContracts.splice(index, 1);
                } else {
                    this.expandedContracts.push(contractId);
                }
            },

            isAnnexExpanded(contractId) {
                return this.expandedContracts.includes(contractId);
            },

            hasAnyAnnexes() {
                return this.contracts.some(c => c.annexes && c.annexes.length > 0);
            },

            expandAllAnnexes() {
                this.expandedContracts = this.contracts
                    .filter(c => c.annexes && c.annexes.length > 0)
                    .map(c => c.id);
            },

            collapseAllAnnexes() {
                this.expandedContracts = [];
            },

            async deleteContract(contractId, contractNumber) {
                if (!confirm(`{{ __('Are you sure you want to delete contract') }} ${contractNumber}? {{ __('This action cannot be undone.') }}`)) {
                    return;
                }

                try {
                    const response = await fetch(`/contracts/${contractId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (result.success || response.ok) {
                        await this.fetchContracts();
                    } else {
                        throw new Error(result.message || '{{ __('Failed to delete contract') }}');
                    }
                } catch (error) {
                    console.error('Error deleting contract:', error);
                    alert(error.message || '{{ __('Failed to delete contract. Please try again.') }}');
                }
            },

            toggleItem(id) {
                const index = this.selectedIds.indexOf(id);
                if (index > -1) {
                    this.selectedIds.splice(index, 1);
                } else {
                    this.selectedIds.push(id);
                }
                this.updateSelectAll();
            },

            toggleSelectAll() {
                if (this.selectAll) {
                    this.selectedIds = this.contracts.map(c => c.id);
                } else {
                    this.selectedIds = [];
                }
            },

            updateSelectAll() {
                this.selectAll = this.contracts.length > 0 &&
                                 this.selectedIds.length === this.contracts.length;
            },

            clearSelection() {
                this.selectedIds = [];
                this.selectAll = false;
            },

            async bulkDelete() {
                if (this.selectedIds.length === 0) return;

                const activeContracts = this.contracts.filter(c =>
                    this.selectedIds.includes(c.id) && c.status === 'active'
                );

                if (activeContracts.length > 0) {
                    alert('{{ __('Active contracts cannot be deleted. Please deselect active contracts first.') }}');
                    return;
                }

                if (!confirm(`{{ __('Are you sure you want to delete') }} ${this.selectedIds.length} {{ __('contracts? This action cannot be undone.') }}`)) {
                    return;
                }

                this.bulkLoading = true;

                try {
                    const ids = this.selectedIds.map(id => parseInt(id, 10));

                    const response = await fetch('/contracts/bulk-delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids: ids })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.clearSelection();
                        await this.fetchContracts();
                        this.$dispatch('toast', { message: result.message, type: 'success' });
                    } else {
                        throw new Error(result.message || '{{ __('Failed to delete contracts') }}');
                    }
                } catch (error) {
                    console.error('Error deleting contracts:', error);
                    alert(error.message || '{{ __('Failed to delete contracts. Please try again.') }}');
                } finally {
                    this.bulkLoading = false;
                }
            },

            async bulkExport() {
                if (this.selectedIds.length === 0) return;

                this.bulkLoading = true;

                try {
                    const response = await fetch('/contracts/bulk-export', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'text/csv'
                        },
                        body: JSON.stringify({ ids: this.selectedIds })
                    });

                    if (!response.ok) {
                        const error = await response.json();
                        throw new Error(error.message || '{{ __('Failed to export contracts') }}');
                    }

                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `contracts-export-${new Date().toISOString().slice(0, 10)}.csv`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();

                    this.$dispatch('toast', { message: '{{ __('Contracts exported successfully!') }}', type: 'success' });
                } catch (error) {
                    console.error('Error exporting contracts:', error);
                    alert(error.message || '{{ __('Failed to export contracts. Please try again.') }}');
                } finally {
                    this.bulkLoading = false;
                }
            },

            async terminateContract(contractId, contractNumber) {
                this.$dispatch('confirm-dialog', {
                    title: '{{ __('Close Contract') }}',
                    message: `{{ __('Are you sure you want to close contract') }} ${contractNumber}? {{ __('This will set the contract status to Terminated.') }}`,
                    confirmText: '{{ __('Close Contract') }}',
                    onConfirm: async () => {
                        try {
                            const response = await fetch(`/contracts/${contractId}/terminate`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                }
                            });

                            if (response.ok) {
                                await this.fetchContracts();
                                this.$dispatch('toast', { message: '{{ __('Contract closed successfully.') }}', type: 'success' });
                            } else {
                                const result = await response.json();
                                throw new Error(result.message || '{{ __('Failed to close contract') }}');
                            }
                        } catch (error) {
                            console.error('Error closing contract:', error);
                            alert(error.message || '{{ __('Failed to close contract. Please try again.') }}');
                        }
                    }
                });
            },

            async bulkTerminate() {
                if (this.selectedIds.length === 0) return;

                const nonActiveContracts = this.contracts.filter(c =>
                    this.selectedIds.includes(c.id) && c.status !== 'active'
                );

                if (nonActiveContracts.length > 0) {
                    alert('{{ __('Only active contracts can be closed. Please deselect non-active contracts first.') }}');
                    return;
                }

                this.$dispatch('confirm-dialog', {
                    title: '{{ __('Close Contracts') }}',
                    message: `{{ __('Are you sure you want to close') }} ${this.selectedIds.length} {{ __('contracts? This will set their status to Terminated.') }}`,
                    confirmText: '{{ __('Close Contracts') }}',
                    onConfirm: async () => {
                        this.bulkLoading = true;

                        try {
                            const ids = this.selectedIds.map(id => parseInt(id, 10));

                            const response = await fetch('/contracts/bulk-terminate', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ ids: ids })
                            });

                            const result = await response.json();

                            if (result.success) {
                                this.clearSelection();
                                await this.fetchContracts();
                                this.$dispatch('toast', { message: result.message, type: 'success' });
                            } else {
                                throw new Error(result.message || '{{ __('Failed to close contracts') }}');
                            }
                        } catch (error) {
                            console.error('Error closing contracts:', error);
                            alert(error.message || '{{ __('Failed to close contracts. Please try again.') }}');
                        } finally {
                            this.bulkLoading = false;
                        }
                    }
                });
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
