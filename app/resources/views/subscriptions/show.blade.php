<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Subscription: {{ $subscription->vendor_name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('subscriptions.edit', $subscription) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</a>
                <a href="{{ route('subscriptions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Subscription Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Subscription Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Vendor Name</label>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $subscription->vendor_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Price</label>
                            <p class="mt-1 text-lg font-semibold text-blue-600 dark:text-blue-400">{{ number_format($subscription->price, 2) }} RON</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Billing Cycle</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">
                                {{ ucfirst($subscription->billing_cycle) }}
                                @if($subscription->billing_cycle === 'custom')
                                    <span class="text-sm text-gray-500">(every {{ $subscription->custom_days }} days)</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                            <p class="mt-1">
                                @if($subscription->status === 'active')
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">Active</span>
                                @elseif($subscription->status === 'paused')
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">Paused</span>
                                @else
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">Cancelled</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $subscription->start_date->format('M d, Y') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Next Renewal Date</label>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $subscription->next_renewal_date->format('M d, Y') }}</p>
                            <p class="mt-1">
                                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $subscription->renewal_badge_color }}">
                                    {{ $subscription->renewal_badge_emoji }} {{ $subscription->renewal_text }}
                                </span>
                            </p>
                        </div>

                        @if ($subscription->notes)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Notes</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $subscription->notes }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Created</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $subscription->created_at->format('M d, Y H:i') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $subscription->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cost Projections -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Cost Projections</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Estimated Monthly Cost</label>
                            <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">
                                @if($subscription->billing_cycle === 'monthly')
                                    {{ number_format($subscription->price, 2) }} RON
                                @elseif($subscription->billing_cycle === 'annual')
                                    {{ number_format($subscription->price / 12, 2) }} RON
                                @else
                                    {{ number_format(($subscription->price / $subscription->custom_days) * 30, 2) }} RON
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Estimated Annual Cost</label>
                            <p class="mt-1 text-2xl font-bold text-purple-600 dark:text-purple-400">
                                @if($subscription->billing_cycle === 'monthly')
                                    {{ number_format($subscription->price * 12, 2) }} RON
                                @elseif($subscription->billing_cycle === 'annual')
                                    {{ number_format($subscription->price, 2) }} RON
                                @else
                                    {{ number_format(($subscription->price / $subscription->custom_days) * 365, 2) }} RON
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Renewal Change History (Audit Log) -->
            @if($subscription->logs->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Renewal Change History</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date Changed</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Old Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">New Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Reason</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Changed By</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($subscription->logs as $log)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $log->changed_at->format('M d, Y H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $log->old_renewal_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $log->new_renewal_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $log->change_reason }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                @if($log->changedBy)
                                                    {{ $log->changedBy->name }}
                                                @else
                                                    <span class="text-gray-500 italic">System</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
