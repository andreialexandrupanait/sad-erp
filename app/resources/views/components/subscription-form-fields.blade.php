@props(['subscription' => null, 'idSuffix' => '', 'billingCycles' => [], 'statuses' => []])

<div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
    <!-- Vendor Name (Required) -->
    <div class="sm:col-span-3 field-wrapper">
        <x-ui.label for="vendor_name{{ $idSuffix }}">
            Vendor Name <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="text"
                name="vendor_name"
                id="vendor_name{{ $idSuffix }}"
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
        <x-ui.label for="price{{ $idSuffix }}">
            Price (RON) <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="number"
                step="0.01"
                name="price"
                id="price{{ $idSuffix }}"
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
        <x-ui.label for="billing_cycle{{ $idSuffix }}">
            Billing Cycle <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-2">
            <x-ui.select name="billing_cycle" id="billing_cycle{{ $idSuffix }}" required x-model="billingCycle">
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
        <x-ui.label for="custom_days{{ $idSuffix }}">
            Custom Days <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="number"
                name="custom_days"
                id="custom_days{{ $idSuffix }}"
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
        <x-ui.label for="start_date{{ $idSuffix }}">
            Start Date <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="date"
                name="start_date"
                id="start_date{{ $idSuffix }}"
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
        <x-ui.label for="next_renewal_date{{ $idSuffix }}">
            Next Renewal Date <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="date"
                name="next_renewal_date"
                id="next_renewal_date{{ $idSuffix }}"
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
        <x-ui.label for="status{{ $idSuffix }}">
            Status <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-2">
            <x-ui.select name="status" id="status{{ $idSuffix }}" required>
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
        <x-ui.label for="notes{{ $idSuffix }}">Notes</x-ui.label>
        <div class="mt-2">
            <x-ui.textarea name="notes" id="notes{{ $idSuffix }}" rows="4" placeholder="Optional notes about this subscription...">{{ old('notes', $subscription->notes ?? '') }}</x-ui.textarea>
        </div>
        @error('notes')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
