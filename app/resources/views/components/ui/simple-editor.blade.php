@props([
    'name' => 'content',
    'id' => null,
    'value' => '',
    'placeholder' => 'Start typing...',
    'minHeight' => '80px',
    'clients' => [],
    'clientFieldId' => null,
])

@php
    $editorId = $id ?? 'editor-' . $name . '-' . uniqid();
    $uniqueId = uniqid('se_');
    $clientsJson = collect($clients)->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()->toJson();
@endphp

<div class="simple-editor-wrapper relative" id="wrapper-{{ $uniqueId }}">
    <input type="hidden" name="{{ $name }}" id="hidden-{{ $uniqueId }}" value="{{ $value }}">

    <div class="border border-slate-200 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500">
        <!-- Toolbar -->
        <div id="toolbar-{{ $uniqueId }}" class="flex flex-wrap items-center gap-1 px-2 py-1.5 bg-slate-50 border-b border-slate-200">
            <button type="button" class="ql-bold px-2 py-1 rounded hover:bg-slate-200 text-slate-600 text-xs font-medium" title="Bold">B</button>
            <button type="button" class="ql-italic px-2 py-1 rounded hover:bg-slate-200 text-slate-600 text-xs" title="Italic"><em>I</em></button>
            <button type="button" class="ql-underline px-2 py-1 rounded hover:bg-slate-200 text-slate-600 text-xs" title="Underline"><u>U</u></button>
            <button type="button" class="ql-strike px-2 py-1 rounded hover:bg-slate-200 text-slate-600 text-xs" title="Strike"><s>S</s></button>
            <span class="w-px h-4 bg-slate-300 mx-1"></span>
            <button type="button" class="ql-list px-2 py-1 rounded hover:bg-slate-200 text-slate-600 text-xs" value="bullet" title="Bullet List">• List</button>
            <button type="button" class="ql-list px-2 py-1 rounded hover:bg-slate-200 text-slate-600 text-xs" value="ordered" title="Numbered List">1. List</button>
            <span class="w-px h-4 bg-slate-300 mx-1"></span>
            <button type="button" class="ql-link px-2 py-1 rounded hover:bg-slate-200 text-slate-600 text-xs" title="Link">Link</button>
            <button type="button" class="ql-blockquote px-2 py-1 rounded hover:bg-slate-200 text-slate-600 text-xs" title="Quote">Quote</button>
            <span class="w-px h-4 bg-slate-300 mx-1"></span>
            <button type="button" class="ql-clean px-2 py-1 rounded hover:bg-slate-200 text-slate-600 text-xs" title="Clear">Clear</button>
        </div>

        <!-- Editor -->
        <div id="editor-{{ $uniqueId }}" style="min-height: {{ $minHeight }}; background: white;"></div>
    </div>

    <!-- Client Detection Banner -->
    <div id="client-banner-{{ $uniqueId }}" class="hidden mt-3 flex items-center justify-between gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-blue-900">
                    {{ __('Client detected:') }} <span id="client-name-{{ $uniqueId }}" class="font-semibold"></span>
                </p>
                <p class="text-xs text-blue-700">{{ __('Assign this note to this client?') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="simpleEditors['{{ $uniqueId }}'].rejectClient()" class="px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-md">
                {{ __('No') }}
            </button>
            <button type="button" onclick="simpleEditors['{{ $uniqueId }}'].acceptClient()" class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                {{ __('Yes') }}
            </button>
        </div>
    </div>
</div>

<style>
#wrapper-{{ $uniqueId }} .ql-toolbar.ql-snow { display: none !important; }
#wrapper-{{ $uniqueId }} .ql-container.ql-snow { border: none !important; font-family: inherit; font-size: 0.875rem; }
#wrapper-{{ $uniqueId }} .ql-editor { padding: 0.5rem 0.75rem; min-height: {{ $minHeight }}; }
#wrapper-{{ $uniqueId }} .ql-editor.ql-blank::before { color: #94a3b8; font-style: normal; }
#wrapper-{{ $uniqueId }} .ql-editor p { margin-bottom: 0.25em; }
#wrapper-{{ $uniqueId }} button.ql-active { background-color: #e2e8f0 !important; }
</style>

<script>
(function() {
    window.simpleEditors = window.simpleEditors || {};

    var uniqueId = '{{ $uniqueId }}';
    var clients = {!! $clientsJson !!};
    var clientFieldId = '{{ $clientFieldId }}';
    var initialValue = {!! json_encode($value) !!};

    function initEditor() {
        if (typeof Quill === 'undefined') {
            setTimeout(initEditor, 100);
            return;
        }

        var quill = new Quill('#editor-' + uniqueId, {
            theme: 'snow',
            modules: { toolbar: '#toolbar-' + uniqueId },
            placeholder: '{{ $placeholder }}'
        });

        var hiddenInput = document.getElementById('hidden-' + uniqueId);
        var banner = document.getElementById('client-banner-' + uniqueId);
        var clientNameEl = document.getElementById('client-name-' + uniqueId);
        var detectedClient = null;
        var dismissedClients = [];
        var debounceTimer = null;

        // Set initial value
        if (initialValue && initialValue.trim()) {
            quill.clipboard.dangerouslyPasteHTML(0, initialValue);
        }

        quill.on('text-change', function() {
            hiddenInput.value = quill.root.innerHTML;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(detectClient, 500);
        });

        function detectClient() {
            // Check if client already selected
            if (clientFieldId) {
                var clientInput = document.querySelector('input[name="client_id"]');
                if (clientInput) {
                    var wrapper = clientInput.closest('[x-data]');
                    if (wrapper && window.Alpine) {
                        var data = Alpine.$data(wrapper);
                        if (data && data.selectedValue) {
                            banner.classList.add('hidden');
                            return;
                        }
                    }
                }
            }

            var text = quill.getText().toLowerCase();
            if (text.length < 3) {
                banner.classList.add('hidden');
                return;
            }

            var bestMatch = null;
            var bestScore = 0;

            for (var i = 0; i < clients.length; i++) {
                var client = clients[i];
                if (dismissedClients.indexOf(client.id) !== -1) continue;

                var name = client.name.toLowerCase();
                if (text.indexOf(name) !== -1) {
                    var score = name.length * 2;
                    if (score > bestScore) {
                        bestScore = score;
                        bestMatch = client;
                    }
                }
            }

            if (bestMatch && bestScore >= 6) {
                detectedClient = bestMatch;
                clientNameEl.textContent = bestMatch.name;
                banner.classList.remove('hidden');
            } else {
                detectedClient = null;
                banner.classList.add('hidden');
            }
        }

        window.simpleEditors[uniqueId] = {
            quill: quill,
            acceptClient: function() {
                if (!detectedClient) return;
                var clientInput = document.querySelector('input[name="client_id"]');
                if (clientInput) {
                    var wrapper = clientInput.closest('[x-data]');
                    if (wrapper && window.Alpine) {
                        var data = Alpine.$data(wrapper);
                        if (data) {
                            data.selectedValue = String(detectedClient.id);
                            data.selectedLabel = detectedClient.name;
                        }
                    }
                }
                detectedClient = null;
                banner.classList.add('hidden');
            },
            rejectClient: function() {
                if (detectedClient) {
                    dismissedClients.push(detectedClient.id);
                }
                detectedClient = null;
                banner.classList.add('hidden');
            }
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditor);
    } else {
        initEditor();
    }
})();
</script>
