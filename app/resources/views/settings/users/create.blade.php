<x-app-layout>
    <x-slot name="pageTitle">{{ __("Add User") }}</x-slot>

    <div class="p-6">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __("Add New User") }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __("Create a new team member account.") }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('settings.users.store') }}" x-data="{ passwordOption: 'invite' }">
                        @csrf

                        <div class="space-y-6">
                            <div>
                                <x-ui.label for="name" required>{{ __("Name") }}</x-ui.label>
                                <x-ui.input type="text" name="name" id="name" value="{{ old('name') }}" required />
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-ui.label for="email" required>{{ __("Email") }}</x-ui.label>
                                <x-ui.input type="email" name="email" id="email" value="{{ old('email') }}" required />
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-ui.label for="role" required>{{ __("Role") }}</x-ui.label>
                                <select name="role" id="role" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>{{ __("User") }} - {{ __("Basic access with limited permissions") }}</option>
                                    <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>{{ __("Manager") }} - {{ __("Extended access, can manage most resources") }}</option>
                                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>{{ __("Admin") }} - {{ __("Full access to all features") }}</option>
                                </select>
                                @error('role')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-ui.label for="phone">{{ __("Phone") }}</x-ui.label>
                                <x-ui.input type="tel" name="phone" id="phone" value="{{ old('phone') }}" />
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Password Setup") }}</label>
                                <div class="mt-3 space-y-3">
                                    <label class="flex items-start gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50" :class="{ 'ring-2 ring-blue-500 border-blue-500': passwordOption === 'invite' }">
                                        <input type="radio" name="password_option" value="invite" x-model="passwordOption" class="mt-1 text-blue-600 focus:ring-blue-500" checked>
                                        <div>
                                            <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __("Send invitation email") }}</span>
                                            <span class="block text-sm text-gray-500 dark:text-gray-400">{{ __("User will receive an email to set their own password.") }}</span>
                                        </div>
                                    </label>
                                    <label class="flex items-start gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50" :class="{ 'ring-2 ring-blue-500 border-blue-500': passwordOption === 'manual' }">
                                        <input type="radio" name="password_option" value="manual" x-model="passwordOption" class="mt-1 text-blue-600 focus:ring-blue-500">
                                        <div>
                                            <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ __("Set password manually") }}</span>
                                            <span class="block text-sm text-gray-500 dark:text-gray-400">{{ __("Create a password and share it with the user yourself.") }}</span>
                                        </div>
                                    </label>
                                </div>
                                @error('password_option')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div x-show="passwordOption === 'manual'" x-cloak class="space-y-4">
                                <div>
                                    <x-ui.label for="password" required>{{ __("Password") }}</x-ui.label>
                                    <x-ui.input type="password" name="password" id="password" autocomplete="new-password" />
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <x-ui.label for="password_confirmation" required>{{ __("Confirm Password") }}</x-ui.label>
                                    <x-ui.input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <a href="{{ route('settings.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                {{ __("Cancel") }}
                            </a>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                {{ __("Create User") }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
    </div>
</x-app-layout>
