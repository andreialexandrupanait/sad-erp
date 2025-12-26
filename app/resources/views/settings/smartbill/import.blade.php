<x-app-layout>
    <x-slot name="pageTitle">{{ __('Import Smartbill Invoices') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

    <div class="flex-1 overflow-y-auto">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('settings.smartbill.index') }}" class="text-blue-600 hover:text-blue-700 mb-4 inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ __('app.back_to_settings') }}
                </a>
                <h1 class="text-3xl font-bold text-slate-900 mt-4">{{ __('settings.import_invoices_title') }}</h1>
                <p class="mt-2 text-slate-600">{{ __('settings.upload_csv_excel') }}</p>
            </div>

            <!-- Upload Form (shown initially) -->
            <div id="uploadForm" class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('settings.step1_export') }}</h3>
                    <ol class="list-decimal list-inside text-slate-600 space-y-1">
                        <li>{{ __('settings.log_into_smartbill') }}</li>
                        <li>{{ __('settings.go_to_reports_export') }}</li>
                        <li>{{ __('settings.select_date_range') }} - {{ __('settings.export_csv_excel') }}</li>
                        <li>{{ __('settings.download_the_file') }}</li>
                    </ol>
                </div>

                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ __('settings.step2_upload') }}</h3>

                    <form id="importForm" class="space-y-4">
                        @csrf

                        <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center hover:border-blue-500 transition-colors cursor-pointer" id="dropZone">
                            <input type="file" id="csvFile" name="csv_file" accept=".csv,.xls,.xlsx" class="hidden" onchange="handleFileSelect(this)">
                            <label for="csvFile" class="cursor-pointer block">
                                <svg class="w-12 h-12 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-lg font-medium text-slate-700">{{ __('app.click_to_upload') }}</p>
                                <p class="text-sm text-slate-500 mt-1">{{ __('settings.file_types_hint') }}</p>
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
                                <span class="font-medium">{{ __('settings.download_pdfs') }}</span>
                                <span class="block text-xs text-slate-500 mt-0.5">{{ __('settings.download_pdfs_hint') }}</span>
                            </label>
                        </div>

                        <button type="submit" id="submitBtn" class="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ __('settings.start_import') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Progress Display (shown during import) -->
            <div id="progressDisplay" class="hidden bg-white rounded-lg shadow-sm border border-slate-200 p-8">
                <h3 class="text-xl font-semibold text-slate-900 mb-6">{{ __('settings.importing') }}</h3>

                <!-- Progress Bar -->
                <div class="mb-6">
                    <div class="flex justify-between text-sm text-slate-600 mb-2">
                        <span id="progressText">{{ __('settings.initializing') }}</span>
                        <span id="progressPercent">0%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                        <div id="progressBar" class="bg-blue-600 h-full transition-all duration-300 ease-out" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                        <p class="text-sm text-slate-600 mb-1">{{ __('app.total') }}</p>
                        <p id="statTotal" class="text-2xl font-bold text-slate-900">0</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <p class="text-sm text-green-600 mb-1">{{ __('app.created') }}</p>
                        <p id="statCreated" class="text-2xl font-bold text-green-600">0</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                        <p class="text-sm text-yellow-600 mb-1">{{ __('settings.skipped') }}</p>
                        <p id="statSkipped" class="text-2xl font-bold text-yellow-600">0</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                        <p class="text-sm text-red-600 mb-1">{{ __('settings.errors') }}</p>
                        <p id="statErrors" class="text-2xl font-bold text-red-600">0</p>
                    </div>
                </div>

                <!-- Processing Animation -->
                <div id="processingAnimation" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-slate-200 border-t-blue-600"></div>
                    <p class="mt-4 text-slate-600">{{ __('settings.processing') }}</p>
                </div>

                <!-- Completion Message -->
                <div id="completionMessage" class="hidden text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-green-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">{{ __('settings.import_complete') }}</h3>
                    <p id="completionText" class="text-slate-600 mb-6"></p>
                    <div class="flex gap-4 justify-center">
                        <a href="{{ route('financial.revenues.index') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            {{ __('settings.view_revenues') }}
                        </a>
                        <button onclick="resetImport()" class="px-6 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors">
                            {{ __('settings.import_another') }}
                        </button>
                    </div>
                </div>

                <!-- Duplicates Warning Display -->
                <div id="duplicatesDisplay" class="hidden mt-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-900 mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('Facturi duplicate (deja existente)') }}
                        </h4>
                        <p class="text-sm text-blue-700 mb-2">{{ __('Aceste facturi există deja în sistem și au fost omise:') }}</p>
                        <ul id="duplicatesList" class="list-disc list-inside text-sm text-blue-700 space-y-1"></ul>
                    </div>
                </div>

                <!-- Error Display -->
                <div id="errorDisplay" class="hidden mt-6">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h4 class="font-semibold text-red-900 mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('settings.errors_encountered') }}
                        </h4>
                        <ul id="errorList" class="list-disc list-inside text-sm text-red-700 space-y-1 mt-2"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Translations for JS
