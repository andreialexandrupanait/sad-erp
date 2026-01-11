<x-app-layout>
    <x-slot name="pageTitle">{{ __('Settings') }}</x-slot>

    <div class="flex flex-col lg:flex-row min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-4 md:p-6">


                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-slate-900">{{ __('Database Backup') }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ __('Create, download, restore and manage backups') }}</p>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('import_results'))
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl">
                        <strong>{{ __('Import Results:') }}</strong>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach(session('import_results')['imported'] as $table => $count)
                                <li>{{ $table }}: {{ $count }} {{ __('records') }}</li>
                            @endforeach
                        </ul>
                        @if(!empty(session('import_results')['errors']))
                            <div class="mt-2 text-red-600">
                                @foreach(session('import_results')['errors'] as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Export Section -->
                    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Export Backup') }}</h3>
                                <p class="text-sm text-slate-500">{{ __('Create a backup of your database.') }}</p>
                            </div>
                        </div>
                        <div class="p-4 md:p-6">


                            <form id="export-form" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Tables to Export') }}
                                    </label>
                                    <div class="space-y-2 max-h-64 overflow-y-auto border border-slate-200 rounded-xl p-3">
                                        @foreach($tables as $table)
                                            <label class="flex items-center cursor-pointer hover:bg-slate-50 px-2 py-1 rounded-lg">
                                                <input type="checkbox" name="tables[]" value="{{ is_array($table) ? $table['name'] : $table }}" checked
                                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                <span class="ml-2 text-sm text-slate-700">
                                                    {{ is_array($table) ? $table['name'] : $table }}
                                                    @if(is_array($table) && isset($table['count']))
                                                        <span class="text-slate-400">({{ $table['count'] }} {{ __('records') }})</span>
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Export Progress -->
                                <div id="export-progress" class="hidden">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-blue-700">{{ __('Creating backup...') }}</span>
                                        <span id="export-percentage" class="text-sm font-medium text-blue-700">0%</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                                        <div id="export-bar" class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
                                    </div>
                                    <p id="export-status" class="text-xs text-slate-500 mt-2">{{ __('Preparing...') }}</p>
                                </div>

                                <button type="button" id="export-btn" onclick="exportBackup()"
                                        class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    {{ __('Create Backup') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Import Section -->
                    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Import Backup') }}</h3>
                                <p class="text-sm text-slate-500">{{ __('Restore data from a backup file.') }}</p>
                            </div>
                        </div>
                        <div class="p-4 md:p-6">


                            <form id="import-form" action="{{ route('settings.backup.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Backup File') }}
                                    </label>
                                    <input type="file" name="backup_file" accept=".json" required
                                           class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">
                                        {{ __('Import Mode') }}
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" name="mode" value="merge" checked
                                                   class="border-slate-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-slate-700">
                                                <strong>{{ __('Merge') }}</strong> - {{ __('Update existing records, add new ones') }}
                                            </span>
                                        </label>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="radio" name="mode" value="replace"
                                                   class="border-slate-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-slate-700">
                                                <strong>{{ __('Replace') }}</strong> - {{ __('Delete existing data and import fresh') }}
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                                    <div class="flex">
                                        <svg class="w-5 h-5 text-amber-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <p class="text-sm text-amber-700">
                                            <strong>{{ __('Warning:') }}</strong> {{ __('Importing data will modify your database. Make sure you have a backup before proceeding.') }}
                                        </p>
                                    </div>
                                </div>

                                <button type="submit" id="import-btn"
                                        onclick="return startImport()"
                                        class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-amber-600 text-white text-sm font-medium rounded-xl hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    {{ __('Import Backup') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Existing Backups -->
                <div class="mt-6 bg-white rounded-lg border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Existing Backups') }}</h3>
                            <p class="text-sm text-slate-500">{{ count($backups) }} {{ __('backup(s) available') }}</p>
                        </div>
                    </div>
                    <div class="p-4 md:p-6">


                        @if(count($backups) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase">{{ __('Filename') }}</th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase">{{ __('Created') }}</th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase">{{ __('Size') }}</th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase">{{ __('Tables') }}</th>
                                            <th class="px-6 py-4 text-right text-xs font-medium text-slate-500 uppercase">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        @foreach($backups as $backup)
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-6 py-4 text-sm text-slate-900 font-medium">{{ $backup['filename'] }}</td>
                                                <td class="px-6 py-4 text-sm text-slate-500">
                                                    {{ $backup['created_at'] ? \Carbon\Carbon::parse($backup['created_at'])->format('d M Y, H:i') : '-' }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-slate-500">
                                                    {{ number_format($backup['size'] / 1024, 1) }} KB
                                                </td>
                                                <td class="px-6 py-4 text-sm text-slate-500">
                                                    {{ isset($backup['tables']) ? count($backup['tables']) : '-' }}
                                                </td>
                                                <td class="px-6 py-4 text-right text-sm space-x-3">
                                                    <a href="{{ route('settings.backup.download', $backup['filename']) }}"
                                                       class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                        </svg>
                                                        {{ __('Download') }}
                                                    </a>
                                                    <button type="button"
                                                            onclick="openRestoreModal('{{ $backup['filename'] }}')"
                                                            class="inline-flex items-center text-amber-600 hover:text-amber-800 font-medium">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                        </svg>
                                                        {{ __('Restore') }}
                                                    </button>
                                                    <form action="{{ route('settings.backup.destroy', $backup['filename']) }}"
                                                          method="POST" class="inline"
                                                          onsubmit="return confirm('{{ __('Are you sure you want to delete this backup?') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center text-red-600 hover:text-red-800 font-medium">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                            {{ __('Delete') }}
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                                <p class="text-slate-500">{{ __('No backups found. Create your first backup above.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Restore Modal -->
    <div id="restoreModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeRestoreModal()"></div>

            <!-- Centering spacer -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="restoreForm" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ __('Restore Backup') }}
                                </h3>

                                <!-- Form Content (hidden during loading) -->
                                <div id="restore-form-content" class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        {{ __('You are about to restore:') }} <strong id="restoreFilename"></strong>
                                    </p>

                                    <div class="space-y-3">
                                        <label class="flex items-start cursor-pointer p-3 border border-slate-200 rounded-xl hover:bg-slate-50">
                                            <input type="radio" name="mode" value="merge" checked
                                                   class="mt-0.5 border-slate-300 text-blue-600 focus:ring-blue-500">
                                            <div class="ml-3">
                                                <span class="text-sm font-medium text-slate-900">{{ __('Merge') }}</span>
                                                <p class="text-xs text-slate-500">{{ __('Update existing records, add new ones. Safer option.') }}</p>
                                            </div>
                                        </label>
                                        <label class="flex items-start cursor-pointer p-3 border border-slate-200 rounded-xl hover:bg-slate-50">
                                            <input type="radio" name="mode" value="replace"
                                                   class="mt-0.5 border-slate-300 text-blue-600 focus:ring-blue-500">
                                            <div class="ml-3">
                                                <span class="text-sm font-medium text-slate-900">{{ __('Replace') }}</span>
                                                <p class="text-xs text-slate-500">{{ __('Delete existing data and import fresh. Use with caution!') }}</p>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                                        <div class="flex">
                                            <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            <p class="text-sm text-red-700">
                                                <strong>{{ __('Warning:') }}</strong> {{ __('This action will modify your database. This cannot be undone!') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Loading Progress (shown during restore) -->
                                <div id="restore-progress" class="mt-4 hidden">
                                    <div class="text-center py-4">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 mb-4">
                                            <svg class="w-8 h-8 text-amber-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                        <p class="text-lg font-medium text-slate-900">{{ __('Restoring backup...') }}</p>
                                        <p class="text-sm text-slate-500 mt-1">{{ __('Please wait, this may take a few moments.') }}</p>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden mt-4">
                                        <div class="bg-gradient-to-r from-amber-400 to-amber-600 h-2 rounded-full animate-pulse" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="restore-buttons" class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" id="restore-submit-btn"
                                onclick="return startRestore()"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:w-auto sm:text-sm">
                            {{ __('Restore') }}
                        </button>
                        <button type="button" onclick="closeRestoreModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function exportBackup() {
            const form = document.getElementById('export-form');
            const formData = new FormData(form);
            const btn = document.getElementById('export-btn');
            const progressDiv = document.getElementById('export-progress');
            const progressBar = document.getElementById('export-bar');
            const progressPercent = document.getElementById('export-percentage');
            const progressStatus = document.getElementById('export-status');

            // Show progress, disable button
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('Creating...') }}
            `;
            progressDiv.classList.remove('hidden');

            // Animate progress
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                progressBar.style.width = progress + '%';
                progressPercent.textContent = Math.round(progress) + '%';

                if (progress < 30) {
                    progressStatus.textContent = '{{ __("Reading database tables...") }}';
                } else if (progress < 60) {
                    progressStatus.textContent = '{{ __("Exporting records...") }}';
                } else {
                    progressStatus.textContent = '{{ __("Saving backup file...") }}';
                }
            }, 200);

            fetch('{{ route('settings.backup.export') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(interval);

                if (data.success) {
                    progressBar.style.width = '100%';
                    progressPercent.textContent = '100%';
                    progressStatus.textContent = '{{ __("Backup created successfully!") }}';
                    progressBar.classList.remove('from-blue-500', 'to-blue-600');
                    progressBar.classList.add('from-green-500', 'to-green-600');

                    // Trigger download
                    setTimeout(() => {
                        window.location.href = data.download_url;
                        setTimeout(() => window.location.reload(), 1000);
                    }, 500);
                } else {
                    throw new Error(data.message || 'Export failed');
                }
            })
            .catch(error => {
                clearInterval(interval);
                progressBar.style.width = '100%';
                progressBar.classList.remove('from-blue-500', 'to-blue-600');
                progressBar.classList.add('from-red-500', 'to-red-600');
                progressStatus.textContent = '{{ __("Error:") }} ' + error.message;

                btn.disabled = false;
                btn.innerHTML = `
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    {{ __('Create Backup') }}
                `;
            });
        }

        function startImport() {
            if (!confirm('{{ __("Are you sure you want to import this backup? This will modify your database.") }}')) {
                return false;
            }

            const btn = document.getElementById('import-btn');
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('Importing...') }}
            `;
            return true;
        }

        function openRestoreModal(filename) {
            document.getElementById('restoreFilename').textContent = filename;
            document.getElementById('restoreForm').action = '{{ url("settings/backup/restore") }}/' + filename;
            document.getElementById('restoreModal').classList.remove('hidden');

            // Reset modal state
            document.getElementById('restore-form-content').classList.remove('hidden');
            document.getElementById('restore-progress').classList.add('hidden');
            document.getElementById('restore-buttons').classList.remove('hidden');
        }

        function closeRestoreModal() {
            document.getElementById('restoreModal').classList.add('hidden');
        }

        function startRestore() {
            // Show loading state
            document.getElementById('restore-form-content').classList.add('hidden');
            document.getElementById('restore-progress').classList.remove('hidden');
            document.getElementById('restore-buttons').classList.add('hidden');

            return true;
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRestoreModal();
            }
        });
    </script>
    @endpush
</x-app-layout>
