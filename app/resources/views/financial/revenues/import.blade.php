<x-app-layout>
    <x-slot name="pageTitle">{{ __('Import Revenues') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="ghost" onclick="window.location.href='{{ route('financial.revenues.index') }}'">
            <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Revenues
        </x-ui.button>
    </x-slot>

    <div class="p-4 md:p-6">
        <div class="max-w-3xl mx-auto">
            <!-- Instructions Card -->
            <x-ui.card class="mb-6">
                <x-ui.card-header>
                    <h3 class="text-lg font-semibold text-slate-900">Import Instructions</h3>
                </x-ui.card-header>
                <x-ui.card-content>
                    <div class="space-y-4 text-sm text-slate-600">
                        <div>
                            <h4 class="font-medium text-slate-900 mb-2">File Format Requirements:</h4>
                            <ul class="list-disc list-inside space-y-1 ml-2">
                                <li>Supported formats: CSV (.csv, .txt), Excel (.xls, .xlsx)</li>
                                <li>Maximum file size: 5MB</li>
                                <li>First row must contain column headers</li>
                                <li>Required columns: document_name, amount, currency, occurred_at</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-slate-900 mb-2">Supported Columns:</h4>
                            <div class="grid grid-cols-2 gap-2 ml-2">
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">document_name</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">amount</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">currency (RON/EUR)</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">occurred_at (YYYY-MM-DD)</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">client_name</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">note</code>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-medium text-blue-900 mb-2 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Smartbill Integration
                            </h4>
                            <ul class="list-disc list-inside space-y-1 ml-2 text-sm text-blue-800">
                                <li>Automatically detects Smartbill CSV exports</li>
                                <li>Stores invoice series and numbers for reference</li>
                                <li>Can download invoice PDFs directly from Smartbill API</li>
                                <li>Smartbill columns: Serie, Numar, CIF</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-slate-900 mb-2">Notes:</h4>
                            <ul class="list-disc list-inside space-y-1 ml-2">
                                <li>Client names will be matched automatically if they exist in your system</li>
                                <li>Year and month will be calculated automatically from occurred_at date</li>
                                <li>All revenues will be assigned to your user account</li>
                            </ul>
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Download Template -->
            <x-ui.card class="mb-6">
                <x-ui.card-content class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-slate-900">Download Template</h3>
                        <p class="text-sm text-slate-600 mt-1">Get a pre-formatted CSV template with example data (works for reference - you can upload Smartbill XLS directly)</p>
                    </div>
                    <x-ui.button variant="outline" onclick="window.location.href='{{ route('financial.revenues.import.template') }}'">
                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download Template
                    </x-ui.button>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Upload Form with Preview -->
            <x-ui.card x-data="importPreview()">
                <x-ui.card-header>
                    <h3 class="text-lg font-semibold text-slate-900">Upload CSV File</h3>
                </x-ui.card-header>
                <x-ui.card-content>
                    <!-- Step 1: File Selection -->
                    <div x-show="step === 1">
                        <form @submit.prevent="previewFile" enctype="multipart/form-data" class="space-y-6">
                            <div>
                                <x-ui.label for="csv_file">Import File (CSV or Excel)</x-ui.label>
                                <div class="mt-2">
                                    <input
                                        type="file"
                                        name="csv_file"
                                        id="csv_file"
                                        x-ref="fileInput"
                                        accept=".csv,.txt,.xls,.xlsx"
                                        required
                                        @change="selectedFile = $event.target.files[0]; previewData = null"
                                        class="block w-full text-sm text-slate-900 border border-slate-300 rounded-lg cursor-pointer bg-slate-50 focus:outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900 file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800"
                                    />
                                </div>
                                @error('csv_file')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end gap-x-4 pt-4 border-t border-slate-200">
                                <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('financial.revenues.index') }}'">
                                    Cancel
                                </x-ui.button>
                                <x-ui.button type="submit" variant="default" :disabled="loading || !selectedFile">
                                    <template x-if="!loading">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Preview Import
                                        </span>
                                    </template>
                                    <template x-if="loading">
                                        <span class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Analyzing file...
                                        </span>
                                    </template>
                                </x-ui.button>
                            </div>
                        </form>
                    </div>

                    <!-- Step 2: Preview Results -->
                    <div x-show="step === 2" x-cloak>
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-slate-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-slate-900" x-text="previewData?.summary?.total || 0"></div>
                                <div class="text-sm text-slate-600">Total Rows</div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-green-600" x-text="previewData?.summary?.new || 0"></div>
                                <div class="text-sm text-slate-600">New Entries</div>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-yellow-600" x-text="previewData?.summary?.duplicates || 0"></div>
                                <div class="text-sm text-slate-600">Duplicates</div>
                            </div>
                            <div class="bg-red-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-red-600" x-text="previewData?.summary?.errors || 0"></div>
                                <div class="text-sm text-slate-600">Errors</div>
                            </div>
                        </div>

                        <!-- Amount Summary -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-sm text-blue-800">Total to Import:</span>
                                    <span class="font-semibold text-blue-900 ml-2">
                                        <span x-text="formatNumber(previewData?.summary?.total_amount_ron || 0)"></span> RON
                                        <template x-if="(previewData?.summary?.total_amount_eur || 0) > 0">
                                            <span>
                                                + <span x-text="formatNumber(previewData?.summary?.total_amount_eur || 0)"></span> EUR
                                            </span>
                                        </template>
                                    </span>
                                </div>
                                <template x-if="previewData?.summary?.new_clients > 0">
                                    <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                        <span x-text="previewData?.summary?.new_clients"></span> new client(s) will be created
                                    </span>
                                </template>
                            </div>
                            <template x-if="previewData?.is_smartbill">
                                <div class="mt-2 text-sm text-blue-700 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Smartbill export detected
                                </div>
                            </template>
                        </div>

                        <!-- Preview Table -->
                        <div class="border border-slate-200 rounded-lg overflow-hidden mb-6">
                            <div class="bg-slate-50 px-4 py-2 border-b border-slate-200 flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-700">Preview (first 50 rows)</span>
                                <template x-if="previewData?.has_more">
                                    <span class="text-xs text-slate-500">+ more rows not shown</span>
                                </template>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-100 sticky top-0">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">#</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Status</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Document</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-slate-500">Amount</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Date</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Client</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-100">
                                        <template x-for="row in previewData?.preview_rows || []" :key="row.row">
                                            <tr :class="{
                                                'bg-red-50': row.has_error,
                                                'bg-yellow-50': row.is_duplicate && !row.has_error
                                            }">
                                                <td class="px-3 py-2 text-xs text-slate-500" x-text="row.row"></td>
                                                <td class="px-3 py-2">
                                                    <template x-if="row.has_error">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800" x-text="row.error_msg"></span>
                                                    </template>
                                                    <template x-if="row.is_duplicate && !row.has_error">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Duplicate</span>
                                                    </template>
                                                    <template x-if="!row.has_error && !row.is_duplicate">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">New</span>
                                                    </template>
                                                </td>
                                                <td class="px-3 py-2 text-sm text-slate-900 max-w-[200px] truncate" x-text="row.document_name"></td>
                                                <td class="px-3 py-2 text-sm text-slate-900 text-right whitespace-nowrap">
                                                    <span x-text="formatNumber(row.amount)"></span>
                                                    <span class="text-xs text-slate-500" x-text="row.currency"></span>
                                                </td>
                                                <td class="px-3 py-2 text-sm text-slate-600" x-text="row.date"></td>
                                                <td class="px-3 py-2 text-sm max-w-[150px] truncate">
                                                    <span :class="{
                                                        'text-slate-900': row.client_status === 'existing',
                                                        'text-blue-600': row.client_status === 'new',
                                                        'text-slate-400': row.client_status === 'none'
                                                    }" x-text="row.client_name || '-'"></span>
                                                    <template x-if="row.client_status === 'new'">
                                                        <span class="text-xs text-blue-500 ml-1">(new)</span>
                                                    </template>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <form method="POST" action="{{ route('financial.revenues.import.post') }}" enctype="multipart/form-data" x-ref="importForm">
                            @csrf
                            <input type="file" name="csv_file" x-ref="hiddenFileInput" class="hidden">

                            <!-- Smartbill PDF Download Option -->
                            <div class="flex items-start mb-6" x-show="previewData?.is_smartbill">
                                <div class="flex h-6 items-center">
                                    <input
                                        type="checkbox"
                                        name="download_smartbill_pdfs"
                                        id="download_smartbill_pdfs"
                                        value="1"
                                        class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                                    >
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="download_smartbill_pdfs" class="font-medium text-slate-900">Download invoice PDFs from Smartbill</label>
                                    <p class="text-slate-500">Automatically download and attach invoice PDFs using the Smartbill API.</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                                <x-ui.button type="button" variant="ghost" @click="step = 1; previewData = null">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                    Back
                                </x-ui.button>
                                <div class="flex items-center gap-x-4">
                                    <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('financial.revenues.index') }}'">
                                        Cancel
                                    </x-ui.button>
                                    <x-ui.button type="button" variant="default" @click="submitImport" :disabled="importing || (previewData?.summary?.new || 0) === 0">
                                        <template x-if="!importing">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                                Import <span x-text="previewData?.summary?.new || 0" class="ml-1"></span> Revenues
                                            </span>
                                        </template>
                                        <template x-if="importing">
                                            <span class="flex items-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Importing...
                                            </span>
                                        </template>
                                    </x-ui.button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Error Display -->
                    <template x-if="error">
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-700" x-text="error"></p>
                        </div>
                    </template>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Display Import Errors if any -->
            @if(session('import_errors') && count(session('import_errors')) > 0)
                <x-ui.card class="mt-6 border-red-200 bg-red-50">
                    <x-ui.card-header>
                        <h3 class="text-lg font-semibold text-red-900">Import Errors</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-ui.card-content>
                </x-ui.card>
            @endif

            <!-- Recent Imports -->
            @if(isset($recentImports) && $recentImports->count() > 0)
                <x-ui.card class="mt-6">
                    <x-ui.card-header>
                        <h3 class="text-lg font-semibold text-slate-900">Recent Imports</h3>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div class="space-y-3">
                            @foreach($recentImports as $import)
                                <div class="flex items-center justify-between p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors" id="import-{{ $import->id }}">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            @if($import->status === 'completed')
                                                <span class="flex-shrink-0 w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </span>
                                            @elseif($import->status === 'failed')
                                                <span class="flex-shrink-0 w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </span>
                                            @elseif($import->status === 'cancelled')
                                                <span class="flex-shrink-0 w-8 h-8 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
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
                                                    <span class="font-medium text-slate-900">{{ $import->file_name ?? 'Import' }}</span>
                                                    <span class="text-xs px-2 py-1 rounded-full
                                                        @if($import->status === 'completed') bg-green-100 text-green-700
                                                        @elseif($import->status === 'failed') bg-red-100 text-red-700
                                                        @elseif($import->status === 'cancelled') bg-yellow-100 text-yellow-700
                                                        @elseif($import->status === 'running') bg-blue-100 text-blue-700
                                                        @else bg-slate-100 text-slate-700
                                                        @endif">
                                                        {{ ucfirst($import->status) }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-slate-600 mt-1">
                                                    Started {{ $import->created_at->diffForHumans() }}
                                                    @if($import->status === 'running' || $import->status === 'pending')
                                                        <span class="import-progress" data-import-id="{{ $import->id }}">
                                                            • {{ $import->processed_rows }}/{{ $import->total_rows }} rows ({{ $import->progress_percentage }}%)
                                                        </span>
                                                    @elseif($import->completed_at && $import->started_at)
                                                        • Completed in {{ $import->started_at->diffInSeconds($import->completed_at) }}s
                                                    @endif
                                                </p>
                                                @if($import->stats)
                                                    <div class="flex gap-4 mt-2 text-xs text-slate-500">
                                                        <span>Imported: {{ $import->stats['imported'] ?? 0 }}</span>
                                                        <span>Skipped: {{ $import->stats['skipped'] ?? 0 }}</span>
                                                        @if(($import->stats['pdfs_downloaded'] ?? 0) > 0)
                                                            <span>PDFs: {{ $import->stats['pdfs_downloaded'] }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-2 ml-4">
                                        @if(in_array($import->status, ['running', 'pending']))
                                            <button onclick="cancelImport({{ $import->id }})" class="px-3 py-1.5 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors" title="Cancel">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        @endif
                                        @if($import->status !== 'running')
                                            <button onclick="deleteImport({{ $import->id }})" class="px-3 py-1.5 text-sm text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            @endif
        </div>
    </div>

<script>
// Import Preview Alpine Component
function importPreview() {
    return {
        step: 1,
        selectedFile: null,
        previewData: null,
        loading: false,
        importing: false,
        error: null,

        async previewFile() {
            if (!this.selectedFile) return;

            this.loading = true;
            this.error = null;

            const formData = new FormData();
            formData.append('csv_file', this.selectedFile);

            try {
                const response = await fetch('{{ route('financial.revenues.import.preview') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.previewData = data;
                    this.step = 2;
                } else {
                    this.error = data.message || 'Failed to preview file';
                }
            } catch (err) {
                this.error = 'Failed to analyze file. Please try again.';
                console.error('Preview error:', err);
            } finally {
                this.loading = false;
            }
        },

        submitImport() {
            if (!this.selectedFile) return;

            this.importing = true;

            // Copy the file to the hidden input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(this.selectedFile);
            this.$refs.hiddenFileInput.files = dataTransfer.files;

            // Submit the form
            this.$refs.importForm.submit();
        },

        formatNumber(num) {
            if (!num && num !== 0) return '0';
            return new Intl.NumberFormat('ro-RO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }
    };
}

// Poll for status updates on running imports
document.addEventListener('DOMContentLoaded', function() {
    const runningImports = document.querySelectorAll('.import-progress');
    if (runningImports.length > 0) {
        setInterval(function() {
            runningImports.forEach(function(el) {
                const importId = el.dataset.importId;
                fetch(`/financial/revenues/import/${importId}/status`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const importData = data.import;
                            el.textContent = ` • ${importData.processed_rows}/${importData.total_rows} rows (${importData.progress_percentage}%)`;

                            if (['completed', 'failed', 'cancelled'].includes(importData.status)) {
                                window.location.reload();
                            }
                        }
                    })
                    .catch(error => console.error('Error fetching import status:', error));
            });
        }, 3000); // Poll every 3 seconds
    }
});

function cancelImport(importId) {
    if (!confirm('Are you sure you want to cancel this import?')) return;

    fetch(`/financial/revenues/import/${importId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to cancel import');
        }
    })
    .catch(error => {
        alert('Failed to cancel import. Please try again.');
    });
}

function deleteImport(importId) {
    if (!confirm('Are you sure you want to delete this import record?')) return;

    fetch(`/financial/revenues/import/${importId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to delete import');
        }
    })
    .catch(error => {
        alert('Failed to delete import. Please try again.');
    });
}
</script>
</x-app-layout>
