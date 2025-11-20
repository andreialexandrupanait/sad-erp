<x-app-layout>
    <x-slot name="pageTitle">{{ __('Import Smartbill Invoices') }}</x-slot>

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

                        <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center hover:border-blue-500 transition-colors cursor-pointer" id="dropZone">
                            <input type="file" id="csvFile" name="csv_file" accept=".csv,.xls,.xlsx" class="hidden" onchange="handleFileSelect(this)">
                            <label for="csvFile" class="cursor-pointer block">
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

                        <div class="flex items-center bg-slate-50 rounded-lg p-3">
                            <input type="checkbox" id="downloadPdfs" name="download_pdfs" value="1" checked class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                            <label for="downloadPdfs" class="ml-3 text-sm text-slate-700">
                                <span class="font-medium">Download invoice PDFs from Smartbill</span>
                                <span class="block text-xs text-slate-500 mt-0.5">Automatically fetch and attach PDF files for each invoice (recommended)</span>
                            </label>
                        </div>

                        <button type="submit" id="submitBtn" class="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
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
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                        <p class="text-sm text-slate-600 mb-1">Total</p>
                        <p id="statTotal" class="text-2xl font-bold text-slate-900">0</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <p class="text-sm text-green-600 mb-1">Created</p>
                        <p id="statCreated" class="text-2xl font-bold text-green-600">0</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                        <p class="text-sm text-yellow-600 mb-1">Skipped</p>
                        <p id="statSkipped" class="text-2xl font-bold text-yellow-600">0</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                        <p class="text-sm text-red-600 mb-1">Errors</p>
                        <p id="statErrors" class="text-2xl font-bold text-red-600">0</p>
                    </div>
                </div>

                <!-- Processing Animation -->
                <div id="processingAnimation" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-slate-200 border-t-blue-600"></div>
                    <p class="mt-4 text-slate-600">Processing your invoices...</p>
                </div>

                <!-- Completion Message -->
                <div id="completionMessage" class="hidden text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-green-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Import Complete!</h3>
                    <p id="completionText" class="text-slate-600 mb-6"></p>
                    <div class="flex gap-4 justify-center">
                        <a href="{{ route('financial.revenues.index') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            View Revenues
                        </a>
                        <button onclick="resetImport()" class="px-6 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors">
                            Import Another File
                        </button>
                    </div>
                </div>

                <!-- Error Display -->
                <div id="errorDisplay" class="hidden mt-6">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h4 class="font-semibold text-red-900 mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Errors Encountered:
                        </h4>
                        <ul id="errorList" class="list-disc list-inside text-sm text-red-700 space-y-1 mt-2"></ul>
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

// Drag and drop support
const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('csvFile').files = files;
        handleFileSelect({ files: [files[0]] });
    }
});

function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;

    // Validate file size (10MB max)
    if (file.size > 10 * 1024 * 1024) {
        alert('File is too large. Maximum size is 10MB.');
        return;
    }

    // Validate file type
    const validTypes = ['.csv', '.xls', '.xlsx'];
    const fileName = file.name.toLowerCase();
    const isValid = validTypes.some(type => fileName.endsWith(type));

    if (!isValid) {
        alert('Invalid file type. Please upload a CSV, XLS, or XLSX file.');
        return;
    }

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
        alert('Please select a file first');
        return;
    }

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Uploading...';

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
        console.error('Import error:', error);
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
    document.getElementById('completionMessage').classList.add('hidden');
    document.getElementById('errorDisplay').classList.remove('hidden');
    const errorList = document.getElementById('errorList');
    errorList.innerHTML = `<li>${message}</li>`;

    // Show back button
    const completionDiv = document.getElementById('completionMessage');
    completionDiv.classList.remove('hidden');
    completionDiv.innerHTML = `
        <svg class="w-16 h-16 mx-auto text-red-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="text-2xl font-bold text-slate-900 mb-2">Import Failed</h3>
        <p class="text-slate-600 mb-6">There was an error processing your import.</p>
        <button onclick="resetImport()" class="px-6 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-800 transition-colors">
            Try Again
        </button>
    `;
}

function resetImport() {
    location.reload();
}
</script>
</x-app-layout>
