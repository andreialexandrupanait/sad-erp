<x-app-layout>
    <x-slot name="pageTitle">Enable Two-Factor Authentication</x-slot>

    <div class="p-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __("Scan QR Code") }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __("Scan this QR code with your authenticator app (Google Authenticator, Authy, 1Password, etc.)") }}
                            </p>
                        </header>

                        <div class="mt-6 flex flex-col items-center">
                            <div class="p-4 bg-white rounded-lg border border-gray-200">
                                {!! $qrCode !!}
                            </div>

                            <div class="mt-4 text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __("Or enter this code manually:") }}</p>
                                <code class="mt-2 inline-block px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded font-mono text-sm tracking-wider">
                                    {{ $secret }}
                                </code>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('profile.two-factor.confirm') }}" class="mt-6">
                            @csrf
                            <div>
                                <x-ui.label for="code">{{ __("Verification Code") }}</x-ui.label>
                                <x-ui.input id="code" name="code" type="text" placeholder="000000" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code" required autofocus />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __("Enter the 6-digit code from your authenticator app") }}</p>
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>

                            <div class="mt-6 flex items-center gap-4">
                                <x-ui.button type="submit" variant="default">{{ __("Confirm & Enable") }}</x-ui.button>
                                <a href="{{ route('profile.two-factor') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __("Cancel") }}</a>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
