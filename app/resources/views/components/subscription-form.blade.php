@props(['subscription' => null, 'action', 'method' => 'POST', 'billingCycles' => [], 'statuses' => []])

<form method="POST" action="{{ $action }}" class="space-y-6" x-data="{ billingCycle: '{{ old('billing_cycle', $subscription->billing_cycle ?? 'monthly') }}' }">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <!-- Vendor Name (Required) -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="vendor_name">
                        Vendor Name <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="vendor_name"
                            id="vendor_name"
                            required
                            value="{{ old('vendor_name', $subscription->vendor_name ?? '') }}"
                            placeholder="e.g., Adobe, Microsoft, Netflix"
                        />
                    </div>
                    @error('vendor_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price (Required) -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="price">
                        Price (RON) <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="number"
                            step="0.01"
                            name="price"
                            id="price"
                            required
                            value="{{ old('price', $subscription->price ?? '') }}"
                            placeholder="0.00"
                        />
                    </div>
                    @error('price')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Billing Cycle (Required) -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="billing_cycle">
                        Billing Cycle <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="billing_cycle" id="billing_cycle" required x-model="billingCycle">
                            @foreach($billingCycles as $cycle)
                                <option value="{{ $cycle->value }}" {{ old('billing_cycle', $subscription->billing_cycle ?? 'monthly') === $cycle->value ? 'selected' : '' }}>
                                    {{ $cycle->label }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('billing_cycle')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Custom Days (only if billing_cycle is 'custom') -->
                <div class="sm:col-span-3 field-wrapper" x-show="billingCycle === 'custom'" x-cloak>
                    <x-ui.label for="custom_days">
                        Custom Days <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="number"
                            name="custom_days"
                            id="custom_days"
                            value="{{ old('custom_days', $subscription->custom_days ?? '') }}"
                            placeholder="e.g., 90 for quarterly"
                        />
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Enter the number of days between renewals (e.g., 90 for quarterly)</p>
                    @error('custom_days')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start Date (Required) -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="start_date">
                        Start Date <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="date"
                            name="start_date"
                            id="start_date"
                            required
                            placeholder="YYYY-MM-DD"
                            value="{{ old('start_date', $subscription ? $subscription->start_date?->format('Y-m-d') : date('Y-m-d')) }}"
                        />
                    </div>
                    @error('start_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Next Renewal Date (Required) -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="next_renewal_date">
                        Next Renewal Date <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="date"
                            name="next_renewal_date"
                            id="next_renewal_date"
                            required
                            placeholder="YYYY-MM-DD"
                            value="{{ old('next_renewal_date', $subscription ? $subscription->next_renewal_date?->format('Y-m-d') : '') }}"
                        />
                    </div>
                    <p class="mt-1 text-xs text-slate-500">The date when the next payment is due</p>
                    @error('next_renewal_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status (Required) -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="status">
                        Status <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="status" id="status" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" {{ old('status', $subscription->status ?? 'active') === $status->value ? 'selected' : '' }}>
                                    {{ $status->label }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="notes">Notes</x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea name="notes" id="notes" rows="4" placeholder="Optional notes about this subscription...">{{ old('notes', $subscription->notes ?? '') }}</x-ui.textarea>
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
