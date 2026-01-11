<x-app-layout>
    <x-slot name="pageTitle">{{ __('Import Clients') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="ghost" onclick="window.location.href='{{ route('clients.index') }}'">
            <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Clients
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
                            <h4 class="font-medium text-slate-900 mb-2">CSV Format Requirements:</h4>
                            <ul class="list-disc list-inside space-y-1 ml-2">
                                <li>File must be in CSV format (.csv or .txt)</li>
                                <li>Maximum file size: 2MB</li>
                                <li>First row must contain column headers</li>
                                <li>Required column: <code class="px-1 py-0.5 bg-slate-100 rounded text-xs">name</code></li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-slate-900 mb-2">Supported Columns:</h4>
                            <div class="grid grid-cols-2 gap-2 ml-2">
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">name</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">company_name</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">tax_id</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">registration_number</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">contact_person</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">email</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">phone</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">address</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">vat_payer</code>
                                <code class="px-2 py-1 bg-slate-100 rounded text-xs">notes</code>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-medium text-slate-900 mb-2">Alternative Column Names:</h4>
                            <ul class="list-disc list-inside space-y-1 ml-2">
                                <li><code class="px-1 py-0.5 bg-slate-100 rounded text-xs">cui</code> can be used instead of <code class="px-1 py-0.5 bg-slate-100 rounded text-xs">tax_id</code></li>
                                <li><code class="px-1 py-0.5 bg-slate-100 rounded text-xs">company</code> can be used instead of <code class="px-1 py-0.5 bg-slate-100 rounded text-xs">company_name</code></li>
                                <li><code class="px-1 py-0.5 bg-slate-100 rounded text-xs">reg_number</code> can be used instead of <code class="px-1 py-0.5 bg-slate-100 rounded text-xs">registration_number</code></li>
                            </ul>
                        </div>

                        <div class="pt-4 border-t border-slate-200">
                            <p class="text-slate-700">
                                <strong>Note:</strong> Clients with duplicate tax_id values will be skipped. All imported clients will be assigned the default active status.
                            </p>
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Download Template -->
            <x-ui.card class="mb-6">
                <x-ui.card-content class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-slate-900">Download CSV Template</h3>
                        <p class="text-sm text-slate-600 mt-1">Get a pre-formatted template with example data</p>
                    </div>
                    <x-ui.button variant="outline" onclick="window.location.href='{{ route('clients.import.template') }}'">
                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download Template
                    </x-ui.button>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Upload Form -->
            <x-ui.card>
                <x-ui.card-header>
                    <h3 class="text-lg font-semibold text-slate-900">Upload CSV File</h3>
                </x-ui.card-header>
                <x-ui.card-content>
                    <form method="POST" action="{{ route('clients.import') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <x-ui.label for="csv_file">CSV File</x-ui.label>
                            <div class="mt-2">
                                <input
                                    type="file"
                                    name="csv_file"
                                    id="csv_file"
                                    accept=".csv,.txt"
                                    required
                                    class="block w-full text-sm text-slate-900 border border-slate-300 rounded-lg cursor-pointer bg-slate-50 focus:outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900 file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800"
                                />
                            </div>
                            @error('csv_file')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-x-4 pt-4 border-t border-slate-200">
                            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('clients.index') }}'">
                                Cancel
                            </x-ui.button>
                            <x-ui.button type="submit" variant="default">
                                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Import Clients
                            </x-ui.button>
                        </div>
                    </form>
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
        </div>
    </div>
</x-app-layout>
