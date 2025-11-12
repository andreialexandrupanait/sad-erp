<x-app-layout>
    <x-slot name="pageTitle">
        {{date('H') < 12 ? 'Bună dimineața' : (date('H') < 18 ? 'Bună ziua' : 'Bună seara')}}, {{ auth()->user()->name }}
    </x-slot>

    @php($hideBreadcrumb = true)

    <x-slot name="headerActions">
        <x-dashboard.quick-actions :quickActions="$quickActions" />
    </x-slot>

    <div class="p-6 space-y-6" x-data>
        {{-- Key Metrics Row --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-widgets.metrics.stat-card
                title="Clienți activi"
                :value="$activeClients"
                :subtitle="'din ' . $totalClients . ' total'"
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
                title="Abonamente"
                :value="$activeSubscriptions"
                :subtitle="number_format($monthlySubscriptionCost, 2) . ' RON/lună'"
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
                title="Domenii"
                :value="$activeDomains"
                :subtitle="$expiringDomains->count() > 0 ? $expiringDomains->count() . ' expiră în 30 zile' : 'Toate sunt valide'"
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
                title="Acces & parole"
                :value="$totalCredentials"
                subtitle="credențiale salvate"
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <x-widgets.financial.summary-card
                title="Venituri luna curentă"
                :amount="number_format($currentMonthRevenue, 2) . ' RON'"
                :yearlyAmount="number_format($yearlyRevenue, 2) . ' RON'"
                type="revenue"
                :href="route('financial.revenues.index')"
            />

            <x-widgets.financial.summary-card
                title="Cheltuieli luna curentă"
                :amount="number_format($currentMonthExpenses, 2) . ' RON'"
                :yearlyAmount="number_format($yearlyExpenses, 2) . ' RON'"
                type="expense"
                :href="route('financial.expenses.index')"
            />

            <x-widgets.financial.summary-card
                title="Profit net luna curentă"
                :amount="number_format($currentMonthProfit, 2) . ' RON'"
                :yearlyAmount="number_format($yearlyProfit, 2) . ' RON'"
                type="profit"
                :href="route('financial.dashboard')"
            />
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Recent Activity & Top Clients --}}
            <div class="lg:col-span-2 space-y-6">
                <x-dashboard.trend-chart :revenueTrend="$revenueTrend" :expenseTrend="$expenseTrend" />

                {{-- Top Clients --}}
                <x-widgets.activity.list-card
                    title="Top clienți"
                    :items="$topClients"
                    emptyMessage="Niciun client cu venituri înregistrate"
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
                                    <p class="text-xs text-slate-500">{{ $client->email ?? 'Fără email' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-slate-900">{{ number_format($client->total_revenue, 2) }} RON</p>
                                <p class="text-xs text-slate-500">venituri totale</p>
                            </div>
                        </div>
                    @endforeach
                </x-widgets.activity.list-card>
            </div>

            {{-- Right Column: Renewals & Alerts --}}
            <div class="space-y-6">
                {{-- Expiring Domains --}}
                @if($expiringDomains->count() > 0)
                    <x-widgets.alerts.alert-card
                        title="Domenii expiră în curând"
                        :items="$expiringDomains"
                        type="warning"
                    >
                        @foreach($expiringDomains->take(5) as $domain)
                            <div class="flex items-start justify-between p-3 bg-white rounded-lg hover:shadow-sm transition-shadow cursor-pointer"
                                 onclick="window.location.href='{{ route('domains.edit', $domain) }}'">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-900 truncate">{{ $domain->domain_name }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $domain->client ? $domain->client->display_name : 'Fără client' }}
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0 ml-2">
                                    <p class="text-xs font-semibold text-orange-700">
                                        {{ $domain->expiry_date->diffInDays(now()) }} zile
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $domain->expiry_date->format('d.m.Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                        @if($expiringDomains->count() > 5)
                            <a href="{{ route('domains.index') }}" class="block mt-3 text-center text-sm text-orange-700 hover:text-orange-900 font-medium">
                                +{{ $expiringDomains->count() - 5 }} mai multe domenii
                            </a>
                        @endif
                    </x-widgets.alerts.alert-card>
                @endif

                {{-- Overdue Subscriptions --}}
                @if($overdueSubscriptions->count() > 0)
                    <x-widgets.alerts.alert-card
                        title="Abonamente restante"
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
                                    <p class="text-xs font-semibold text-red-700">
                                        Restant {{ abs($subscription->next_renewal_date->diffInDays(now())) }} zile
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $subscription->next_renewal_date->format('d.m.Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </x-widgets.alerts.alert-card>
                @endif

                {{-- Upcoming Renewals --}}
                @if($upcomingRenewals['subscriptions']->count() > 0)
                    <x-widgets.activity.list-card
                        title="Reînnoiri următoare"
                        :items="$upcomingRenewals['subscriptions']"
                        emptyMessage="Nicio reînnoire în 30 zile"
                    >
                        @foreach($upcomingRenewals['subscriptions']->take(5) as $subscription)
                            <div class="flex items-start justify-between p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-900 truncate">{{ $subscription->vendor_name }}</p>
                                    <p class="text-xs text-slate-500">{{ number_format($subscription->price, 2) }} RON / {{ $subscription->billing_cycle }}</p>
                                </div>
                                <div class="text-right flex-shrink-0 ml-2">
                                    <p class="text-xs font-semibold text-blue-700">
                                        {{ $subscription->next_renewal_date->diffInDays(now()) }} zile
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $subscription->next_renewal_date->format('d.m.Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </x-widgets.activity.list-card>
                @endif

                {{-- Recent Clients --}}
                <x-widgets.activity.list-card
                    title="Clienți recenți"
                    :items="$recentClients"
                    emptyMessage="Niciun client înregistrat"
                    :viewAllHref="route('clients.index')"
                >
                    @foreach($recentClients as $client)
                        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer"
                             onclick="window.location.href='{{ route('clients.show', $client) }}'">
                            <div class="flex-shrink-0 w-10 h-10 bg-slate-900 text-white rounded-full flex items-center justify-center text-sm font-bold">
                                {{ strtoupper(substr($client->display_name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-900 truncate">{{ $client->display_name }}</p>
                                <p class="text-xs text-slate-500">{{ $client->email ?? 'Fără email' }}</p>
                            </div>
                            @if($client->status)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $client->status->status_name }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </x-widgets.activity.list-card>
            </div>
        </div>
    </div>

    {{-- Toast Notifications --}}
    <x-toast />
</x-app-layout>
