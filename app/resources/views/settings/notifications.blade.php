<x-app-layout>
    <x-slot name="pageTitle">{{ __('Notification Settings') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-4 md:p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-slate-900">{{ __('Notification Settings') }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ __('Configure email notifications, reminders, and SMTP settings') }}</p>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                        <div class="font-semibold mb-2">{{ __('Please fix the following errors') }}:</div>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Test Email Success/Error (AJAX) -->
                <div id="testEmailAlert" class="hidden mb-6 p-4 rounded-lg"></div>

                <form method="POST" action="{{ route('settings.notifications.update') }}" id="notificationForm">
                    @csrf

                    <!-- Master Toggle -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100">
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('Master Settings') }}</h3>
                        </div>
                        <div class="p-4 md:p-6">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox"
                                       name="notifications_enabled"
                                       value="1"
                                       {{ old('notifications_enabled', $notificationSettings['notifications_enabled']) ? 'checked' : '' }}
                                       class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded">
                                <span class="ml-3">
                                    <span class="text-sm font-medium text-slate-900">{{ __('Enable Email Notifications') }}</span>
                                    <span class="block text-xs text-slate-500 mt-1">{{ __('Turn on/off all email notifications from the system') }}</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Notification Types -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100">
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('Notification Types') }}</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Domain Expiry -->
                            <div class="flex items-start justify-between pb-6 border-b border-slate-100">
                                <div class="flex-1">
                                    <label class="flex items-start cursor-pointer">
                                        <input type="checkbox"
                                               name="notify_domain_expiry"
                                               value="1"
                                               {{ old('notify_domain_expiry', $notificationSettings['notify_domain_expiry']) ? 'checked' : '' }}
                                               class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded mt-0.5">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-slate-900">{{ __('Domain Expiry Alerts') }}</span>
                                            <span class="block text-xs text-slate-500 mt-1">{{ __('Get notified before domains expire') }}</span>
                                        </span>
                                    </label>
                                </div>
                                <div class="ml-6">
                                    <div class="flex items-center gap-2">
                                        <input type="number"
                                               name="domain_expiry_days_before"
                                               value="{{ old('domain_expiry_days_before', $notificationSettings['domain_expiry_days_before']) }}"
                                               min="1"
                                               max="365"
                                               class="w-20 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm">
                                        <span class="text-sm text-slate-600">{{ __('days before') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Subscription Renewal -->
                            <div class="flex items-start justify-between pb-6 border-b border-slate-100">
                                <div class="flex-1">
                                    <label class="flex items-start cursor-pointer">
                                        <input type="checkbox"
                                               name="notify_subscription_renewal"
                                               value="1"
                                               {{ old('notify_subscription_renewal', $notificationSettings['notify_subscription_renewal']) ? 'checked' : '' }}
                                               class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded mt-0.5">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-slate-900">{{ __('Subscription Renewal Alerts') }}</span>
                                            <span class="block text-xs text-slate-500 mt-1">{{ __('Get notified before subscriptions renew') }}</span>
                                        </span>
                                    </label>
                                </div>
                                <div class="ml-6">
                                    <div class="flex items-center gap-2">
                                        <input type="number"
                                               name="subscription_renewal_days"
                                               value="{{ old('subscription_renewal_days', $notificationSettings['subscription_renewal_days']) }}"
                                               min="1"
                                               max="90"
                                               class="w-20 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm">
                                        <span class="text-sm text-slate-600">{{ __('days before') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- New Client -->
                            <div class="pb-6 border-b border-slate-100">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox"
                                           name="notify_new_client"
                                           value="1"
                                           {{ old('notify_new_client', $notificationSettings['notify_new_client']) ? 'checked' : '' }}
                                           class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded mt-0.5">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('New Client Notifications') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Get notified when a new client is added') }}</span>
                                    </span>
                                </label>
                            </div>

                            <!-- Payment Received -->
                            <div class="pb-6 border-b border-slate-100">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox"
                                           name="notify_payment_received"
                                           value="1"
                                           {{ old('notify_payment_received', $notificationSettings['notify_payment_received']) ? 'checked' : '' }}
                                           class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded mt-0.5">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('Payment Received Notifications') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Get notified when payments are recorded') }}</span>
                                    </span>
                                </label>
                            </div>

                            <!-- Monthly Summary -->
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <label class="flex items-start cursor-pointer">
                                        <input type="checkbox"
                                               name="notify_monthly_summary"
                                               value="1"
                                               {{ old('notify_monthly_summary', $notificationSettings['notify_monthly_summary']) ? 'checked' : '' }}
                                               class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded mt-0.5">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-slate-900">{{ __('Monthly Financial Summary') }}</span>
                                            <span class="block text-xs text-slate-500 mt-1">{{ __('Receive monthly financial reports') }}</span>
                                        </span>
                                    </label>
                                </div>
                                <div class="ml-6">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-slate-600">{{ __('Day') }}</span>
                                        <input type="number"
                                               name="monthly_summary_day"
                                               value="{{ old('monthly_summary_day', $notificationSettings['monthly_summary_day']) }}"
                                               min="1"
                                               max="28"
                                               class="w-20 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm">
                                        <span class="text-sm text-slate-600">{{ __('of month') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Recipients -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100">
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('Email Recipients') }}</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Primary Email -->
                                <div>
                                    <label for="notification_email_primary" class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Primary Email') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email"
                                           id="notification_email_primary"
                                           name="notification_email_primary"
                                           value="{{ old('notification_email_primary', $notificationSettings['notification_email_primary']) }}"
                                           required
                                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('notification_email_primary') border-red-500 @enderror">
                                    @error('notification_email_primary')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- CC Emails -->
                                <div>
                                    <label for="notification_email_cc" class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('CC Emails') }}
                                    </label>
                                    <input type="text"
                                           id="notification_email_cc"
                                           name="notification_email_cc"
                                           value="{{ old('notification_email_cc', $notificationSettings['notification_email_cc']) }}"
                                           placeholder="email1@example.com, email2@example.com"
                                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <p class="mt-1 text-xs text-slate-500">{{ __('Comma-separated email addresses') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SMTP Configuration -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100">
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('SMTP Configuration') }}</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- SMTP Enable Toggle -->
                            <div class="pb-6 border-b border-slate-200">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           name="smtp_enabled"
                                           value="1"
                                           id="smtpToggle"
                                           {{ old('smtp_enabled', $notificationSettings['smtp_enabled']) ? 'checked' : '' }}
                                           class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('Use Custom SMTP Server') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Configure custom SMTP settings for sending emails') }}</span>
                                    </span>
                                </label>
                            </div>

                            <!-- SMTP Fields (hidden by default) -->
                            <div id="smtpFields" class="space-y-6" style="display: {{ old('smtp_enabled', $notificationSettings['smtp_enabled']) ? 'block' : 'none' }}">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- SMTP Host -->
                                    <div>
                                        <label for="smtp_host" class="block text-sm font-medium text-slate-700 mb-2">
                                            {{ __('SMTP Host') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               id="smtp_host"
                                               name="smtp_host"
                                               value="{{ old('smtp_host', $notificationSettings['smtp_host']) }}"
                                               placeholder="smtp.gmail.com"
                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>

                                    <!-- SMTP Port -->
                                    <div>
                                        <label for="smtp_port" class="block text-sm font-medium text-slate-700 mb-2">
                                            {{ __('SMTP Port') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number"
                                               id="smtp_port"
                                               name="smtp_port"
                                               value="{{ old('smtp_port', $notificationSettings['smtp_port']) }}"
                                               placeholder="587"
                                               min="1"
                                               max="65535"
                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>

                                    <!-- SMTP Username -->
                                    <div>
                                        <label for="smtp_username" class="block text-sm font-medium text-slate-700 mb-2">
                                            {{ __('SMTP Username') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               id="smtp_username"
                                               name="smtp_username"
                                               value="{{ old('smtp_username', $notificationSettings['smtp_username']) }}"
                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>

                                    <!-- SMTP Password -->
                                    <div>
                                        <label for="smtp_password" class="block text-sm font-medium text-slate-700 mb-2">
                                            {{ __('SMTP Password') }}
                                        </label>
                                        <input type="password"
                                               id="smtp_password"
                                               name="smtp_password"
                                               placeholder="{{ $notificationSettings['smtp_password'] ? '••••••••' : '' }}"
                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <p class="mt-1 text-xs text-slate-500">{{ __('Leave blank to keep current password') }}</p>
                                    </div>

                                    <!-- Encryption -->
                                    <div>
                                        <label for="smtp_encryption" class="block text-sm font-medium text-slate-700 mb-2">
                                            {{ __('Encryption') }}
                                        </label>
                                        <select id="smtp_encryption"
                                                name="smtp_encryption"
                                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                            <option value="tls" {{ old('smtp_encryption', $notificationSettings['smtp_encryption']) === 'tls' ? 'selected' : '' }}>TLS</option>
                                            <option value="ssl" {{ old('smtp_encryption', $notificationSettings['smtp_encryption']) === 'ssl' ? 'selected' : '' }}>SSL</option>
                                            <option value="none" {{ old('smtp_encryption', $notificationSettings['smtp_encryption']) === 'none' ? 'selected' : '' }}>{{ __('None') }}</option>
                                        </select>
                                    </div>

                                    <!-- From Email -->
                                    <div>
                                        <label for="smtp_from_email" class="block text-sm font-medium text-slate-700 mb-2">
                                            {{ __('From Email') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email"
                                               id="smtp_from_email"
                                               name="smtp_from_email"
                                               value="{{ old('smtp_from_email', $notificationSettings['smtp_from_email']) }}"
                                               placeholder="noreply@example.com"
                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>

                                    <!-- From Name -->
                                    <div>
                                        <label for="smtp_from_name" class="block text-sm font-medium text-slate-700 mb-2">
                                            {{ __('From Name') }}
                                        </label>
                                        <input type="text"
                                               id="smtp_from_name"
                                               name="smtp_from_name"
                                               value="{{ old('smtp_from_name', $notificationSettings['smtp_from_name']) }}"
                                               placeholder="ERP System"
                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>

                                    <!-- Test Email -->
                                    <div class="md:col-span-2">
                                        <label for="test_email" class="block text-sm font-medium text-slate-700 mb-2">
                                            {{ __('Test Email Configuration') }}
                                        </label>
                                        <div class="flex gap-3">
                                            <input type="email"
                                                   id="test_email"
                                                   placeholder="your@email.com"
                                                   class="flex-1 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                            <button type="button"
                                                    id="sendTestEmailBtn"
                                                    class="px-6 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors inline-flex items-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                {{ __('Send Test Email') }}
                                            </button>
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500">{{ __('Save your settings first, then send a test email') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                                        <!-- Push Notifications -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100">
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('Push Notifications') }}</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Push Enable Toggle -->
                            <div class="pb-6 border-b border-slate-200">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           name="push_notifications_enabled"
                                           value="1"
                                           id="pushToggle"
                                           {{ old('push_notifications_enabled', $notificationSettings['push_notifications_enabled'] ?? true) ? 'checked' : '' }}
                                           class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('Enable Push Notifications') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Receive real-time browser notifications for important events') }}</span>
                                    </span>
                                </label>
                            </div>

                            <!-- Push Notification Types -->
                            <div id="pushFields" class="space-y-4" style="display: {{ old('push_notifications_enabled', $notificationSettings['push_notifications_enabled'] ?? true) ? 'block' : 'none' }}">
                                <p class="text-sm text-slate-600 mb-4">{{ __('Choose which events trigger push notifications:') }}</p>
                                
                                <!-- Offer Accepted -->
                                <div class="flex items-start">
                                    <label class="flex items-start cursor-pointer">
                                        <input type="checkbox"
                                               name="push_notify_offer_accepted"
                                               value="1"
                                               {{ old('push_notify_offer_accepted', $notificationSettings['push_notify_offer_accepted'] ?? true) ? 'checked' : '' }}
                                               class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded mt-0.5">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-slate-900">{{ __('Offer Accepted') }}</span>
                                            <span class="block text-xs text-slate-500 mt-1">{{ __('When a client accepts an offer') }}</span>
                                        </span>
                                    </label>
                                </div>

                                <!-- Offer Rejected -->
                                <div class="flex items-start">
                                    <label class="flex items-start cursor-pointer">
                                        <input type="checkbox"
                                               name="push_notify_offer_rejected"
                                               value="1"
                                               {{ old('push_notify_offer_rejected', $notificationSettings['push_notify_offer_rejected'] ?? true) ? 'checked' : '' }}
                                               class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded mt-0.5">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-slate-900">{{ __('Offer Rejected') }}</span>
                                            <span class="block text-xs text-slate-500 mt-1">{{ __('When a client rejects an offer') }}</span>
                                        </span>
                                    </label>
                                </div>

                                <!-- New Client (Push) -->
                                <div class="flex items-start">
                                    <label class="flex items-start cursor-pointer">
                                        <input type="checkbox"
                                               name="push_notify_new_client"
                                               value="1"
                                               {{ old('push_notify_new_client', $notificationSettings['push_notify_new_client'] ?? false) ? 'checked' : '' }}
                                               class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded mt-0.5">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-slate-900">{{ __('New Client') }}</span>
                                            <span class="block text-xs text-slate-500 mt-1">{{ __('When a new client is added') }}</span>
                                        </span>
                                    </label>
                                </div>

                                <!-- Payment Received (Push) -->
                                <div class="flex items-start">
                                    <label class="flex items-start cursor-pointer">
                                        <input type="checkbox"
                                               name="push_notify_payment_received"
                                               value="1"
                                               {{ old('push_notify_payment_received', $notificationSettings['push_notify_payment_received'] ?? false) ? 'checked' : '' }}
                                               class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded mt-0.5">
                                        <span class="ml-3">
                                            <span class="text-sm font-medium text-slate-900">{{ __('Payment Received') }}</span>
                                            <span class="block text-xs text-slate-500 mt-1">{{ __('When a payment is recorded') }}</span>
                                        </span>
                                    </label>
                                </div>

                                <!-- Browser Subscription Status -->
                                <div class="mt-6 pt-6 border-t border-slate-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-sm font-medium text-slate-900">{{ __('Browser Subscription') }}</span>
                                            <span class="block text-xs text-slate-500 mt-1" id="pushStatusText">{{ __('Checking status...') }}</span>
                                        </div>
                                        <button type="button"
                                                id="subscribePushBtn"
                                                class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 transition-colors hidden">
                                            {{ __('Enable Browser Notifications') }}
                                        </button>
                                        <button type="button"
                                                id="unsubscribePushBtn"
                                                class="px-4 py-2 bg-slate-600 text-white text-sm rounded-lg hover:bg-slate-700 transition-colors hidden">
                                            {{ __('Disable Browser Notifications') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Save Button -->
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('dashboard') }}"
                           class="px-6 py-2.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('Save Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Toggle SMTP fields visibility
        document.getElementById('smtpToggle').addEventListener('change', function() {
            const smtpFields = document.getElementById('smtpFields');
            smtpFields.style.display = this.checked ? 'block' : 'none';
        });

        // Send test email via AJAX
        document.getElementById('sendTestEmailBtn').addEventListener('click', function() {
            const testEmail = document.getElementById('test_email').value;
            const alertBox = document.getElementById('testEmailAlert');
            const btn = this;

            if (!testEmail) {
                showAlert('error', '{{ __("Please enter an email address") }}');
                return;
            }

            // Disable button
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> {{ __("Sending...") }}';

            // Send AJAX request
            fetch('{{ route("settings.notifications.test-email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ test_email: testEmail })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                showAlert('error', '{{ __("An error occurred while sending the test email") }}');
            })
            .finally(() => {
                // Re-enable button
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg> {{ __("Send Test Email") }}';
            });
        });

        function showAlert(type, message) {
            const alertBox = document.getElementById('testEmailAlert');
            alertBox.className = `mb-6 p-4 rounded-lg ${type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'}`;
            alertBox.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        ${type === 'success'
                            ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>'
                            : '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>'
                        }
                    </svg>
                    ${message}
                </div>
            `;
            alertBox.classList.remove('hidden');

            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertBox.classList.add('hidden');
            }, 5000);
        }
        // Toggle Push Notification fields visibility
        const pushToggle = document.getElementById('pushToggle');
        if (pushToggle) {
            pushToggle.addEventListener('change', function() {
                const pushFields = document.getElementById('pushFields');
                pushFields.style.display = this.checked ? 'block' : 'none';
            });
        }

        // Check push notification subscription status
        async function checkPushStatus() {
            const statusText = document.getElementById('pushStatusText');
            const subscribeBtn = document.getElementById('subscribePushBtn');
            const unsubscribeBtn = document.getElementById('unsubscribePushBtn');
            
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                statusText.textContent = '{{ __("Push notifications not supported in this browser") }}';
                return;
            }

            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();
                
                if (subscription) {
                    statusText.textContent = '{{ __("Notifications are enabled for this browser") }}';
                    statusText.classList.add('text-green-600');
                    unsubscribeBtn.classList.remove('hidden');
                } else {
                    statusText.textContent = '{{ __("Notifications are not enabled for this browser") }}';
                    subscribeBtn.classList.remove('hidden');
                }
            } catch (error) {
                statusText.textContent = '{{ __("Unable to check notification status") }}';
            }
        }

        // Subscribe to push notifications
        document.getElementById('subscribePushBtn')?.addEventListener('click', async function() {
            try {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    alert('{{ __("Please allow notifications in your browser settings") }}');
                    return;
                }

                const registration = await navigator.serviceWorker.ready;
                
                // Get VAPID public key
                const response = await fetch('/push/vapid-key');
                const data = await response.json();
                
                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(data.key)
                });

                // Send subscription to server
                await fetch('/push/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(subscription)
                });

                location.reload();
            } catch (error) {
                console.error('Push subscription error:', error);
                alert('{{ __("Failed to enable notifications") }}');
            }
        });

        // Unsubscribe from push notifications
        document.getElementById('unsubscribePushBtn')?.addEventListener('click', async function() {
            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();
                
                if (subscription) {
                    await subscription.unsubscribe();
                    
                    await fetch('/push/unsubscribe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ endpoint: subscription.endpoint })
                    });
                }

                location.reload();
            } catch (error) {
                console.error('Push unsubscription error:', error);
                alert('{{ __("Failed to disable notifications") }}');
            }
        });

        // Helper function to convert VAPID key
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        // Check status on page load
        if ('serviceWorker' in navigator) {
            checkPushStatus();
        }

    </script>
    @endpush
</x-app-layout>
