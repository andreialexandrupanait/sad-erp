<x-app-layout>
    <x-slot name="pageTitle">{{ __('ClickUp Integration') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

    <div class="flex-1 p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900">{{ __('ClickUp Integration') }}</h1>
                <p class="mt-2 text-slate-600">{{ __('Configure your ClickUp API credentials and import tasks, projects, and time tracking data') }}</p>
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
                <p class="text-sm text-slate-600 mb-6">{{ __('Enter your ClickUp Personal API Token. You can generate one in your ClickUp account settings.') }}</p>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h4 class="font-medium text-blue-900 mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('How to get your Personal API Token:') }}
                    </h4>
                    <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                        <li>{{ __('Log in to your ClickUp account') }}</li>
                        <li>{{ __('Click your avatar in the bottom left → Settings') }}</li>
                        <li>{{ __('Navigate to') }} <strong>{{ __('Apps') }}</strong> {{ __('in the left sidebar') }}</li>
                        <li>{{ __('Click') }} <strong>{{ __('Generate') }}</strong> {{ __('under API Token section') }}</li>
                        <li>{{ __('Copy the token (starts with') }} <code class="bg-blue-100 px-1 rounded">pk_</code>{{ __(') and paste it below') }}</li>
                    </ol>
                    <p class="mt-2 text-xs text-blue-700">
                        <strong>{{ __('Note:') }}</strong> {{ __('Make sure you have') }} <strong>{{ __('admin access') }}</strong> {{ __('to the workspace you want to import.') }}
                    </p>
                </div>

                <form method="POST" action="{{ route('settings.clickup.credentials.update') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Personal API Token') }}</label>
                        <input type="password" name="token" value="{{ old('token', $clickUpSettings['token'] ?? '') }}"
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="pk_12345678_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
                               required>
                        <p class="mt-1 text-xs text-slate-500">{{ __('Your ClickUp Personal API Token (should start with pk_)') }}</p>
                        @error('token')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Workspace ID (Optional)') }}</label>
                        <input type="text" name="workspace_id" value="{{ old('workspace_id', $clickUpSettings['workspace_id'] ?? '') }}"
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="12345678">
                        <p class="mt-1 text-xs text-slate-500">Default workspace for imports (can be changed during import)</p>
                        @error('workspace_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
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
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold text-slate-900 mb-2">{{ __('Import from ClickUp') }}</h2>
                            <p class="text-slate-600 mb-4">{{ __('Import your entire workspace including spaces, folders, lists, tasks, time entries, comments, and attachments.') }}</p>

                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                                <h4 class="font-medium text-purple-900 mb-2">{{ __('What gets imported:') }}</h4>
                                <ul class="list-disc list-inside text-sm text-purple-800 space-y-1">
                                    <li>{{ __('Spaces, Folders, and Lists (hierarchy)') }}</li>
                                    <li>{{ __('Tasks with all details (status, priority, dates, etc.)') }}</li>
                                    <li>{{ __('Assignees, watchers, and tags') }}</li>
                                    <li>{{ __('Checklists with items') }}</li>
                                    <li>{{ __('Time tracking entries (optional)') }}</li>
                                    <li>{{ __('Comments with threading (optional)') }}</li>
                                    <li>{{ __('Attachments and files (optional)') }}</li>
                                </ul>
                            </div>

                            <a href="{{ route('settings.clickup.import') }}" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                {{ __('Start Import') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Syncs -->
                @if($recentSyncs->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                        <h2 class="text-xl font-semibold text-slate-900 mb-4">{{ __('Recent Imports') }}</h2>

                        <div class="space-y-3">
                            @foreach($recentSyncs as $sync)
                                <div class="flex items-center justify-between p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            @if($sync->status === 'completed')
                                                <span class="flex-shrink-0 w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </span>
                                            @elseif($sync->status === 'failed')
                                                <span class="flex-shrink-0 w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </span>
                                            @else
                                                <span class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </span>
                                            @endif

                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium text-slate-900">{{ ucfirst($sync->sync_type) }} Import</span>
                                                    <span class="text-xs px-2 py-1 rounded-full
                                                        @if($sync->status === 'completed') bg-green-100 text-green-700
                                                        @elseif($sync->status === 'failed') bg-red-100 text-red-700
                                                        @elseif($sync->status === 'running') bg-blue-100 text-blue-700
                                                        @else bg-slate-100 text-slate-700
                                                        @endif">
                                                        {{ ucfirst($sync->status) }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-slate-600 mt-1">
                                                    Started {{ $sync->created_at->diffForHumans() }}
                                                    @if($sync->completed_at)
                                                        • Completed in {{ $sync->started_at->diffInSeconds($sync->completed_at) }}s
                                                    @endif
                                                </p>
                                                @if($sync->stats)
                                                    <div class="flex gap-4 mt-2 text-xs text-slate-500">
                                                        <span>Spaces: {{ $sync->stats['spaces'] ?? 0 }}</span>
                                                        <span>Folders: {{ $sync->stats['folders'] ?? 0 }}</span>
                                                        <span>Lists: {{ $sync->stats['lists'] ?? 0 }}</span>
                                                        <span>Tasks: {{ $sync->stats['tasks'] ?? 0 }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-yellow-900 mb-1">{{ __('Configure your credentials first') }}</h3>
                            <p class="text-sm text-yellow-800">{{ __('Please enter your ClickUp Personal API Token above before you can start importing data.') }}</p>
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

    fetch('{{ route('settings.clickup.test-connection') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusDiv.innerHTML = `<div class="flex items-center gap-2 text-green-600 font-medium"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Connection successful! Logged in as: ${data.user.username} (${data.user.email})</div>`;
        } else {
            statusDiv.innerHTML = `<div class="flex items-start gap-2 text-red-600 font-medium"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>${data.message}</span></div>`;
        }
    })
    .catch(error => {
        statusDiv.innerHTML = '<div class="flex items-center gap-2 text-red-600 font-medium"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Connection failed. Please check your token.</div>';
    });
}
</script>
</x-app-layout>
