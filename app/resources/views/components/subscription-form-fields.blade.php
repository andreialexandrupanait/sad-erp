@props([
    'subscription' => null,
    'billingCycles' => [],
    'statuses' => [],
    'currencies' => [],
    'prefix' => '',
    'compact' => false,
])

@php
    $p = $prefix;
    $gridClass = $compact ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-6';
    $colSpan3 = $compact ? '' : 'sm:col-span-3';
    $colSpan6 = $compact ? '' : 'sm:col-span-6';
@endphp

<div class="grid {{ $gridClass }} gap-x-6 gap-y-5" x-data="{
    billingCycle: '{{ old($p.'billing_cycle', $subscription->billing_cycle ?? 'monthly') }}',
    startDate: '{{ old($p.'start_date', $subscription ? $subscription->start_date?->format('Y-m-d') : date('Y-m-d')) }}',
    customDays: '{{ old($p.'custom_days', $subscription->custom_days ?? '') }}',
    nextRenewalDate: '{{ old($p.'next_renewal_date', $subscription ? $subscription->next_renewal_date?->format('Y-m-d') : '') }}',

    calculateNextRenewal() {
        if (!this.startDate) return;
        const date = new Date(this.startDate);
        switch(this.billingCycle) {
            case 'weekly': date.setDate(date.getDate() + 7); break;
            case 'monthly': date.setMonth(date.getMonth() + 1); break;
            case 'annual': date.setFullYear(date.getFullYear() + 1); break;
            case 'custom':
                const days = parseInt(this.customDays) || 30;
                date.setDate(date.getDate() + days);
                break;
        }
        this.nextRenewalDate = date.toISOString().split('T')[0];
    },

    init() {
        this.$watch('startDate', () => this.calculateNextRenewal());
        this.$watch('billingCycle', () => this.calculateNextRenewal());
        this.$watch('customDays', () => this.calculateNextRenewal());
        if (!this.nextRenewalDate) this.calculateNextRenewal();
    }
}">
    <!-- Vendor Name (Required) -->
    <div class="{{ $colSpan6 }}">
        <x-ui.label :for="$p.'vendor_name'">
            {{ __('Vendor Name') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <x-ui.input
                type="text"
                :name="$p.'vendor_name'"
                :id="$p.'vendor_name'"
                required
                :value="old($p.'vendor_name', $subscription->vendor_name ?? '')"
                :placeholder="__('e.g., Adobe, Microsoft, Netflix')"
            />
        </div>
        @error($p.'vendor_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Price (Required) -->
    <div class="{{ $colSpan3 }}">
        <x-ui.label :for="$p.'price'">
            {{ __('Price') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5 flex gap-2">
            <div class="flex-1">
                <x-ui.input
                    type="number"
                    step="0.01"
                    :name="$p.'price'"
                    :id="$p.'price'"
                    required
                    :value="old($p.'price', $subscription->price ?? '')"
                    :placeholder="__('0.00')"
                />
            </div>
            <div class="w-24">
                <select name="{{ $p }}currency" id="{{ $p }}currency" required class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->value }}" {{ old($p.'currency', $subscription->currency ?? 'RON') === $currency->value ? 'selected' : '' }}>
                            {{ $currency->value }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        @error($p.'price')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Billing Cycle (Required) -->
    <div class="{{ $colSpan3 }}" @nomenclature-selected.window="if ($event.target.closest('[id^=nom-sel]')?.querySelector('input[name={{ $p }}billing_cycle]')) billingCycle = $event.detail.value">
        <x-ui.label :for="$p.'billing_cycle'">
            {{ __('Billing Cycle') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <x-ui.nomenclature-select
                :name="$p.'billing_cycle'"
                category="billing_cycles"
                :options="$billingCycles"
                :selected="old($p.'billing_cycle', $subscription->billing_cycle ?? 'monthly')"
                :placeholder="__('Select billing cycle')"
                :hasColors="false"
                :required="true"
            />
        </div>
        @error($p.'billing_cycle')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Custom Days (only if billing_cycle is 'custom') -->
    <div class="{{ $colSpan6 }}" x-show="billingCycle === 'custom'" x-cloak>
        <x-ui.label :for="$p.'custom_days'">
            {{ __('Custom Days') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <input
                type="number"
                name="{{ $p }}custom_days"
                id="{{ $p }}custom_days"
                x-model="customDays"
                placeholder="{{ __('e.g., 90 for quarterly') }}"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>
        @error($p.'custom_days')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Start Date (Required) -->
    <div class="{{ $colSpan3 }}">
        <x-ui.label :for="$p.'start_date'">
            {{ __('Start Date') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <input
                type="date"
                name="{{ $p }}start_date"
                id="{{ $p }}start_date"
                required
                x-model="startDate"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>
        @error($p.'start_date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Status (Required) -->
    <div class="{{ $colSpan3 }}">
        <x-ui.label :for="$p.'status'">
            {{ __('Status') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <x-ui.nomenclature-select
                :name="$p.'status'"
                category="subscription_statuses"
                :options="$statuses"
                :selected="old($p.'status', $subscription->status ?? 'active')"
                :placeholder="__('Select status')"
                :hasColors="true"
                :required="true"
            />
        </div>
        @error($p.'status')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Next Renewal Date (auto-calculated) -->
    <div class="{{ $colSpan6 }}">
        <x-ui.label :for="$p.'next_renewal_date'">
            {{ __('Next Renewal Date') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <input
                type="date"
                name="{{ $p }}next_renewal_date"
                id="{{ $p }}next_renewal_date"
                required
                x-model="nextRenewalDate"
                readonly
                class="flex h-10 w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>
        <p class="mt-1 text-xs text-slate-500">{{ __('Auto-calculated based on start date and billing cycle') }}</p>
        @error($p.'next_renewal_date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if(!$compact)
    <!-- Notes -->
    <div class="{{ $colSpan6 }}">
        <x-ui.label :for="$p.'notes'">{{ __('Notes') }}</x-ui.label>
        <div class="mt-1.5">
            <x-ui.textarea :name="$p.'notes'" :id="$p.'notes'" rows="3" :placeholder="__('Additional information...')">{{ old($p.'notes', $subscription->notes ?? '') }}</x-ui.textarea>
        </div>
        @error($p.'notes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    @endif
</div>
