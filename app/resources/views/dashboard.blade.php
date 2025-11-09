<x-app-layout>
    @section('page-title', 'Dashboard')
    @section('page-description', 'Welcome back, ' . auth()->user()->name)

    <!-- Welcome Message -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Good {{ date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}! 👋</h1>
        <p class="text-gray-600">Here's what's happening with your business today.</p>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stat-card
            title="Total Clients"
            value="{{ \App\Models\Client::count() }}"
            color="blue"
            :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z\'/></svg>'"
        />

        <x-stat-card
            title="Active Subscriptions"
            value="{{ \App\Models\Subscription::where('status', 'active')->count() }}"
            color="green"
            :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z\'/></svg>'"
        />

        <x-stat-card
            title="Total Domains"
            value="{{ \App\Models\Domain::count() }}"
            color="purple"
            :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9\'/></svg>'"
        />

        <x-stat-card
            title="Expiring Soon"
            value="{{ \App\Models\Domain::where('expiry_date', '<=', now()->addDays(30))->where('expiry_date', '>=', now())->count() }}"
            color="orange"
            :icon="'<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>'"
        />
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Recent Clients -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Clients</h3>
                    <a href="{{ route('clients.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">View all →</a>
                </div>
                <div class="p-6">
                    @php
                        $recentClients = \App\Models\Client::latest()->take(5)->get();
                    @endphp

                    @if($recentClients->isEmpty())
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-gray-500 mb-4">No clients yet</p>
                            <a href="{{ route('clients.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                                Add your first client
                            </a>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($recentClients as $client)
                                <a href="{{ route('clients.show', $client) }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-medium">
                                            {{ strtoupper(substr($client->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 group-hover:text-primary-600 transition-colors">{{ $client->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $client->company ?? $client->email }}</div>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full {{ $client->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($client->status) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('clients.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Add Client</div>
                            <div class="text-sm text-gray-500">Create new client</div>
                        </div>
                    </a>

                    <a href="{{ route('subscriptions.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                        <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">New Subscription</div>
                            <div class="text-sm text-gray-500">Track a subscription</div>
                        </div>
                    </a>

                    <a href="{{ route('domains.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Add Domain</div>
                            <div class="text-sm text-gray-500">Register new domain</div>
                        </div>
                    </a>

                    <a href="{{ route('credentials.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Save Credential</div>
                            <div class="text-sm text-gray-500">Store access info</div>
                        </div>
                    </a>
                </div>
            </div>
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
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Alerts & Notifications</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($expiringDomains as $domain)
                    <div class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">Domain expiring soon</div>
                            <div class="text-sm text-gray-600">{{ $domain->domain_name }} expires {{ $domain->expiry_date->diffForHumans() }}</div>
                        </div>
                        <a href="{{ route('domains.edit', $domain) }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Renew →</a>
                    </div>
                @endforeach

                @foreach($overdueSubscriptions as $subscription)
                    <div class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">Overdue subscription</div>
                            <div class="text-sm text-gray-600">{{ $subscription->vendor_name }} - {{ abs($subscription->days_until_renewal) }} days overdue</div>
                        </div>
                        <a href="{{ route('subscriptions.edit', $subscription) }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Update →</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-app-layout>
