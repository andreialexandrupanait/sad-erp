# Smartbill Import Optimization - Implementation Guide

## What Has Been Completed ✅

### 1. Backend Infrastructure
- ✅ **SmartbillController** created at `/app/Http/Controllers/Settings/SmartbillController.php`
- ✅ **Routes** added to `/routes/web.php` under `settings/smartbill` prefix
- ✅ **Settings sidebar** updated with "Smartbill Import" link under Integrations section

### 2. Key Features Implemented
- ✅ Credentials management (store/update Smartbill API credentials)
- ✅ Connection testing endpoint
- ✅ CSV/Excel file upload and parsing
- ✅ Progress tracking using Laravel Cache
- ✅ Server-Sent Events (SSE) endpoint for real-time progress updates
- ✅ Import ID generation for tracking multiple simultaneous imports

---

## What Still Needs To Be Done 📋

### 1. Create View Files

#### A. Main Settings Page
**File:** `/resources/views/settings/smartbill/index.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    @include('settings.partials.sidebar')

    <div class="flex-1 p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900">Smartbill Integration</h1>
                <p class="mt-2 text-slate-600">Configure your Smartbill API credentials and import invoices automatically</p>
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
                <h2 class="text-xl font-semibold text-slate-900 mb-4">API Credentials</h2>

                <form method="POST" action="{{ route('settings.smartbill.credentials.update') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Username</label>
                        <input type="text" name="username" value="{{ old('username', $smartbillSettings['username'] ?? '') }}"
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">API Token</label>
                        <input type="password" name="token" value="{{ old('token', $smartbillSettings['token'] ?? '') }}"
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">CIF</label>
                        <input type="text" name="cif" value="{{ old('cif', $smartbillSettings['cif'] ?? '') }}"
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Save Credentials
                        </button>

                        @if($hasCredentials)
                            <button type="button" onclick="testConnection()" class="px-6 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors">
                                Test Connection
                            </button>
                        @endif
                    </div>
                </form>

                <div id="connectionStatus" class="mt-4"></div>
            </div>

            <!-- Import Card -->
            @if($hasCredentials)
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                    <h2 class="text-xl font-semibold text-slate-900 mb-4">Import Invoices</h2>
                    <p class="text-slate-600 mb-6">Upload a CSV or Excel file exported from Smartbill to import invoices with automatic PDF download.</p>

                    <a href="{{ route('settings.smartbill.import') }}" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Start Import
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function testConnection() {
    const statusDiv = document.getElementById('connectionStatus');
    statusDiv.innerHTML = '<div class="text-blue-600">Testing connection...</div>';

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
            statusDiv.innerHTML = '<div class="text-green-600 font-medium">✓ Connection successful!</div>';
        } else {
            statusDiv.innerHTML = `<div class="text-red-600 font-medium">✗ ${data.message}</div>`;
        }
    })
    .catch(error => {
        statusDiv.innerHTML = '<div class="text-red-600 font-medium">✗ Connection failed</div>';
    });
}
</script>
@endsection
```

