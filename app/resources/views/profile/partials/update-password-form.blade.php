<form method="post" action="{{ route('password.update') }}" class="space-y-6">
    @csrf
    @method('put')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-ui.form-group name="current_password" label="{{ __('Current Password') }}" :error="$errors->updatePassword->first('current_password')">
            <x-ui.input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
        </x-ui.form-group>

        <div></div>

        <x-ui.form-group name="password" label="{{ __('New Password') }}" :error="$errors->updatePassword->first('password')">
            <x-ui.input id="update_password_password" name="password" type="password" autocomplete="new-password" />
        </x-ui.form-group>

        <x-ui.form-group name="password_confirmation" label="{{ __('Confirm Password') }}" :error="$errors->updatePassword->first('password_confirmation')">
            <x-ui.input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
        </x-ui.form-group>
    </div>

    <div class="flex items-center gap-4 pt-4 border-t border-slate-200">
        <x-ui.button type="submit">{{ __('Update Password') }}</x-ui.button>

        @if (session('status') === 'password-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-green-600"
            >{{ __('Saved.') }}</p>
        @endif
    </div>
</form>
