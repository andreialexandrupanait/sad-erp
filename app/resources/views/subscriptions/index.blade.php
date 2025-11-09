<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Abonamente (Subscriptions)
            </h2>
            <a href="{{ route('subscriptions.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + Add Subscription
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('info'))
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Active Subscriptions -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Active Subscriptions</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['active'] }}</p>
                            </div>
                            <div class="text-4xl">✅</div>
                        </div>
                    </div>
                </div>

                <!-- Paused Subscriptions -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Paused Subscriptions</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['paused'] }}</p>
                            </div>
                            <div class="text-4xl">⏸️</div>
                        </div>
                    </div>
                </div>

                <!-- Cancelled Subscriptions -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Cancelled Subscriptions</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['cancelled'] }}</p>
                            </div>
                            <div class="text-4xl">❌</div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Cost -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Estimated Monthly Cost</p>
                                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['monthly_cost'], 2) }} RON</p>
                            </div>
                            <div class="text-4xl">📅</div>
                        </div>
                    </div>
                </div>

                <!-- Annual Cost -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Estimated Annual Cost</p>
                                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['annual_cost'], 2) }} RON</p>
                            </div>
                            <div class="text-4xl">💰</div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Renewals -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Renewals Next 30 Days</p>
                                <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['upcoming_renewals'] }}</p>
                            </div>
                            <div class="text-4xl">🔔</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('subscriptions.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <!-- Search -->
                            <div>
                                <input
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Search by vendor..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Statuses</option>
                                    @foreach(\App\Models\Subscription::statusOptions() as $value => $label)
                                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Billing Cycle Filter -->
                            <div>
                                <select name="billing_cycle" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Billing Cycles</option>
                                    @foreach(\App\Models\Subscription::billingCycleOptions() as $value => $label)
                                        <option value="{{ $value }}" {{ request('billing_cycle') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Renewal Range Filter -->
                            <div>
                                <select name="renewal_range" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Renewal Ranges</option>
                                    <option value="overdue" {{ request('renewal_range') === 'overdue' ? 'selected' : '' }}>🚨 Overdue</option>
                                    <option value="urgent" {{ request('renewal_range') === 'urgent' ? 'selected' : '' }}>🔴 Urgent (0-7 days)</option>
                                    <option value="warning" {{ request('renewal_range') === 'warning' ? 'selected' : '' }}>🟡 Warning (8-14 days)</option>
                                    <option value="normal" {{ request('renewal_range') === 'normal' ? 'selected' : '' }}>🟢 Normal (>14 days)</option>
                                </select>
                            </div>

                            <!-- Buttons -->
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Filter
                                </button>
                                @if($activeFilters > 0)
                                    <a href="{{ route('subscriptions.index') }}" class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center">
                                        Clear
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <!-- Check Renewals Button -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <form method="POST" action="{{ route('subscriptions.check-renewals') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                🔄 Verifică renewals (Update overdue subscriptions)
                            </button>
                        </form>
                        <p class="text-sm text-gray-500 mt-2">Click to automatically advance all overdue renewal dates based on billing cycles.</p>
                    </div>
                </div>
            </div>

            <!-- Subscriptions Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($subscriptions->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">No subscriptions found. Click "Add Subscription" to create one.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <a href="{{ route('subscriptions.index', array_merge(request()->all(), ['sort' => 'vendor_name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                                Vendor
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <a href="{{ route('subscriptions.index', array_merge(request()->all(), ['sort' => 'price', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                                Price (RON)
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <a href="{{ route('subscriptions.index', array_merge(request()->all(), ['sort' => 'billing_cycle', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                                Billing Cycle
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <a href="{{ route('subscriptions.index', array_merge(request()->all(), ['sort' => 'next_renewal_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                                Next Renewal
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            <a href="{{ route('subscriptions.index', array_merge(request()->all(), ['sort' => 'status', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}">
                                                Status
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($subscriptions as $subscription)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('subscriptions.show', $subscription) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 font-medium">
                                                    {{ $subscription->vendor_name }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">
                                                {{ number_format($subscription->price, 2) }} RON
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100">
                                                {{ ucfirst($subscription->billing_cycle) }}
                                                @if($subscription->billing_cycle === 'custom')
                                                    <span class="text-xs text-gray-500">({{ $subscription->custom_days }} days)</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col">
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $subscription->next_renewal_date->format('M d, Y') }}</span>
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $subscription->renewal_badge_color }} mt-1 inline-block">
                                                        {{ $subscription->renewal_badge_emoji }} {{ $subscription->renewal_text }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($subscription->status === 'active')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                                @elseif($subscription->status === 'paused')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Paused</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end gap-2">
                                                    <a href="{{ route('subscriptions.show', $subscription) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                                    <a href="{{ route('subscriptions.edit', $subscription) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                                    <form method="POST" action="{{ route('subscriptions.destroy', $subscription) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this subscription?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $subscriptions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
