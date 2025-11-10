@props(['subscription' => null, 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6" x-data="{ billingCycle: '{{ old('billing_cycle', $subscription->billing_cycle ?? 'monthly') }}' }">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <!-- Vendor Name (Required) -->
                <div class="sm:col-span-3">
                    <label for="vendor_name" class="block text-sm font-medium leading-6 text-gray-900">
                        Vendor Name <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2">
                        <input type="text" name="vendor_name" id="vendor_name" required
                            value="{{ old('vendor_name', $subscription->vendor_name ?? '') }}"
                            placeholder="e.g., Adobe, Microsoft, Netflix"
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('vendor_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price (Required) -->
                <div class="sm:col-span-3">
                    <label for="price" class="block text-sm font-medium leading-6 text-gray-900">
                        Price (RON) <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2">
                        <input type="number" step="0.01" name="price" id="price" required
                            value="{{ old('price', $subscription->price ?? '') }}"
                            placeholder="0.00"
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('price')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Billing Cycle (Required) -->
                <div class="sm:col-span-3">
                    <label for="billing_cycle" class="block text-sm font-medium leading-6 text-gray-900">
                        Billing Cycle <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2">
                        <select name="billing_cycle" id="billing_cycle" required x-model="billingCycle"
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            @foreach(\App\Models\Subscription::billingCycleOptions() as $value => $label)
                                <option value="{{ $value }}" {{ old('billing_cycle', $subscription->billing_cycle ?? 'monthly') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('billing_cycle')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Custom Days (only if billing_cycle is 'custom') -->
                <div class="sm:col-span-3" x-show="billingCycle === 'custom'" x-cloak>
                    <label for="custom_days" class="block text-sm font-medium leading-6 text-gray-900">
                        Custom Days <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2">
                        <input type="number" name="custom_days" id="custom_days"
                            value="{{ old('custom_days', $subscription->custom_days ?? '') }}"
                            placeholder="e.g., 90 for quarterly"
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Enter the number of days between renewals (e.g., 90 for quarterly)</p>
                    @error('custom_days')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start Date (Required) -->
                <div class="sm:col-span-3">
                    <label for="start_date" class="block text-sm font-medium leading-6 text-gray-900">
                        Start Date <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2">
                        <input type="date" name="start_date" id="start_date" required
                            value="{{ old('start_date', $subscription ? $subscription->start_date?->format('Y-m-d') : date('Y-m-d')) }}"
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('start_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Next Renewal Date (Required) -->
                <div class="sm:col-span-3">
                    <label for="next_renewal_date" class="block text-sm font-medium leading-6 text-gray-900">
                        Next Renewal Date <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2">
                        <input type="date" name="next_renewal_date" id="next_renewal_date" required
                            value="{{ old('next_renewal_date', $subscription ? $subscription->next_renewal_date?->format('Y-m-d') : '') }}"
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">The date when the next payment is due</p>
                    @error('next_renewal_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status (Required) -->
                <div class="sm:col-span-6">
                    <label for="status" class="block text-sm font-medium leading-6 text-gray-900">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-2">
                        <select name="status" id="status" required
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            @foreach(\App\Models\Subscription::statusOptions() as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $subscription->status ?? 'active') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="sm:col-span-6">
                    <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">
                        Notes
                    </label>
                    <div class="mt-2">
                        <textarea name="notes" id="notes" rows="4"
                            placeholder="Optional notes about this subscription..."
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">{{ old('notes', $subscription->notes ?? '') }}</textarea>
                    </div>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
            <a href="{{ route('subscriptions.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                {{ $subscription ? 'Update Subscription' : 'Create Subscription' }}
            </button>
        </div>
    </div>
</form>
