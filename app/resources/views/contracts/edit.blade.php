<x-app-layout>
    <x-slot name="pageTitle">{{ __('Edit Contract') }}: {{ $contract->formatted_number }}</x-slot>

    <x-slot name="headerActions">
        <div class="flex items-center gap-2">
            <x-ui.button variant="outline" onclick="window.location.href='{{ route('contracts.show', $contract) }}'">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Back') }}
            </x-ui.button>
            <x-ui.button variant="primary" onclick="saveContract()">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('Save') }}
            </x-ui.button>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        {{-- Status Messages --}}
        <div id="status-message" class="mb-4 hidden"></div>

        {{-- Contract Info --}}
        <div class="mb-4 p-4 bg-white rounded-lg shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div>
                        <span class="text-sm text-slate-500">{{ __('Contract') }}:</span>
                        <span class="font-medium text-slate-900">{{ $contract->formatted_number }}</span>
                    </div>
                    @if($contract->client)
                        <div>
                            <span class="text-sm text-slate-500">{{ __('Client') }}:</span>
                            <span class="font-medium text-slate-900">{{ $contract->client->display_name }}</span>
                        </div>
                    @endif
                    <div>
                        <span class="text-sm text-slate-500">{{ __('Status') }}:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            @if($contract->status === 'draft') bg-slate-100 text-slate-700
                            @elseif($contract->status === 'active') bg-green-100 text-green-700
                            @else bg-slate-100 text-slate-700 @endif">
                            {{ $contract->status_label ?? ucfirst($contract->status) }}
                        </span>
                    </div>
                </div>
                <div id="save-status" class="text-sm text-green-600">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Saved') }}
                </div>
            </div>
        </div>

        {{-- Editor --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 md:p-6">
                <textarea id="contract-content" class="w-full">{{ $contract->content }}</textarea>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let hasUnsavedChanges = false;

        // Load TinyMCE on-demand (shared promise with other components)
        function loadTinyMCE() {
            if (!window.__tinymceLoading) {
                window.__tinymceLoading = new Promise(function(resolve, reject) {
                    if (typeof tinymce !== 'undefined') { resolve(); return; }
                    var s = document.createElement('script');
                    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js';
                    s.referrerPolicy = 'origin';
                    s.onload = resolve;
                    s.onerror = function() { reject(new Error('Failed to load TinyMCE')); };
                    document.head.appendChild(s);
                });
            }
            return window.__tinymceLoading;
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadTinyMCE().then(function() {

            tinymce.init({
                selector: '#contract-content',
                height: 600,
                menubar: true,
                plugins: [
                    'lists', 'link', 'table', 'code', 'fullscreen',
                    'searchreplace', 'wordcount', 'visualblocks'
                ],
                toolbar: [
                    'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough',
                    'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
                    'link table | removeformat code fullscreen'
                ],
                content_style: `
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                        font-size: 14px;
                        line-height: 1.6;
                        padding: 1rem;
                    }
                    p { margin: 0 0 1em 0; }
                    h1, h2, h3 { margin: 1em 0 0.5em 0; }
                    table { border-collapse: collapse; width: 100%; }
                    table td, table th { border: 1px solid #ccc; padding: 8px; }
                `,
                branding: false,
                promotion: false,
                resize: true,
                statusbar: true,
                elementpath: false,
                setup: function(editor) {
                    editor.on('change', function() {
                        markUnsaved();
                    });
                }
            });

            // Keyboard shortcut: Ctrl+S to save
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    saveContract();
                }
            });
            }).catch(function(e) { console.error('[ContractEdit]', e); });
        });

        function markUnsaved() {
            hasUnsavedChanges = true;
            document.getElementById('save-status').innerHTML = `
                <svg class="w-4 h-4 inline text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="text-amber-600">{{ __('Unsaved changes') }}</span>
            `;
        }

        function markSaved() {
            hasUnsavedChanges = false;
            document.getElementById('save-status').innerHTML = `
                <svg class="w-4 h-4 inline text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-green-600">{{ __('Saved') }}</span>
            `;
        }

        async function saveContract() {
            const editor = tinymce.get('contract-content');
            if (!editor) {
                return;
            }

            const content = editor.getContent();

            document.getElementById('save-status').innerHTML = `
                <svg class="w-4 h-4 inline animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-blue-600">{{ __('Saving...') }}</span>
            `;

            try {
                const response = await fetch('{{ route('contracts.update-content', $contract) }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ content: content })
                });

                const data = await response.json();

                if (data.success) {
                    markSaved();
                    showMessage('success', '{{ __('Contract saved successfully.') }}');
                } else {
                    throw new Error(data.message || 'Save failed');
                }
            } catch (error) {
                document.getElementById('save-status').innerHTML = `
                    <svg class="w-4 h-4 inline text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span class="text-red-600">{{ __('Save failed') }}</span>
                `;
                showMessage('error', error.message);
            }
        }

        function showMessage(type, message) {
            const el = document.getElementById('status-message');
            el.className = 'mb-4 p-4 rounded-lg ' + (type === 'success'
                ? 'bg-green-50 border border-green-200 text-green-700'
                : 'bg-red-50 border border-red-200 text-red-700');
            el.textContent = message;
            el.classList.remove('hidden');
            setTimeout(() => el.classList.add('hidden'), 3000);
        }

        window.addEventListener('beforeunload', function(e) {
            if (hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
    @endpush
</x-app-layout>
