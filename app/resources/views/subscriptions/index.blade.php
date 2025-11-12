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

    <div class="p-6 space-y-6" x-data>
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
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <!-- Monthly Cost - Featured -->
            <div class="rounded-lg border border-slate-200 bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-300">{{ __('Monthly Cost') }}</p>
                            <p class="mt-2 text-3xl font-bold">{{ number_format($stats['monthly_cost'], 2) }}</p>
                            <p class="mt-1 text-xs text-slate-400">RON {{ __('per month') }}</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-white/10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Annual Cost -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-600">{{ __('Annual Cost') }}</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($stats['annual_cost'], 2) }}</p>
                            <p class="mt-1 text-xs text-slate-500">RON {{ __('per year') }}</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Subscriptions -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-600">{{ __('Active') }}</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['active'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ __('subscriptions') }}</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-green-50">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Renewals -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-600">{{ __('Upcoming') }}</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['upcoming_renewals'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ __('next 30 days') }}</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-orange-50">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <div class="flex gap-2">
                            <x-ui.button type="submit" variant="default">
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
                <div class="overflow-x-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr class="border-b transition-colors hover:bg-slate-50/50">
                                <x-ui.table-head>{{ __('Vendor Name') }}</x-ui.table-head>
                                <x-ui.table-head>{{ __('Price (RON)') }}</x-ui.table-head>
                                <x-ui.table-head>{{ __('Billing Cycle') }}</x-ui.table-head>
                                <x-ui.table-head>{{ __('Next Renewal Date') }}</x-ui.table-head>
                                <x-ui.table-head>{{ __('Status') }}</x-ui.table-head>
                                <x-ui.table-head class="text-right">{{ __('Actions') }}</x-ui.table-head>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            @foreach($subscriptions as $subscription)
                                <x-ui.table-row>
                                    <x-ui.table-cell>
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-slate-400 to-slate-600 rounded-lg flex items-center justify-center">
                                                <span class="text-white text-sm font-bold">{{ strtoupper(substr($subscription->vendor_name, 0, 2)) }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <a href="{{ route('subscriptions.show', $subscription) }}" class="text-sm font-semibold text-slate-900 hover:text-slate-600 transition-colors">
                                                    {{ $subscription->vendor_name }}
                                                </a>
                                            </div>
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm font-semibold text-slate-900">{{ number_format($subscription->price, 2) }} <span class="text-slate-500 font-normal">RON</span></div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm text-slate-700">
                                            {{ ucfirst($subscription->billing_cycle) }}
                                            @if($subscription->billing_cycle === 'custom')
                                                <span class="text-xs text-slate-500">({{ $subscription->custom_days }}d)</span>
                                            @endif
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm font-medium text-slate-900">{{ $subscription->next_renewal_date->format('M d, Y') }}</div>
                                        @php
                                            $renewalBadgeVariant = 'default';
                                            if (str_contains($subscription->renewal_badge_color, 'red')) {
                                                $renewalBadgeVariant = 'destructive';
                                            } elseif (str_contains($subscription->renewal_badge_color, 'yellow')) {
                                                $renewalBadgeVariant = 'warning';
                                            } elseif (str_contains($subscription->renewal_badge_color, 'green')) {
                                                $renewalBadgeVariant = 'success';
                                            }
                                        @endphp
                                        <x-ui.badge variant="{{ $renewalBadgeVariant }}" class="mt-1">
                                            {{ $subscription->renewal_text }}
                                        </x-ui.badge>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        @php
                                            $statusBadgeVariant = match($subscription->status) {
                                                'active' => 'success',
                                                'paused' => 'warning',
                                                'cancelled' => 'secondary',
                                                default => 'default',
                                            };
                                            $statusLabel = match($subscription->status) {
                                                'active' => __('Active'),
                                                'paused' => __('Paused'),
                                                'cancelled' => __('Cancelled'),
                                                default => ucfirst($subscription->status),
                                            };
                                        @endphp
                                        <x-ui.badge variant="{{ $statusBadgeVariant }}">
                                            <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                            {{ $statusLabel }}
                                        </x-ui.badge>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-ui.button
                                                variant="secondary"
                                                size="sm"
                                                onclick="window.location.href='{{ route('subscriptions.show', $subscription) }}'"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                {{ __('View') }}
                                            </x-ui.button>
                                            <x-ui.button
                                                variant="outline"
                                                size="sm"
                                                onclick="window.location.href='{{ route('subscriptions.edit', $subscription) }}'"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                {{ __('Edit') }}
                                            </x-ui.button>
                                            <form action="{{ route('subscriptions.destroy', $subscription) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this subscription?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.button type="submit" variant="destructive" size="sm">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    {{ __('Delete') }}
                                                </x-ui.button>
                                            </form>
                                        </div>
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($subscriptions->hasPages())
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                        {{ $subscriptions->links() }}
                    </div>
                @endif
            @endif
        </x-ui.card>
    </div>

    <!-- Toast Notifications -->
    <x-toast />
</x-app-layout>
