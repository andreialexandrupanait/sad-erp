<x-guest-layout>
    <div class="mb-4 text-sm text-slate-600">
        {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
    </div>

    <form method="POST" action="{{ route('2fa.verify') }}">
        @csrf

        <!-- Authentication Code -->
        <div>
            <x-ui.label for="code">{{ __('Code') }}</x-ui.label>
            <x-ui.input
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

        <div class="flex items-center justify-end mt-4">
            <x-ui.button type="submit">
                {{ __('Verify') }}
            </x-ui.button>
        </div>

        <div class="mt-4 text-center">
            <form method="POST" action="{{ route('2fa.cancel') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-slate-600 hover:text-slate-900 underline">
                    {{ __('Cancel and log out') }}
                </button>
            </form>
        </div>
    </form>
</x-guest-layout>
