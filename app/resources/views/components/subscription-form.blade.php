@props(['subscription' => null, 'action', 'method' => 'POST', 'billingCycles' => [], 'statuses' => [], 'currencies' => []])

<form method="POST" action="{{ $action }}" class="space-y-6" x-data="{
    billingCycle: '{{ old('billing_cycle', $subscription->billing_cycle ?? 'monthly') }}',
    startDate: '{{ old('start_date', $subscription ? $subscription->start_date?->format('Y-m-d') : date('Y-m-d')) }}',
    customDays: '{{ old('custom_days', $subscription->custom_days ?? '') }}',
    nextRenewalDate: '{{ old('next_renewal_date', $subscription ? $subscription->next_renewal_date?->format('Y-m-d') : '') }}',

    calculateNextRenewal() {
        if (!this.startDate) return;

        const date = new Date(this.startDate);

        switch(this.billingCycle) {
            case 'weekly':
                date.setDate(date.getDate() + 7);
                break;
            case 'monthly':
                date.setMonth(date.getMonth() + 1);
                break;
            case 'annual':
                date.setFullYear(date.getFullYear() + 1);
                break;
            case 'custom':
                const days = parseInt(this.customDays) || 30;
                date.setDate(date.getDate() + days);
                break;
        }

        this.nextRenewalDate = date.toISOString().split('T')[0];
    }
}" x-init="
    $watch('startDate', () => calculateNextRenewal());
    $watch('billingCycle', () => calculateNextRenewal());
    $watch('customDays', () => calculateNextRenewal());
">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-ui.card-content>
            <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <!-- Vendor Name (Required) -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="vendor_name">
                        {{ __('Vendor Name') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="vendor_name"
                            id="vendor_name"
                            required
                            value="{{ old('vendor_name', $subscription->vendor_name ?? '') }}"
                            placeholder="{{ __('e.g., Adobe, Microsoft, Netflix') }}"
                        />
                    </div>
                    @error('vendor_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price (Required) -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="price">
                        {{ __('Price') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2 flex gap-2">
                        <div class="flex-1">
                            <x-ui.input
                                type="number"
                                step="0.01"
                                name="price"
                                id="price"
                                required
                                value="{{ old('price', $subscription->price ?? '') }}"
                                placeholder="{{ __('0.00') }}"
                            />
                        </div>
                        <div class="w-24">
                            <x-ui.select name="currency" id="currency" required>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->value }}" {{ old('currency', $subscription->currency ?? 'RON') === $currency->value ? 'selected' : '' }}>
                                        {{ $currency->value }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    </div>
                    @error('price')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('currency')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Billing Cycle (Required) -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="billing_cycle">
                        {{ __('Billing Cycle') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="billing_cycle" id="billing_cycle" required x-model="billingCycle">
                            @foreach($billingCycles as $cycle)
                                <option value="{{ $cycle->value }}" {{ old('billing_cycle', $subscription->billing_cycle ?? 'monthly') === $cycle->value ? 'selected' : '' }}>
                                    {{ __($cycle->label) }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('billing_cycle')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Custom Days (only if billing_cycle is 'custom') -->
                <div class="sm:col-span-6 field-wrapper" x-show="billingCycle === 'custom'" x-cloak>
                    <x-ui.label for="custom_days">
                        {{ __('Custom Days') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="number"
                            name="custom_days"
                            id="custom_days"
                            x-model="customDays"
                            placeholder="{{ __('e.g., 90 for quarterly') }}"
                        />
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Enter the number of days between renewals (e.g., 90 for quarterly)') }}</p>
                    @error('custom_days')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start Date (Required) -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="start_date">
                        {{ __('Start Date') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="date"
                            name="start_date"
                            id="start_date"
                            required
                            placeholder="{{ __('YYYY-MM-DD') }}"
                            x-model="startDate"
                        />
                    </div>
                    @error('start_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status (Required) -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="status">
                        {{ __('Status') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="status" id="status" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" {{ old('status', $subscription->status ?? 'active') === $status->value ? 'selected' : '' }}>
                                    {{ __($status->label) }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Next Renewal Date (Required) - with info box -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="next_renewal_date">
                        {{ __('Next Renewal Date') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2 bg-slate-50 border border-slate-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-700 mb-2">{{ __('Next Payment Date') }}</p>
                                <p class="text-xs text-slate-500 mb-3">{{ __('Automatically calculated based on start date and billing cycle.') }}</p>
                                <x-ui.input
                                    type="date"
                                    name="next_renewal_date"
                                    id="next_renewal_date"
                                    required
                                    placeholder="{{ __('YYYY-MM-DD') }}"
                                    x-model="nextRenewalDate"
                                    readonly
                                    class="bg-white"
                                />
                            </div>
                        </div>
                    </div>
                    @error('next_renewal_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="notes">{{ __('Notes') }}</x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea name="notes" id="notes" rows="3" placeholder="{{ __('Additional information about this subscription...') }}">{{ old('notes', $subscription->notes ?? '') }}</x-ui.textarea>
                    </div>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-6 border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('subscriptions.index') }}'">
                {{ __('Cancel') }}
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $subscription ? __('Update Subscription') : __('Create Subscription') }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
