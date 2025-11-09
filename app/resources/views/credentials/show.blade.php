<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $credential->display_name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('credentials.edit', $credential) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
                <a href="{{ route('credentials.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Credential Details -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Credential Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Client</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">
                                <a href="{{ route('clients.show', $credential->client) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $credential->client->display_name }}
                                </a>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Platform</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $credential->platform }}</p>
                        </div>

                        @if ($credential->url)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">URL</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100">
                                    <a href="{{ $credential->url }}" target="_blank" class="text-blue-600 hover:text-blue-800 break-all">
                                        {{ $credential->url }}
                                    </a>
                                </p>
                            </div>
                        @endif

                        @if ($credential->username)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Username / Email</label>
                                <div class="mt-1 flex items-center gap-2">
                                    <p class="text-gray-900 dark:text-gray-100">{{ $credential->username }}</p>
                                    <button onclick="copyToClipboard('{{ $credential->username }}')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if ($credential->password)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Password</label>
                                <div class="mt-1 flex items-center gap-2">
                                    <p id="password-display" class="text-gray-900 dark:text-gray-100 font-mono">{{ $credential->masked_password }}</p>
                                    <button onclick="togglePassword()" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        <svg id="eye-closed" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button id="copy-password-btn" onclick="copyPassword()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hidden">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Last Accessed</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">
                                {{ $credential->last_accessed_at ? $credential->last_accessed_at->format('M d, Y H:i') : 'Never' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Access Count</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $credential->access_count }} times</p>
                        </div>

                        @if ($credential->notes)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Notes</label>
                                <p class="mt-1 text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $credential->notes }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Created</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $credential->created_at->format('M d, Y H:i') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</label>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $credential->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            @if ($credential->url)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ $credential->url }}" target="_blank" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Open Platform
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
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
                // Show a temporary success message
                const message = document.createElement('div');
                message.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
                message.textContent = 'Copied to clipboard!';
                document.body.appendChild(message);

                setTimeout(() => {
                    message.remove();
                }, 2000);
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
</x-app-layout>
