<x-app-layout>
    <x-slot name="pageTitle">{{ $credential->display_name }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('credentials.edit', $credential) }}'">
            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('Edit') }}
        </x-ui.button>
        <x-ui.button variant="outline" onclick="window.location.href='{{ route('credentials.index') }}'">
            {{ __('Back') }}
        </x-ui.button>
    </x-slot>

    <div class="p-6 space-y-6">
        <!-- Success Messages -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- Credential Details -->
        <x-ui.card>
            <x-ui.card-header>
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Credential Information') }}</h3>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-500">{{ __('Client') }}</label>
                        <p class="mt-1.5 text-slate-900">
                            <a href="{{ route('clients.show', $credential->client) }}" class="text-slate-900 hover:text-slate-600 font-medium underline">
                                {{ $credential->client->display_name }}
                            </a>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500">{{ __('Platform') }}</label>
                        <div class="mt-1.5">
                            <x-ui.badge variant="secondary">
                                {{ $credential->platform }}
                            </x-ui.badge>
                        </div>
                    </div>

                    @if ($credential->site_name)
                        <div>
                            <label class="block text-sm font-medium text-slate-500">{{ __('Site Name') }}</label>
                            <p class="mt-1.5 text-slate-900 font-medium">{{ $credential->site_name }}</p>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-slate-500">{{ __('Credential Type') }}</label>
                        <div class="mt-1.5">
                            <x-ui.badge variant="{{ $credential->type_badge_color }}">
                                {{ __($credential->type_label) }}
                            </x-ui.badge>
                        </div>
                    </div>

                    @if ($credential->url)
                        <div>
                            <label class="block text-sm font-medium text-slate-500">{{ __('Login URL') }}</label>
                            <p class="mt-1.5 text-slate-900">
                                <a href="{{ $credential->url }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline break-all">
                                    {{ $credential->url }}
                                </a>
                            </p>
                        </div>
                    @endif

                    @if ($credential->website)
                        <div>
                            <label class="block text-sm font-medium text-slate-500">{{ __('Website / Project URL') }}</label>
                            <p class="mt-1.5 text-slate-900">
                                <a href="{{ $credential->website }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline break-all">
                                    {{ $credential->website }}
                                </a>
                            </p>
                        </div>
                    @endif

                    @if ($credential->username)
                        <div>
                            <label class="block text-sm font-medium text-slate-500">{{ __('Username / Email') }}</label>
                            <div class="mt-1.5 flex items-center gap-2">
                                <p class="text-slate-900 font-medium">{{ $credential->username }}</p>
                                <button onclick="copyToClipboard('{{ addslashes($credential->username) }}')" class="text-slate-500 hover:text-slate-700 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if ($credential->password)
                        <div>
                            <label class="block text-sm font-medium text-slate-500">{{ __('Password') }}</label>
                            <div class="mt-1.5 flex items-center gap-2">
                                <p id="password-display" class="text-slate-900 font-mono font-medium">{{ $credential->masked_password }}</p>
                                <button onclick="togglePassword()" class="text-slate-600 hover:text-slate-900 transition-colors">
                                    <svg id="eye-icon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                <button id="copy-password-btn" onclick="copyPassword()" class="text-slate-500 hover:text-slate-700 transition-colors hidden">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-slate-500">{{ __('Last Accessed') }}</label>
                        <p class="mt-1.5 text-slate-900">
                            {{ $credential->last_accessed_at ? $credential->last_accessed_at->format('M d, Y H:i') : __('Never') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500">{{ __('Access Count') }}</label>
                        <p class="mt-1.5 text-slate-900 font-semibold">{{ $credential->access_count }} {{ __('times') }}</p>
                    </div>

                    @if ($credential->notes)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-500">{{ __('Notes') }}</label>
                            <p class="mt-1.5 text-slate-900 whitespace-pre-line">{{ $credential->notes }}</p>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-slate-500">{{ __('Created') }}</label>
                        <p class="mt-1.5 text-slate-700 text-sm">{{ $credential->created_at->format('M d, Y H:i') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-500">{{ __('Last Updated') }}</label>
                        <p class="mt-1.5 text-slate-700 text-sm">{{ $credential->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Quick Actions -->
        <x-ui.card>
            <x-ui.card-header>
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Quick Actions') }}</h3>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="flex flex-wrap gap-3">
                    @if ($credential->url)
                        <x-ui.button variant="default" onclick="quickLogin()">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            {{ __('Quick Login') }}
                        </x-ui.button>
                    @endif

                    @if ($credential->website)
                        <x-ui.button variant="outline" onclick="window.open('{{ $credential->website }}', '_blank')">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                            {{ __('Open Website') }}
                        </x-ui.button>
                    @elseif ($credential->url)
                        <x-ui.button variant="outline" onclick="window.open('{{ $credential->url }}', '_blank')">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            {{ __('Open Platform') }}
                        </x-ui.button>
                    @endif

                    <x-ui.button variant="outline" onclick="copyAllCredentials()">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        {{ __('Copy All') }}
                    </x-ui.button>
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>

    <x-toast />

    <script>
        let passwordRevealed = false;
        const actualPassword = '{{ addslashes($credential->password) }}';
        const username = '{{ addslashes($credential->username) }}';
        const loginUrl = '{{ $credential->url }}';

        function togglePassword() {
            const passwordDisplay = document.getElementById('password-display');
            const copyBtn = document.getElementById('copy-password-btn');

            if (!passwordRevealed) {
                passwordDisplay.textContent = actualPassword;
                copyBtn.classList.remove('hidden');
                passwordRevealed = true;
            } else {
                passwordDisplay.textContent = '{{ $credential->masked_password }}';
                copyBtn.classList.add('hidden');
                passwordRevealed = false;
            }
        }

        function copyPassword() {
            if (actualPassword) {
                copyToClipboard(actualPassword);
            }
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                showToast('{{ __("Copied to clipboard!") }}', 'success');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }

        function copyAllCredentials() {
            const text = `Username: ${username}\nPassword: ${actualPassword}`;
            navigator.clipboard.writeText(text).then(function() {
                showToast('{{ __("Credentials copied to clipboard!") }}', 'success');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }

        function quickLogin() {
            // Copy username first, then open login page
            navigator.clipboard.writeText(username).then(function() {
                showToast('{{ __("Username copied! Paste it on the login page, then click the password button to copy password.") }}', 'success');
                window.open(loginUrl, '_blank');
            }, function(err) {
                console.error('Could not copy text: ', err);
                window.open(loginUrl, '_blank');
            });
        }

        function showToast(message, type = 'info') {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { message, type }
            }));
        }
    </script>
</x-app-layout>
