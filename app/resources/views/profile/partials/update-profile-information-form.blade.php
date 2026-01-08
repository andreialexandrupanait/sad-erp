<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data">
    @csrf
    @method('patch')

    <!-- Avatar -->
    <div class="flex items-start gap-6 pb-6 border-b border-slate-200">
        <div class="flex-shrink-0">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full object-cover border-2 border-slate-200 shadow-sm" id="avatar-preview">
            @else
                <div class="w-20 h-20 rounded-full bg-slate-900 flex items-center justify-center text-white text-2xl font-semibold shadow-sm" id="avatar-initials">
                    {{ $user->initials }}
                </div>
                <img src="" alt="" class="w-20 h-20 rounded-full object-cover border-2 border-slate-200 shadow-sm hidden" id="avatar-preview">
            @endif
        </div>
        <div class="flex-1">
            <x-ui.label for="avatar">{{ __('Profile Photo') }}</x-ui.label>
            <div class="mt-2">
                <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/gif,image/webp" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-slate-900 file:text-white hover:file:bg-slate-800 file:cursor-pointer" onchange="previewAvatar(this)">
                <p class="mt-1 text-xs text-slate-500">{{ __('JPG, PNG, GIF or WebP. Max 2MB.') }}</p>
            </div>
            @if($user->avatar)
                <label class="mt-3 inline-flex items-center gap-2 text-sm text-red-600 cursor-pointer hover:text-red-700">
                    <input type="checkbox" name="remove_avatar" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                    {{ __('Remove current photo') }}
                </label>
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Name -->
        <x-ui.form-group name="name" label="{{ __('Name') }}" required>
            <x-ui.input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
        </x-ui.form-group>

        <!-- Email -->
        <x-ui.form-group name="email" label="{{ __('Email') }}" required>
            <x-ui.input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
        </x-ui.form-group>

        <!-- Phone -->
        <x-ui.form-group name="phone" label="{{ __('Phone') }}">
            <x-ui.input id="phone" name="phone" type="tel" :value="old('phone', $user->phone)" autocomplete="tel" />
        </x-ui.form-group>
    </div>

    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-700">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="font-medium underline hover:text-amber-600">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="flex items-center gap-4 pt-4 border-t border-slate-200">
        <x-ui.button type="submit">{{ __('Save') }}</x-ui.button>

        @if (session('status') === 'profile-updated')
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

<script>
    function previewAvatar(input) {
        const preview = document.getElementById('avatar-preview');
        const initials = document.getElementById('avatar-initials');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (initials) {
                    initials.classList.add('hidden');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
