<form method="post" action="{{ route('profile.preferences.update') }}" class="space-y-6">
    @csrf
    @method('patch')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Timezone -->
        <x-ui.form-group name="timezone" label="{{ __('Timezone') }}">
            <x-ui.select name="timezone" id="timezone">
                @php
                    $currentTimezone = $user->getSetting('timezone', 'Europe/Bucharest');
                    $timezones = [
                        'Europe/Bucharest' => 'Europe/Bucharest (UTC+2/+3)',
                        'Europe/London' => 'Europe/London (UTC+0/+1)',
                        'Europe/Paris' => 'Europe/Paris (UTC+1/+2)',
                        'Europe/Berlin' => 'Europe/Berlin (UTC+1/+2)',
                        'America/New_York' => 'America/New York (UTC-5/-4)',
                        'America/Los_Angeles' => 'America/Los Angeles (UTC-8/-7)',
                        'Asia/Tokyo' => 'Asia/Tokyo (UTC+9)',
                        'UTC' => 'UTC',
                    ];
                @endphp
                @foreach($timezones as $value => $label)
                    <option value="{{ $value }}" {{ $currentTimezone === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-group>

        <!-- Date Format -->
        <x-ui.form-group name="date_format" label="{{ __('Date Format') }}">
            <x-ui.select name="date_format" id="date_format">
                @php
                    $currentFormat = $user->getSetting('date_format', 'd/m/Y');
                    $formats = [
                        'd/m/Y' => '31/12/2026',
                        'd.m.Y' => '31.12.2026',
                        'Y-m-d' => '2026-12-31',
                        'm/d/Y' => '12/31/2026',
                    ];
                @endphp
                @foreach($formats as $value => $example)
                    <option value="{{ $value }}" {{ $currentFormat === $value ? 'selected' : '' }}>{{ $example }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-group>

        <!-- Language -->
        <x-ui.form-group name="language" label="{{ __('Language') }}">
            <x-ui.select name="language" id="language">
                @php
                    $currentLanguage = $user->getSetting('language', 'ro');
                @endphp
                <option value="ro" {{ $currentLanguage === 'ro' ? 'selected' : '' }}>Romana</option>
                <option value="en" {{ $currentLanguage === 'en' ? 'selected' : '' }}>English</option>
            </x-ui.select>
        </x-ui.form-group>

        <!-- Items per page -->
        <x-ui.form-group name="items_per_page" label="{{ __('Items per page') }}">
            <x-ui.select name="items_per_page" id="items_per_page">
                @php
                    $currentPerPage = $user->getSetting('items_per_page', 25);
                @endphp
                @foreach([10, 25, 50, 100] as $value)
                    <option value="{{ $value }}" {{ $currentPerPage == $value ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-group>
    </div>

    <div class="flex items-center gap-4 pt-4 border-t border-slate-200">
        <x-ui.button type="submit">{{ __('Save Preferences') }}</x-ui.button>

        @if (session('status') === 'preferences-updated')
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
