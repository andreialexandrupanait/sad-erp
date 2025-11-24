<x-app-layout>
    <x-slot name="pageTitle">{{ __('Import from ClickUp') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <a href="{{ route('settings.clickup.index') }}" class="text-slate-600 hover:text-slate-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <h1 class="text-3xl font-bold text-slate-900">{{ __('Import from ClickUp') }}</h1>
                    </div>
                    <p class="text-slate-600">{{ __('Select your workspace and configure what data to import') }}</p>
                </div>

                <!-- Import Form -->
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
                    <form id="importForm" class="space-y-6">
                        @csrf

                        <!-- Workspace Selection -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                {{ __('Workspace ID') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="text"
                                       id="workspace_id"
                                       name="workspace_id"
                                       value="{{ $clickUpSettings['workspace_id'] ?? '' }}"
                                       class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                       placeholder="12345678"
                                       required>
                                <button type="button"
                                        onclick="loadWorkspaces()"
                                        class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors whitespace-nowrap">
                                    {{ __('Load Workspaces') }}
                                </button>
                            </div>
                            <div class="mt-2 text-xs text-slate-600 space-y-1">
                                <p class="font-medium">{{ __('How to find your Workspace ID:') }}</p>
                                <ol class="list-decimal list-inside ml-2 space-y-0.5">
                                    <li>{{ __('Open ClickUp in your browser') }}</li>
                                    <li>{{ __('Look at the URL: https://app.clickup.com/[WORKSPACE_ID]/...') }}</li>
                                    <li>{{ __('The number after app.clickup.com/ is your Workspace ID') }}</li>
                                </ol>
                                <p class="text-slate-500 italic">{{ __('Or try clicking "Load Workspaces" button above to fetch automatically') }}</p>
                            </div>

                            <!-- Workspace List (will be populated dynamically) -->
                            <div id="workspaceList" class="mt-3 hidden"></div>
                        </div>

                        <!-- Import Options -->
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Import Options') }}</h3>

                            <div class="space-y-3">
                                <!-- Tasks (always enabled) -->
                                <label class="flex items-start gap-3 p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="checkbox"
                                           name="import_tasks"
                                           checked
                                           disabled
                                           class="mt-1 w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500">
                                    <div class="flex-1">
                                        <div class="font-medium text-slate-900">{{ __('Tasks') }}</div>
                                        <div class="text-sm text-slate-600">{{ __('Import all tasks with details, assignees, watchers, tags, and checklists (always included)') }}</div>
                                    </div>
                                </label>

                                <!-- Time Entries -->
                                <label class="flex items-start gap-3 p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="checkbox"
                                           name="import_time_entries"
                                           id="import_time_entries"
                                           class="mt-1 w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500">
                                    <div class="flex-1">
                                        <div class="font-medium text-slate-900">{{ __('Time Tracking Entries') }}</div>
                                        <div class="text-sm text-slate-600">{{ __('Import individual time tracking entries with user, duration, and billable status') }}</div>
                                    </div>
                                </label>

                                <!-- Comments -->
                                <label class="flex items-start gap-3 p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="checkbox"
                                           name="import_comments"
                                           id="import_comments"
                                           class="mt-1 w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500">
                                    <div class="flex-1">
                                        <div class="font-medium text-slate-900">{{ __('Comments') }}</div>
                                        <div class="text-sm text-slate-600">{{ __('Import all task comments with threading and replies') }}</div>
                                    </div>
                                </label>

                                <!-- Attachments -->
                                <label class="flex items-start gap-3 p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="checkbox"
                                           name="import_attachments"
                                           id="import_attachments"
                                           onchange="toggleDownloadFiles()"
                                           class="mt-1 w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500">
                                    <div class="flex-1">
                                        <div class="font-medium text-slate-900">{{ __('Attachments') }}</div>
                                        <div class="text-sm text-slate-600">{{ __('Import attachment metadata (URLs and thumbnails)') }}</div>
                                    </div>
                                </label>

                                <!-- Download Files (sub-option) -->
                                <label id="downloadFilesOption" class="flex items-start gap-3 p-4 ml-8 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 hidden">
                                    <input type="checkbox"
                                           name="download_attachments"
                                           id="download_attachments"
                                           class="mt-1 w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500">
                                    <div class="flex-1">
                                        <div class="font-medium text-slate-900">{{ __('Download Files') }}</div>
                                        <div class="text-sm text-slate-600">{{ __('Download and store attachment files locally (may take longer and use storage)') }}</div>
                                    </div>
                                </label>

                                <!-- Update Existing -->
                                <label class="flex items-start gap-3 p-4 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="checkbox"
                                           name="update_existing"
                                           id="update_existing"
                                           class="mt-1 w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500">
                                    <div class="flex-1">
                                        <div class="font-medium text-slate-900">{{ __('Update Existing Tasks') }}</div>
                                        <div class="text-sm text-slate-600">{{ __('Update previously imported tasks instead of skipping them') }}</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Warning -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div class="text-sm text-yellow-800">
                                    <p class="font-medium mb-1">{{ __('Important Notes:') }}</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>{{ __('Large workspaces may take several minutes to import') }}</li>
                                        <li>{{ __('Import runs in the background - you can close this page') }}</li>
                                        <li>{{ __('Rate limits: 100 requests/minute (respects API limits automatically)') }}</li>
                                        <li>{{ __('Progress can be monitored from the ClickUp settings page') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex gap-3">
                            <button type="submit"
                                    id="submitBtn"
                                    class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                                {{ __('Start Import') }}
                            </button>
                            <a href="{{ route('settings.clickup.index') }}"
                               class="px-6 py-3 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors font-medium">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Progress Section (hidden initially) -->
                <div id="progressSection" class="hidden bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <h2 class="text-xl font-semibold text-slate-900 mb-4">{{ __('Import Progress') }}</h2>

                    <!-- Status -->
                    <div id="statusDisplay" class="mb-4"></div>

                    <!-- Statistics -->
                    <div id="statsDisplay" class="grid grid-cols-4 gap-4 mb-4"></div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-slate-200 rounded-full h-2 mb-4">
                        <div id="progressBar" class="bg-purple-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>

                    <!-- Errors -->
                    <div id="errorsDisplay" class="hidden"></div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 mt-6">
                        <a href="{{ route('settings.clickup.index') }}"
                           class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                            {{ __('Back to ClickUp Settings') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    let eventSource = null;
    let currentSyncId = null;

    function toggleDownloadFiles() {
        const attachmentsChecked = document.getElementById('import_attachments').checked;
        const downloadOption = document.getElementById('downloadFilesOption');

        if (attachmentsChecked) {
            downloadOption.classList.remove('hidden');
        } else {
            downloadOption.classList.add('hidden');
            document.getElementById('download_attachments').checked = false;
        }
    }

    async function loadWorkspaces() {
        const btn = event.target;
        btn.disabled = true;
        btn.textContent = '{{ __("Loading...") }}';

        try {
            const response = await fetch('{{ route("settings.clickup.workspaces") }}', {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (data.success && data.workspaces.length > 0) {
                const listHtml = `
                    <div class="border border-slate-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-slate-700 mb-2">{{ __("Select a workspace:") }}</p>
                        <div class="space-y-2">
                            ${data.workspaces.map(ws => `
                                <button type="button"
                                        onclick="selectWorkspace('${ws.id}', '${ws.name}')"
                                        class="w-full text-left px-3 py-2 border border-slate-200 rounded hover:bg-purple-50 hover:border-purple-300 transition-colors">
                                    <div class="font-medium text-slate-900">${ws.name}</div>
                                    <div class="text-xs text-slate-500">ID: ${ws.id}</div>
                                </button>
                            `).join('')}
                        </div>
                    </div>
                `;

                document.getElementById('workspaceList').innerHTML = listHtml;
                document.getElementById('workspaceList').classList.remove('hidden');
            } else {
                const errorMsg = data.message || '{{ __("No workspaces found") }}';
                alert(errorMsg + '\n\n{{ __("Please check that your API token is valid and starts with pk_") }}');
            }
        } catch (error) {
            console.error(error);
            alert('{{ __("Failed to load workspaces. Please check your API token and try again.") }}');
        } finally {
            btn.disabled = false;
            btn.textContent = '{{ __("Load Workspaces") }}';
        }
    }

    function selectWorkspace(id, name) {
        document.getElementById('workspace_id').value = id;
        document.getElementById('workspaceList').classList.add('hidden');
    }

    document.getElementById('importForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = '{{ __("Starting...") }}';

        const formData = new FormData(this);
        const data = {
            workspace_id: formData.get('workspace_id'),
            import_tasks: true, // Always true
            import_time_entries: formData.get('import_time_entries') === 'on',
            import_comments: formData.get('import_comments') === 'on',
            import_attachments: formData.get('import_attachments') === 'on',
            download_attachments: formData.get('download_attachments') === 'on',
            update_existing: formData.get('update_existing') === 'on',
        };

        try {
            const response = await fetch('{{ route("settings.clickup.import.start") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                // Hide form, show progress
                document.getElementById('importForm').closest('.bg-white').classList.add('hidden');
                document.getElementById('progressSection').classList.remove('hidden');

                currentSyncId = result.sync_id;

                // Start monitoring progress
                monitorProgress(result.sync_id);
            } else {
                alert(result.message || '{{ __("Failed to start import") }}');
                submitBtn.disabled = false;
                submitBtn.textContent = '{{ __("Start Import") }}';
            }
        } catch (error) {
            console.error(error);
            alert('{{ __("An error occurred") }}');
            submitBtn.disabled = false;
            submitBtn.textContent = '{{ __("Start Import") }}';
        }
    });

    function monitorProgress(syncId) {
        // Poll for progress every 3 seconds
        const pollInterval = setInterval(async () => {
            try {
                const response = await fetch(`/settings/clickup/sync/${syncId}/status`);
                const data = await response.json();

                if (data.success) {
                    updateProgressDisplay(data.sync);

                    // Stop polling if completed or failed
                    if (data.sync.status === 'completed' || data.sync.status === 'failed') {
                        clearInterval(pollInterval);
                    }
                }
            } catch (error) {
                console.error('Progress polling error:', error);
            }
        }, 3000);
    }

    function updateProgressDisplay(sync) {
        const stats = sync.stats || {};
        const status = sync.status;

        // Update status
        const statusColors = {
            'pending': 'text-slate-600',
            'running': 'text-blue-600',
            'completed': 'text-green-600',
            'failed': 'text-red-600'
        };

        const statusIcons = {
            'pending': '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>',
            'running': '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>',
            'completed': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
            'failed': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
        };

        document.getElementById('statusDisplay').innerHTML = `
            <div class="flex items-center gap-2 ${statusColors[status]} font-medium text-lg">
                ${statusIcons[status]}
                <span>${status.charAt(0).toUpperCase() + status.slice(1)}</span>
            </div>
        `;

        // Update statistics
        document.getElementById('statsDisplay').innerHTML = `
            <div class="text-center p-4 bg-slate-50 rounded-lg">
                <div class="text-2xl font-bold text-slate-900">${stats.spaces || 0}</div>
                <div class="text-sm text-slate-600">{{ __("Spaces") }}</div>
            </div>
            <div class="text-center p-4 bg-slate-50 rounded-lg">
                <div class="text-2xl font-bold text-slate-900">${stats.folders || 0}</div>
                <div class="text-sm text-slate-600">{{ __("Folders") }}</div>
            </div>
            <div class="text-center p-4 bg-slate-50 rounded-lg">
                <div class="text-2xl font-bold text-slate-900">${stats.lists || 0}</div>
                <div class="text-sm text-slate-600">{{ __("Lists") }}</div>
            </div>
            <div class="text-center p-4 bg-slate-50 rounded-lg">
                <div class="text-2xl font-bold text-slate-900">${stats.tasks || 0}</div>
                <div class="text-sm text-slate-600">{{ __("Tasks") }}</div>
            </div>
        `;

        // Update progress bar (rough estimate based on status)
        const progressPercent = status === 'completed' ? 100 : (status === 'running' ? 50 : 10);
        document.getElementById('progressBar').style.width = progressPercent + '%';

        // Show errors if any
        if (sync.errors && sync.errors.length > 0) {
            const errorsHtml = `
                <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <h3 class="font-medium text-red-900 mb-2">{{ __("Errors encountered:") }}</h3>
                    <ul class="list-disc list-inside text-sm text-red-800 space-y-1">
                        ${sync.errors.slice(0, 5).map(error => `<li>${typeof error === 'string' ? error : error.error || JSON.stringify(error)}</li>`).join('')}
                    </ul>
                    ${sync.errors.length > 5 ? `<p class="text-sm text-red-700 mt-2">... and ${sync.errors.length - 5} more errors</p>` : ''}
                </div>
            `;
            document.getElementById('errorsDisplay').innerHTML = errorsHtml;
            document.getElementById('errorsDisplay').classList.remove('hidden');
        }
    }
    </script>
</x-app-layout>
