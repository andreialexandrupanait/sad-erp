<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    Good {{ date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">Here's what's happening with your business today.</p>
            </div>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8 space-y-6">
        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Clients -->
            <x-ui.card class="bg-gradient-to-br from-slate-900 to-slate-800 text-white border-slate-800">
                <x-ui.card-content class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-300 mb-1">Total Clients</p>
                            <p class="text-3xl font-bold mb-2">{{ \App\Models\Client::count() }}</p>
                        </div>
                        <div class="flex-shrink-0 w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Active Subscriptions -->
            <x-ui.card>
                <x-ui.card-content class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-600 mb-1">Active Subscriptions</p>
                            <p class="text-3xl font-bold text-slate-900 mb-2">{{ \App\Models\Subscription::where('status', 'active')->count() }}</p>
                        </div>
                        <div class="flex-shrink-0 w-12 h-12 bg-slate-100 text-slate-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Total Domains -->
            <x-ui.card>
                <x-ui.card-content class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-600 mb-1">Total Domains</p>
                            <p class="text-3xl font-bold text-slate-900 mb-2">{{ \App\Models\Domain::count() }}</p>
                        </div>
                        <div class="flex-shrink-0 w-12 h-12 bg-slate-100 text-slate-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Expiring Soon -->
            <x-ui.card>
                <x-ui.card-content class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-600 mb-1">Expiring Soon</p>
                            <p class="text-3xl font-bold text-slate-900 mb-2">{{ \App\Models\Domain::where('expiry_date', '<=', now()->addDays(30))->where('expiry_date', '>=', now())->count() }}</p>
                        </div>
                        <div class="flex-shrink-0 w-12 h-12 bg-slate-100 text-slate-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Clients -->
            <div class="lg:col-span-2">
                <x-ui.card>
                    <x-ui.card-header>
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-slate-900">Recent Clients</h3>
                            <a href="{{ route('clients.index') }}" class="text-sm text-slate-900 hover:text-slate-600 font-medium transition-colors">View all →</a>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        @php
                            $recentClients = \App\Models\Client::latest()->take(5)->get();
                        @endphp

                        @if($recentClients->isEmpty())
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-slate-500 mb-4">No clients yet</p>
                                <x-ui.button variant="default" onclick="window.location.href='{{ route('clients.create') }}'">
                                    Add your first client
                                </x-ui.button>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($recentClients as $client)
                                    <a href="{{ route('clients.show', $client) }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-900 to-slate-800 flex items-center justify-center text-white font-medium">
                                                {{ strtoupper(substr($client->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-medium text-slate-900 group-hover:text-slate-600 transition-colors">{{ $client->name }}</div>
                                                <div class="text-sm text-slate-500">{{ $client->company ?? $client->email }}</div>
                                            </div>
                                        </div>
                                        <x-ui.badge :variant="$client->status === 'active' ? 'success' : 'secondary'">
                                            {{ ucfirst($client->status) }}
                                        </x-ui.badge>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </x-ui.card-content>
                </x-ui.card>
            </div>

            <!-- Quick Actions -->
            <div class="lg:col-span-1">
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="text-lg font-semibold text-slate-900">Quick Actions</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div class="space-y-3">
                            <a href="{{ route('clients.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-slate-900">Add Client</div>
                                    <div class="text-sm text-slate-500">Create new client</div>
                                </div>
                            </a>

                            <a href="{{ route('subscriptions.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-slate-900">New Subscription</div>
                                    <div class="text-sm text-slate-500">Track a subscription</div>
                                </div>
                            </a>

                            <a href="{{ route('domains.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-slate-900">Add Domain</div>
                                    <div class="text-sm text-slate-500">Register new domain</div>
                                </div>
                            </a>

                            <a href="{{ route('credentials.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-slate-900">Save Credential</div>
                                    <div class="text-sm text-slate-500">Store access info</div>
                                </div>
                            </a>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>

        <!-- Alerts & Notifications -->
        @php
            $expiringDomains = \App\Models\Domain::where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>=', now())
                ->orderBy('expiry_date', 'asc')
                ->take(5)
                ->get();

            $overdueSubscriptions = \App\Models\Subscription::where('status', 'active')
                ->where('next_renewal_date', '<', now())
                ->take(5)
                ->get();
        @endphp

        @if($expiringDomains->isNotEmpty() || $overdueSubscriptions->isNotEmpty())
            <x-ui.card>
                <x-ui.card-header>
                    <h3 class="text-lg font-semibold text-slate-900">Alerts & Notifications</h3>
                </x-ui.card-header>
                <x-ui.card-content class="p-0">
                    <div class="divide-y divide-slate-200">
                        @foreach($expiringDomains as $domain)
                            <div class="px-6 py-4 flex items-center gap-4 hover:bg-slate-50 transition-colors">
                                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-slate-900">Domain expiring soon</div>
                                    <div class="text-sm text-slate-600">{{ $domain->domain_name }} expires {{ $domain->expiry_date->diffForHumans() }}</div>
                                </div>
                                <a href="{{ route('domains.edit', $domain) }}" class="text-sm text-slate-900 hover:text-slate-600 font-medium transition-colors">Renew →</a>
                            </div>
                        @endforeach

                        @foreach($overdueSubscriptions as $subscription)
                            <div class="px-6 py-4 flex items-center gap-4 hover:bg-slate-50 transition-colors">
                                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-slate-900">Overdue subscription</div>
                                    <div class="text-sm text-slate-600">{{ $subscription->vendor_name }} - {{ abs($subscription->days_until_renewal) }} days overdue</div>
                                </div>
                                <a href="{{ route('subscriptions.edit', $subscription) }}" class="text-sm text-slate-900 hover:text-slate-600 font-medium transition-colors">Update →</a>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>
