<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Internal Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('internal-accounts.update', $internalAccount) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Account Name -->
                            <div>
                                <label for="nume_cont_aplicatie" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Account / Application Name <span class="text-red-500">*</span>
                                </label>
                                <input id="nume_cont_aplicatie" type="text" name="nume_cont_aplicatie" value="{{ old('nume_cont_aplicatie', $internalAccount->nume_cont_aplicatie) }}" required
                                    placeholder="e.g., Company Bank Account, AWS Root"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                @error('nume_cont_aplicatie')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Platform -->
                            <div>
                                <label for="platforma" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Platform <span class="text-red-500">*</span>
                                </label>
                                <select id="platforma" name="platforma" required
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">Select a platform</option>
                                    @foreach ($platforms as $key => $value)
                                        <option value="{{ $key }}" {{ old('platforma', $internalAccount->platforma) == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('platforma')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- URL -->
                            <div>
                                <label for="url" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    URL
                                </label>
                                <input id="url" type="url" name="url" value="{{ old('url', $internalAccount->url) }}"
                                    placeholder="https://example.com/login"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                @error('url')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Username -->
                            <div>
                                <label for="username" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Username / Email
                                </label>
                                <input id="username" type="text" name="username" value="{{ old('username', $internalAccount->username) }}"
                                    placeholder="admin or email@company.com"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                @error('username')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Password
                                </label>
                                <div class="relative">
                                    <input id="password" type="password" name="password" value=""
                                        placeholder="Leave blank to keep current password"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <button type="button" onclick="togglePasswordVisibility()"
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                        <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Leave blank to keep the current password</p>
                                @error('password')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Team Accessible -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="accesibil_echipei" name="accesibil_echipei" type="checkbox" value="1"
                                        {{ old('accesibil_echipei', $internalAccount->accesibil_echipei) ? 'checked' : '' }}
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded dark:border-gray-700 dark:bg-gray-900">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="accesibil_echipei" class="font-medium text-gray-700 dark:text-gray-300">
                                        Make accessible to team
                                    </label>
                                    <p class="text-gray-500 dark:text-gray-400">
                                        Allow all team members in your organization to view this account (you will remain the owner)
                                    </p>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label for="notes" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                    Notes
                                </label>
                                <textarea id="notes" name="notes" rows="3"
                                    placeholder="Additional information, recovery codes, etc."
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ old('notes', $internalAccount->notes) }}</textarea>
                                @error('notes')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 gap-4">
                            <a href="{{ route('internal-accounts.show', $internalAccount) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }
    </script>
</x-app-layout>
