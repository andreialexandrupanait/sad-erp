<x-app-layout>
    <x-slot name="header">
        <div class="px-6 lg:px-8 py-8">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                {{ __('Create New Internal Account') }}
            </h2>
            <p class="mt-2 text-sm text-slate-600">Add a new internal account to your organization</p>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 pb-8">
        <div class="max-w-2xl">
            <x-ui.card>
                <div class="p-6">
                    <form method="POST" action="{{ route('internal-accounts.store') }}">
                        @csrf

                        <div class="space-y-6">
                            <!-- Account Name -->
                            <div class="space-y-2">
                                <x-ui.label for="nume_cont_aplicatie" :required="true">
                                    Account / Application Name
                                </x-ui.label>
                                <x-ui.input
                                    id="nume_cont_aplicatie"
                                    type="text"
                                    name="nume_cont_aplicatie"
                                    value="{{ old('nume_cont_aplicatie') }}"
                                    placeholder="e.g., Company Bank Account, AWS Root"
                                    required
                                />
                                @error('nume_cont_aplicatie')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Platform -->
                            <div class="space-y-2">
                                <x-ui.label for="platforma" :required="true">
                                    Platform
                                </x-ui.label>
                                <x-ui.select id="platforma" name="platforma" required>
                                    <option value="">Select a platform</option>
                                    @foreach ($platforms as $key => $value)
                                        <option value="{{ $key }}" {{ old('platforma') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </x-ui.select>
                                @error('platforma')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- URL -->
                            <div class="space-y-2">
                                <x-ui.label for="url">
                                    URL
                                </x-ui.label>
                                <x-ui.input
                                    id="url"
                                    type="url"
                                    name="url"
                                    value="{{ old('url') }}"
                                    placeholder="https://example.com/login"
                                />
                                @error('url')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Username -->
                            <div class="space-y-2">
                                <x-ui.label for="username">
                                    Username / Email
                                </x-ui.label>
                                <x-ui.input
                                    id="username"
                                    type="text"
                                    name="username"
                                    value="{{ old('username') }}"
                                    placeholder="admin or email@company.com"
                                />
                                @error('username')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="space-y-2">
                                <x-ui.label for="password">
                                    Password
                                </x-ui.label>
                                <div class="relative">
                                    <x-ui.input
                                        id="password"
                                        type="password"
                                        name="password"
                                        value="{{ old('password') }}"
                                        placeholder="Enter password"
                                    />
                                    <button type="button" onclick="togglePasswordVisibility()"
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-500 hover:text-slate-700">
                                        <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Team Accessible -->
                            <div class="flex items-start space-x-3">
                                <div class="flex items-center h-5">
                                    <input id="accesibil_echipei" name="accesibil_echipei" type="checkbox" value="1" {{ old('accesibil_echipei') ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950 focus:ring-offset-2">
                                </div>
                                <div class="text-sm">
                                    <label for="accesibil_echipei" class="font-medium text-slate-900">
                                        Make accessible to team
                                    </label>
                                    <p class="text-slate-500">
                                        Allow all team members in your organization to view this account (you will remain the owner)
                                    </p>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="space-y-2">
                                <x-ui.label for="notes">
                                    Notes
                                </x-ui.label>
                                <x-ui.textarea
                                    id="notes"
                                    name="notes"
                                    rows="3"
                                    placeholder="Additional information, recovery codes, etc."
                                >{{ old('notes') }}</x-ui.textarea>
                                @error('notes')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-slate-200">
                            <x-ui.button type="button" variant="outline" onclick="window.location.href='{{ route('internal-accounts.index') }}'">
                                Cancel
                            </x-ui.button>
                            <x-ui.button type="submit" variant="default">
                                Create Account
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </x-ui.card>
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
