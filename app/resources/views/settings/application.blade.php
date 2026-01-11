<x-app-layout>
    <x-slot name="pageTitle">{{ __('Settings') }}</x-slot>

    <div class="flex flex-col lg:flex-row min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-4 md:p-6">

                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-slate-900">{{ __('Application Settings') }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ __('Configure your application preferences and branding') }}</p>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                        <div class="font-semibold mb-2">{{ __('Please fix the following errors') }}:</div>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('settings.application.update') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Basic Information -->
                    <div class="bg-white rounded-lg border border-slate-200 mb-6 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Basic Information') }}</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- App Name -->
                                <div>
                                    <label for="app_name" class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Application Name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="app_name"
                                           name="app_name"
                                           value="{{ old('app_name', $appSettings['app_name']) }}"
                                           required
                                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('app_name') border-red-500 @enderror">
                                    @error('app_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Language -->
                                <div>
                                    <label for="language" class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Language') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select id="language"
                                            name="language"
                                            required
                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="en" {{ old('language', $appSettings['language']) === 'en' ? 'selected' : '' }}>English</option>
                                        <option value="ro" {{ old('language', $appSettings['language']) === 'ro' ? 'selected' : '' }}>Română</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Branding -->
                    <div class="bg-white rounded-lg border border-slate-200 mb-6 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Branding') }}</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Logo Upload -->
                            <div>
                                <label for="app_logo" class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('Application Logo') }}
                                </label>
                                @if(isset($appSettings['app_logo']) && $appSettings['app_logo'])
                                    <div class="mb-3 flex items-center gap-4">
                                        <img src="{{ asset('storage/' . $appSettings['app_logo']) }}"
                                             alt="Current logo"
                                             class="h-16 object-contain border border-slate-200 rounded-lg p-2 bg-white">
                                        <div class="text-sm text-slate-600">
                                            {{ __('Current logo') }}
                                        </div>
                                    </div>
                                @endif
                                <input type="file"
                                       id="app_logo"
                                       name="app_logo"
                                       accept="image/*"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('app_logo') border-red-500 @enderror">
                                <p class="mt-1 text-xs text-slate-500">{{ __('Recommended size: 200x50px. Accepted formats: PNG, JPG, SVG') }}</p>
                                @error('app_logo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Favicon Upload -->
                            <div>
                                <label for="app_favicon" class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('Favicon') }}
                                </label>
                                @if(isset($appSettings['app_favicon']) && $appSettings['app_favicon'])
                                    <div class="mb-3 flex items-center gap-4">
                                        <img src="{{ asset('storage/' . $appSettings['app_favicon']) }}"
                                             alt="Current favicon"
                                             class="h-8 w-8 object-contain border border-slate-200 rounded p-1 bg-white">
                                        <div class="text-sm text-slate-600">
                                            {{ __('Current favicon') }}
                                        </div>
                                    </div>
                                @endif
                                <input type="file"
                                       id="app_favicon"
                                       name="app_favicon"
                                       accept="image/*"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('app_favicon') border-red-500 @enderror">
                                <p class="mt-1 text-xs text-slate-500">{{ __('Recommended size: 32x32px or 64x64px. Accepted formats: PNG, ICO') }}</p>
                                @error('app_favicon')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Primary Color -->
                            <div>
                                <label for="primary_color" class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('Primary Color') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-4">
                                    <input type="color"
                                           id="primary_color"
                                           name="primary_color"
                                           value="{{ old('primary_color', $appSettings['primary_color']) }}"
                                           required
                                           class="h-10 w-20 border border-slate-300 rounded-lg cursor-pointer @error('primary_color') border-red-500 @enderror">
                                    <input type="text"
                                           id="primary_color_hex"
                                           value="{{ old('primary_color', $appSettings['primary_color']) }}"
                                           readonly
                                           class="px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 text-slate-600 w-32">
                                    <button type="button"
                                            onclick="document.getElementById('primary_color').value='#3b82f6'; document.getElementById('primary_color_hex').value='#3b82f6';"
                                            class="px-3 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">
                                        {{ __('Reset to default') }}
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">{{ __('This color will be used throughout the application for buttons, links, and accents') }}</p>
                                @error('primary_color')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Appearance -->
                    <div class="bg-white rounded-lg border border-slate-200 mb-6 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Appearance') }}</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Theme Mode -->
                                <div>
                                    <label for="theme_mode" class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Theme Mode') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select id="theme_mode"
                                            name="theme_mode"
                                            required
                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="light" {{ old('theme_mode', $appSettings['theme_mode']) === 'light' ? 'selected' : '' }}>
                                            {{ __('Light') }}
                                        </option>
                                        <option value="dark" {{ old('theme_mode', $appSettings['theme_mode']) === 'dark' ? 'selected' : '' }}>
                                            {{ __('Dark') }}
                                        </option>
                                        <option value="auto" {{ old('theme_mode', $appSettings['theme_mode']) === 'auto' ? 'selected' : '' }}>
                                            {{ __('Auto (System)') }}
                                        </option>
                                    </select>
                                    <p class="mt-1 text-xs text-slate-500">{{ __('Currently only Light theme is implemented') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Regional Settings -->
                    <div class="bg-white rounded-lg border border-slate-200 mb-6 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Regional Settings') }}</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Timezone -->
                                <div>
                                    <label for="timezone" class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Timezone') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select id="timezone"
                                            name="timezone"
                                            required
                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="Europe/Bucharest" {{ old('timezone', $appSettings['timezone']) === 'Europe/Bucharest' ? 'selected' : '' }}>Europe/Bucharest (Romania)</option>
                                        <option value="Europe/London" {{ old('timezone', $appSettings['timezone']) === 'Europe/London' ? 'selected' : '' }}>Europe/London (UK)</option>
                                        <option value="Europe/Paris" {{ old('timezone', $appSettings['timezone']) === 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris (France)</option>
                                        <option value="Europe/Berlin" {{ old('timezone', $appSettings['timezone']) === 'Europe/Berlin' ? 'selected' : '' }}>Europe/Berlin (Germany)</option>
                                        <option value="America/New_York" {{ old('timezone', $appSettings['timezone']) === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                                        <option value="America/Los_Angeles" {{ old('timezone', $appSettings['timezone']) === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles (PST)</option>
                                        <option value="Asia/Tokyo" {{ old('timezone', $appSettings['timezone']) === 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (Japan)</option>
                                        <option value="UTC" {{ old('timezone', $appSettings['timezone']) === 'UTC' ? 'selected' : '' }}>UTC</option>
                                    </select>
                                </div>

                                <!-- Date Format -->
                                <div>
                                    <label for="date_format" class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Date Format') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select id="date_format"
                                            name="date_format"
                                            required
                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        <option value="d/m/Y" {{ old('date_format', $appSettings['date_format']) === 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY ({{ now()->format('d/m/Y') }})</option>
                                        <option value="m/d/Y" {{ old('date_format', $appSettings['date_format']) === 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY ({{ now()->format('m/d/Y') }})</option>
                                        <option value="Y-m-d" {{ old('date_format', $appSettings['date_format']) === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD ({{ now()->format('Y-m-d') }})</option>
                                        <option value="d.m.Y" {{ old('date_format', $appSettings['date_format']) === 'd.m.Y' ? 'selected' : '' }}>DD.MM.YYYY ({{ now()->format('d.m.Y') }})</option>
                                        <option value="d-m-Y" {{ old('date_format', $appSettings['date_format']) === 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY ({{ now()->format('d-m-Y') }})</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <a href="{{ route('dashboard') }}"
                           class="w-full sm:w-auto px-6 py-2.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-center">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit"
                                class="w-full sm:w-auto justify-center px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('Save Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Sync color picker with hex input
        const colorPicker = document.getElementById('primary_color');
        const colorHex = document.getElementById('primary_color_hex');

        if (colorPicker && colorHex) {
            colorPicker.addEventListener('input', function() {
                colorHex.value = this.value;
            });
        }
    </script>
    @endpush
</x-app-layout>
