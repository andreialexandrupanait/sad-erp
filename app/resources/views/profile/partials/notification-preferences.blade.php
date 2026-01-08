<form method="post" action="{{ route('profile.notifications.update') }}" class="space-y-6">
    @csrf
    @method('patch')

    <div class="space-y-3">
        <!-- Domain Expiry -->
        <label class="flex items-center justify-between p-4 bg-slate-50 rounded-lg hover:bg-slate-100 cursor-pointer transition">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-blue-100 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                    </svg>
                </div>
                <div>
                    <span class="font-medium text-slate-900">{{ __('Domain Expiry') }}</span>
                    <p class="text-sm text-slate-500">{{ __('Get notified when domains are about to expire.') }}</p>
                </div>
            </div>
            <div class="relative">
                <input type="hidden" name="notify_domain_expiry" value="0">
                <input type="checkbox" name="notify_domain_expiry" value="1" {{ $user->getSetting('notify_domain_expiry', true) ? 'checked' : '' }} class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </div>
        </label>

        <!-- Subscription Renewal -->
        <label class="flex items-center justify-between p-4 bg-slate-50 rounded-lg hover:bg-slate-100 cursor-pointer transition">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-green-100 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <div>
                    <span class="font-medium text-slate-900">{{ __('Subscription Renewals') }}</span>
                    <p class="text-sm text-slate-500">{{ __('Get notified about upcoming subscription renewals.') }}</p>
                </div>
            </div>
            <div class="relative">
                <input type="hidden" name="notify_subscription_renewal" value="0">
                <input type="checkbox" name="notify_subscription_renewal" value="1" {{ $user->getSetting('notify_subscription_renewal', true) ? 'checked' : '' }} class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </div>
        </label>

        <!-- Payment Received -->
        <label class="flex items-center justify-between p-4 bg-slate-50 rounded-lg hover:bg-slate-100 cursor-pointer transition">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-emerald-100 rounded-lg">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <span class="font-medium text-slate-900">{{ __('Payments Received') }}</span>
                    <p class="text-sm text-slate-500">{{ __('Get notified when new payments are recorded.') }}</p>
                </div>
            </div>
            <div class="relative">
                <input type="hidden" name="notify_payment_received" value="0">
                <input type="checkbox" name="notify_payment_received" value="1" {{ $user->getSetting('notify_payment_received', true) ? 'checked' : '' }} class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </div>
        </label>

        <!-- Contract Expiry -->
        <label class="flex items-center justify-between p-4 bg-slate-50 rounded-lg hover:bg-slate-100 cursor-pointer transition">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-amber-100 rounded-lg">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <span class="font-medium text-slate-900">{{ __('Contract Expiry') }}</span>
                    <p class="text-sm text-slate-500">{{ __('Get notified when contracts are about to expire.') }}</p>
                </div>
            </div>
            <div class="relative">
                <input type="hidden" name="notify_contract_expiry" value="0">
                <input type="checkbox" name="notify_contract_expiry" value="1" {{ $user->getSetting('notify_contract_expiry', true) ? 'checked' : '' }} class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </div>
        </label>

        <!-- Offer Status Changes -->
        <label class="flex items-center justify-between p-4 bg-slate-50 rounded-lg hover:bg-slate-100 cursor-pointer transition">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-purple-100 rounded-lg">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <div>
                    <span class="font-medium text-slate-900">{{ __('Offer Status Changes') }}</span>
                    <p class="text-sm text-slate-500">{{ __('Get notified when offers are viewed, accepted, or rejected.') }}</p>
                </div>
            </div>
            <div class="relative">
                <input type="hidden" name="notify_offer_status" value="0">
                <input type="checkbox" name="notify_offer_status" value="1" {{ $user->getSetting('notify_offer_status', true) ? 'checked' : '' }} class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </div>
        </label>
    </div>

    <!-- Email Frequency -->
    <div class="pt-4 border-t border-slate-200">
        <x-ui.form-group name="email_frequency" label="{{ __('Email Frequency') }}" hint="{{ __('Choose how often you want to receive email notifications.') }}">
            <x-ui.select name="email_frequency" id="email_frequency" class="max-w-xs">
                @php
                    $currentFrequency = $user->getSetting('email_frequency', 'instant');
                @endphp
                <option value="instant" {{ $currentFrequency === 'instant' ? 'selected' : '' }}>{{ __('Instant') }}</option>
                <option value="daily" {{ $currentFrequency === 'daily' ? 'selected' : '' }}>{{ __('Daily Digest') }}</option>
                <option value="weekly" {{ $currentFrequency === 'weekly' ? 'selected' : '' }}>{{ __('Weekly Digest') }}</option>
            </x-ui.select>
        </x-ui.form-group>
    </div>

    <div class="flex items-center gap-4 pt-4 border-t border-slate-200">
        <x-ui.button type="submit">{{ __('Save Notifications') }}</x-ui.button>

        @if (session('status') === 'notifications-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-green-600"
            >{{ __('Saved.') }}</p>
        @endif
    </div>
</form>
