<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
    </div>

    <form method="POST" action="{{ route('2fa.verify') }}" x-data="{ useRecoveryCode: false }">
        @csrf

        <div x-show="!useRecoveryCode">
            <!-- Authentication Code -->
            <div>
                <x-input-label for="code" :value="__('Code')" />
                <x-text-input
                    id="code"
                    class="block mt-1 w-full"
                    type="text"
                    name="code"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    autocomplete="one-time-code"
                    autofocus
                />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>
        </div>

        <div x-show="useRecoveryCode" x-cloak>
            <!-- Recovery Code -->
            <div>
                <x-input-label for="recovery_code" :value="__('Recovery Code')" />
                <x-text-input
                    id="recovery_code"
                    class="block mt-1 w-full"
                    type="text"
                    name="recovery_code"
                    autocomplete="off"
                />
                <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-between mt-4">
            <button
                type="button"
                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline cursor-pointer"
                x-on:click="useRecoveryCode = !useRecoveryCode"
                x-text="useRecoveryCode ? '{{ __('Use an authentication code') }}' : '{{ __('Use a recovery code') }}'"
            >
            </button>

            <x-primary-button>
                {{ __('Verify') }}
            </x-primary-button>
        </div>

        <div class="mt-4 text-center">
            <form method="POST" action="{{ route('2fa.cancel') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline">
                    {{ __('Cancel and log out') }}
                </button>
            </form>
        </div>
    </form>
</x-guest-layout>
