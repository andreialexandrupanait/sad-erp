<x-app-layout>
    <x-slot name="pageTitle">{{ __('Settings') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <!-- Breadcrumb -->
                <div class="mb-6">
                    <nav class="flex items-center text-sm">
                        <a href="{{ route('settings.integrations') }}" class="text-slate-400 hover:text-slate-600">
                            {{ __('Integrations') }}
                        </a>
                        <svg class="w-4 h-4 mx-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="text-slate-900 font-medium">ClickUp</span>
                    </nav>
                </div>

                <!-- Header -->
                <div class="flex items-center gap-4 mb-8">
                    <div class="flex-shrink-0 w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">ClickUp</h1>
                        <p class="text-slate-500">{{ __('Import tasks and time entries from ClickUp') }}</p>
                    </div>
                    @if($clickupSettings['clickup_enabled'] && $clickupSettings['clickup_api_token'])
                        <span class="ml-auto inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            {{ __('Connected') }}
                        </span>
                    @endif
                </div>

                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 rounded-lg border border-red-200">
                        <p class="text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                @endif

                <!-- Configuration Card -->
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <form method="POST" action="{{ route('settings.clickup.update') }}">
                        @csrf

                        <!-- Enable Toggle -->
                        <div class="flex items-center justify-between pb-6 border-b border-slate-200">
                            <div>
                                <h3 class="text-lg font-medium text-slate-900">{{ __('Enable ClickUp Integration') }}</h3>
                                <p class="text-sm text-slate-500 mt-1">{{ __('Sync tasks and time entries from your ClickUp workspace') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="clickup_enabled" value="1" {{ $clickupSettings['clickup_enabled'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <!-- API Token -->
                        <div class="py-6 border-b border-slate-200">
                            <label for="clickup_api_token" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ __('API Token') }}
                            </label>
                            <input type="password" 
                                   name="clickup_api_token" 
                                   id="clickup_api_token" 
                                   value="{{ $clickupSettings['clickup_api_token'] }}"
                                   placeholder="pk_12345678_ABCDEFGHIJKLMNOPQRSTUVWXYZ"
                                   class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-colors">
                            <p class="mt-2 text-sm text-slate-500">
                                {{ __('Get your API token from') }} 
                                <a href="https://app.clickup.com/settings/apps" target="_blank" class="text-purple-600 hover:text-purple-700 font-medium">
                                    ClickUp Settings → Apps
                                </a>
                            </p>
                            @error('clickup_api_token')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Team ID -->
                        <div class="py-6 border-b border-slate-200">
                            <label for="clickup_team_id" class="block text-sm font-medium text-slate-700 mb-2">
                                {{ __('Team ID') }} <span class="text-slate-400">({{ __('Optional') }})</span>
                            </label>
                            <input type="text" 
                                   name="clickup_team_id" 
                                   id="clickup_team_id" 
                                   value="{{ $clickupSettings['clickup_team_id'] }}"
                                   placeholder="12345678"
                                   class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-colors">
                            <p class="mt-2 text-sm text-slate-500">
                                {{ __('Leave empty to sync from all teams, or enter a specific team ID') }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="pt-6 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <button type="submit" class="px-4 py-2.5 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-colors">
                                    {{ __('Save Settings') }}
                                </button>
                                <button type="button" 
                                        onclick="testConnection()"
                                        class="px-4 py-2.5 bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-colors">
                                    {{ __('Test Connection') }}
                                </button>
                            </div>

                            @if($clickupSettings['clickup_enabled'] && $clickupSettings['clickup_api_token'])
                                <form method="POST" action="{{ route('settings.clickup.disconnect') }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            onclick="return confirm('{{ __('Are you sure you want to disconnect ClickUp?') }}')"
                                            class="px-4 py-2.5 text-red-600 font-medium hover:text-red-700 transition-colors">
                                        {{ __('Disconnect') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- Coming Soon Features -->
                <div class="mt-8">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Features') }}</h2>
                    <div class="bg-slate-50 rounded-xl border border-slate-200 border-dashed p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center gap-3 p-3 bg-white rounded-lg">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-slate-700">{{ __('Import Tasks') }}</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-white rounded-lg">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-slate-700">{{ __('Time Tracking Sync') }}</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-white rounded-lg">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-slate-700">{{ __('Team Workload') }}</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-white rounded-lg">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-slate-700">{{ __('Project Reports') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Link -->
                <div class="mt-8">
                    <a href="{{ route('settings.integrations') }}" class="text-sm text-slate-600 hover:text-slate-900">
                        &larr; {{ __('Back to Integrations') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testConnection() {
            const button = event.target;
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = '{{ __("Testing...") }}';

            fetch('{{ route("settings.clickup.test") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Connection failed: ' + error.message);
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = originalText;
            });
        }
    </script>
</x-app-layout>
