<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-ui.label for="update_password_current_password">{{ __('Current Password') }}</x-ui.label>
            <x-ui.input id="update_password_current_password" name="current_password" type="password" placeholder="{{ __('Enter current password') }}" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-ui.label for="update_password_password">{{ __('New Password') }}</x-ui.label>
            <x-ui.input id="update_password_password" name="password" type="password" placeholder="{{ __('Enter new password') }}" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-ui.label for="update_password_password_confirmation">{{ __('Confirm Password') }}</x-ui.label>
            <x-ui.input id="update_password_password_confirmation" name="password_confirmation" type="password" placeholder="{{ __('Confirm new password') }}" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-ui.button type="submit" variant="default">{{ __('Save') }}</x-ui.button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
