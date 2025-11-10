<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ $internalAccount->nume_cont_aplicatie }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">Internal account details and credentials</p>
            </div>
            <div class="flex gap-2">
                @if ($internalAccount->isOwner())
                    <x-ui.button variant="outline" onclick="window.location.href='{{ route('internal-accounts.edit', $internalAccount) }}'">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </x-ui.button>
                @endif
                <x-ui.button variant="secondary" onclick="window.location.href='{{ route('internal-accounts.index') }}'">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to List
                </x-ui.button>
            </div>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 pb-8 space-y-6">
        <!-- Success Messages -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- Access Restriction Notice -->
        @if (!$internalAccount->isOwner())
            <x-ui.alert variant="warning" title="View-Only Access">
                <p>This account is owned by {{ $internalAccount->user->name }}. You can view the details but cannot edit or delete it.</p>
            </x-ui.alert>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Main Account Information -->
            <div class="lg:col-span-2">
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="text-lg font-semibold text-slate-900">Account Information</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div class="grid gap-6">
                            <!-- Account Name -->
                            <div>
                                <x-ui.label class="text-slate-500">Account Name</x-ui.label>
                                <p class="mt-1 text-slate-900 font-medium">{{ $internalAccount->nume_cont_aplicatie }}</p>
                            </div>

                            <!-- Platform -->
                            <div>
                                <x-ui.label class="text-slate-500">Platform</x-ui.label>
                                <p class="mt-1 text-slate-900">{{ $internalAccount->platforma }}</p>
                            </div>

                            <!-- URL -->
                            @if ($internalAccount->url)
                                <div>
                                    <x-ui.label class="text-slate-500">URL</x-ui.label>
                                    <p class="mt-1">
                                        <a href="{{ $internalAccount->url }}" target="_blank" class="text-blue-600 hover:text-blue-800 break-all inline-flex items-center gap-1">
                                            {{ $internalAccount->url }}
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    </p>
                                </div>
                            @endif

                            <!-- Username -->
                            @if ($internalAccount->username)
                                <div>
                                    <x-ui.label class="text-slate-500">Username / Email</x-ui.label>
                                    <div class="mt-1 flex items-center gap-2">
                                        <p class="text-slate-900 font-mono">{{ $internalAccount->username }}</p>
                                        <button onclick="copyToClipboard('{{ $internalAccount->username }}')" class="text-slate-500 hover:text-slate-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <!-- Password -->
                            @if ($internalAccount->password)
                                <div>
                                    <x-ui.label class="text-slate-500">Password</x-ui.label>
                                    <div class="mt-1 flex items-center gap-2">
                                        <p id="password-display" class="text-slate-900 font-mono">{{ $internalAccount->masked_password }}</p>
                                        <button onclick="togglePassword()" class="text-blue-600 hover:text-blue-800">
                                            <svg id="eye-icon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button id="copy-password-btn" onclick="copyPassword()" class="text-slate-500 hover:text-slate-700 hidden">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <!-- Notes -->
                            @if ($internalAccount->notes)
                                <div>
                                    <x-ui.label class="text-slate-500">Notes</x-ui.label>
                                    <p class="mt-1 text-slate-900 whitespace-pre-line">{{ $internalAccount->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Access & Metadata -->
                <x-ui.card>
                    <x-ui.card-header>
                        <h3 class="text-lg font-semibold text-slate-900">Details</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div class="space-y-4">
                            <!-- Access Level -->
                            <div>
                                <x-ui.label class="text-slate-500">Access Level</x-ui.label>
                                <div class="mt-1">
                                    @if ($internalAccount->accesibil_echipei)
                                        <x-ui.badge variant="success">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            Team Accessible
                                        </x-ui.badge>
                                    @else
                                        <x-ui.badge variant="secondary">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            Private
                                        </x-ui.badge>
                                    @endif
                                </div>
                            </div>

                            <!-- Owner -->
                            <div>
                                <x-ui.label class="text-slate-500">Owner</x-ui.label>
                                <p class="mt-1 text-slate-900">
                                    {{ $internalAccount->user->name }}
                                    @if ($internalAccount->isOwner())
                                        <span class="text-sm text-slate-500">(You)</span>
                                    @endif
                                </p>
                            </div>

                            <!-- Created -->
                            <div>
                                <x-ui.label class="text-slate-500">Created</x-ui.label>
                                <p class="mt-1 text-slate-900 text-sm">{{ $internalAccount->created_at->format('M d, Y H:i') }}</p>
                            </div>

                            <!-- Last Updated -->
                            <div>
                                <x-ui.label class="text-slate-500">Last Updated</x-ui.label>
                                <p class="mt-1 text-slate-900 text-sm">{{ $internalAccount->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                <!-- Quick Actions -->
                @if ($internalAccount->url)
                    <x-ui.card>
                        <x-ui.card-header>
                            <h3 class="text-lg font-semibold text-slate-900">Quick Actions</h3>
                        </x-ui.card-header>
                        <x-ui.card-content>
                            <x-ui.button variant="default" class="w-full" onclick="window.open('{{ $internalAccount->url }}', '_blank')">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Open Platform
                            </x-ui.button>
                        </x-ui.card-content>
                    </x-ui.card>
                @endif
            </div>
        </div>
    </div>

    <script>
        let passwordRevealed = false;
        let actualPassword = '';

        function togglePassword() {
            const passwordDisplay = document.getElementById('password-display');
            const copyBtn = document.getElementById('copy-password-btn');

            if (!passwordRevealed) {
                // Reveal password
                actualPassword = '{{ $internalAccount->password }}';
                passwordDisplay.textContent = actualPassword;
                copyBtn.classList.remove('hidden');
                passwordRevealed = true;

                // Show toast notification
                showToast('Password revealed', 'info');
            } else {
                // Hide password
                passwordDisplay.textContent = '{{ $internalAccount->masked_password }}';
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
                showToast('Copied to clipboard!', 'success');
            }, function(err) {
                console.error('Could not copy text: ', err);
                showToast('Failed to copy', 'error');
            });
        }

        function showToast(message, type = 'success') {
            const bgColors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500'
            };

            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 ${bgColors[type]} text-white px-4 py-2 rounded shadow-lg z-50 transition-opacity duration-300`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 2000);
        }
    </script>
</x-app-layout>
