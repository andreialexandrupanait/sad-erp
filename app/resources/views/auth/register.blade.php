<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-ui.label for="name">{{ __('Name') }}</x-ui.label>
            <x-ui.input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-ui.label for="email">{{ __('Email') }}</x-ui.label>
            <x-ui.input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-ui.label for="password">{{ __('Password') }}</x-ui.label>
            <x-ui.input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-ui.label for="password_confirmation">{{ __('Confirm Password') }}</x-ui.label>
            <x-ui.input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-slate-600 hover:text-slate-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-ui.button type="submit" class="ms-4">
                {{ __('Register') }}
            </x-ui.button>
        </div>
    </form>
</x-guest-layout>
