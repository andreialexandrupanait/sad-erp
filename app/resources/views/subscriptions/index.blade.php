<x-app-layout>
    <x-slot name="pageTitle">{{ __('Subscriptions') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('subscriptions.create') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('Add Subscription') }}
        </x-ui.button>
    </x-slot>

    <div class="p-4 md:p-6 space-y-4 md:space-y-6"
         x-data="subscriptionsPage()"
         @keydown.escape.window="clearSelection()">
        <!-- Success/Info Messages -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        @if (session('info'))
            <x-ui.alert variant="info">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('info') }}</div>
            </x-ui.alert>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 gap-4 md:gap-6 lg:grid-cols-4">
            <!-- Monthly Cost - Featured -->
            <div class="rounded-lg border border-slate-200 bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-lg">
                <div class="p-4 md:p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs md:text-sm font-medium text-slate-300">{{ __('Monthly Cost') }}</p>
                            <p class="mt-1 md:mt-2 text-xl md:text-2xl font-bold">{{ number_format($stats['monthly_cost'], 2) }}</p>
                            <p class="mt-1 text-xs text-slate-400 hidden md:block">RON {{ __("per month") }}@if(($stats['monthly_cost_eur'] ?? 0) > 0) + {{ number_format($stats['monthly_cost_eur'], 2) }} EUR @endif@if(($stats['monthly_cost_usd'] ?? 0) > 0) + {{ number_format($stats['monthly_cost_usd'], 2) }} USD @endif</p>
                        </div>
                        <div class="ml-2 md:ml-4 flex h-8 w-8 md:h-12 md:w-12 items-center justify-center rounded-lg bg-white/10">
                            <svg class="h-4 w-4 md:h-6 md:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Annual Cost -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-4 md:p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs md:text-sm font-medium text-slate-600">{{ __('Annual Cost') }}</p>
                            <p class="mt-1 md:mt-2 text-xl md:text-2xl font-bold text-slate-900">{{ number_format($stats['annual_cost'], 2) }}</p>
                            <p class="mt-1 text-xs text-slate-500 hidden md:block">RON {{ __("per year") }}@if(($stats['annual_cost_eur'] ?? 0) > 0) + {{ number_format($stats['annual_cost_eur'], 2) }} EUR @endif@if(($stats['annual_cost_usd'] ?? 0) > 0) + {{ number_format($stats['annual_cost_usd'], 2) }} USD @endif</p>
                        </div>
                        <div class="ml-2 md:ml-4 flex h-8 w-8 md:h-12 md:w-12 items-center justify-center rounded-lg bg-purple-50">
                            <svg class="h-4 w-4 md:h-6 md:w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Subscriptions -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-4 md:p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs md:text-sm font-medium text-slate-600">{{ __('Active') }}</p>
                            <p class="mt-1 md:mt-2 text-xl md:text-2xl font-bold text-slate-900">{{ $stats['active'] }}</p>
                            <p class="mt-1 text-xs text-slate-500 hidden md:block">{{ __('subscriptions') }}</p>
                        </div>
                        <div class="ml-2 md:ml-4 flex h-8 w-8 md:h-12 md:w-12 items-center justify-center rounded-lg bg-green-50">
                            <svg class="h-4 w-4 md:h-6 md:w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Renewals -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-4 md:p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs md:text-sm font-medium text-slate-600">{{ __('Upcoming') }}</p>
                            <p class="mt-1 md:mt-2 text-xl md:text-2xl font-bold text-slate-900">{{ $stats['upcoming_renewals'] }}</p>
                            <p class="mt-1 text-xs text-slate-500 hidden md:block">{{ __('next 30 days') }}</p>
                        </div>
                        <div class="ml-2 md:ml-4 flex h-8 w-8 md:h-12 md:w-12 items-center justify-center rounded-lg bg-orange-50">
                            <svg class="h-4 w-4 md:h-6 md:w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <x-ui.card>
            <x-ui.card-content>
                <form method="GET" action="{{ route('subscriptions.index') }}">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Search -->
                        <div class="flex-1">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <x-ui.input
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="{{ __('Search subscriptions') }}"
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="w-full sm:w-44">
                            <x-ui.select name="status">
                                <option value="">{{ __('All Statuses') }}</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                                        {{ $status->label }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <!-- Billing Cycle Filter -->
                        <div class="w-full sm:w-44">
                            <x-ui.select name="billing_cycle">
                                <option value="">{{ __('All Billing Cycles') }}</option>
                                @foreach($billingCycles as $cycle)
                                    <option value="{{ $cycle->value }}" {{ request('billing_cycle') === $cycle->value ? 'selected' : '' }}>
                                        {{ $cycle->label }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <!-- Renewal Range Filter -->
                        <div class="w-full sm:w-48">
                            <x-ui.select name="renewal_range">
                                <option value="">{{ __('All Renewals') }}</option>
                                <option value="overdue" {{ request('renewal_range') === 'overdue' ? 'selected' : '' }}>{{ __('Overdue') }}</option>
                                <option value="urgent" {{ request('renewal_range') === 'urgent' ? 'selected' : '' }}>0-7 {{ __('days') }}</option>
                                <option value="warning" {{ request('renewal_range') === 'warning' ? 'selected' : '' }}>8-14 {{ __('days') }}</option>
                                <option value="normal" {{ request('renewal_range') === 'normal' ? 'selected' : '' }}>>14 {{ __('days') }}</option>
                            </x-ui.select>
                        </div>

                        <!-- Buttons -->
                        <div class="w-full sm:w-auto flex gap-2">
                            <x-ui.button type="submit" variant="default" class="w-full sm:w-auto">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                {{ __('Filter') }}
                            </x-ui.button>
                            @if($activeFilters > 0)
                                <x-ui.button variant="outline" onclick="window.location.href='{{ route('subscriptions.index') }}'">
                                    {{ __('Clear') }}
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

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
                            <span x-text="selectedIds.length === 1 ? '{{ __('subscription') }}' : '{{ __('subscriptions') }}'"></span>
                            {{ __('selected') }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- Renew Selected --}}
                        <x-ui.button
                            variant="outline"
                            class="!bg-slate-800 !border-slate-600 !text-white hover:!bg-slate-700"
                            @click="bulkRenew()"
                            x-bind:disabled="bulkLoading"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            {{ __('Renew Selected') }}
                        </x-ui.button>

                        {{-- Export to CSV --}}
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

                        {{-- Delete Selected --}}
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

                        {{-- Clear Selection --}}
                        <button
                            @click="clearSelection()"
                            class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors"
                            x-bind:disabled="bulkLoading"
                        >
                            {{ __('Deselect') }}
                        </button>
                    </div>
                </div>

                {{-- Loading overlay --}}
                <div x-show="bulkLoading"
                     class="absolute inset-0 bg-slate-900/80 rounded-xl flex items-center justify-center">
                    <svg class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Subscriptions Table -->
        <x-ui.card>
            @if($subscriptions->isEmpty())
                <div class="px-6 py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No subscriptions') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Get started by creating your first subscription') }}</p>
                    <div class="mt-6">
                        <x-ui.button variant="default" onclick="window.location.href='{{ route('subscriptions.create') }}'">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Add Subscription') }}
                        </x-ui.button>
                    </div>
                </div>
            @else
                {{-- Desktop Table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="bg-slate-100">
                            <tr class="border-b border-slate-200">
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500 w-12">
                                    <x-bulk-checkbox x-model="selectAll" @change="toggleSelectAll()" />
                                </th>
                                <x-ui.sortable-header column="vendor_name" label="{{ __('Vendor Name') }}" />
                                <x-ui.sortable-header column="price" label="{{ __('Price') }}" class="text-right" />
                                <x-ui.sortable-header column="billing_cycle" label="{{ __('Billing Cycle') }}" />
                                <x-ui.sortable-header column="next_renewal_date" label="{{ __('Expiry Date') }}" />
                                <x-ui.table-head class="text-right">{{ __('Actions') }}</x-ui.table-head>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            @foreach($subscriptions as $subscription)
                                <x-ui.table-row
                                    data-selectable
                                    data-subscription-id="{{ $subscription->id }}"
                                    class="{{ !$subscription->auto_renew ? 'bg-slate-100/70 opacity-75' : '' }}"
                                    x-bind:class="{ 'bg-blue-50': selectedIds.includes({{ $subscription->id }}) }"
                                >
                                    <x-ui.table-cell>
                                        <input type="checkbox"
                                               :checked="selectedIds.includes({{ $subscription->id }})"
                                               @change="toggleItem({{ $subscription->id }})"
                                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 cursor-pointer transition-colors">
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="font-medium text-slate-900">
                                            <a href="{{ route('subscriptions.show', $subscription) }}" class="hover:text-slate-600 transition-colors">
                                                {{ $subscription->vendor_name }}
                                            </a>
                                        </div>
                                        @if($subscription->status === 'active')
                                            <div class="flex items-center gap-1 mt-1">
                                                @if($subscription->auto_renew)
                                                    {{-- Show Cancel button for auto-renewing subscriptions --}}
                                                    <button
                                                        type="button"
                                                        onclick="cancelSubscription({{ $subscription->id }}, '{{ $subscription->vendor_name }}')"
                                                        class="inline-flex items-center text-xs text-red-600 hover:text-red-800 transition-colors"
                                                        title="{{ __('Cancel subscription') }}">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        {{ __('Cancel') }}
                                                    </button>
                                                @else
                                                    {{-- Show Reactivate button for cancelled subscriptions --}}
                                                    <button
                                                        type="button"
                                                        onclick="reactivateSubscription({{ $subscription->id }}, '{{ $subscription->vendor_name }}')"
                                                        class="inline-flex items-center text-xs text-green-600 hover:text-green-800 transition-colors"
                                                        title="{{ __('Reactivate subscription') }}">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                        </svg>
                                                        {{ __('Reactivate') }}
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="text-right">
                                        <div class="text-sm font-semibold text-slate-900">{{ number_format($subscription->price, 2) }} <span class="text-slate-500 font-normal">{{ $subscription->currency ?? 'RON' }}</span></div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm text-slate-700">
                                            {{ $subscription->billing_cycle_label }}
                                            @if($subscription->billing_cycle === 'custom')
                                                <span class="text-xs text-slate-500">({{ $subscription->custom_days }}d)</span>
                                            @endif
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        @if($subscription->status === 'paused')
                                            <x-ui.badge variant="default">{{ __('Paused') }}</x-ui.badge>
                                        @elseif($subscription->status === 'cancelled')
                                            <x-ui.badge variant="destructive">{{ __('Cancelled') }}</x-ui.badge>
                                        @else
                                            <div class="text-sm font-medium {{ !$subscription->auto_renew ? 'text-slate-500' : 'text-slate-900' }}">
                                                {{ $subscription->next_renewal_date->translatedFormat('d M Y') }}
                                            </div>
                                            @if(!$subscription->auto_renew)
                                                {{-- Cancelled subscription (auto_renew disabled) - show in orange/gray --}}
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-50 text-orange-600">
                                                    {{ $subscription->renewal_text }}
                                                </span>
                                            @else
                                                {{-- Active subscription - show renewal status with urgency colors --}}
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $subscription->renewal_urgency === 'overdue' ? 'bg-red-100 text-red-700' : ($subscription->renewal_urgency === 'urgent' ? 'bg-red-50 text-red-600' : ($subscription->renewal_urgency === 'warning' ? 'bg-orange-50 text-orange-600' : 'bg-green-50 text-green-600')) }}">
                                                    {{ $subscription->renewal_text }}
                                                </span>
                                            @endif
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="text-right">
                                        <x-table-actions
                                            :viewUrl="route('subscriptions.show', $subscription)"
                                            :editUrl="route('subscriptions.edit', $subscription)"
                                            :deleteAction="route('subscriptions.destroy', $subscription)"
                                            :deleteConfirm="__('Are you sure you want to delete this subscription?')"
                                        />
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach($subscriptions as $subscription)
                        <div class="p-4 {{ !$subscription->auto_renew ? 'bg-slate-50 opacity-75' : '' }}"
                             data-selectable
                             data-subscription-id="{{ $subscription->id }}"
                             x-bind:class="{ 'bg-blue-50': selectedIds.includes({{ $subscription->id }}) }">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox"
                                           :checked="selectedIds.includes({{ $subscription->id }})"
                                           @change="toggleItem({{ $subscription->id }})"
                                           class="h-4 w-4 rounded border-slate-300 text-blue-600 cursor-pointer">
                                    <div>
                                        <a href="{{ route('subscriptions.show', $subscription) }}" class="font-medium text-slate-900">
                                            {{ $subscription->vendor_name }}
                                        </a>
                                        <div class="text-sm font-semibold text-slate-700">
                                            {{ number_format($subscription->price, 2) }} {{ $subscription->currency ?? 'RON' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('subscriptions.edit', $subscription) }}" class="p-1.5 text-blue-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('subscriptions.destroy', $subscription) }}" class="inline" onsubmit="return confirm('{{ __('Delete?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="p-1.5 text-red-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                @if($subscription->status === 'paused')
                                    <x-ui.badge variant="default">{{ __('Paused') }}</x-ui.badge>
                                @elseif($subscription->status === 'cancelled')
                                    <x-ui.badge variant="destructive">{{ __('Cancelled') }}</x-ui.badge>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ !$subscription->auto_renew ? 'bg-orange-50 text-orange-600' : ($subscription->renewal_urgency === 'overdue' ? 'bg-red-100 text-red-700' : ($subscription->renewal_urgency === 'urgent' ? 'bg-red-50 text-red-600' : ($subscription->renewal_urgency === 'warning' ? 'bg-orange-50 text-orange-600' : 'bg-green-50 text-green-600'))) }}">
                                        {{ $subscription->renewal_text }}
                                    </span>
                                    <span class="text-slate-500">{{ $subscription->next_renewal_date->format('d.m.Y') }}</span>
                                @endif
                                <span class="text-slate-400">|</span>
                                <span class="text-slate-500">{{ $subscription->billing_cycle_label }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($subscriptions->hasPages())
                    <div class="bg-slate-100 px-6 py-4 border-t border-slate-200">
                        {{ $subscriptions->links() }}
                    </div>
                @endif
            @endif
        </x-ui.card>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

    <script>
        function subscriptionsPage() {
            return {
                // Bulk selection state
                selectedIds: [],
                selectAll: false,
                bulkLoading: false,

                // Selection methods
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
                        // Select all visible subscriptions
                        const rows = document.querySelectorAll('[data-selectable]');
                        this.selectedIds = Array.from(rows).map(row =>
                            parseInt(row.getAttribute('data-subscription-id'))
                        );
                    } else {
                        this.selectedIds = [];
                    }
                },

                updateSelectAll() {
                    const rows = document.querySelectorAll('[data-selectable]');
                    this.selectAll = rows.length > 0 && this.selectedIds.length === rows.length;
                },

                clearSelection() {
                    this.selectedIds = [];
                    this.selectAll = false;
                },

                // Bulk action methods
                async bulkRenew() {
                    if (this.selectedIds.length === 0) return;

                    if (!confirm(`{{ __('Renew selected subscriptions? The next renewal dates will be advanced according to their billing cycles.') }}`)) {
                        return;
                    }

                    this.bulkLoading = true;

                    try {
                        const response = await fetch('{{ route('subscriptions.bulk-renew') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ ids: this.selectedIds })
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            this.$dispatch('toast', { message: result.message || '{{ __('Subscriptions renewed successfully!') }}', type: 'success' });
                            this.clearSelection();
                            window.location.reload();
                        } else {
                            throw new Error(result.message || '{{ __('Failed to renew subscriptions') }}');
                        }
                    } catch (error) {
                        console.error('Error renewing subscriptions:', error);
                        alert(error.message || '{{ __('Failed to renew subscriptions. Please try again.') }}');
                    } finally {
                        this.bulkLoading = false;
                    }
                },

                async bulkExport() {
                    if (this.selectedIds.length === 0) return;

                    this.bulkLoading = true;

                    try {
                        const response = await fetch('{{ route('subscriptions.bulk-export') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ ids: this.selectedIds })
                        });

                        if (!response.ok) {
                            const error = await response.json();
                            throw new Error(error.message || '{{ __('Failed to export subscriptions') }}');
                        }

                        // Download the CSV
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `subscriptions-export-${new Date().toISOString().slice(0, 10)}.csv`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        a.remove();

                        this.$dispatch('toast', { message: '{{ __('Subscriptions exported successfully!') }}', type: 'success' });
                        this.clearSelection();
                    } catch (error) {
                        console.error('Error exporting subscriptions:', error);
                        alert(error.message || '{{ __('Failed to export subscriptions. Please try again.') }}');
                    } finally {
                        this.bulkLoading = false;
                    }
                },

                async bulkDelete() {
                    if (this.selectedIds.length === 0) return;

                    if (!confirm(`{{ __('Are you sure you want to delete') }} ${this.selectedIds.length} {{ __('subscriptions? This action cannot be undone.') }}`)) {
                        return;
                    }

                    this.bulkLoading = true;

                    try {
                        const response = await fetch('{{ route('subscriptions.bulk-update') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                ids: this.selectedIds,
                                action: 'delete'
                            })
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            this.$dispatch('toast', { message: result.message || '{{ __('Subscriptions deleted successfully!') }}', type: 'success' });
                            this.clearSelection();
                            window.location.reload();
                        } else {
                            throw new Error(result.message || '{{ __('Failed to delete subscriptions') }}');
                        }
                    } catch (error) {
                        console.error('Error deleting subscriptions:', error);
                        alert(error.message || '{{ __('Failed to delete subscriptions. Please try again.') }}');
                    } finally {
                        this.bulkLoading = false;
                    }
                }
            };
        }

        function renewSubscription(subscriptionId, vendorName) {
            const confirmMsg = `{{ __('Confirm renewal of subscription') }} "${vendorName}"?\n\n{{ __('The next renewal date will be advanced according to the billing cycle.') }}`;
            if (!confirm(confirmMsg)) {
                return;
            }

            fetch(`/subscriptions/${subscriptionId}/renew`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || `HTTP error! status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || '{{ __('Error renewing subscription.') }}');
                }
            })
            .catch(error => {
                alert('{{ __('Error renewing subscription:') }} ' + error.message);
            });
        }

        function cancelSubscription(subscriptionId, vendorName) {
            const confirmMsg = `Cancel subscription "${vendorName}"?\n\nThe subscription will not renew automatically. You can reactivate it at any time before the renewal date.`;
            if (!confirm(confirmMsg)) {
                return;
            }

            fetch(`/subscriptions/${subscriptionId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || `HTTP error! status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || '{{ __('Error cancelling subscription.') }}');
                }
            })
            .catch(error => {
                alert('{{ __('Error cancelling subscription:') }} ' + error.message);
            });
        }

        function reactivateSubscription(subscriptionId, vendorName) {
            const confirmMsg = `Reactivate subscription "${vendorName}"?\n\nThe subscription will automatically renew on the next renewal date.`;
            if (!confirm(confirmMsg)) {
                return;
            }

            fetch(`/subscriptions/${subscriptionId}/reactivate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || `HTTP error! status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || '{{ __('Error reactivating subscription.') }}');
                }
            })
            .catch(error => {
                alert('{{ __('Error reactivating subscription:') }} ' + error.message);
            });
        }
    </script>
</x-app-layout>
