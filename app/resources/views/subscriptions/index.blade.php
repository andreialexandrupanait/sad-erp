<x-app-layout>
    <x-slot name="pageTitle">Abonamente</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" @click="$dispatch('open-slide-panel', 'subscription-create')">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Abonament nou
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
                            <p class="text-sm font-medium text-slate-300">Monthly Cost</p>
                            <p class="mt-2 text-3xl font-bold">{{ number_format($stats['monthly_cost'], 2) }}</p>
                            <p class="mt-1 text-xs text-slate-400">RON per month</p>
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
                            <p class="text-sm font-medium text-slate-600">Annual Cost</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($stats['annual_cost'], 2) }}</p>
                            <p class="mt-1 text-xs text-slate-500">RON per year</p>
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
                            <p class="text-sm font-medium text-slate-600">Active</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['active'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">subscriptions</p>
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
                            <p class="text-sm font-medium text-slate-600">Upcoming</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['upcoming_renewals'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">next 30 days</p>
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
                                    placeholder="Search by vendor name..."
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="w-full sm:w-44">
                            <x-ui.select name="status">
                                <option value="">All Statuses</option>
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
                                <option value="">All Cycles</option>
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
                                <option value="">All Renewals</option>
                                <option value="overdue" {{ request('renewal_range') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                                <option value="urgent" {{ request('renewal_range') === 'urgent' ? 'selected' : '' }}>0-7 days</option>
                                <option value="warning" {{ request('renewal_range') === 'warning' ? 'selected' : '' }}>8-14 days</option>
                                <option value="normal" {{ request('renewal_range') === 'normal' ? 'selected' : '' }}>>14 days</option>
                            </x-ui.select>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-2">
                            <x-ui.button type="submit" variant="default">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                Filter
                            </x-ui.button>
                            @if($activeFilters > 0)
                                <x-ui.button variant="outline" onclick="window.location.href='{{ route('subscriptions.index') }}'">
                                    Clear
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
                    <h3 class="mt-2 text-sm font-medium text-slate-900">No subscriptions</h3>
                    <p class="mt-1 text-sm text-slate-500">Get started by creating your first subscription.</p>
                    <div class="mt-6">
                        <x-ui.button variant="default" @click="$dispatch('open-slide-panel', 'subscription-create')">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Subscription
                        </x-ui.button>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr class="border-b transition-colors hover:bg-slate-50/50">
                                <x-ui.table-head>Vendor</x-ui.table-head>
                                <x-ui.table-head>Price</x-ui.table-head>
                                <x-ui.table-head>Cycle</x-ui.table-head>
                                <x-ui.table-head>Next Renewal</x-ui.table-head>
                                <x-ui.table-head>Status</x-ui.table-head>
                                <x-ui.table-head class="text-right">Actions</x-ui.table-head>
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
                                                'active' => 'Active',
                                                'paused' => 'Paused',
                                                'cancelled' => 'Cancelled',
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
                                                View
                                            </x-ui.button>
                                            <x-ui.button
                                                variant="outline"
                                                size="sm"
                                                @click="$dispatch('open-slide-panel', 'subscription-edit-{{ $subscription->id }}')"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </x-ui.button>
                                            <form action="{{ route('subscriptions.destroy', $subscription) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this subscription?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.button type="submit" variant="destructive" size="sm">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Delete
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

    <!-- Create Subscription Slide Panel -->
    <x-slide-panel name="subscription-create" :show="false" maxWidth="2xl">
        <!-- Header -->
        <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
            <h2 class="text-2xl font-bold text-slate-900">New Subscription</h2>
            <button type="button" @click="$dispatch('close-slide-panel', 'subscription-create')" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto px-8 py-6">
            <form id="subscription-create-form"
                x-data="{
                    billingCycle: 'monthly',
                    loading: false,
                    async submit(event) {
                        event.preventDefault();
                        this.loading = true;

                        // Clear previous errors
                        document.querySelectorAll('#subscription-create-form .error-message').forEach(el => el.remove());

                        const formData = new FormData(event.target);

                        try {
                            const response = await fetch('{{ route('subscriptions.store') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: formData
                            });

                            const data = await response.json();

                            if (response.ok) {
                                $dispatch('close-slide-panel', 'subscription-create');
                                $dispatch('toast', { message: 'Subscription created successfully!', type: 'success' });
                                setTimeout(() => window.location.reload(), 500);
                            } else {
                                if (data.errors) {
                                    Object.keys(data.errors).forEach(key => {
                                        const input = document.querySelector(`#subscription-create-form [name='${key}']`);
                                        if (input) {
                                            const wrapper = input.closest('div');
                                            const errorDiv = document.createElement('p');
                                            errorDiv.className = 'error-message mt-2 text-sm text-red-600';
                                            errorDiv.textContent = data.errors[key][0];
                                            wrapper.appendChild(errorDiv);
                                        }
                                    });
                                    $dispatch('toast', { message: 'Please correct the errors in the form.', type: 'error' });
                                }
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            $dispatch('toast', { message: 'An error occurred. Please try again.', type: 'error' });
                        } finally {
                            this.loading = false;
                        }
                    }
                }"
                @submit="submit"
            >
                @csrf
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <!-- Vendor Name -->
                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="vendor_name">
                            Vendor Name <span class="text-red-500">*</span>
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="text" name="vendor_name" id="vendor_name" required
                                placeholder="e.g., Adobe, Microsoft, Netflix" />
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="price">
                            Price (RON) <span class="text-red-500">*</span>
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="number" step="0.01" name="price" id="price" required
                                placeholder="0.00" />
                        </div>
                    </div>

                    <!-- Billing Cycle -->
                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="billing_cycle">
                            Billing Cycle <span class="text-red-500">*</span>
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.select name="billing_cycle" id="billing_cycle" required x-model="billingCycle">
                                @foreach($billingCycles as $cycle)
                                    <option value="{{ $cycle->value }}">{{ $cycle->label }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    </div>

                    <!-- Custom Days -->
                    <div class="sm:col-span-3 field-wrapper" x-show="billingCycle === 'custom'" x-cloak>
                        <x-ui.label for="custom_days">
                            Custom Days <span class="text-red-500">*</span>
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="number" name="custom_days" id="custom_days"
                                placeholder="e.g., 90 for quarterly" />
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Enter the number of days between renewals</p>
                    </div>

                    <!-- Start Date -->
                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="start_date">
                            Start Date <span class="text-red-500">*</span>
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="date" name="start_date" id="start_date" required value="{{ date('Y-m-d') }}" />
                        </div>
                    </div>

                    <!-- Next Renewal Date -->
                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="next_renewal_date">
                            Next Renewal Date <span class="text-red-500">*</span>
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="date" name="next_renewal_date" id="next_renewal_date" required />
                        </div>
                        <p class="mt-1 text-xs text-slate-500">The date when the next payment is due</p>
                    </div>

                    <!-- Status -->
                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="status">
                            Status <span class="text-red-500">*</span>
                        </x-ui.label>
                        <div class="mt-2">
                            <x-ui.select name="status" id="status" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" {{ $status->value === 'active' ? 'selected' : '' }}>{{ $status->label }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="notes">Notes</x-ui.label>
                        <div class="mt-2">
                            <x-ui.textarea name="notes" id="notes" rows="4"
                                placeholder="Optional notes about this subscription..."></x-ui.textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
            <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel', 'subscription-create')">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="default" form="subscription-create-form">
                Create Subscription
            </x-ui.button>
        </div>
    </x-slide-panel>

    <!-- Edit Subscription Slide Panels -->
    @foreach($subscriptions as $subscription)
        <x-slide-panel name="subscription-edit-{{ $subscription->id }}" :show="false" maxWidth="2xl">
            <!-- Header -->
            <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
                <h2 class="text-2xl font-bold text-slate-900">Edit Subscription: {{ $subscription->vendor_name }}</h2>
                <button type="button" @click="$dispatch('close-slide-panel', 'subscription-edit-{{ $subscription->id }}')" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto px-8 py-6">
                <form id="subscription-edit-form-{{ $subscription->id }}"
                    x-data="{
                        billingCycle: '{{ $subscription->billing_cycle }}',
                        loading: false,
                        async submit(event) {
                            event.preventDefault();
                            this.loading = true;

                            // Clear previous errors
                            document.querySelectorAll('#subscription-edit-form-{{ $subscription->id }} .error-message').forEach(el => el.remove());

                            const formData = new FormData(event.target);

                            try {
                                const response = await fetch('{{ route('subscriptions.update', $subscription) }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    },
                                    body: formData
                                });

                                const data = await response.json();

                                if (response.ok) {
                                    $dispatch('close-slide-panel', 'subscription-edit-{{ $subscription->id }}');
                                    $dispatch('toast', { message: 'Subscription updated successfully!', type: 'success' });
                                    setTimeout(() => window.location.reload(), 500);
                                } else {
                                    if (data.errors) {
                                        Object.keys(data.errors).forEach(key => {
                                            const input = document.querySelector(`#subscription-edit-form-{{ $subscription->id }} [name='${key}']`);
                                            if (input) {
                                                const wrapper = input.closest('div');
                                                const errorDiv = document.createElement('p');
                                                errorDiv.className = 'error-message mt-2 text-sm text-red-600';
                                                errorDiv.textContent = data.errors[key][0];
                                                wrapper.appendChild(errorDiv);
                                            }
                                        });
                                        $dispatch('toast', { message: 'Please correct the errors in the form.', type: 'error' });
                                    }
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                $dispatch('toast', { message: 'An error occurred. Please try again.', type: 'error' });
                            } finally {
                                this.loading = false;
                            }
                        }
                    }"
                    @submit="submit"
                >
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                        <!-- Vendor Name -->
                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="vendor_name_edit_{{ $subscription->id }}">
                                Vendor Name <span class="text-red-500">*</span>
                            </x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="text" name="vendor_name" id="vendor_name_edit_{{ $subscription->id }}" required
                                    value="{{ $subscription->vendor_name }}" />
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="price_edit_{{ $subscription->id }}">
                                Price (RON) <span class="text-red-500">*</span>
                            </x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="number" step="0.01" name="price" id="price_edit_{{ $subscription->id }}" required
                                    value="{{ $subscription->price }}" />
                            </div>
                        </div>

                        <!-- Billing Cycle -->
                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="billing_cycle_edit_{{ $subscription->id }}">
                                Billing Cycle <span class="text-red-500">*</span>
                            </x-ui.label>
                            <div class="mt-2">
                                <x-ui.select name="billing_cycle" id="billing_cycle_edit_{{ $subscription->id }}" required x-model="billingCycle">
                                    @foreach($billingCycles as $cycle)
                                        <option value="{{ $cycle->value }}" {{ $subscription->billing_cycle === $cycle->value ? 'selected' : '' }}>{{ $cycle->label }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                        </div>

                        <!-- Custom Days -->
                        <div class="sm:col-span-3 field-wrapper" x-show="billingCycle === 'custom'" x-cloak>
                            <x-ui.label for="custom_days_edit_{{ $subscription->id }}">
                                Custom Days <span class="text-red-500">*</span>
                            </x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="number" name="custom_days" id="custom_days_edit_{{ $subscription->id }}"
                                    value="{{ $subscription->custom_days }}" />
                            </div>
                        </div>

                        <!-- Start Date -->
                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="start_date_edit_{{ $subscription->id }}">
                                Start Date <span class="text-red-500">*</span>
                            </x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="date" name="start_date" id="start_date_edit_{{ $subscription->id }}" required
                                    value="{{ $subscription->start_date->format('Y-m-d') }}" />
                            </div>
                        </div>

                        <!-- Next Renewal Date -->
                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="next_renewal_date_edit_{{ $subscription->id }}">
                                Next Renewal Date <span class="text-red-500">*</span>
                            </x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="date" name="next_renewal_date" id="next_renewal_date_edit_{{ $subscription->id }}" required
                                    value="{{ $subscription->next_renewal_date->format('Y-m-d') }}" />
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="sm:col-span-6 field-wrapper">
                            <x-ui.label for="status_edit_{{ $subscription->id }}">
                                Status <span class="text-red-500">*</span>
                            </x-ui.label>
                            <div class="mt-2">
                                <x-ui.select name="status" id="status_edit_{{ $subscription->id }}" required>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->value }}" {{ $subscription->status === $status->value ? 'selected' : '' }}>{{ $status->label }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="sm:col-span-6 field-wrapper">
                            <x-ui.label for="notes_edit_{{ $subscription->id }}">Notes</x-ui.label>
                            <div class="mt-2">
                                <x-ui.textarea name="notes" id="notes_edit_{{ $subscription->id }}" rows="4">{{ $subscription->notes }}</x-ui.textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
                <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel', 'subscription-edit-{{ $subscription->id }}')">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="default" form="subscription-edit-form-{{ $subscription->id }}">
                    Update Subscription
                </x-ui.button>
            </div>
        </x-slide-panel>
    @endforeach
</x-app-layout>
