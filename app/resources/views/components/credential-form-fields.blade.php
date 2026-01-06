@props([
    'credential' => null,
    'clients' => [],
    'platforms' => [],
    'sites' => [],
    'clientStatuses' => [],
    'prefix' => '',
    'compact' => false,
    'errors' => [],
    'siteName' => null,
    'clientId' => null,
])

@php
    $p = $prefix; // Shorthand for prefix
    $gridClass = $compact ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-6';
    $colSpan = $compact ? '' : 'sm:col-span-3';

    // Get initial values - prefer passed props, then old(), then credential, then request
    $initialSiteName = $siteName ?? old($p.'site_name', $credential->site_name ?? request('site_name', ''));
    $initialClientId = $clientId ?? old($p.'client_id', $credential->client_id ?? request('client_id', ''));
@endphp

<div class="grid {{ $gridClass }} gap-x-6 gap-y-5" x-data="credentialFormFields({
    prefix: '{{ $p }}',
    password: {{ Js::from(old($p.'password', $credential->password ?? '')) }},
    sites: {{ Js::from($sites) }},
    initialSiteName: {{ Js::from($initialSiteName) }}
})">
    <!-- Site Name -->
    <div class="{{ $colSpan }}">
        <x-ui.label :for="$p.'site_name'">
            {{ __('Site') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5 relative">
            <input
                type="text"
                name="{{ $p }}site_name"
                id="{{ $p }}site_name"
                x-model="siteName"
                @focus="siteDropdownOpen = true"
                @click.away="siteDropdownOpen = false"
                @keydown.escape="siteDropdownOpen = false"
                placeholder="{{ __('example.com') }}"
                required
                autocomplete="off"
                class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2"
            />
            <!-- Dropdown with existing sites -->
            <div x-show="siteDropdownOpen && filteredSites.length > 0"
                 x-transition
                 class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-md shadow-lg max-h-48 overflow-auto">
                <template x-for="site in filteredSites" :key="site">
                    <button type="button"
                            @click="siteName = site; siteDropdownOpen = false"
                            class="w-full px-3 py-2 text-left text-sm hover:bg-slate-100 focus:bg-slate-100"
                            x-text="site">
                    </button>
                </template>
            </div>
        </div>
        @error($p.'site_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <template x-if="errors['site_name']">
            <p class="mt-1 text-sm text-red-600" x-text="errors['site_name'][0]"></p>
        </template>
    </div>

    <!-- Platform -->
    <div class="{{ $colSpan }}">
        <x-ui.label :for="$p.'platform'">
            {{ __('Platform') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <x-ui.nomenclature-select
                :name="$p.'platform'"
                category="access_platforms"
                :options="$platforms"
                :selected="old($p.'platform', $credential->platform ?? '')"
                :placeholder="__('Select platform')"
                :hasColors="true"
                :required="true"
            />
        </div>
        @error($p.'platform')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <template x-if="errors['platform']">
            <p class="mt-1 text-sm text-red-600" x-text="errors['platform'][0]"></p>
        </template>
    </div>

    <!-- Username -->
    <div class="{{ $colSpan }}">
        <x-ui.label :for="$p.'username'">
            {{ __('Username') }} <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-1.5">
            <x-ui.input
                type="text"
                :name="$p.'username'"
                :id="$p.'username'"
                :value="old($p.'username', $credential->username ?? '')"
                :placeholder="__('username or email')"
                required
            />
        </div>
        @error($p.'username')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <template x-if="errors['username']">
            <p class="mt-1 text-sm text-red-600" x-text="errors['username'][0]"></p>
        </template>
    </div>

    <!-- Password -->
    <div class="{{ $colSpan }}">
        <x-ui.label :for="$p.'password'">
            {{ __('Password') }} @if(!$credential)<span class="text-red-500">*</span>@endif
        </x-ui.label>
        <div class="mt-1.5 flex gap-2">
            <div class="relative flex-1">
                <input
                    x-bind:type="showPass ? 'text' : 'password'"
                    x-model="password"
                    name="{{ $p }}password"
                    id="{{ $p }}password"
                    placeholder="{{ __('Enter password') }}"
                    {{ !$credential ? 'required' : '' }}
                    class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 pr-10 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2"
                />
                <button
                    type="button"
                    @click="showPass = !showPass"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600"
                    title="{{ __('Toggle visibility') }}"
                >
                    <svg x-show="!showPass" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPass" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            {{-- Generate Password Button --}}
            <button
                type="button"
                @click="generatePassword()"
                class="inline-flex items-center justify-center h-10 px-3 rounded-md border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors text-sm font-medium"
                title="{{ __('Generate secure password') }}"
            >
                <svg class="w-4 h-4 {{ $compact ? '' : 'mr-1.5' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                @if(!$compact)
                    {{ __('Generate') }}
                @endif
            </button>
        </div>
        {{-- Password Strength Indicator --}}
        <div x-show="password && password.length > 0" x-cloak class="mt-2">
            <div class="flex items-center gap-2">
                <div class="flex-1 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                    <div class="h-full transition-all duration-300 rounded-full"
                         :class="passwordStrengthClass"
                         :style="'width: ' + passwordStrength + '%'"></div>
                </div>
                <span class="text-xs font-medium" :class="passwordStrengthTextClass" x-text="passwordStrengthLabel"></span>
            </div>
        </div>
        @error($p.'password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <template x-if="errors['password']">
            <p class="mt-1 text-sm text-red-600" x-text="errors['password'][0]"></p>
        </template>
    </div>

    <!-- Login URL -->
    <div class="{{ $colSpan }}">
        <x-ui.label :for="$p.'url'">{{ __('Login URL') }}</x-ui.label>
        <div class="mt-1.5">
            <x-ui.input
                type="url"
                :name="$p.'url'"
                :id="$p.'url'"
                :value="old($p.'url', $credential->url ?? '')"
                :placeholder="__('https://example.com/wp-admin')"
            />
        </div>
        @error($p.'url')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <template x-if="errors['url']">
            <p class="mt-1 text-sm text-red-600" x-text="errors['url'][0]"></p>
        </template>
    </div>

    <!-- Client -->
    <div class="{{ $colSpan }}">
        <x-ui.label :for="$p.'client_id'">{{ __('Client') }}</x-ui.label>
        <div class="mt-1.5">
            <x-ui.client-select
                :name="$p.'client_id'"
                :clients="$clients"
                :selected="$initialClientId"
                :placeholder="__('Select client (optional)')"
                :allowEmpty="true"
                :clientStatuses="$clientStatuses"
            />
        </div>
        @error($p.'client_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <template x-if="errors['client_id']">
            <p class="mt-1 text-sm text-red-600" x-text="errors['client_id'][0]"></p>
        </template>
    </div>
</div>

@once
@push('scripts')
<script>
function credentialFormFields(config = {}) {
    return {
        prefix: config.prefix || '',
        password: config.password || '',
        showPass: false,
        sites: config.sites || [],
        siteName: config.initialSiteName || '',
        siteDropdownOpen: false,
        errors: {},

        get filteredSites() {
            if (!this.siteName) return this.sites;
            return this.sites.filter(s => s.toLowerCase().includes(this.siteName.toLowerCase()));
        },

        get passwordStrength() {
            const pwd = this.password;
            if (!pwd) return 0;

            let strength = 0;
            if (pwd.length >= 8) strength += 20;
            if (pwd.length >= 12) strength += 10;
            if (pwd.length >= 16) strength += 10;
            if (/[a-z]/.test(pwd)) strength += 15;
            if (/[A-Z]/.test(pwd)) strength += 15;
            if (/[0-9]/.test(pwd)) strength += 15;
            if (/[^a-zA-Z0-9]/.test(pwd)) strength += 15;

            return Math.min(100, strength);
        },

        get passwordStrengthClass() {
            const strength = this.passwordStrength;
            if (strength < 40) return 'bg-red-500';
            if (strength < 70) return 'bg-yellow-500';
            return 'bg-green-500';
        },

        get passwordStrengthTextClass() {
            const strength = this.passwordStrength;
            if (strength < 40) return 'text-red-600';
            if (strength < 70) return 'text-yellow-600';
            return 'text-green-600';
        },

        get passwordStrengthLabel() {
            const strength = this.passwordStrength;
            if (strength < 40) return '{{ __("Weak") }}';
            if (strength < 70) return '{{ __("Medium") }}';
            return '{{ __("Strong") }}';
        },

        generatePassword() {
            const length = 20;
            const uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            const lowercase = 'abcdefghjkmnpqrstuvwxyz';
            const numbers = '23456789';
            const symbols = '!@#$%^&*';

            const allChars = uppercase + lowercase + numbers + symbols;

            let password = '';
            password += uppercase[Math.floor(Math.random() * uppercase.length)];
            password += lowercase[Math.floor(Math.random() * lowercase.length)];
            password += numbers[Math.floor(Math.random() * numbers.length)];
            password += symbols[Math.floor(Math.random() * symbols.length)];

            for (let i = password.length; i < length; i++) {
                password += allChars[Math.floor(Math.random() * allChars.length)];
            }

            password = password.split('').sort(() => Math.random() - 0.5).join('');

            this.password = password;
            this.showPass = true;
        },

        // Method to collect form data (useful for AJAX submission)
        getFormData() {
            const prefix = this.prefix;
            const container = this.$el;
            const data = {};

            container.querySelectorAll('input, select, textarea').forEach(el => {
                if (el.name) {
                    const key = el.name.replace(prefix, '');
                    if (el.type === 'checkbox') {
                        data[key] = el.checked;
                    } else {
                        data[key] = el.value;
                    }
                }
            });

            return data;
        },

        setErrors(errors) {
            this.errors = errors || {};
        },

        clearErrors() {
            this.errors = {};
        }
    };
}
</script>
@endpush
@endonce
