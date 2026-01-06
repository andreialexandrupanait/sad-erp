<x-app-layout>
    <x-slot name="pageTitle">{{date('H') < 12 ? __('Good morning') : (date('H') < 18 ? __('Good afternoon') : __('Good evening'))}}, {{ auth()->user()->name }}</x-slot>

    <x-slot name="headerActions">
        <x-dashboard.quick-actions :quickActions="$quickActions" />
    </x-slot>

    @php
        $hideBreadcrumb = true;
    @endphp

    <div class="p-6 space-y-6" x-data>
        {{-- Key Metrics Row --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-widgets.metrics.stat-card
                :title="__('Active Clients')"
                :value="$activeClients"
                :subtitle="__('out of :total total', ['total' => $totalClients])"
                color="blue"
                :href="route('clients.index')"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>

            <x-widgets.metrics.stat-card
                :title="__('Subscriptions')"
                :value="$activeSubscriptions"
                :subtitle="number_format($monthlySubscriptionCost, 2) . ' ' . __('RON/month')"
                color="green"
                :href="route('subscriptions.index')"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>

            <x-widgets.metrics.stat-card
                :title="__('Domains')"
                :value="$activeDomains"
                :subtitle="$expiringDomains->count() > 0 ? $expiringDomains->count() . ' ' . __('expire in 30 days') : __('All are valid')"
                :color="$expiringDomains->count() > 0 ? 'orange' : 'purple'"
                :href="route('domains.index')"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>

            <x-widgets.metrics.stat-card
                :title="__('Access & Credentials')"
                :value="$totalCredentials"
                :subtitle="__('credentials saved')"
                color="indigo"
                :href="route('credentials.index')"
            >
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </x-slot>
            </x-widgets.metrics.stat-card>
        </div>

        {{-- Financial Overview --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-widgets.financial.summary-card
                :title="__('Current Month Revenue')"
                :amount="number_format($currentMonthRevenue, 2) . ' RON'"
                :yearlyAmount="number_format($yearlyRevenue, 2) . ' RON'"
                type="revenue"
                :href="route('financial.revenues.index')"
            />

            <x-widgets.financial.summary-card
                :title="__('Current Month Expenses')"
                :amount="number_format($currentMonthExpenses, 2) . ' RON'"
                :yearlyAmount="number_format($yearlyExpenses, 2) . ' RON'"
                type="expense"
                :href="route('financial.expenses.index')"
            />

            <x-widgets.financial.summary-card
                :title="__('Current Month Net Profit')"
                :amount="number_format($currentMonthProfit, 2) . ' RON'"
                :yearlyAmount="number_format($yearlyProfit, 2) . ' RON'"
                type="profit"
                :href="route('financial.dashboard')"
            />
        </div>

        {{-- Monthly Trend & Top Clients Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Monthly Trend Chart --}}
            <x-dashboard.financial-trend-chart
                :yearlyRevenueTrend="$yearlyRevenueTrend"
                :yearlyExpenseTrend="$yearlyExpenseTrend"
                :yearlyProfitTrend="$yearlyProfitTrend"
            />

            {{-- Top Clients --}}
            <x-widgets.activity.list-card
            :title="__('Top Clients')"
            :items="$topClients"
            :emptyMessage="__('No clients with recorded revenue')"
            :viewAllHref="route('clients.index')"
        >
            @foreach($topClients as $index => $client)
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-sm font-bold">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <p class="font-medium text-slate-900">{{ $client->display_name }}</p>
                            <p class="text-xs text-slate-500">{{ $client->email ?? __('No email') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-slate-900">{{ number_format($client->total_revenue, 2) }} RON</p>
                        <p class="text-xs text-slate-500">{{ __('total revenue') }}</p>
                    </div>
                </div>
            @endforeach
        </x-widgets.activity.list-card>
        </div>

        {{-- Business Analytics Row - 4 widgets in single line (1/4 each) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Profit Margin --}}
            <x-dashboard.profit-margin-widget
                :currentMonthProfitMargin="$currentMonthProfitMargin"
                :yearlyProfitMargin="$yearlyProfitMargin"
            />

            {{-- Subscription Costs --}}
            <x-dashboard.subscription-cost-widget
                :monthlySubscriptionCost="$monthlySubscriptionCost"
                :annualProjectedCost="$annualProjectedCost"
                :activeSubscriptionsCount="$activeSubscriptionsCount"
                :pausedSubscriptionsCount="$pausedSubscriptionsCount"
                :cancelledSubscriptionsCount="$cancelledSubscriptionsCount"
            />

            {{-- Revenue Concentration --}}
            <x-dashboard.revenue-concentration-widget
                :revenueConcentration="$revenueConcentration"
                :topThreeClientsRevenue="$topThreeClientsRevenue"
                :yearlyRevenue="$yearlyRevenue"
            />

            {{-- Month-to-Month Growth --}}
            <x-dashboard.growth-metrics-widget
                :revenueGrowth="$revenueGrowth"
                :expenseGrowth="$expenseGrowth"
                :clientGrowth="$clientGrowth"
                :newClientsThisMonth="$newClientsThisMonth"
                :newClientsLastMonth="$newClientsLastMonth"
            />
        </div>

        {{-- Secondary Row - 3 widgets (1/3 each) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Next Renewals --}}
            <x-widgets.activity.list-card
                :title="__('Next Renewals')"
                :items="$upcomingRenewals['subscriptions']"
                :emptyMessage="__('No renewals in the next 30 days')"
                :viewAllHref="route('subscriptions.index')"
            >
                @foreach($upcomingRenewals['subscriptions']->take(5) as $subscription)
                    @php
                        $daysUntilRenewal = now()->startOfDay()->diffInDays($subscription->next_renewal_date->startOfDay(), false);
                        $isPast = $daysUntilRenewal < 0;
                        $daysText = abs($daysUntilRenewal);
                    @endphp
                    <div class="flex items-start justify-between p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer"
                         onclick="window.location.href='{{ route('subscriptions.edit', $subscription) }}'">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-900 truncate">{{ $subscription->vendor_name }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($subscription->price, 2) }} RON / {{ $subscription->billing_cycle }}</p>
                        </div>
                        <div class="text-right flex-shrink-0 ml-2">
                            @if($isPast)
                                <p class="text-xs font-semibold text-red-700">
                                    {{ __('Overdue') }} {{ $daysText }} {{ $daysText == 1 ? __('day') : __('days') }}
                                </p>
                            @else
                                <p class="text-xs font-semibold text-orange-700">
                                    {{ $daysText }} {{ $daysText == 1 ? __('day') : __('days') }}
                                </p>
                            @endif
                            <p class="text-xs text-slate-500">{{ $subscription->next_renewal_date->format('d.m.Y') }}</p>
                        </div>
                    </div>
                @endforeach
            </x-widgets.activity.list-card>

            {{-- Domain Management --}}
            <x-dashboard.domain-widget
                :expiringDomains="$expiringDomains"
                :domainRenewals30Days="$domainRenewals30Days"
                :domainRenewals60Days="$domainRenewals60Days"
                :domainRenewals90Days="$domainRenewals90Days"
            />

            {{-- Category Expenses --}}
            <x-dashboard.expense-category-chart :categoryData="$categoryBreakdown" :year="now()->year" />
        </div>

        {{-- Alerts --}}
        @if($overdueSubscriptions->count() > 0 || $expiringDomains->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Overdue Subscriptions --}}
            @if($overdueSubscriptions->count() > 0)
                <x-widgets.alerts.alert-card
                    :title="__('Overdue Subscriptions')"
                    :items="$overdueSubscriptions"
                    type="error"
                >
                    @foreach($overdueSubscriptions->take(5) as $subscription)
                        <div class="flex items-start justify-between p-3 bg-white rounded-lg hover:shadow-sm transition-shadow cursor-pointer"
                             onclick="window.location.href='{{ route('subscriptions.edit', $subscription) }}'">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-900 truncate">{{ $subscription->vendor_name }}</p>
                                <p class="text-xs text-slate-500">{{ number_format($subscription->price, 2) }} RON</p>
                            </div>
                            <div class="text-right flex-shrink-0 ml-2">
                                @php
                                    $overdueDays = abs(now()->startOfDay()->diffInDays($subscription->next_renewal_date->startOfDay(), false));
                                @endphp
                                <p class="text-xs font-semibold text-red-700">
                                    {{ __('Overdue') }} {{ $overdueDays }} {{ $overdueDays == 1 ? __('day') : __('days') }}
                                </p>
                                <p class="text-xs text-slate-500">{{ $subscription->next_renewal_date->format('d.m.Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </x-widgets.alerts.alert-card>
            @endif
        </div>
        @endif
    </div>

    {{-- Toast Notifications --}}
    <x-toast />

    {{-- Quick Add Client Slide-Over --}}
    <div x-data="quickAddClient()"
         @open-quick-add-client.window="open()">

        <div x-show="isOpen"
             x-cloak
             class="fixed inset-0 z-[100] overflow-hidden"
             aria-labelledby="slide-over-client-title"
             role="dialog"
             aria-modal="true">

            {{-- Backdrop --}}
            <div x-show="isOpen"
                 x-transition:enter="ease-in-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"
                 @click="close()"></div>

            {{-- Panel --}}
            <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div x-show="isOpen"
                     x-transition:enter="transform transition ease-in-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-300"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="w-screen max-w-lg"
                     @keydown.escape.window="close()">

                    <div class="flex h-full flex-col overflow-y-auto bg-white shadow-xl">
                        {{-- Header --}}
                        <div class="bg-slate-50 px-4 py-6 sm:px-6 border-b">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-slate-900" id="slide-over-client-title">
                                    {{ __('Quick Add Client') }}
                                </h2>
                                <button type="button"
                                        @click="close()"
                                        class="rounded-md text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="sr-only">{{ __('Close') }}</span>
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Form --}}
                        <form @submit.prevent="submit()" class="flex-1 flex flex-col">
                            <div class="flex-1 px-4 py-6 sm:px-6 space-y-6">
                                <x-client-form-fields
                                    :statuses="$clientStatuses"
                                    prefix="quick_client_"
                                    :compact="true"
                                />
                            </div>

                            {{-- Footer --}}
                            <div class="flex-shrink-0 border-t border-slate-200 px-4 py-4 sm:px-6">
                                <div class="flex justify-end gap-3">
                                    <x-ui.button type="button" variant="ghost" @click="close()">
                                        {{ __('Cancel') }}
                                    </x-ui.button>
                                    <x-ui.button type="submit" variant="default" x-bind:disabled="saving">
                                        <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-text="saving ? '{{ __('Creating...') }}' : '{{ __('Create Client') }}'"></span>
                                    </x-ui.button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Add Credential Slide-Over --}}
    <div x-data="quickAddCredential()"
         @open-quick-add-credential.window="open()">

        <div x-show="isOpen"
             x-cloak
             class="fixed inset-0 z-[100] overflow-hidden"
             aria-labelledby="slide-over-credential-title"
             role="dialog"
             aria-modal="true">

            {{-- Backdrop --}}
            <div x-show="isOpen"
                 x-transition:enter="ease-in-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"
                 @click="close()"></div>

            {{-- Panel --}}
            <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div x-show="isOpen"
                     x-transition:enter="transform transition ease-in-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-300"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="w-screen max-w-lg"
                     @keydown.escape.window="close()">

                    <div class="flex h-full flex-col overflow-y-auto bg-white shadow-xl">
                        {{-- Header --}}
                        <div class="bg-slate-50 px-4 py-6 sm:px-6 border-b">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-slate-900" id="slide-over-credential-title">
                                    {{ __('Quick Add Credential') }}
                                </h2>
                                <button type="button"
                                        @click="close()"
                                        class="rounded-md text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="sr-only">{{ __('Close') }}</span>
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Form --}}
                        <form @submit.prevent="submit()" class="flex-1 flex flex-col">
                            <div class="flex-1 px-4 py-6 sm:px-6 space-y-6">
                                <x-credential-form-fields
                                    :clients="$clients"
                                    :platforms="$platforms"
                                    :sites="$sites"
                                    :clientStatuses="$clientStatuses"
                                    prefix="quick_cred_"
                                    :compact="true"
                                />
                            </div>

                            {{-- Footer --}}
                            <div class="flex-shrink-0 border-t border-slate-200 px-4 py-4 sm:px-6">
                                <div class="flex justify-end gap-3">
                                    <x-ui.button type="button" variant="ghost" @click="close()">
                                        {{ __('Cancel') }}
                                    </x-ui.button>
                                    <x-ui.button type="submit" variant="default" x-bind:disabled="saving">
                                        <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-text="saving ? '{{ __('Creating...') }}' : '{{ __('Create Credential') }}'"></span>
                                    </x-ui.button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Add Domain Slide-Over --}}
    <x-dashboard.quick-add-slideover
        id="domain"
        :title="__('Quick Add Domain')"
        event="open-quick-add-domain"
        route="{{ route('domains.store') }}"
        prefix="quick_domain_"
        :firstField="'domain_name'"
        :buttonText="__('Create Domain')"
    >
        <x-domain-form-fields
            :clients="$clients"
            :registrars="$registrars"
            :statuses="$domainStatuses"
            :clientStatuses="$clientStatuses"
            prefix="quick_domain_"
            :compact="true"
        />
    </x-dashboard.quick-add-slideover>

    {{-- Quick Add Subscription Slide-Over --}}
    <x-dashboard.quick-add-slideover
        id="subscription"
        :title="__('Quick Add Subscription')"
        event="open-quick-add-subscription"
        route="{{ route('subscriptions.store') }}"
        prefix="quick_sub_"
        :firstField="'vendor_name'"
        :buttonText="__('Create Subscription')"
    >
        <x-subscription-form-fields
            :billingCycles="$billingCycles"
            :statuses="$statuses"
            :currencies="$currencies"
            prefix="quick_sub_"
            :compact="true"
        />
    </x-dashboard.quick-add-slideover>

    {{-- Quick Add Expense Slide-Over --}}
    <x-dashboard.quick-add-slideover
        id="expense"
        :title="__('Quick Add Expense')"
        event="open-quick-add-expense"
        route="{{ route('financial.expenses.store') }}"
        prefix="quick_expense_"
        :firstField="'document_name'"
        :buttonText="__('Create Expense')"
    >
        <x-expense-form-fields
            :categories="$expenseCategories"
            :currencies="$currencies"
            prefix="quick_expense_"
            :compact="true"
        />
    </x-dashboard.quick-add-slideover>

    {{-- Quick Add Revenue Slide-Over --}}
    <x-dashboard.quick-add-slideover
        id="revenue"
        :title="__('Quick Add Revenue')"
        event="open-quick-add-revenue"
        route="{{ route('financial.revenues.store') }}"
        prefix="quick_revenue_"
        :firstField="'document_name'"
        :buttonText="__('Create Revenue')"
    >
        <x-revenue-form-fields
            :clients="$clients"
            :currencies="$currencies"
            :clientStatuses="$clientStatuses"
            prefix="quick_revenue_"
            :compact="true"
        />
    </x-dashboard.quick-add-slideover>

    <script>
    function quickAddClient() {
        return {
            isOpen: false,
            saving: false,

            open() {
                this.isOpen = true;
                this.$nextTick(() => {
                    const firstInput = document.querySelector('[name="quick_client_name"]');
                    if (firstInput) firstInput.focus();
                });
            },

            close() {
                this.isOpen = false;
                this.resetForm();
            },

            resetForm() {
                const form = this.$el.querySelector('form');
                if (form) {
                    form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), select, textarea').forEach(el => {
                        if (el.name && el.name.startsWith('quick_client_')) {
                            el.value = '';
                        }
                    });
                    form.querySelectorAll('input[type="checkbox"]').forEach(el => {
                        if (el.name && el.name.startsWith('quick_client_')) {
                            el.checked = false;
                        }
                    });
                }
            },

            async submit() {
                this.saving = true;
                const formData = this.collectFormData();

                try {
                    const response = await fetch('{{ route('clients.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (data.errors) {
                            const firstError = Object.values(data.errors)[0];
                            showToast(Array.isArray(firstError) ? firstError[0] : firstError, 'error');
                        }
                        return;
                    }

                    showToast(data.message || '{{ __("Client created successfully") }}');
                    this.close();

                    // Optionally redirect to the new client
                    if (data.client && data.client.id) {
                        window.location.href = '{{ url("clients") }}/' + data.client.id;
                    }

                } catch (error) {
                    console.error('Error creating client:', error);
                    showToast('{{ __("An error occurred. Please try again.") }}', 'error');
                } finally {
                    this.saving = false;
                }
            },

            collectFormData() {
                const data = {};
                const form = this.$el.querySelector('form');
                if (!form) return data;

                form.querySelectorAll('input, select, textarea').forEach(el => {
                    if (el.name && el.name.startsWith('quick_client_')) {
                        const key = el.name.replace('quick_client_', '');
                        if (el.type === 'checkbox') {
                            data[key] = el.checked ? 1 : 0;
                        } else if (el.value) {
                            data[key] = el.value;
                        }
                    }
                });

                return data;
            }
        };
    }

    function quickAddCredential() {
        return {
            isOpen: false,
            saving: false,

            open() {
                this.isOpen = true;
                this.$nextTick(() => {
                    const firstInput = document.querySelector('[name="quick_cred_site_name"]');
                    if (firstInput) firstInput.focus();
                });
            },

            close() {
                this.isOpen = false;
                this.resetForm();
            },

            resetForm() {
                const form = this.$el.querySelector('form');
                if (form) {
                    form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
                        if (el.name && el.name.startsWith('quick_cred_')) {
                            el.value = '';
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });
                }
            },

            async submit() {
                this.saving = true;
                const formData = this.collectFormData();

                try {
                    const response = await fetch('{{ route('credentials.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (data.errors) {
                            const firstError = Object.values(data.errors)[0];
                            showToast(Array.isArray(firstError) ? firstError[0] : firstError, 'error');
                        }
                        return;
                    }

                    showToast(data.message || '{{ __("Credential created successfully") }}');
                    this.close();

                } catch (error) {
                    console.error('Error creating credential:', error);
                    showToast('{{ __("An error occurred. Please try again.") }}', 'error');
                } finally {
                    this.saving = false;
                }
            },

            collectFormData() {
                const data = {};
                const form = this.$el.querySelector('form');
                if (!form) return data;

                form.querySelectorAll('input, select, textarea').forEach(el => {
                    if (el.name && el.name.startsWith('quick_cred_')) {
                        const key = el.name.replace('quick_cred_', '');
                        if (el.type === 'checkbox') {
                            data[key] = el.checked;
                        } else if (el.value) {
                            data[key] = el.value;
                        }
                    }
                });

                return data;
            }
        };
    }

    function showToast(message, type = 'success') {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message, type }
        }));
    }
    </script>
</x-app-layout>
