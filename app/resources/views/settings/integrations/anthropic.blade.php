<x-app-layout>
    <x-slot name="pageTitle">{{ __('Claude AI Integration') }}</x-slot>

    <div class="flex flex-col lg:flex-row min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-4 md:p-6">

                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('settings.integrations') }}" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">{{ __('Claude AI Integration') }}</h1>
                            <p class="text-slate-500">{{ __('Configure Anthropic Claude API for AI-powered design generation') }}</p>
                        </div>
                    </div>
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

                <!-- Test Alert (AJAX) -->
                <div id="testAlert" class="hidden mb-6 p-4 rounded-lg"></div>

                <form method="POST" action="{{ route('settings.anthropic.update') }}">
                    @csrf

                    <!-- Connection Settings -->
                    <div class="bg-white rounded-xl border border-slate-200 mb-6">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 rounded-t-xl">
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('API Configuration') }}</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Enable Toggle -->
                            <div class="pb-6 border-b border-slate-200">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           name="anthropic_enabled"
                                           value="1"
                                           id="anthropicToggle"
                                           {{ old('anthropic_enabled', $settings['anthropic_enabled']) ? 'checked' : '' }}
                                           class="h-5 w-5 text-amber-600 focus:ring-amber-500 border-slate-300 rounded">
                                    <span class="ml-3">
                                        <span class="text-sm font-medium text-slate-900">{{ __('Enable Claude AI') }}</span>
                                        <span class="block text-xs text-slate-500 mt-1">{{ __('Use AI for generating landing page designs') }}</span>
                                    </span>
                                </label>
                            </div>

                            <!-- API Key -->
                            <div>
                                <label for="anthropic_api_key" class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('API Key') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password"
                                           id="anthropic_api_key"
                                           name="anthropic_api_key"
                                           value="{{ old('anthropic_api_key', $settings['anthropic_api_key']) }}"
                                           placeholder="sk-ant-api03-..."
                                           class="w-full px-3 py-2 pr-10 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent font-mono text-sm @error('anthropic_api_key') border-red-500 @enderror">
                                    <button type="button"
                                            onclick="togglePasswordVisibility('anthropic_api_key', this)"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                                @if($settings['anthropic_api_key'])
                                    <p class="mt-2 text-xs text-green-600">
                                        <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ __('API key is configured') }}
                                    </p>
                                @else
                                    <p class="mt-2 text-xs text-slate-500">
                                        {{ __('To get an API key:') }}
                                    </p>
                                    <ol class="mt-1 text-xs text-slate-500 list-decimal list-inside space-y-1">
                                        <li>{{ __('Go to') }} <a href="https://console.anthropic.com/" target="_blank" class="text-amber-600 hover:underline">console.anthropic.com</a></li>
                                        <li>{{ __('Sign up or log in to your account') }}</li>
                                        <li>{{ __('Navigate to API Keys section') }}</li>
                                        <li>{{ __('Create a new API key and copy it') }}</li>
                                    </ol>
                                @endif
                            </div>

                            <!-- Model Selection -->
                            <div>
                                <label for="anthropic_model" class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('Model') }}
                                </label>
                                <select id="anthropic_model"
                                        name="anthropic_model"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                    @foreach($models as $value => $label)
                                        <option value="{{ $value }}" {{ old('anthropic_model', $settings['anthropic_model']) === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-xs text-slate-500">
                                    {{ __('Sonnet models provide the best balance of quality and speed for design generation.') }}
                                </p>
                            </div>

                            <!-- Max Tokens -->
                            <div>
                                <label for="anthropic_max_tokens" class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('Max Tokens') }}
                                </label>
                                <input type="number"
                                       id="anthropic_max_tokens"
                                       name="anthropic_max_tokens"
                                       value="{{ old('anthropic_max_tokens', $settings['anthropic_max_tokens']) }}"
                                       min="1024"
                                       max="16384"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                <p class="mt-2 text-xs text-slate-500">
                                    {{ __('Maximum number of tokens for AI responses. Higher values allow for more complex designs but cost more. Recommended: 8192.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- API Limits & Pricing Info -->
                    <div class="bg-white rounded-xl border border-slate-200 mb-6">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 rounded-t-xl">
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('API Limits & Pricing') }}</h3>
                        </div>
                        <div class="p-4 md:p-6">
                            <!-- Current Plan Info -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div class="bg-slate-50 rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        <span class="text-sm font-medium text-slate-700">{{ __('Rate Limit') }}</span>
                                    </div>
                                    <p class="text-2xl font-bold text-slate-900">4,000</p>
                                    <p class="text-xs text-slate-500">{{ __('output tokens/minute (Free tier)') }}</p>
                                </div>
                                <div class="bg-slate-50 rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-sm font-medium text-slate-700">{{ __('Generation Time') }}</span>
                                    </div>
                                    <p class="text-2xl font-bold text-slate-900">30-60s</p>
                                    <p class="text-xs text-slate-500">{{ __('per landing page') }}</p>
                                </div>
                                <div class="bg-slate-50 rounded-lg p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-sm font-medium text-slate-700">{{ __('Estimated Cost') }}</span>
                                    </div>
                                    <p class="text-2xl font-bold text-slate-900">~$0.05</p>
                                    <p class="text-xs text-slate-500">{{ __('per generation (Sonnet)') }}</p>
                                </div>
                            </div>

                            <!-- Model Pricing Table -->
                            <div class="border border-slate-200 rounded-lg overflow-hidden mb-6">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Model') }}</th>
                                            <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Input Price') }}</th>
                                            <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Output Price') }}</th>
                                            <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('Quality') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        <tr class="bg-amber-50">
                                            <td class="px-4 py-3 font-medium">Claude Sonnet 4 <span class="text-xs text-amber-600">(Recommended)</span></td>
                                            <td class="px-4 py-3 text-slate-600">$3 / 1M tokens</td>
                                            <td class="px-4 py-3 text-slate-600">$15 / 1M tokens</td>
                                            <td class="px-4 py-3"><span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium">Best</span></td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-medium">Claude 3.5 Sonnet</td>
                                            <td class="px-4 py-3 text-slate-600">$3 / 1M tokens</td>
                                            <td class="px-4 py-3 text-slate-600">$15 / 1M tokens</td>
                                            <td class="px-4 py-3"><span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">Great</span></td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-medium">Claude 3 Haiku</td>
                                            <td class="px-4 py-3 text-slate-600">$0.25 / 1M tokens</td>
                                            <td class="px-4 py-3 text-slate-600">$1.25 / 1M tokens</td>
                                            <td class="px-4 py-3"><span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs font-medium">Basic</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Rate Limit Warning -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-semibold text-blue-900 mb-1">{{ __('Rate Limit Information') }}</h4>
                                        <p class="text-sm text-blue-700 mb-2">
                                            {{ __('Free tier accounts are limited to 4,000 output tokens per minute. A typical landing page generation uses ~8,000-10,000 tokens, meaning you may need to wait 2-3 minutes between generations.') }}
                                        </p>
                                        <p class="text-sm text-blue-700">
                                            {{ __('To increase limits, you can:') }}
                                        </p>
                                        <ul class="text-sm text-blue-700 list-disc list-inside mt-1 space-y-1">
                                            <li>{{ __('Add a payment method to your Anthropic account') }}</li>
                                            <li>{{ __('Request a rate limit increase at') }} <a href="https://www.anthropic.com/contact-sales" target="_blank" class="underline hover:no-underline">anthropic.com/contact-sales</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Usage Info -->
                    <div class="bg-amber-50 rounded-xl border border-amber-200 mb-6 p-6">
                        <h3 class="text-lg font-semibold text-amber-900 mb-3">{{ __('How It Works') }}</h3>
                        <ul class="space-y-2 text-sm text-amber-800">
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                {{ __('Go to Design Concepts module and create a new concept') }}
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ __('Paste your landing page copy text and set design preferences') }}
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                                {{ __('Claude AI will generate a complete, modern landing page design') }}
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                {{ __('Preview and export the design as HTML') }}
                            </li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors font-medium">
                                {{ __('Save Settings') }}
                            </button>
                            @if($settings['anthropic_api_key'])
                                <button type="button"
                                        onclick="testConnection()"
                                        class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors font-medium">
                                    {{ __('Test Connection') }}
                                </button>
                            @endif
                        </div>

                        @if($settings['anthropic_api_key'])
                            <form method="POST" action="{{ route('settings.anthropic.disconnect') }}" class="inline">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('{{ __('Are you sure you want to disconnect Claude AI?') }}')"
                                        class="px-4 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors font-medium">
                                    {{ __('Disconnect') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const eyeOpen = button.querySelector('.eye-open');
            const eyeClosed = button.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }

        function testConnection() {
            const alertDiv = document.getElementById('testAlert');
            alertDiv.className = 'mb-6 p-4 rounded-lg bg-blue-50 border border-blue-200 text-blue-800';
            alertDiv.innerHTML = '<div class="flex items-center"><svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>{{ __("Testing connection...") }}</div>';
            alertDiv.classList.remove('hidden');

            fetch('{{ route("settings.anthropic.test") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alertDiv.className = 'mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800';
                    alertDiv.innerHTML = '<div class="flex items-center"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' + data.message + '</div>';
                } else {
                    alertDiv.className = 'mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800';
                    alertDiv.innerHTML = '<div class="flex items-center"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>' + data.message + '</div>';
                }
            })
            .catch(error => {
                alertDiv.className = 'mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800';
                alertDiv.innerHTML = '<div class="flex items-center"><svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>{{ __("Connection failed") }}: ' + error.message + '</div>';
            });
        }
    </script>
    @endpush
</x-app-layout>
