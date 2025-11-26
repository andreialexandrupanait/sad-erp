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

                    @if ($credential->url)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-500">{{ __('URL') }}</label>
                            <p class="mt-1.5 text-slate-900">
                                <a href="{{ $credential->url }}" target="_blank" class="text-slate-600 hover:text-slate-900 underline break-all">
                                    {{ $credential->url }}
                                </a>
                            </p>
                        </div>
                    @endif

                    @if ($credential->username)
                        <div>
                            <label class="block text-sm font-medium text-slate-500">{{ __('Username / Email') }}</label>
                            <div class="mt-1.5 flex items-center gap-2">
                                <p class="text-slate-900 font-medium">{{ $credential->username }}</p>
                                <button onclick="copyToClipboard('{{ $credential->username }}')" class="text-slate-500 hover:text-slate-700 transition-colors">
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
        @if ($credential->url)
            <x-ui.card>
                <x-ui.card-header>
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Quick Actions') }}</h3>
                </x-ui.card-header>
                <x-ui.card-content>
                    <div class="flex flex-wrap gap-3">
                        <x-ui.button variant="default" onclick="window.open('{{ $credential->url }}', '_blank')">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            {{ __('Open Platform') }}
                        </x-ui.button>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        @endif
    </div>

    <script>
        let passwordRevealed = false;
        let actualPassword = '';

        function togglePassword() {
            const passwordDisplay = document.getElementById('password-display');
            const copyBtn = document.getElementById('copy-password-btn');

            if (!passwordRevealed) {
                // Reveal password (would normally make an AJAX call here for security)
                actualPassword = '{{ $credential->password }}';
                passwordDisplay.textContent = actualPassword;
                copyBtn.classList.remove('hidden');
                passwordRevealed = true;
            } else {
                // Hide password
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
                // Create toast notification
                const toast = document.createElement('div');
                toast.className = 'fixed top-4 right-4 bg-slate-900 text-white px-4 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                toast.innerHTML = `
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ __('Copied to clipboard!') }}</span>
                `;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 2000);
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
</x-app-layout>
