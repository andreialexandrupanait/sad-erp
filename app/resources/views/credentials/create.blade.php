<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ __('Create New Credential') }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">Add a new client access credential</p>
            </div>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8">
        <div class="max-w-4xl mx-auto">
            <x-ui.card>
                <x-ui.card-content>
                    <form method="POST" action="{{ route('credentials.store') }}">
                        @csrf

                        <div class="space-y-6">
                            <!-- Client -->
                            <div>
                                <x-ui.label for="client_id">
                                    Client <span class="text-red-500">*</span>
                                </x-ui.label>
                                <x-ui.select id="client_id" name="client_id" required class="mt-1.5">
                                    <option value="">Select a client</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->display_name }}
                                        </option>
                                    @endforeach
                                </x-ui.select>
                                @error('client_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Platform -->
                            <div>
                                <x-ui.label for="platform">
                                    Platform <span class="text-red-500">*</span>
                                </x-ui.label>
                                <x-ui.select id="platform" name="platform" required class="mt-1.5">
                                    <option value="">Select a platform</option>
                                    @foreach ($platforms as $key => $value)
                                        <option value="{{ $key }}" {{ old('platform') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </x-ui.select>
                                @error('platform')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- URL -->
                            <div>
                                <x-ui.label for="url">
                                    URL
                                </x-ui.label>
                                <x-ui.input
                                    id="url"
                                    type="url"
                                    name="url"
                                    value="{{ old('url') }}"
                                    placeholder="https://example.com"
                                    class="mt-1.5"
                                />
                                @error('url')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Username/Email -->
                            <div>
                                <x-ui.label for="username">
                                    Username / Email
                                </x-ui.label>
                                <x-ui.input
                                    id="username"
                                    type="text"
                                    name="username"
                                    value="{{ old('username') }}"
                                    placeholder="username or email@example.com"
                                    class="mt-1.5"
                                />
                                @error('username')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div>
                                <x-ui.label for="password">
                                    Password
                                </x-ui.label>
                                <div class="relative mt-1.5">
                                    <x-ui.input
                                        id="password"
                                        type="password"
                                        name="password"
                                        value="{{ old('password') }}"
                                        placeholder="Enter password"
                                        class="pr-10"
                                    />
                                    <button
                                        type="button"
                                        onclick="togglePasswordVisibility()"
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-500 hover:text-slate-700"
                                    >
                                        <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div>
                                <x-ui.label for="notes">
                                    Notes
                                </x-ui.label>
                                <x-ui.textarea
                                    id="notes"
                                    name="notes"
                                    rows="3"
                                    placeholder="Additional information or notes..."
                                    class="mt-1.5"
                                >{{ old('notes') }}</x-ui.textarea>
                                @error('notes')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 gap-3 pt-6 border-t border-slate-200">
                            <x-ui.button
                                type="button"
                                variant="outline"
                                onclick="window.location.href='{{ route('credentials.index') }}'"
                            >
                                Cancel
                            </x-ui.button>
                            <x-ui.button type="submit" variant="default">
                                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Create Credential
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card-content>
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