#### B. Import Page with Live Progress
**File:** `/resources/views/settings/smartbill/import.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    @include('settings.partials.sidebar')

    <div class="flex-1 p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('settings.smartbill.index') }}" class="text-blue-600 hover:text-blue-700 mb-4 inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Settings
                </a>
                <h1 class="text-3xl font-bold text-slate-900 mt-4">Import Smartbill Invoices</h1>
                <p class="mt-2 text-slate-600">Upload your CSV or Excel export from Smartbill</p>
            </div>

            <!-- Upload Form (shown initially) -->
            <div id="uploadForm" class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Step 1: Export from Smartbill</h3>
                    <ol class="list-decimal list-inside text-slate-600 space-y-1">
                        <li>Log in to your Smartbill account</li>
                        <li>Go to <strong>Rapoarte</strong> → <strong>Export</strong></li>
                        <li>Select date range and export as CSV or Excel</li>
                        <li>Download the file</li>
                    </ol>
                </div>

                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Step 2: Upload File</h3>

                    <form id="importForm" class="space-y-4">
                        @csrf

                        <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center hover:border-blue-500 transition-colors">
                            <input type="file" id="csvFile" name="csv_file" accept=".csv,.xls,.xlsx" class="hidden" onchange="handleFileSelect(this)">
                            <label for="csvFile" class="cursor-pointer">
                                <svg class="w-12 h-12 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-lg font-medium text-slate-700">Click to upload or drag and drop</p>
                                <p class="text-sm text-slate-500 mt-1">CSV, XLS, or XLSX (max 10MB)</p>
                            </label>
                        </div>

                        <div id="selectedFile" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div>
                                        <p id="fileName" class="font-medium text-slate-900"></p>
                                        <p id="fileSize" class="text-sm text-slate-600"></p>
                                    </div>
                                </div>
                                <button type="button" onclick="clearFile()" class="text-red-600 hover:text-red-700">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="downloadPdfs" name="download_pdfs" value="1" checked class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                            <label for="downloadPdfs" class="ml-2 text-sm text-slate-700">
                                Download invoice PDFs from Smartbill (recommended)
                            </label>
                        </div>

                        <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            Start Import
                        </button>
                    </form>
                </div>
            </div>

            <!-- Progress Display (shown during import) -->
            <div id="progressDisplay" class="hidden bg-white rounded-lg shadow-sm border border-slate-200 p-8">
                <h3 class="text-xl font-semibold text-slate-900 mb-6">Importing Invoices...</h3>

                <!-- Progress Bar -->
                <div class="mb-6">
                    <div class="flex justify-between text-sm text-slate-600 mb-2">
                        <span id="progressText">Initializing...</span>
                        <span id="progressPercent">0%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                        <div id="progressBar" class="bg-blue-600 h-full transition-all duration-300 ease-out" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-slate-50 rounded-lg p-4">
                        <p class="text-sm text-slate-600">Total</p>
                        <p id="statTotal" class="text-2xl font-bold text-slate-900">0</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-sm text-green-600">Created</p>
                        <p id="statCreated" class="text-2xl font-bold text-green-600">0</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <p class="text-sm text-yellow-600">Skipped</p>
                        <p id="statSkipped" class="text-2xl font-bold text-yellow-600">0</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4">
                        <p class="text-sm text-red-600">Errors</p>
                        <p id="statErrors" class="text-2xl font-bold text-red-600">0</p>
                    </div>
                </div>

                <!-- Processing Animation -->
                <div id="processingAnimation" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-slate-200 border-t-blue-600"></div>
                </div>

                <!-- Completion Message -->
                <div id="completionMessage" class="hidden text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-green-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Import Complete!</h3>
                    <p id="completionText" class="text-slate-600 mb-6"></p>
                    <div class="flex gap-4 justify-center">
                        <a href="{{ route('financial.revenues.index') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            View Revenues
                        </a>
                        <button onclick="resetImport()" class="px-6 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">
                            Import Another File
                        </button>
                    </div>
                </div>

                <!-- Error Display -->
                <div id="errorDisplay" class="hidden">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h4 class="font-semibold text-red-900 mb-2">Errors:</h4>
                        <ul id="errorList" class="list-disc list-inside text-sm text-red-700"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentFile = null;
let importId = null;
let eventSource = null;

function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;

    currentFile = file;
    document.getElementById('selectedFile').classList.remove('hidden');
    document.getElementById('fileName').textContent = file.name;
    document.getElementById('fileSize').textContent = formatFileSize(file.size);
}

function clearFile() {
    currentFile = null;
    document.getElementById('csvFile').value = '';
    document.getElementById('selectedFile').classList.add('hidden');
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' bytes';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

document.getElementById('importForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!currentFile) {
        alert('Please select a file');
        return;
    }

    const formData = new FormData();
    formData.append('csv_file', currentFile);
    formData.append('download_pdfs', document.getElementById('downloadPdfs').checked ? '1' : '0');

    // Hide upload form, show progress
    document.getElementById('uploadForm').classList.add('hidden');
    document.getElementById('progressDisplay').classList.remove('hidden');

    try {
        // Step 1: Upload and initialize
        const response = await fetch('{{ route('settings.smartbill.import.process') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Failed to initialize import');
        }

        importId = data.import_id;

        // Step 2: Start the import
        await fetch(`/settings/smartbill/import/${importId}/start`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        // Step 3: Connect to SSE for progress updates
        eventSource = new EventSource(`/settings/smartbill/import/${importId}/progress`);

        eventSource.onmessage = function(event) {
            const progress = JSON.parse(event.data);
            updateProgress(progress);

            if (progress.status === 'completed' || progress.status === 'failed') {
                eventSource.close();

                if (progress.status === 'completed') {
                    showCompletion(progress);
                } else {
                    showError(progress.message);
                }
            }
        };

        eventSource.onerror = function() {
            eventSource.close();
            showError('Connection lost. Please refresh the page.');
        };

    } catch (error) {
        showError(error.message);
    }
});

function updateProgress(progress) {
    const { total, processed, created, skipped, errors, message } = progress;

    // Update text
    document.getElementById('progressText').textContent = message || 'Processing...';

    // Update stats
    document.getElementById('statTotal').textContent = total || 0;
    document.getElementById('statCreated').textContent = created || 0;
    document.getElementById('statSkipped').textContent = skipped || 0;
    document.getElementById('statErrors').textContent = errors || 0;

    // Update progress bar
    const percent = total > 0 ? Math.round((processed / total) * 100) : 0;
    document.getElementById('progressPercent').textContent = percent + '%';
    document.getElementById('progressBar').style.width = percent + '%';
}

function showCompletion(progress) {
    document.getElementById('processingAnimation').classList.add('hidden');
    document.getElementById('completionMessage').classList.remove('hidden');
    document.getElementById('completionText').textContent = progress.message;

    if (progress.errors_list && progress.errors_list.length > 0) {
        const errorList = document.getElementById('errorList');
        errorList.innerHTML = '';
        progress.errors_list.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });
        document.getElementById('errorDisplay').classList.remove('hidden');
    }
}

function showError(message) {
    document.getElementById('processingAnimation').classList.add('hidden');
    document.getElementById('errorDisplay').classList.remove('hidden');
    const errorList = document.getElementById('errorList');
    errorList.innerHTML = `<li>${message}</li>`;
}

function resetImport() {
    location.reload();
}
</script>
@endsection
```