const translations = {
    invalidFileType: '{{ __('settings.invalid_file_type') }}',
    pleaseSelectFile: '{{ __('settings.please_select_file') }}',
    uploading: '{{ __('settings.uploading') }}',
    importFailed: '{{ __('settings.import_failed') }}',
    tryAgain: '{{ __('settings.try_again') }}',
    errorProcessing: '{{ __('settings.error_processing') }}'
};
let currentFile = null;
let importId = null;

// Prevent browser from opening dropped files
document.addEventListener('dragover', (e) => e.preventDefault());
document.addEventListener('drop', (e) => e.preventDefault());

// Drag and drop support
const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.classList.add('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('dragleave', (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        // Create a DataTransfer object to set files on the input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(files[0]);
        document.getElementById('csvFile').files = dataTransfer.files;
        handleFileSelect(files[0]);
    }
});

function handleFileSelect(input) {
    // Handle both input element (from onchange) and direct File object (from drag-drop)
    const file = input instanceof File ? input : (input.files ? input.files[0] : null);
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
        alert(translations.invalidFileType);
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
        alert(translations.pleaseSelectFile);
        return;
    }

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = translations.uploading;

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

        // Step 3: Poll for progress updates
        startPolling();

    } catch (error) {
        console.error('Import error:', error);
        showError(error.message);
    }
});


let pollInterval = null;

function startPolling() {
    pollInterval = setInterval(async () => {
        try {
            const response = await fetch(`/settings/smartbill/import/${importId}/progress`);
            const progress = await response.json();

            if (progress.error) {
                clearInterval(pollInterval);
                showError(progress.error);
                return;
            }

            updateProgress(progress);

            if (progress.status === 'completed') {
                clearInterval(pollInterval);
                showCompletion(progress);
            } else if (progress.status === 'failed') {
                clearInterval(pollInterval);
                showError(progress.message || 'Import failed');
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }, 1000);
}
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

    // Show duplicates as info (not errors)
    if (progress.duplicates_found && progress.duplicates_found.length > 0) {
        const duplicatesList = document.getElementById('duplicatesList');
        duplicatesList.innerHTML = '';
        progress.duplicates_found.forEach(dup => {
            const li = document.createElement('li');
            li.textContent = `${dup.invoice} - ${dup.date} - ${dup.amount} RON`;
            duplicatesList.appendChild(li);
        });
        document.getElementById('duplicatesDisplay').classList.remove('hidden');
    }

    // Show actual errors (validation errors only)
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
        <h3 class="text-2xl font-bold text-slate-900 mb-2">${translations.importFailed}</h3>
        <p class="text-slate-600 mb-6">${translations.errorProcessing}</p>
        <button onclick="resetImport()" class="px-6 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-800 transition-colors">
            ${translations.tryAgain}
        </button>
    `;
}

function resetImport() {
    location.reload();
}
</script>
</x-app-layout>
