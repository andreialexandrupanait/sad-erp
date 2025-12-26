@props(['credential' => null, 'clients' => [], 'platforms' => [], 'credentialTypes' => [], 'sites' => [], 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6" x-data="{ showPass: false }">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-ui.card-content>
            <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-6">
                <!-- Row 1: Site (URL) + Platform -->
                <div class="sm:col-span-3">
                    <x-ui.label for="site_name">
                        {{ __('Site') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-1.5" x-data="{
                        open: false,
                        search: '{{ old('site_name', $credential->site_name ?? request('site_name', '')) }}',
                        sites: {{ Js::from($sites) }},
                        get filteredSites() {
                            if (!this.search) return this.sites;
                            return this.sites.filter(s => s.toLowerCase().includes(this.search.toLowerCase()));
                        }
                    }">
                        <div class="relative">
                            <input
                                type="text"
                                name="site_name"
                                id="site_name"
                                x-model="search"
                                @focus="open = true"
                                @click.away="open = false"
                                @keydown.escape="open = false"
                                placeholder="{{ __('example.com') }}"
                                required
                                autocomplete="off"
                                class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2"
                            />
                            <!-- Dropdown with existing sites -->
                            <div x-show="open && filteredSites.length > 0"
                                 x-transition
                                 class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-md shadow-lg max-h-48 overflow-auto">
                                <template x-for="site in filteredSites" :key="site">
                                    <button type="button"
                                            @click="search = site; open = false"
                                            class="w-full px-3 py-2 text-left text-sm hover:bg-slate-100 focus:bg-slate-100"
                                            x-text="site">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    @error('site_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-3">
                    <x-ui.label for="platform">
                        {{ __('Platform') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-1.5">
                        <x-ui.select name="platform" id="platform" required>
                            <option value="">{{ __('Select platform') }}</option>
                            @foreach($platforms as $platform)
                                <option value="{{ $platform->value }}" {{ old('platform', $credential->platform ?? '') == $platform->value ? 'selected' : '' }}>
                                    {{ $platform->label }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('platform')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Row 2: Username + Password -->
                <div class="sm:col-span-3">
                    <x-ui.label for="username">
                        {{ __('Username') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-1.5">
                        <x-ui.input
                            type="text"
                            name="username"
                            id="username"
                            value="{{ old('username', $credential->username ?? '') }}"
                            placeholder="{{ __('username or email') }}"
                            required
                        />
                    </div>
                    @error('username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-3">
                    <x-ui.label for="password">
                        {{ __('Password') }} @if(!$credential)<span class="text-red-500">*</span>@endif
                    </x-ui.label>
                    <div class="mt-1.5 relative">
                        <input
                            x-bind:type="showPass ? 'text' : 'password'"
                            name="password"
                            id="password"
                            value="{{ old('password', '') }}"
                            placeholder="{{ $credential ? __('Leave blank to keep current') : __('Enter password') }}"
                            {{ !$credential ? 'required' : '' }}
                            class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 pr-10 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2"
                        />
                        <button
                            type="button"
                            @click="showPass = !showPass"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Row 3: Login URL + Client (optional) -->
                <div class="sm:col-span-3">
                    <x-ui.label for="url">{{ __('Login URL') }}</x-ui.label>
                    <div class="mt-1.5">
                        <x-ui.input
                            type="url"
                            name="url"
                            id="url"
                            value="{{ old('url', $credential->url ?? '') }}"
                            placeholder="{{ __('https://example.com/wp-admin') }}"
                        />
                    </div>
                    @error('url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-3">
                    <x-ui.label for="client_id">{{ __('Client') }}</x-ui.label>
                    <div class="mt-1.5">
                        <x-ui.searchable-select
                            name="client_id"
                            :options="$clients"
                            :selected="old('client_id', $credential->client_id ?? '')"
                            :placeholder="__('Select client (optional)')"
                            :allowEmpty="true"
                        />
                    </div>
                    @error('client_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-4 border-t border-slate-200 px-4 py-4 sm:px-6 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('credentials.index') }}'">
                {{ __('Cancel') }}
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $credential ? __('Save') : __('Create') }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
