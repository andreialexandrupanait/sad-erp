<x-app-layout>
    <x-slot name="pageTitle">Two-Factor Authentication</x-slot>

    <div class="p-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __("Two-Factor Authentication") }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("Add an extra layer of security to your account using two-factor authentication.") }}
                            </p>
                        </header>

                        <div class="mt-6">
                            @if($enabled)
                                <div class="flex items-center gap-3 p-4 bg-green-50 rounded-lg border border-green-200">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-green-800">{{ __("Two-factor authentication is enabled") }}</p>
                                        <p class="text-sm text-green-600">{{ __("Your account is protected with an authenticator app.") }}</p>
                                    </div>
                                </div>

                                <div class="mt-6 space-y-4">
                                    <form method="POST" action="{{ route('profile.two-factor.recovery-codes') }}" class="inline">
                                        @csrf
                                        <div>
                                            <x-ui.label for="recovery_password">{{ __("Password") }}</x-ui.label>
                                            <x-ui.input id="recovery_password" name="password" type="password" placeholder="{{ __('Enter your password') }}" required />
                                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                        </div>
                                        <div class="mt-4 flex gap-3">
                                            <x-ui.button type="submit" variant="secondary">{{ __("Show Recovery Codes") }}</x-ui.button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('profile.two-factor.regenerate-codes') }}" x-data="{ confirmRegenerate: false }">
                                        @csrf
                                        <input type="hidden" name="password" x-ref="regenPassword" />
                                        <x-ui.button type="button" variant="outline" @click="
                                            const pwd = prompt('Enter your password to regenerate recovery codes:');
                                            if (pwd) {
                                                $refs.regenPassword.value = pwd;
                                                $el.closest('form').submit();
                                            }
                                        ">{{ __("Regenerate Recovery Codes") }}</x-ui.button>
                                    </form>

                                    <hr class="border-gray-200" />

                                    <form method="POST" action="{{ route('profile.two-factor.disable') }}" x-data>
                                        @csrf
                                        <input type="hidden" name="password" x-ref="disablePassword" />
                                        <x-ui.button type="button" variant="destructive" @click="
                                            const pwd = prompt('Enter your password to disable 2FA:');
                                            if (pwd) {
                                                $refs.disablePassword.value = pwd;
                                                if (confirm('Are you sure you want to disable two-factor authentication?')) {
                                                    $el.closest('form').submit();
                                                }
                                            }
                                        ">{{ __("Disable Two-Factor Authentication") }}</x-ui.button>
                                    </form>
                                </div>
                            @else
                                <div class="flex items-center gap-3 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-yellow-800">{{ __("Two-factor authentication is not enabled") }}</p>
                                        <p class="text-sm text-yellow-600">{{ __("Enable 2FA to add an extra layer of security.") }}</p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('profile.two-factor.enable') }}" class="mt-6">
                                    @csrf
                                    <x-ui.button type="submit" variant="default">{{ __("Enable Two-Factor Authentication") }}</x-ui.button>
                                </form>
                            @endif
                        </div>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 hover:text-gray-900">
                        &larr; {{ __("Back to Profile") }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
