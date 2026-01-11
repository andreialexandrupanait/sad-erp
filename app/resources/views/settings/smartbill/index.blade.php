<x-app-layout>
    <x-slot name="pageTitle">{{ __('Smartbill Integration') }}</x-slot>

    <div class="flex flex-col lg:flex-row min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

    <div class="flex-1 overflow-y-auto">
        <div class="p-4 md:p-6">


            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900">{{ __('Smartbill Integration') }}</h1>
                <p class="mt-2 text-slate-600">{{ __('Configure your Smartbill API credentials and import invoices automatically') }}</p>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- API Credentials Card -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">{{ __('API Credentials') }}</h2>
                <p class="text-sm text-slate-600 mb-6">{{ __('Enter your Smartbill API credentials. You can find these in your Smartbill account settings.') }}</p>

                <form method="POST" action="{{ route('settings.smartbill.credentials.update') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Username') }}</label>
                        <input type="text" name="username" value="{{ old('username', $smartbillSettings['username'] ?? '') }}"
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="your@email.com"
                               required>
                        @error('username')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('API Token') }}</label>
                        <input type="password" name="token" value="{{ old('token', $smartbillSettings['token'] ?? '') }}"
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="{{ __('Your API token') }}"
                               required>
                        @error('token')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('CIF (Company Tax ID)') }}</label>
                        <input type="text" name="cif" value="{{ old('cif', $smartbillSettings['cif'] ?? '') }}"
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="RO12345678"
                               required>
                        @error('cif')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            {{ __('Save Credentials') }}
                        </button>

                        @if($hasCredentials)
                            <button type="button" onclick="testConnection()" class="px-6 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors font-medium">
                                {{ __('Test Connection') }}
                            </button>
                        @endif
                    </div>
                </form>

                <div id="connectionStatus" class="mt-4"></div>
            </div>

            <!-- Import Card -->
            @if($hasCredentials)
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold text-slate-900 mb-2">{{ __('Import Invoices') }}</h2>
                            <p class="text-slate-600 mb-4">{{ __('Upload a CSV or Excel file exported from Smartbill to import invoices with automatic PDF download.') }}</p>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <h4 class="font-medium text-blue-900 mb-2">{{ __('How to export from Smartbill:') }}</h4>
                                <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                                    <li>{{ __('Log in to your Smartbill account') }}</li>
                                    <li>{{ __('Go to') }} <strong>{{ __('Rapoarte') }}</strong> → <strong>{{ __('Export') }}</strong></li>
                                    <li>{{ __('Select your date range') }}</li>
                                    <li>{{ __('Export as CSV or Excel') }}</li>
                                    <li>{{ __('Upload the file here') }}</li>
                                </ol>
                            </div>

                            <a href="{{ route('settings.smartbill.import') }}" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                {{ __('Start Import') }}
                            </a>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-yellow-900 mb-1">{{ __('Configure your credentials first') }}</h3>
                            <p class="text-sm text-yellow-800">{{ __('Please enter your Smartbill API credentials above before you can start importing invoices.') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function testConnection() {
    const statusDiv = document.getElementById('connectionStatus');
    statusDiv.innerHTML = '<div class="flex items-center gap-2 text-blue-600 font-medium"><svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Testing connection...</div>';

    fetch('{{ route('settings.smartbill.test-connection') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusDiv.innerHTML = '<div class="flex items-center gap-2 text-green-600 font-medium"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Connection successful! Your credentials are working.</div>';
        } else {
            // Use DOM methods to prevent XSS from server messages
            statusDiv.textContent = '';
            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-start gap-2 text-red-600 font-medium';
            const icon = document.createElement('span');
            icon.innerHTML = '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            const text = document.createElement('span');
            text.textContent = data.message;  // Safe: textContent escapes HTML
            wrapper.appendChild(icon);
            wrapper.appendChild(text);
            statusDiv.appendChild(wrapper);
        }
    })
    .catch(error => {
        statusDiv.innerHTML = '<div class="flex items-center gap-2 text-red-600 font-medium"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Connection failed. Please check your credentials.</div>';
    });
}
</script>
</x-app-layout>
