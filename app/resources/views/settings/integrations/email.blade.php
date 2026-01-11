@extends('layouts.app')

@section('title', __('Email Notifications'))

@section('content')
<div class="p-4 md:p-6">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Email Notifications') }}</h1>
        <p class="text-slate-600 mt-1">{{ __('Configure email notifications for important system events.') }}</p>
    </div>

    {{-- Mail Configuration Status --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Mail Configuration') }}</h2>
        </div>
        <div class="p-4 md:p-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-slate-500">{{ __('Mail Driver') }}:</span>
                    <span class="ml-2 font-medium text-slate-900">{{ $mailConfig['driver'] ?? 'Not configured' }}</span>
                </div>
                <div>
                    <span class="text-slate-500">{{ __('SMTP Host') }}:</span>
                    <span class="ml-2 font-medium text-slate-900">{{ $mailConfig['host'] ?? 'Not configured' }}</span>
                </div>
                <div>
                    <span class="text-slate-500">{{ __('From Address') }}:</span>
                    <span class="ml-2 font-medium text-slate-900">{{ $mailConfig['from_address'] ?? 'Not configured' }}</span>
                </div>
                <div>
                    <span class="text-slate-500">{{ __('From Name') }}:</span>
                    <span class="ml-2 font-medium text-slate-900">{{ $mailConfig['from_name'] ?? 'Not configured' }}</span>
                </div>
            </div>
            <p class="mt-4 text-xs text-slate-500">
                {{ __('Mail settings are configured in the .env file. Contact your administrator to update these settings.') }}
            </p>
        </div>
    </div>

    {{-- Email Notification Settings Form --}}
    <form action="{{ route('settings.email.update') }}" method="POST" class="bg-white rounded-lg shadow-sm border border-slate-200">
        @csrf
        @method('PUT')

        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Notification Settings') }}</h2>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox"
                           name="email_notifications_enabled"
                           value="1"
                           class="sr-only peer"
                           {{ $emailSettings['email_notifications_enabled'] ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    <span class="ml-3 text-sm font-medium text-slate-700">{{ __('Enable Email Notifications') }}</span>
                </label>
            </div>
        </div>

        <div class="p-6 space-y-6">
            {{-- Admin Email --}}
            <div>
                <label for="email_notifications_admin" class="block text-sm font-medium text-slate-700 mb-1">
                    {{ __('Notification Recipients') }}
                </label>
                <input type="text"
                       name="email_notifications_admin"
                       id="email_notifications_admin"
                       value="{{ old('email_notifications_admin', $emailSettings['email_notifications_admin']) }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="admin@example.com, manager@example.com">
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('Enter one or more email addresses separated by commas.') }}
                </p>
                @error('email_notifications_admin')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notification Types --}}
            <div>
                <h3 class="text-sm font-medium text-slate-700 mb-3">{{ __('Notification Types') }}</h3>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="email_notify_domain_expiry"
                               value="1"
                               class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                               {{ $emailSettings['email_notify_domain_expiry'] ? 'checked' : '' }}>
                        <span class="ml-3 text-sm text-slate-700">{{ __('Domain Expiry Alerts') }}</span>
                        <span class="ml-2 text-xs text-slate-500">{{ __('Get notified when domains are expiring soon') }}</span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox"
                               name="email_notify_subscription_renewal"
                               value="1"
                               class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                               {{ $emailSettings['email_notify_subscription_renewal'] ? 'checked' : '' }}>
                        <span class="ml-3 text-sm text-slate-700">{{ __('Subscription Renewals') }}</span>
                        <span class="ml-2 text-xs text-slate-500">{{ __('Get notified about upcoming subscription renewals') }}</span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox"
                               name="email_notify_new_revenue"
                               value="1"
                               class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                               {{ $emailSettings['email_notify_new_revenue'] ? 'checked' : '' }}>
                        <span class="ml-3 text-sm text-slate-700">{{ __('New Revenue') }}</span>
                        <span class="ml-2 text-xs text-slate-500">{{ __('Get notified when new revenue is recorded') }}</span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox"
                               name="email_notify_client_status"
                               value="1"
                               class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                               {{ $emailSettings['email_notify_client_status'] ? 'checked' : '' }}>
                        <span class="ml-3 text-sm text-slate-700">{{ __('Client Status Changes') }}</span>
                        <span class="ml-2 text-xs text-slate-500">{{ __('Get notified when client status changes') }}</span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox"
                               name="email_notify_system_errors"
                               value="1"
                               class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                               {{ $emailSettings['email_notify_system_errors'] ? 'checked' : '' }}>
                        <span class="ml-3 text-sm text-slate-700">{{ __('System Errors') }}</span>
                        <span class="ml-2 text-xs text-slate-500">{{ __('Get notified about critical system errors') }}</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-slate-100 flex justify-between items-center">
            <button type="button"
                    onclick="testEmail()"
                    class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                {{ __('Send Test Email') }}
            </button>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                {{ __('Save Settings') }}
            </button>
        </div>
    </form>

    {{-- Disable Section --}}
    @if($emailSettings['email_notifications_enabled'])
    <div class="mt-6 bg-white rounded-lg shadow-sm border border-slate-200 p-6">
        <h3 class="text-sm font-medium text-slate-900 mb-2">{{ __('Disable Email Notifications') }}</h3>
        <p class="text-sm text-slate-500 mb-4">
            {{ __('This will disable all email notifications. You can re-enable them at any time.') }}
        </p>
        <form action="{{ route('settings.email.disconnect') }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                {{ __('Disable Email Notifications') }}
            </button>
        </form>
    </div>
    @endif
