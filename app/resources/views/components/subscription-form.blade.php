@props(['subscription' => null, 'action', 'method' => 'POST', 'billingCycles' => [], 'statuses' => [], 'currencies' => []])

<form method="POST" action="{{ $action }}" class="space-y-6" x-data="{
    billingCycle: '{{ old('billing_cycle', $subscription->billing_cycle ?? 'monthly') }}',
    startDate: '{{ old('start_date', $subscription ? $subscription->start_date?->format('Y-m-d') : date('Y-m-d')) }}',
    customDays: '{{ old('custom_days', $subscription->custom_days ?? '') }}',
    nextRenewalDate: '{{ old('next_renewal_date', $subscription ? $subscription->next_renewal_date?->format('Y-m-d') : '') }}',
    currency: '{{ old('currency', $subscription->currency ?? 'RON') }}',
    priceEur: '{{ old('price_eur', $subscription->price_eur ?? '') }}',
    priceRon: '{{ old('price', $subscription->price ?? '') }}',
    exchangeRate: '{{ old('exchange_rate', $subscription->exchange_rate ?? '') }}',
    convertToRon: {{ old('convert_to_ron', ($subscription && $subscription->price_eur && $subscription->currency === 'RON') ? 'true' : 'false') }},
    loadingRate: false,
    rateError: null,

    calculateNextRenewal() {
        if (!this.startDate) return;
        const date = new Date(this.startDate);
        const cycle = (this.billingCycle || '').toLowerCase();
        switch(cycle) {
            case 'weekly':
            case 'saptamanal':
                date.setDate(date.getDate() + 7);
                break;
            case 'monthly':
            case 'lunar':
                date.setMonth(date.getMonth() + 1);
                break;
            case 'annual':
            case 'anual':
                date.setFullYear(date.getFullYear() + 1);
                break;
            case 'custom':
                const days = parseInt(this.customDays) || 30;
                date.setDate(date.getDate() + days);
                break;
        }
        this.nextRenewalDate = date.toISOString().split('T')[0];
    },

    async fetchRate() {
        if (this.currency !== 'EUR' || !this.convertToRon) return;
        this.loadingRate = true;
        this.rateError = null;
        try {
            const response = await fetch(`/api/exchange-rate?from=EUR&to=RON&date=${new Date().toISOString().split('T')[0]}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.rate) {
                    this.exchangeRate = data.rate.toFixed(4);
                    this.calculateRon();
                }
            } else {
                this.rateError = 'Nu s-a putut obține cursul BNR';
            }
        } catch (e) {
            this.rateError = 'Eroare la obținerea cursului';
        } finally {
            this.loadingRate = false;
        }
    },

    calculateRon() {
        if (this.priceEur && this.exchangeRate && this.convertToRon) {
            this.priceRon = (parseFloat(this.priceEur) * parseFloat(this.exchangeRate)).toFixed(2);
        }
    },

    onCurrencyChange() {
        if (this.currency !== 'EUR') {
            this.convertToRon = false;
            this.priceEur = '';
            this.exchangeRate = '';
        }
    },

    onConvertToggle() {
        if (this.convertToRon && this.currency === 'EUR') {
            this.fetchRate();
        }
    },

    init() {
        this.$watch('startDate', () => this.calculateNextRenewal());
        this.$watch('billingCycle', () => this.calculateNextRenewal());
        this.$watch('customDays', () => this.calculateNextRenewal());
    }
}">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-unsaved-form-warning />

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

                <!-- Currency -->
                <div class="sm:col-span-2 field-wrapper">
                    <x-ui.label for="currency">
                        {{ __('Currency') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <select name="currency" id="currency" required x-model="currency" @change="onCurrencyChange()"
                            class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            @foreach($currencies as $curr)
                                <option value="{{ $curr->value }}">{{ $curr->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('currency')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- EUR Price (shown when currency is EUR) -->
                <template x-if="currency === 'EUR'">
                    <div class="sm:col-span-2 field-wrapper">
                        <x-ui.label for="price_eur">
                            {{ __('Price EUR') }} <span class="text-red-500">*</span>
                        </x-ui.label>
                        <div class="mt-2">
                            <input type="number" step="0.01" name="price_eur" id="price_eur"
                                x-model="priceEur" @input="calculateRon()"
                                placeholder="0.00" required
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6" />
                        </div>
                    </div>
                </template>

                <!-- Price RON (always shown, but behavior changes based on currency) -->
                <div class="field-wrapper" :class="currency === 'EUR' ? 'sm:col-span-2' : 'sm:col-span-4'">
                    <x-ui.label for="price">
                        <span x-text="currency === 'EUR' && convertToRon ? 'Price RON (calculated)' : 'Price'"></span>
                        <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <input type="number" step="0.01" name="price" id="price"
                            x-model="priceRon"
                            placeholder="0.00" required
                            :readonly="currency === 'EUR' && convertToRon"
                            :class="currency === 'EUR' && convertToRon ? 'bg-slate-50' : ''"
                            class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6" />
                    </div>
                    <p x-show="currency === 'EUR' && convertToRon && priceEur && exchangeRate" class="mt-1 text-xs text-slate-500">
                        <span x-text="priceEur"></span> EUR × <span x-text="exchangeRate"></span> = <span x-text="priceRon"></span> RON
                    </p>
                    @error('price')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Convert to RON option (only for EUR subscriptions) -->
                <template x-if="currency === 'EUR'">
                    <div class="sm:col-span-6 field-wrapper">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" id="convert_to_ron" name="convert_to_ron" value="1"
                                    x-model="convertToRon" @change="onConvertToggle()"
                                    class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600" />
                                <div class="flex-1">
                                    <label for="convert_to_ron" class="text-sm font-medium text-slate-700 cursor-pointer">
                                        {{ __('Convert to RON for statistics') }}
                                    </label>
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ __('Enable this if you want this subscription included in RON totals. The EUR amount will be saved as reference.') }}
                                    </p>
                                </div>
                            </div>
                            <template x-if="convertToRon">
                                <div class="mt-3 pl-7">
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-slate-600">{{ __('Exchange Rate') }}:</label>
                                        <input type="number" step="0.0001" name="exchange_rate" id="exchange_rate"
                                            x-model="exchangeRate" @input="calculateRon()"
                                            placeholder="0.0000"
                                            class="w-28 rounded-md border-0 py-1 px-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600" />
                                        <span x-show="loadingRate" class="text-slate-400 text-xs">(loading...)</span>
                                        <span x-show="rateError" x-text="rateError" class="text-xs text-red-500"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Billing Cycle (Required) -->
                <div class="sm:col-span-3 field-wrapper" x-on:nomenclature-selected.window="if ($event.target.closest('[id^=nom-sel]')?.querySelector('input[name=billing_cycle]')) { billingCycle = $event.detail.value; calculateNextRenewal(); }">
                    <x-ui.label for="billing_cycle">
                        {{ __('Billing Cycle') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.nomenclature-select
                            name="billing_cycle"
                            category="billing_cycles"
                            :options="$billingCycles"
                            :selected="old('billing_cycle', $subscription->billing_cycle ?? 'monthly')"
                            :placeholder="__('Select billing cycle')"
                            :hasColors="false"
                            :required="true"
                        />
                    </div>
                    @error('billing_cycle')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status (Required) -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="status">
                        {{ __('Status') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.nomenclature-select
                            name="status"
                            category="subscription_statuses"
                            :options="$statuses"
                            :selected="old('status', $subscription->status ?? 'active')"
                            :placeholder="__('Select status')"
                            :hasColors="true"
                            :required="true"
                        />
                    </div>
                    @error('status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Custom Days (only if billing_cycle is 'custom') -->
                <div class="sm:col-span-6 field-wrapper" x-show="billingCycle === 'custom'" x-cloak>
                    <x-ui.label for="custom_days">
                        {{ __('Custom Days') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <input
                            type="number"
                            name="custom_days"
                            id="custom_days"
                            x-model="customDays"
                            x-on:input="calculateNextRenewal()"
                            placeholder="{{ __('e.g., 90 for quarterly') }}"
                            class="flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm ring-offset-white transition-colors placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 border-slate-200 focus-visible:ring-slate-950"
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
                        <input
                            type="date"
                            name="start_date"
                            id="start_date"
                            required
                            x-model="startDate"
                            x-on:change="calculateNextRenewal()"
                            class="flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm ring-offset-white transition-colors placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 border-slate-200 focus-visible:ring-slate-950"
                        />
                    </div>
                    @error('start_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Next Renewal Date (Required) - with info box -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="next_renewal_date">
                        {{ __('Next Renewal Date') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="date"
                            name="next_renewal_date"
                            id="next_renewal_date"
                            required
                            placeholder="{{ __('YYYY-MM-DD') }}"
                            x-model="nextRenewalDate"
                            readonly
                        />
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Auto-calculated from start date and billing cycle') }}</p>
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

        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 sm:gap-x-4 border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
            <x-ui.button type="button" variant="ghost" class="w-full sm:w-auto" onclick="window.location.href='{{ route('subscriptions.index') }}'">
                {{ __('Cancel') }}
            </x-ui.button>
            <x-ui.button type="submit" variant="default" class="w-full sm:w-auto">
                {{ $subscription ? __('Update Subscription') : __('Create Subscription') }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
