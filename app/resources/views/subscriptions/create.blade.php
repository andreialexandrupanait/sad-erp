<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Add New Subscription
            </h2>
            <a href="{{ route('subscriptions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('subscriptions.store') }}" x-data="{ billingCycle: 'monthly' }">
                        @csrf

                        <!-- Vendor Name -->
                        <div class="mb-4">
                            <label for="vendor_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Vendor Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="vendor_name"
                                id="vendor_name"
                                value="{{ old('vendor_name') }}"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('vendor_name') border-red-500 @enderror"
                                placeholder="e.g., Adobe, Microsoft, Netflix"
                            >
                            @error('vendor_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Price -->
                        <div class="mb-4">
                            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Price (RON) <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                name="price"
                                id="price"
                                value="{{ old('price') }}"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('price') border-red-500 @enderror"
                                placeholder="0.00"
                            >
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Billing Cycle -->
                        <div class="mb-4">
                            <label for="billing_cycle" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Billing Cycle <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="billing_cycle"
                                id="billing_cycle"
                                required
                                x-model="billingCycle"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('billing_cycle') border-red-500 @enderror"
                            >
                                @foreach(\App\Models\Subscription::billingCycleOptions() as $value => $label)
                                    <option value="{{ $value }}" {{ old('billing_cycle') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('billing_cycle')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Custom Days (only if billing_cycle is 'custom') -->
                        <div class="mb-4" x-show="billingCycle === 'custom'" x-cloak>
                            <label for="custom_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Custom Days <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                name="custom_days"
                                id="custom_days"
                                value="{{ old('custom_days') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('custom_days') border-red-500 @enderror"
                                placeholder="e.g., 90 for quarterly"
                            >
                            <p class="mt-1 text-xs text-gray-500">Enter the number of days between renewals (e.g., 90 for quarterly)</p>
                            @error('custom_days')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Start Date -->
                        <div class="mb-4">
                            <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                name="start_date"
                                id="start_date"
                                value="{{ old('start_date', date('Y-m-d')) }}"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('start_date') border-red-500 @enderror"
                            >
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Next Renewal Date -->
                        <div class="mb-4">
                            <label for="next_renewal_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Next Renewal Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                name="next_renewal_date"
                                id="next_renewal_date"
                                value="{{ old('next_renewal_date') }}"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('next_renewal_date') border-red-500 @enderror"
                            >
                            <p class="mt-1 text-xs text-gray-500">The date when the next payment is due</p>
                            @error('next_renewal_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="status"
                                id="status"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-500 @enderror"
                            >
                                @foreach(\App\Models\Subscription::statusOptions() as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', 'active') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notes
                            </label>
                            <textarea
                                name="notes"
                                id="notes"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('notes') border-red-500 @enderror"
                                placeholder="Optional notes about this subscription..."
                            >{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end gap-2 mt-6">
                            <a href="{{ route('subscriptions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Subscription
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