---

## Next Steps to Complete

### 2. Optimize the Import Processing

The current `processImportData` method in `SmartbillController.php` needs to be expanded to use the existing `ImportController` logic. Here's what needs to be integrated:

```php
// In SmartbillController.php, replace processImportData() with:
private function processImportData($importId, $data)
{
    $csvData = $data['csv_data'];
    $downloadPdfs = $data['download_pdfs'];
    $userId = $data['user_id'];

    // Set auth context
    auth()->loginUsingId($userId);

    $organization = auth()->user()->organization;
    $smartbillSettings = $organization->settings['smartbill'] ?? [];

    // Initialize Smartbill service
    $smartbillService = new SmartbillService(
        $smartbillSettings['username'],
        $smartbillSettings['token'],
        $smartbillSettings['cif']
    );

    // Find and process header (copy logic from ImportController)
    // ... (use the existing detectSmartbillExport and mapSmartbillColumns methods)

    // Process each row with progress updates
    // ... (integrate the existing importRevenues logic)
}
```

### 3. Test the Flow

1. Start Docker containers: `docker compose up -d`
2. Visit `/settings/smartbill`
3. Configure credentials
4. Test connection
5. Upload a CSV file
6. Watch the real-time progress

### 4. Performance Optimizations

Consider adding these enhancements:

1. **Batch Processing**: Process invoices in chunks of 50-100
2. **Queue Jobs**: Move heavy processing to Laravel queue for better performance
3. **Caching**: Store frequently accessed data (clients, categories)
4. **Database Optimization**: Use bulk inserts instead of individual creates

---

## File Structure Summary

```
app/
├── Http/Controllers/
│   └── Settings/
│       └── SmartbillController.php ✅ CREATED
routes/
└── web.php ✅ UPDATED
resources/views/
├── settings/
│   ├── partials/
│   │   └── sidebar.blade.php ✅ UPDATED
│   └── smartbill/
│       ├── index.blade.php ❌ TODO
│       └── import.blade.php ❌ TODO
```

---

## Benefits of This Solution

1. **✨ Real-time Progress**: Users see exactly what's happening
2. **🚀 Better UX**: Modern, intuitive interface
3. **📊 Live Stats**: Created, skipped, errors updated in real-time
4. **⚡ Non-blocking**: Uses SSE for efficient real-time updates
5. **🔒 Secure**: Server-side processing with proper auth
6. **📱 Responsive**: Works on mobile and desktop
7. **♻️ Reusable**: Can adapt for other import types

---

## How to Finish

1. Create the two view files above
2. Test the upload and progress flow
3. Integrate the full import logic from `ImportController`
4. Add error handling for edge cases
5. Consider adding email notifications on completion

This provides a production-ready foundation for automated Smartbill imports with modern UX!
