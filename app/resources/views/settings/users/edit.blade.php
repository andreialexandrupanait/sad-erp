<x-app-layout>
    <x-slot name="pageTitle">{{ __("Users & Permissions") }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-4 md:p-6">
            <div class="bg-white rounded-lg border border-slate-200">
                <div class="p-4 md:p-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-medium text-slate-900">
                            {{ __("Edit User") }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __("Update user information for :name.", ['name' => $user->name]) }}
                        </p>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('settings.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div>
                                <x-ui.label for="name" required>{{ __("Name") }}</x-ui.label>
                                <x-ui.input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required />
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-ui.label for="email" required>{{ __("Email") }}</x-ui.label>
                                <x-ui.input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required />
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-ui.label for="role" required>{{ __("Role") }}</x-ui.label>
                                <select name="role" id="role" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>{{ __("User") }}</option>
                                    <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>{{ __("Manager") }}</option>
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>{{ __("Admin") }}</option>
                                </select>
                                @if($user->id === auth()->id())
                                    <input type="hidden" name="role" value="{{ $user->role }}">
                                    <p class="mt-1 text-sm text-gray-500">{{ __("You cannot change your own role.") }}</p>
                                @endif
                                @error('role')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-ui.label for="phone">{{ __("Phone") }}</x-ui.label>
                                <x-ui.input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" />
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-ui.label for="status" required>{{ __("Status") }}</x-ui.label>
                                <select name="status" id="status" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>{{ __("Active") }}</option>
                                    <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>{{ __("Inactive") }}</option>
                                </select>
                                @if($user->id === auth()->id())
                                    <input type="hidden" name="status" value="{{ $user->status }}">
                                    <p class="mt-1 text-sm text-gray-500">{{ __("You cannot deactivate your own account.") }}</p>
                                @endif
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Change Password") }}</label>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __("Leave blank to keep current password.") }}</p>
                                <div class="space-y-4">
                                    <div>
                                        <x-ui.label for="password">{{ __("New Password") }}</x-ui.label>
                                        <x-ui.input type="password" name="password" id="password" autocomplete="new-password" />
                                        @error('password')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <x-ui.label for="password_confirmation">{{ __("Confirm New Password") }}</x-ui.label>
                                        <x-ui.input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                            <form method="POST" action="{{ route('settings.users.resend-invite', $user) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ __("Send password reset email") }}
                                </button>
                            </form>

                            <div class="flex items-center gap-3">
                                <a href="{{ route('settings.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                    {{ __("Cancel") }}
                                </a>
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    {{ __("Save Changes") }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
