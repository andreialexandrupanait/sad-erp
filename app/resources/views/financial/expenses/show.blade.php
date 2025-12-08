<x-app-layout>
    <x-slot name="pageTitle">Detalii cheltuială</x-slot>

    <x-slot name="headerActions">
        <div class="flex gap-2">
            <x-ui.button variant="outline" onclick="window.location.href='{{ route('financial.expenses.index') }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Back') }}
            </x-ui.button>
            <x-ui.button variant="default" onclick="window.location.href='{{ route('financial.expenses.edit', $expense) }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                {{ __('Edit') }}
            </x-ui.button>
        </div>
    </x-slot>

    <div class="p-6">
        <div class="max-w-4xl mx-auto">
            <x-ui.card>
                <x-ui.card-content>
                    <div class="space-y-6">
                        <!-- Header Section -->
                        <div class="border-b border-slate-200 pb-4">
                            <h2 class="text-2xl font-bold text-slate-900">{{ $expense->document_name }}</h2>
                            <p class="text-sm text-slate-500 mt-1">{{ __('Expense Details') }}</p>
                        </div>

                        <!-- Main Details Grid -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Document Name -->
                            <div>
                                <label class="text-sm font-medium text-slate-500">{{ __('Document') }}</label>
                                <p class="mt-1 text-base text-slate-900">{{ $expense->document_name }}</p>
                            </div>

                            <!-- Amount -->
                            <div>
                                <label class="text-sm font-medium text-slate-500">{{ __('Amount') }}</label>
                                <p class="mt-1 text-2xl font-bold text-red-600">{{ number_format($expense->amount, 2) }} {{ $expense->currency }}</p>
                            </div>

                            <!-- Date -->
                            <div>
                                <label class="text-sm font-medium text-slate-500">{{ __('Date') }}</label>
                                <p class="mt-1 text-base text-slate-900">{{ $expense->occurred_at->format('d M Y') }}</p>
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="text-sm font-medium text-slate-500">{{ __('Category') }}</label>
                                <p class="mt-1">
                                    @if($expense->category)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm {{ $expense->category->badge_class }}">
                                            {{ $expense->category->label }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </p>
                            </div>

                            <!-- Year & Month -->
                            <div>
                                <label class="text-sm font-medium text-slate-500">{{ __('Period') }}</label>
                                <p class="mt-1 text-base text-slate-900">
                                    {{ \Carbon\Carbon::create($expense->year, $expense->month, 1)->format('F Y') }}
                                </p>
                            </div>

                            <!-- Currency -->
                            <div>
                                <label class="text-sm font-medium text-slate-500">{{ __('Currency') }}</label>
                                <p class="mt-1 text-base text-slate-900">{{ $expense->currency }}</p>
                            </div>
                        </div>

                        <!-- Notes -->
                        @if($expense->note)
                            <div class="pt-4 border-t border-slate-200">
                                <label class="text-sm font-medium text-slate-500">{{ __('Note') }}</label>
                                <p class="mt-2 text-base text-slate-700 whitespace-pre-wrap">{{ $expense->note }}</p>
                            </div>
                        @endif

                        <!-- Attached Files -->
                        @if($expense->files->isNotEmpty())
                            <div class="pt-4 border-t border-slate-200">
                                <label class="text-sm font-medium text-slate-500 mb-3 block">{{ __('Attached files') }} ({{ $expense->files->count() }})</label>
                                <div class="space-y-2">
                                    @foreach($expense->files as $file)
                                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors">
                                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                                <span class="text-2xl flex-shrink-0">{{ $file->icon }}</span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-slate-900 truncate">{{ $file->file_name }}</p>
                                                    <div class="flex items-center gap-3 mt-1">
                                                        <span class="text-xs text-slate-500">{{ number_format($file->file_size / 1024, 2) }} KB</span>
                                                        <span class="text-xs text-slate-400">•</span>
                                                        <span class="text-xs text-slate-500">{{ strtoupper(pathinfo($file->file_name, PATHINFO_EXTENSION)) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 ml-4">
                                                <!-- View/Preview -->
                                                <a href="{{ route('financial.files.show', $file) }}" target="_blank" class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors" title="{{ __('View') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                <!-- Download -->
                                                <a href="{{ route('financial.files.download', $file) }}" class="p-2 text-green-600 hover:text-green-900 hover:bg-green-50 rounded-lg transition-colors" title="{{ __('Download') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </a>
                                                <!-- Copy Link -->
                                                <button type="button" onclick="copyToClipboard('{{ route('financial.files.show', $file) }}')" class="p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition-colors" title="{{ __('Copy link') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="pt-4 border-t border-slate-200">
                                <label class="text-sm font-medium text-slate-500">{{ __('Attached files') }}</label>
                                <p class="mt-2 text-sm text-slate-400">{{ __('No files attached') }}</p>
                            </div>
                        @endif

                        <!-- Metadata -->
                        <div class="pt-4 border-t border-slate-200">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-xs text-slate-500">
                                <div>
                                    <span class="font-medium">{{ __('Created at') }}:</span>
                                    <span class="ml-2">{{ $expense->created_at->format('d M Y, H:i') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">{{ __('Updated at') }}:</span>
                                    <span class="ml-2">{{ $expense->updated_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card-content>

                <!-- Action Footer -->
                <div class="flex items-center justify-between border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
                    <form method="POST" action="{{ route('financial.expenses.destroy', $expense) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this expense?') }}')">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="ghost" class="text-red-600 hover:text-red-900 hover:bg-red-50">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            {{ __('Delete') }}
                        </x-ui.button>
                    </form>
                    <x-ui.button variant="default" onclick="window.location.href='{{ route('financial.expenses.edit', $expense) }}'">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        {{ __('Edit') }}
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </div>

    <script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('{{ __("Link copied to clipboard!") }}');
        }, function(err) {
            console.error('{{ __("Could not copy link") }}:', err);
        });
    }
    </script>

    <!-- Toast Notifications -->
    <x-toast />
</x-app-layout>
