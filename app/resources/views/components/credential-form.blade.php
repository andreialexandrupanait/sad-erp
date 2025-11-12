@props(['credential' => null, 'clients' => [], 'platforms' => [], 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6" x-data="{ showPass: false }">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-ui.card-content>
            <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <!-- Client (Required) -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="client_id">
                        {{ __('Client') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="client_id" id="client_id" required>
                            <option value="">{{ __('Select a client') }}</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $credential->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                    {{ $client->display_name }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('client_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Platform (Required) -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="platform">
                        {{ __('Platform') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="platform" id="platform" required>
                            <option value="">{{ __('Select a platform') }}</option>
                            @foreach($platforms as $platform)
                                <option value="{{ $platform->value }}" {{ old('platform', $credential->platform ?? '') == $platform->value ? 'selected' : '' }}>
                                    {{ $platform->label }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('platform')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- URL -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="url">{{ __('URL') }}</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="url"
                            name="url"
                            id="url"
                            value="{{ old('url', $credential->url ?? '') }}"
                            placeholder="{{ __('https://example.com') }}"
                        />
                    </div>
                    @error('url')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username/Email -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="username">{{ __('Username / Email') }}</x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="username"
                            id="username"
                            value="{{ old('username', $credential->username ?? '') }}"
                            placeholder="{{ __('username or email@example.com') }}"
                        />
                    </div>
                    @error('username')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="password">{{ __('Password') }}</x-ui.label>
                    <div class="mt-2 relative">
                        <input
                            x-bind:type="showPass ? 'text' : 'password'"
                            name="password"
                            id="password"
                            value="{{ old('password', $credential && isset($credential->id) ? '' : '') }}"
                            placeholder="{{ $credential ? __('Leave blank to keep current password') : __('Enter password') }}"
                            class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 pr-10 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <button
                            type="button"
                            @click="showPass = !showPass"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-500 hover:text-slate-700"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @if($credential)
                        <p class="mt-1 text-xs text-slate-500">{{ __('Leave blank to keep the current password') }}</p>
                    @endif
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="notes">{{ __('Notes') }}</x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea name="notes" id="notes" rows="3" placeholder="{{ __('Additional information or notes...') }}">{{ old('notes', $credential->notes ?? '') }}</x-ui.textarea>
                    </div>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-6 border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('credentials.index') }}'">
                {{ __('Cancel') }}
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $credential ? __('Update Credential') : __('Create Credential') }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
