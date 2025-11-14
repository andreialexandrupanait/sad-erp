<x-app-layout>
    <x-slot name="pageTitle">{{ __('Import / Export') }}</x-slot>

    <div class="p-6">
        <!-- Success Message -->
        @if (session('success'))
            <x-ui.alert variant="success" class="mb-6">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- Import Errors -->
        @if(session('import_errors') && count(session('import_errors')) > 0)
            <x-ui.card class="mb-6 border-yellow-200 bg-yellow-50">
                <x-ui.card-header>
                    <h3 class="text-lg font-semibold text-yellow-900">Import Warnings</h3>
                </x-ui.card-header>
                <x-ui.card-content>
                    <ul class="list-disc list-inside space-y-1 text-sm text-yellow-700">
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.card-content>
            </x-ui.card>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Clients -->
            <x-ui.card>
                <x-ui.card-header>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Clients</h3>
                    </div>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="text-sm text-slate-600 mb-4">Import or export client data including company information, contact details, and tax IDs</p>
                    <div class="flex gap-2">
                        <x-ui.button variant="outline" class="flex-1" onclick="window.location.href='{{ route('import-export.import.form', 'clients') }}'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            Import
                        </x-ui.button>
                        <x-ui.button variant="outline" class="flex-1" onclick="window.location.href='{{ route('import-export.export', 'clients') }}'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export
                        </x-ui.button>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Revenues -->
            <x-ui.card>
                <x-ui.card-header>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Revenues</h3>
                    </div>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="text-sm text-slate-600 mb-4">Import or export revenue entries with invoice details, amounts, and client associations</p>
                    <div class="flex gap-2">
                        <x-ui.button variant="outline" class="flex-1" onclick="window.location.href='{{ route('import-export.import.form', 'revenues') }}'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            Import
                        </x-ui.button>
                        <x-ui.button variant="outline" class="flex-1" onclick="window.location.href='{{ route('import-export.export', 'revenues') }}'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export
                        </x-ui.button>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Expenses -->
            <x-ui.card>
                <x-ui.card-header>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Expenses</h3>
                    </div>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="text-sm text-slate-600 mb-4">Import or export expense entries with categories, amounts, and supporting documents</p>
                    <div class="flex gap-2">
                        <x-ui.button variant="outline" class="flex-1" onclick="window.location.href='{{ route('import-export.import.form', 'expenses') }}'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            Import
                        </x-ui.button>
                        <x-ui.button variant="outline" class="flex-1" onclick="window.location.href='{{ route('import-export.export', 'expenses') }}'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export
                        </x-ui.button>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Coming Soon modules -->
            <x-ui.card class="opacity-60">
                <x-ui.card-header>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Subscriptions</h3>
                    </div>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="text-sm text-slate-600 mb-4">Import/export coming soon...</p>
                </x-ui.card-content>
            </x-ui.card>

            <x-ui.card class="opacity-60">
                <x-ui.card-header>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-100 rounded-lg">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Domains</h3>
                    </div>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="text-sm text-slate-600 mb-4">Import/export coming soon...</p>
                </x-ui.card-content>
            </x-ui.card>

            <x-ui.card class="opacity-60">
                <x-ui.card-header>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Credentials</h3>
                    </div>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="text-sm text-slate-600 mb-4">Import/export coming soon...</p>
                </x-ui.card-content>
            </x-ui.card>

        </div>
    </div>
</x-app-layout>
