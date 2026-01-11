<x-app-layout>
    <x-slot name="pageTitle">{{ __('Slack Integration') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-4 md:p-6">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('settings.integrations') }}" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <div class="flex-shrink-0 w-10 h-10 bg-[#4A154B]/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-[#4A154B]" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313zM8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834zM8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312zM18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834zM17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312zM15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52zM15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">{{ __('Slack Integration') }}</h1>
                            <p class="text-slate-500">{{ __('Send notifications to Slack channels') }}</p>
                        </div>
                    </div>
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

                <!-- Test Alert (AJAX) -->
                <div id="testAlert" class="hidden mb-6 p-4 rounded-lg"></div>

                <form method="POST" action="{{ route('settings.slack.update') }}">
                    @csrf

                    <!-- Connection Settings -->
                    <div class="bg-white rounded-xl border border-slate-200 mb-6">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 rounded-t-xl">
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('Connection Settings') }}</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Enable Toggle -->
                            <div class="pb-6 border-b border-slate-200">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           name="slack_enabled"
                                           value="1"
                                           id="slackToggle"
                                           {{ old('slack_enabled', $slackSettings['slack_enabled']) ? 'checked' : '' }}
                                           class="h-5 w-5 text-[#4A154B] focus:ring-[#4A154B] border-slate-300 rounded">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('Enable Slack Notifications') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Send notifications to Slack when enabled') }}</span>
                                    </span>
                                </label>
                            </div>

                            <!-- Webhook URL -->
                            <div>
                                <label for="slack_webhook_url" class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('Webhook URL') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="url"
                                       id="slack_webhook_url"
                                       name="slack_webhook_url"
                                       value="{{ old('slack_webhook_url', $slackSettings['slack_webhook_url']) }}"
                                       placeholder="https://hooks.slack.com/services/YOUR_WORKSPACE/YOUR_CHANNEL/YOUR_TOKEN"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#4A154B] focus:border-transparent font-mono text-sm @error('slack_webhook_url') border-red-500 @enderror">
                                <p class="mt-2 text-xs text-slate-500">
                                    {{ __('To get a webhook URL, create a Slack App:') }}
                                </p>
                                <ol class="mt-1 text-xs text-slate-500 list-decimal list-inside space-y-1">
                                    <li>{{ __('Go to') }} <a href="https://api.slack.com/apps" target="_blank" class="text-[#4A154B] hover:underline">api.slack.com/apps</a></li>
                                    <li>{{ __('Click "Create New App" > "From scratch"') }}</li>
                                    <li>{{ __('Name your app and select your workspace') }}</li>
                                    <li>{{ __('Go to "Incoming Webhooks" and activate it') }}</li>
                                    <li>{{ __('Click "Add New Webhook to Workspace" and select a channel') }}</li>
                                    <li>{{ __('Copy the Webhook URL') }}</li>
                                </ol>
                                <a href="https://api.slack.com/messaging/webhooks" target="_blank" class="inline-flex items-center text-xs text-[#4A154B] hover:underline mt-2">
                                    {{ __('Slack Webhooks Documentation') }}
                                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>

                            <!-- Channel Override (optional) -->
                            <div>
                                <label for="slack_channel" class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('Channel Override') }}
                                </label>
                                <input type="text"
                                       id="slack_channel"
                                       name="slack_channel"
                                       value="{{ old('slack_channel', $slackSettings['slack_channel']) }}"
                                       placeholder="#notifications"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#4A154B] focus:border-transparent">
                                <p class="mt-1 text-xs text-slate-500">{{ __('Leave empty to use the channel configured in the webhook') }}</p>
                            </div>

                            <!-- Test Button -->
                            <div class="pt-4 border-t border-slate-200">
                                <button type="button"
                                        id="testSlackBtn"
                                        class="px-4 py-2 bg-[#4A154B] text-white rounded-lg hover:bg-[#611f69] transition-colors inline-flex items-center gap-2">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52zM6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313z"/>
                                    </svg>
                                    {{ __('Send Test Message') }}
                                </button>
                                <p class="mt-2 text-xs text-slate-500">{{ __('Save your settings first, then send a test message') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Types -->
                    <div class="bg-white rounded-xl border border-slate-200 mb-6">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 rounded-t-xl">
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('Notification Types') }}</h3>
                            <p class="text-sm text-slate-500 mt-1">{{ __('Choose which notifications to send to Slack') }}</p>
                        </div>
                        <div class="p-4 md:p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Domain Expiry -->
                                <label class="flex items-start cursor-pointer p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                                    <input type="checkbox"
                                           name="slack_notify_domain_expiry"
                                           value="1"
                                           {{ old('slack_notify_domain_expiry', $slackSettings['slack_notify_domain_expiry']) ? 'checked' : '' }}
                                           class="h-4 w-4 text-[#4A154B] focus:ring-[#4A154B] border-slate-300 rounded mt-0.5">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('Domain Expiry Alerts') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Receive alerts when domains are about to expire') }}</span>
                                    </span>
                                </label>

                                <!-- Subscription Renewal -->
                                <label class="flex items-start cursor-pointer p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                                    <input type="checkbox"
                                           name="slack_notify_subscription_renewal"
                                           value="1"
                                           {{ old('slack_notify_subscription_renewal', $slackSettings['slack_notify_subscription_renewal']) ? 'checked' : '' }}
                                           class="h-4 w-4 text-[#4A154B] focus:ring-[#4A154B] border-slate-300 rounded mt-0.5">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('Subscription Renewal Alerts') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Receive alerts for upcoming subscription renewals') }}</span>
                                    </span>
                                </label>

                                <!-- New Revenue -->
                                <label class="flex items-start cursor-pointer p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                                    <input type="checkbox"
                                           name="slack_notify_new_revenue"
                                           value="1"
                                           {{ old('slack_notify_new_revenue', $slackSettings['slack_notify_new_revenue']) ? 'checked' : '' }}
                                           class="h-4 w-4 text-[#4A154B] focus:ring-[#4A154B] border-slate-300 rounded mt-0.5">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('New Revenue Recorded') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Get notified when new revenue is recorded') }}</span>
                                    </span>
                                </label>

                                <!-- Client Status -->
                                <label class="flex items-start cursor-pointer p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                                    <input type="checkbox"
                                           name="slack_notify_client_status"
                                           value="1"
                                           {{ old('slack_notify_client_status', $slackSettings['slack_notify_client_status']) ? 'checked' : '' }}
                                           class="h-4 w-4 text-[#4A154B] focus:ring-[#4A154B] border-slate-300 rounded mt-0.5">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('Client Status Changes') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Get notified when client status changes') }}</span>
                                    </span>
                                </label>

                                <!-- System Errors -->
                                <label class="flex items-start cursor-pointer p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                                    <input type="checkbox"
                                           name="slack_notify_system_errors"
                                           value="1"
                                           {{ old('slack_notify_system_errors', $slackSettings['slack_notify_system_errors']) ? 'checked' : '' }}
                                           class="h-4 w-4 text-[#4A154B] focus:ring-[#4A154B] border-slate-300 rounded mt-0.5">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('System Errors') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Get notified of critical system errors') }}</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Save/Cancel Buttons -->
                    <div class="flex justify-between items-center">
                        @if($slackSettings['slack_enabled'] && $slackSettings['slack_webhook_url'])
                            <button type="button"
                                    onclick="if(confirm('{{ __('Are you sure you want to disconnect Slack?') }}')) document.getElementById('disconnectForm').submit();"
                                    class="px-4 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                {{ __('Disconnect') }}
                            </button>
                        @else
                            <div></div>
                        @endif
                        <div class="flex gap-3">
                            <a href="{{ route('settings.integrations') }}"
                               class="px-6 py-2.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit"
                                    class="px-6 py-2.5 bg-[#4A154B] text-white rounded-lg hover:bg-[#611f69] transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('Save Settings') }}
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Hidden disconnect form -->
                <form id="disconnectForm" method="POST" action="{{ route('settings.slack.disconnect') }}" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Test Slack connection
        document.getElementById('testSlackBtn').addEventListener('click', function() {
            const btn = this;
            const originalHtml = btn.innerHTML;

            // Disable button
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> {{ __("Sending...") }}';

            // Send AJAX request
            fetch('{{ route("settings.slack.test") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                showAlert(data.success ? 'success' : 'error', data.message);
            })
            .catch(error => {
                showAlert('error', '{{ __("An error occurred while sending the test message") }}');
            })
            .finally(() => {
                // Re-enable button
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        });

        function showAlert(type, message) {
            const alertBox = document.getElementById('testAlert');
            alertBox.className = `mb-6 p-4 rounded-lg flex items-center ${type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'}`;
            alertBox.innerHTML = `
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    ${type === 'success'
                        ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>'
                        : '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>'
                    }
                </svg>
                ${message}
            `;
            alertBox.classList.remove('hidden');

            // Scroll to alert
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertBox.classList.add('hidden');
            }, 5000);
        }
    </script>
    @endpush
</x-app-layout>
