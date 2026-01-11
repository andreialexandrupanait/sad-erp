<div class="space-y-4">
    <p class="text-sm text-slate-600">
        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
    </p>

    <x-ui.button
        variant="destructive"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-ui.button>
</div>

<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('profile.destroy') }}" class="p-4 md:p-6">
        @csrf
        @method('delete')

        <h2 class="text-lg font-medium text-slate-900">
            {{ __('Are you sure you want to delete your account?') }}
        </h2>

        <p class="mt-1 text-sm text-slate-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
        </p>

        <div class="mt-6">
            <x-ui.form-group name="password" label="{{ __('Password') }}" :error="$errors->userDeletion->first('password')">
                <x-ui.input
                    id="password"
                    name="password"
                    type="password"
                    class="w-full"
                    placeholder="{{ __('Enter your password') }}"
                />
            </x-ui.form-group>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <x-ui.button variant="outline" type="button" x-on:click="$dispatch('close')">
                {{ __('Cancel') }}
            </x-ui.button>

            <x-ui.button type="submit" variant="destructive">
                {{ __('Delete Account') }}
            </x-ui.button>
        </div>
    </form>
</x-modal>
