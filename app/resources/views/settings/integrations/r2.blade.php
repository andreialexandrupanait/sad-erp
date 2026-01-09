<x-app-layout>
    <x-slot name="pageTitle">{{ __('Cloudflare R2 Storage') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('settings.integrations') }}" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">{{ __('Cloudflare R2 Storage') }}</h1>
                            <p class="text-slate-500">{{ __('Store files in Cloudflare R2 object storage') }}</p>
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

                <form method="POST" action="{{ route('settings.r2.update') }}">
                    @csrf

                    <!-- Connection Settings -->
                    <div class="bg-white rounded-xl border border-slate-200 mb-6">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 rounded-t-xl flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">{{ __('R2 Credentials') }}</h3>
                                <p class="text-sm text-slate-500 mt-1">{{ __('Get these from Cloudflare R2 API Tokens page') }}</p>
                            </div>
                            <!-- Collapsible Help Link -->
                            <button type="button" onclick="document.getElementById('setupGuide').classList.toggle('hidden')" class="text-sm text-orange-600 hover:text-orange-700 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ __('How to get credentials?') }}
                            </button>
                        </div>
                        
                        <!-- Collapsible Setup Guide -->
                        <div id="setupGuide" class="hidden border-b border-slate-200 bg-orange-50 px-6 py-4">
                            <ol class="space-y-2 text-sm text-orange-800">
                                <li class="flex items-center gap-2">
                                    <span class="flex-shrink-0 w-5 h-5 bg-orange-200 text-orange-700 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                    <a href="https://dash.cloudflare.com/?to=/:account/r2/overview" target="_blank" class="text-orange-700 hover:text-orange-900 underline">
                                        {{ __('Open Cloudflare R2 Dashboard') }} →
                                    </a>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="flex-shrink-0 w-5 h-5 bg-orange-200 text-orange-700 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                    <span>{{ __('Create a bucket (e.g., "erp-files")') }}</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="flex-shrink-0 w-5 h-5 bg-orange-200 text-orange-700 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                    <a href="https://dash.cloudflare.com/?to=/:account/r2/api-tokens" target="_blank" class="text-orange-700 hover:text-orange-900 underline">
                                        {{ __('Create API Token (Object Read & Write)') }} →
                                    </a>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="flex-shrink-0 w-5 h-5 bg-orange-200 text-orange-700 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                                    <span>{{ __('Copy Access Key ID, Secret Key, and Endpoint below') }}</span>
                                </li>
                            </ol>
                        </div>

                        <div class="p-6 space-y-6">
                            <!-- Enable Toggle -->
                            <div class="pb-6 border-b border-slate-200">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           name="r2_enabled"
                                           value="1"
                                           id="r2Toggle"
                                           {{ old('r2_enabled', $r2Settings['r2_enabled']) ? 'checked' : '' }}
                                           class="h-5 w-5 text-orange-600 focus:ring-orange-600 border-slate-300 rounded">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('Enable R2 Storage') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Store files in Cloudflare R2 when enabled') }}</span>
                                    </span>
                                </label>
                            </div>

                            <!-- Access Key ID -->
                            <div>
                                <label for="r2_access_key_id" class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('Access Key ID') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="r2_access_key_id"
                                       name="r2_access_key_id"
                                       value="{{ old('r2_access_key_id', $r2Settings['r2_access_key_id']) }}"
                                       placeholder="e.g., 7d9e5f8a2b4c6d8e0f1a3b5c7d9e1f3a"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-600 focus:border-transparent font-mono text-sm">
                            </div>

                            <!-- Secret Access Key -->
                            <div>
                                <label for="r2_secret_access_key" class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('Secret Access Key') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="password"
                                       id="r2_secret_access_key"
                                       name="r2_secret_access_key"
                                       value=""
                                       placeholder="{{ $r2Settings['r2_secret_access_key'] ? '••••••••••••••••' : 'Paste your secret key' }}"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-600 focus:border-transparent font-mono text-sm">
                                @if($r2Settings['r2_secret_access_key'])
                                    <p class="mt-1 text-xs text-slate-500">{{ __('Leave empty to keep existing key') }}</p>
                                @endif
                            </div>

                            <!-- Endpoint URL -->
                            <div>
                                <label for="r2_endpoint" class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('S3 API Endpoint') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="url"
                                       id="r2_endpoint"
                                       name="r2_endpoint"
                                       value="{{ old('r2_endpoint', $r2Settings['r2_endpoint']) }}"
                                       placeholder="https://your-account-id.r2.cloudflarestorage.com"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-600 focus:border-transparent font-mono text-sm">
                                <p class="mt-1 text-xs text-slate-500">{{ __('Found in: R2 > Bucket > Settings > S3 API') }}</p>
                            </div>

                            <!-- Bucket Name -->
                            <div>
                                <label for="r2_bucket" class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('Bucket Name') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="r2_bucket"
                                       name="r2_bucket"
                                       value="{{ old('r2_bucket', $r2Settings['r2_bucket']) }}"
                                       placeholder="e.g., erp-files"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-600 focus:border-transparent text-sm">
                            </div>

                            <input type="hidden" name="r2_region" value="{{ old('r2_region', $r2Settings['r2_region'] ?: 'auto') }}">

                            <!-- Test Button -->
                            <div class="pt-4 border-t border-slate-200">
                                <button type="button"
                                        id="testR2Btn"
                                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors inline-flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    {{ __('Test Connection') }}
                                </button>
                                <span class="ml-3 text-xs text-slate-500">{{ __('Save first, then test') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Storage Options -->
                    <div class="bg-white rounded-xl border border-slate-200 mb-6">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 rounded-t-xl">
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('What to Store in R2') }}</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="flex items-start cursor-pointer p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                                    <input type="checkbox"
                                           name="r2_use_for_financial"
                                           value="1"
                                           {{ old('r2_use_for_financial', $r2Settings['r2_use_for_financial']) ? 'checked' : '' }}
                                           class="h-4 w-4 text-orange-600 focus:ring-orange-600 border-slate-300 rounded mt-0.5">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('Financial Documents') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Bank statements, invoices, receipts') }}</span>
                                    </span>
                                </label>

                                <label class="flex items-start cursor-pointer p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                                    <input type="checkbox"
                                           name="r2_use_for_contracts"
                                           value="1"
                                           {{ old('r2_use_for_contracts', $r2Settings['r2_use_for_contracts']) ? 'checked' : '' }}
                                           class="h-4 w-4 text-orange-600 focus:ring-orange-600 border-slate-300 rounded mt-0.5">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('Contract PDFs') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Generated contract and annex PDFs') }}</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Save/Cancel Buttons -->
                    <div class="flex justify-between items-center mb-6">
                        @if($r2Settings['r2_enabled'] && $r2Settings['r2_bucket'])
                            <button type="button"
                                    onclick="if(confirm('{{ __('Are you sure you want to disconnect R2 storage?') }}')) document.getElementById('disconnectForm').submit();"
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
                                    class="px-6 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('Save Settings') }}
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Migration Section (shown only when R2 is configured) -->
                @if($r2Settings['r2_enabled'] && $r2Settings['r2_bucket'])
                <div class="bg-white rounded-xl border border-slate-200 mb-6">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 rounded-t-xl">
                        <h3 class="text-lg font-semibold text-slate-900">{{ __('Migrate Existing Files') }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ __('Move your existing local files to R2 storage') }}</p>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-600 mb-4">
                            {{ __('After configuring R2, you can migrate existing files from local storage to R2 using the command below.') }}
                        </p>
                        
                        <div class="bg-slate-900 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-slate-400">{{ __('Run in terminal') }}</span>
                                <button type="button" onclick="copyCommand(this)" class="text-xs text-slate-400 hover:text-white flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    {{ __('Copy') }}
                                </button>
                            </div>
                            <code class="text-green-400 text-sm font-mono block">php artisan storage:migrate-r2</code>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <span class="font-medium text-slate-700">{{ __('Preview first (dry run):') }}</span>
                                    <code class="ml-2 text-xs bg-slate-100 px-2 py-1 rounded text-slate-700">php artisan storage:migrate-r2 --dry-run</code>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <span class="font-medium text-slate-700">{{ __('Migrate only financial files:') }}</span>
                                    <code class="ml-2 text-xs bg-slate-100 px-2 py-1 rounded text-slate-700">php artisan storage:migrate-r2 --type=financial</code>
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <span class="font-medium text-slate-700">{{ __('Migrate only contracts:') }}</span>
                                    <code class="ml-2 text-xs bg-slate-100 px-2 py-1 rounded text-slate-700">php artisan storage:migrate-r2 --type=contracts</code>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div class="text-sm text-amber-800">
                                    <strong>{{ __('Important:') }}</strong> {{ __('Local files are kept as backup. You can safely delete them after verifying migration success.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Hidden disconnect form -->
                <form id="disconnectForm" method="POST" action="{{ route('settings.r2.disconnect') }}" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('testR2Btn').addEventListener('click', function() {
            const btn = this;
            const originalHtml = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> {{ __("Testing...") }}';

            fetch('{{ route("settings.r2.test") }}', {
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
                showAlert('error', '{{ __("An error occurred while testing the connection") }}');
            })
            .finally(() => {
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
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => alertBox.classList.add('hidden'), 8000);
        }

        function copyCommand(btn) {
            const code = btn.closest('div').querySelector('code').textContent;
            navigator.clipboard.writeText(code).then(() => {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> {{ __("Copied!") }}';
                setTimeout(() => btn.innerHTML = originalText, 2000);
            });
        }
    </script>
    @endpush
</x-app-layout>