</div>

{{-- Test Email Modal --}}
<div id="testEmailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100">
            <h3 class="text-lg font-semibold text-slate-900">{{ __('Send Test Email') }}</h3>
        </div>
        <div class="p-4 md:p-6">
            <label for="test_email" class="block text-sm font-medium text-slate-700 mb-1">
                {{ __('Test Email Address') }}
            </label>
            <input type="email"
                   id="test_email"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="{{ __('Enter email address...') }}"
                   value="{{ $emailSettings['email_notifications_admin'] ? explode(',', $emailSettings['email_notifications_admin'])[0] : '' }}">
            <p class="mt-1 text-xs text-slate-500">
                {{ __('Leave empty to use the configured admin email.') }}
            </p>
            <div id="testEmailResult" class="mt-4 hidden">
                <div class="p-3 rounded-lg text-sm"></div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-100 flex justify-end gap-3">
            <button type="button"
                    onclick="closeTestModal()"
                    class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">
                {{ __('Cancel') }}
            </button>
            <button type="button"
                    onclick="sendTestEmail()"
                    id="sendTestBtn"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                {{ __('Send Test') }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function testEmail() {
    document.getElementById('testEmailModal').classList.remove('hidden');
    document.getElementById('testEmailModal').classList.add('flex');
    document.getElementById('testEmailResult').classList.add('hidden');
}

function closeTestModal() {
    document.getElementById('testEmailModal').classList.add('hidden');
    document.getElementById('testEmailModal').classList.remove('flex');
}

function sendTestEmail() {
    const btn = document.getElementById('sendTestBtn');
    const resultDiv = document.getElementById('testEmailResult');
    const resultContent = resultDiv.querySelector('div');
    const testEmail = document.getElementById('test_email').value;

    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> {{ __("Sending...") }}';

    fetch('{{ route("settings.email.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ test_email: testEmail })
    })
    .then(response => response.json())
    .then(data => {
        resultDiv.classList.remove('hidden');
        if (data.success) {
            resultContent.className = 'p-3 rounded-lg text-sm bg-green-50 text-green-700 border border-green-200';
            resultContent.textContent = data.message;
        } else {
            resultContent.className = 'p-3 rounded-lg text-sm bg-red-50 text-red-700 border border-red-200';
            resultContent.textContent = data.message;
        }
    })
    .catch(error => {
        resultDiv.classList.remove('hidden');
        resultContent.className = 'p-3 rounded-lg text-sm bg-red-50 text-red-700 border border-red-200';
        resultContent.textContent = '{{ __("Failed to send test email. Please try again.") }}';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '{{ __("Send Test") }}';
    });
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTestModal();
    }
});

// Close modal on backdrop click
document.getElementById('testEmailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTestModal();
    }
});
</script>
@endpush
@endsection
