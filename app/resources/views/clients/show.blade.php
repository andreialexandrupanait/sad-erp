<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $client->display_name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('clients.edit', $client) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
                <a href="{{ route('clients.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 dark:text-gray-400 text-sm">Total Offers</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_offers'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 dark:text-gray-400 text-sm">Total Contracts</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_contracts'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 dark:text-gray-400 text-sm">Active Subscriptions</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['active_subscriptions'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 dark:text-gray-400 text-sm">Total Revenue</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">${{ number_format($stats['total_revenue'], 2) }}</div>
                </div>
            </div>

            <!-- Client Details -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Client Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Name</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $client->name }}</p>
                        </div>

                        @if ($client->company)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Company</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $client->company }}</p>
                            </div>
                        @endif

                        @if ($client->email)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">
                                    <a href="mailto:{{ $client->email }}" class="text-blue-600 hover:text-blue-800">{{ $client->email }}</a>
                                </p>
                            </div>
                        @endif

                        @if ($client->phone)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Phone</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">
                                    <a href="tel:{{ $client->phone }}" class="text-blue-600 hover:text-blue-800">{{ $client->phone }}</a>
                                </p>
                            </div>
                        @endif

                        @if ($client->tax_id)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Tax ID</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $client->tax_id }}</p>
                            </div>
                        @endif

                        @if ($client->website)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Website</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">
                                    <a href="{{ $client->website }}" target="_blank" class="text-blue-600 hover:text-blue-800">{{ $client->website }}</a>
                                </p>
                            </div>
                        @endif

                        @if ($client->address)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Address</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $client->address }}</p>
                                <p class="text-gray-900 dark:text-gray-100">
                                    {{ $client->city ? $client->city . ', ' : '' }}
                                    {{ $client->state ? $client->state . ' ' : '' }}
                                    {{ $client->postal_code }}
                                </p>
                                @if ($client->country)
                                    <p class="text-gray-900 dark:text-gray-100">{{ $client->country }}</p>
                                @endif
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                            <p class="mt-1">
                                @if ($client->status === 'active')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @endif
                            </p>
                        </div>

                        @if ($client->notes)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Notes</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $client->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                    <div class="flex flex-wrap gap-2">
                        <a href="#" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create Offer
                        </a>
                        <a href="#" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Create Contract
                        </a>
                        <a href="#" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            Add Subscription
                        </a>
                        <a href="#" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                            Upload File
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
