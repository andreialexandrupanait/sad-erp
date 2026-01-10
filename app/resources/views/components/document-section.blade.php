@props(['documentable', 'type' => 'contract'])

@php
    // Load document relationships if not already loaded
    if (!$documentable->relationLoaded('activeDraftFile')) {
        $documentable->load(['activeDraftFile', 'activeSignedFile', 'documentFiles']);
    }

    $activeDraft = $documentable->activeDraftFile;
    $activeSigned = $documentable->activeSignedFile;

    // Get all versions for display
    $draftVersions = $documentable->documentFiles
        ->where('document_type', 'draft')
        ->sortByDesc('version');
    $signedVersions = $documentable->documentFiles
        ->where('document_type', 'signed')
        ->sortByDesc('version');

    // Determine upload route based on type
    if ($type === 'contract') {
        $uploadRoute = route('contracts.upload-signed', $documentable);
        $regenerateRoute = route('contracts.regenerate-draft', $documentable);
    } else {
        // Annex
        $uploadRoute = route('contracts.annexes.upload-signed', [$documentable->contract, $documentable]);
        $regenerateRoute = null;
    }
@endphp

<x-ui.card x-data="documentSection()">
    <x-ui.card-header>
        <h3 class="font-semibold text-slate-900">{{ __('Documents') }}</h3>
    </x-ui.card-header>
    <x-ui.card-content class="space-y-4">
        {{-- Draft Document Section --}}
        <div class="space-y-2">
            <h4 class="text-sm font-medium text-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('Draft') }}
            </h4>
            
            @if($activeDraft)
                <div class="bg-slate-50 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ __('Version') }} {{ $activeDraft->version }}</p>
                                <p class="text-xs text-slate-500">{{ $activeDraft->created_at->format('d.m.Y H:i') }} &bull; {{ $activeDraft->file_size_formatted }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('documents.view', $activeDraft) }}" target="_blank"
                               class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded" title="{{ __('View') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('documents.download', $activeDraft) }}"
                               class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded" title="{{ __('Download') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    {{-- Previous draft versions --}}
                    @if($draftVersions->count() > 1)
                        <div class="mt-2 pt-2 border-t border-slate-200" x-data="{ showDraftVersions: false }">
                            <button @click="showDraftVersions = !showDraftVersions" 
                                    class="text-xs text-slate-500 hover:text-slate-700 flex items-center gap-1">
                                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-90': showDraftVersions }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                {{ __('Previous versions') }} ({{ $draftVersions->count() - 1 }})
                            </button>
                            <div x-show="showDraftVersions" x-collapse class="mt-2 space-y-1">
                                @foreach($draftVersions->skip(1) as $version)
                                    <div class="flex items-center justify-between text-xs py-1">
                                        <span class="text-slate-500">v{{ $version->version }} &bull; {{ $version->created_at->format('d.m.Y H:i') }}</span>
                                        <a href="{{ route('documents.download', $version) }}" class="text-blue-600 hover:text-blue-800">{{ __('Download') }}</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                
                @if($regenerateRoute)
                    <form action="{{ $regenerateRoute }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1"
                                onclick="return confirm('{{ __('This will create a new draft version. Continue?') }}')">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            {{ __('Regenerate PDF') }}
                        </button>
                    </form>
                @endif
            @else
                <div class="bg-slate-50 rounded-lg p-3 text-center">
                    <p class="text-sm text-slate-500">{{ __('No draft document generated') }}</p>
                    @if($regenerateRoute)
                        <form action="{{ $regenerateRoute }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="text-xs text-blue-600 hover:text-blue-800">
                                {{ __('Generate PDF') }}
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
        
        {{-- Signed Document Section --}}
        <div class="space-y-2 pt-3 border-t border-slate-200">
            <h4 class="text-sm font-medium text-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('Signed Document') }}
            </h4>
            
            @if($activeSigned)
                <div class="bg-green-50 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ __('Version') }} {{ $activeSigned->version }}</p>
                                <p class="text-xs text-slate-500">{{ $activeSigned->created_at->format('d.m.Y H:i') }} &bull; {{ $activeSigned->file_size_formatted }}</p>
                                @if($activeSigned->original_filename)
                                    <p class="text-xs text-slate-400">{{ $activeSigned->original_filename }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('documents.view', $activeSigned) }}" target="_blank"
                               class="p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-100 rounded" title="{{ __('View') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('documents.download', $activeSigned) }}"
                               class="p-1.5 text-slate-400 hover:text-green-600 hover:bg-green-100 rounded" title="{{ __('Download') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    {{-- Previous signed versions --}}
                    @if($signedVersions->count() > 1)
                        <div class="mt-2 pt-2 border-t border-green-200" x-data="{ showSignedVersions: false }">
                            <button @click="showSignedVersions = !showSignedVersions" 
                                    class="text-xs text-green-600 hover:text-green-800 flex items-center gap-1">
                                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-90': showSignedVersions }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                {{ __('Previous versions') }} ({{ $signedVersions->count() - 1 }})
                            </button>
                            <div x-show="showSignedVersions" x-collapse class="mt-2 space-y-1">
                                @foreach($signedVersions->skip(1) as $version)
                                    <div class="flex items-center justify-between text-xs py-1">
                                        <span class="text-slate-500">v{{ $version->version }} &bull; {{ $version->created_at->format('d.m.Y H:i') }}</span>
                                        <a href="{{ route('documents.download', $version) }}" class="text-green-600 hover:text-green-800">{{ __('Download') }}</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                
                <button @click="showUploadModal = true" class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    {{ __('Upload new version') }}
                </button>
            @else
                <div class="bg-amber-50 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-sm text-amber-700">{{ __('No signed document uploaded') }}</p>
                    </div>
                    <button @click="showUploadModal = true" 
                            class="mt-2 w-full px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        {{ __('Upload Signed Document') }}
                    </button>
                </div>
            @endif
        </div>
        
        {{-- Upload Modal --}}
        <div x-show="showUploadModal" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" @click="showUploadModal = false"></div>
                
                <div class="relative bg-white rounded-xl shadow-xl transform transition-all sm:max-w-md sm:w-full mx-auto"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @click.stop>
                    <form action="{{ $uploadRoute }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 rounded-t-xl">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-slate-900">{{ __('Upload Signed Document') }}</h3>
                                <button type="button" @click="showUploadModal = false" class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="relative border-2 border-dashed border-slate-300 rounded-lg p-6 text-center hover:border-blue-500 transition-colors"
                                 x-data="{ fileName: '' }"
                                 @dragover.prevent="$el.classList.add('border-blue-500', 'bg-blue-50')"
                                 @dragleave.prevent="$el.classList.remove('border-blue-500', 'bg-blue-50')"
                                 @drop.prevent="handleDrop($event)">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="mt-2 text-sm text-slate-600">{{ __('Drop PDF here or click to browse') }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ __('PDF only, max 20MB') }}</p>
                                <input type="file" name="signed_document" accept="application/pdf" required
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                       @change="fileName = $event.target.files[0]?.name">
                                <p x-show="fileName" x-text="fileName" class="mt-2 text-sm text-blue-600 font-medium"></p>
                            </div>
                            
                            <div class="flex items-start gap-2 p-3 bg-blue-50 rounded-lg">
                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-xs text-blue-700">{{ __('This will create a new version. Previous versions are preserved and can be accessed from the version history.') }}</p>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end gap-3">
                            <button type="button" @click="showUploadModal = false"
                                    class="px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                {{ __('Upload') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-ui.card-content>
</x-ui.card>

@push('scripts')
<script>
function documentSection() {
    return {
        showUploadModal: false,
        
        handleDrop(event) {
            event.target.classList.remove('border-blue-500', 'bg-blue-50');
            const files = event.dataTransfer.files;
            if (files.length > 0 && files[0].type === 'application/pdf') {
                const input = event.target.querySelector('input[type="file"]');
                input.files = files;
                input.dispatchEvent(new Event('change'));
            }
        }
    };
}
</script>
@endpush
