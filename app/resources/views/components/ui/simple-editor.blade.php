@props([
    'name' => 'content',
    'id' => null,
    'value' => '',
    'placeholder' => 'Start typing...',
    'minHeight' => '400',
    'clients' => [],
    'clientFieldId' => null,
])

@php
    $editorId = $id ?? 'editor-' . $name . '-' . uniqid();
    $uniqueId = uniqid('se_');
    $clientsJson = collect($clients)->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()->toJson();
    $heightNum = (int) preg_replace('/[^0-9]/', '', $minHeight);
    if ($heightNum < 300) $heightNum = 400;
    $locale = app()->getLocale();
    $tinymceLang = $locale === 'ro' ? 'ro' : null;
@endphp

{{-- Skeleton styles (inline — only renders on pages with this component) --}}
<style>
    .simple-editor-skeleton {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .simple-editor-skeleton-toolbar {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 6px 8px;
        display: flex;
        gap: 6px;
        align-items: center;
    }
    .simple-editor-skeleton-btn {
        width: 28px;
        height: 24px;
        background: #e2e8f0;
        border-radius: 4px;
        animation: se-pulse 1.5s ease-in-out infinite;
    }
    .simple-editor-skeleton-btn.wide { width: 72px; }
    .simple-editor-skeleton-sep {
        width: 1px;
        height: 20px;
        background: #e2e8f0;
        margin: 0 2px;
    }
    .simple-editor-skeleton-body {
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 13px;
    }
    @@keyframes se-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
</style>

<div class="simple-editor-wrapper" id="wrapper-{{ $uniqueId }}">
    {{-- Loading skeleton (shown until TinyMCE is ready) --}}
    <div id="skeleton-{{ $uniqueId }}" class="simple-editor-skeleton">
        <div class="simple-editor-skeleton-toolbar">
            <div class="simple-editor-skeleton-btn wide"></div>
            <div class="simple-editor-skeleton-sep"></div>
            <div class="simple-editor-skeleton-btn"></div>
            <div class="simple-editor-skeleton-btn"></div>
            <div class="simple-editor-skeleton-btn"></div>
            <div class="simple-editor-skeleton-sep"></div>
            <div class="simple-editor-skeleton-btn"></div>
            <div class="simple-editor-skeleton-btn"></div>
            <div class="simple-editor-skeleton-sep"></div>
            <div class="simple-editor-skeleton-btn"></div>
            <div class="simple-editor-skeleton-btn"></div>
        </div>
        <div class="simple-editor-skeleton-body" style="height: {{ $heightNum - 42 }}px">
            {{ __('Loading editor...') }}
        </div>
    </div>

    {{-- Hidden textarea that TinyMCE will enhance --}}
    <textarea
        name="{{ $name }}"
        id="{{ $editorId }}"
        class="tinymce-editor"
        style="display:none"
        placeholder="{{ $placeholder }}"
    >{!! $value !!}</textarea>

    <!-- Client Detection Banner -->
    @if(count($clients) > 0)
    <div
        id="client-banner-{{ $uniqueId }}"
        class="hidden mt-3 flex items-center justify-between gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg"
    >
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
            <button type="button" id="reject-client-{{ $uniqueId }}" class="px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-md">
                {{ __('No') }}
            </button>
            <button type="button" id="accept-client-{{ $uniqueId }}" class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                {{ __('Yes') }}
            </button>
        </div>
    </div>
    @endif
</div>

<script>
(function() {
    var uniqueId = '{{ $uniqueId }}';
    var editorId = '{{ $editorId }}';
    var editorHeight = {{ $heightNum }};
    var placeholder = '{{ $placeholder }}';
    var clients = {!! $clientsJson !!};
    var clientFieldId = '{{ $clientFieldId }}';
    var tinymceLang = {!! json_encode($tinymceLang) !!};

    var editor = null;
    var detectedClient = null;
    var dismissedClients = [];
    var debounceTimer = null;

    /**
     * Load TinyMCE on-demand via a shared Promise.
     * First editor on the page creates the <script> element;
     * subsequent editors reuse the same promise — no polling.
     */
    function loadTinyMCE() {
        if (!window.__tinymceLoading) {
            window.__tinymceLoading = new Promise(function(resolve, reject) {
                if (typeof tinymce !== 'undefined') {
                    resolve();
                    return;
                }
                var script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js';
                script.referrerPolicy = 'origin';
                script.onload = resolve;
                script.onerror = function() {
                    reject(new Error('Failed to load TinyMCE'));
                };
                document.head.appendChild(script);
            });
        }
        return window.__tinymceLoading;
    }

    function initEditor() {
        // Remove existing instance if any
        var existing = tinymce.get(editorId);
        if (existing) {
            existing.remove();
        }

        var config = {
            selector: '#' + editorId,
            height: editorHeight,
            menubar: false,
            plugins: 'lists link autolink table code wordcount',
            toolbar: 'blocks fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link table | removeformat',
            toolbar_mode: 'sliding',
            placeholder: placeholder,
            content_style: 'body { font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; line-height: 1.6; padding: 0; margin: 8px; }',
            branding: false,
            promotion: false,
            statusbar: true,
            elementpath: false,
            resize: true,
            setup: function(ed) {
                editor = ed;

                // Remove skeleton and show editor once TinyMCE renders
                ed.on('init', function() {
                    var skeleton = document.getElementById('skeleton-' + uniqueId);
                    if (skeleton) skeleton.remove();
                    var textarea = document.getElementById(editorId);
                    if (textarea) textarea.style.display = '';
                });

                ed.on('input change keyup', function() {
                    // Client detection
                    if (clients.length > 0) {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(detectClient, 500);
                    }
                });
            }
        };

        // Add language support if not English
        if (tinymceLang) {
            config.language = tinymceLang;
            config.language_url = 'https://cdn.jsdelivr.net/npm/tinymce-i18n@24.10.7/langs6/' + tinymceLang + '.js';
        }

        tinymce.init(config);

        // Client detection buttons
        var acceptBtn = document.getElementById('accept-client-' + uniqueId);
        var rejectBtn = document.getElementById('reject-client-' + uniqueId);

        if (acceptBtn) {
            acceptBtn.addEventListener('click', acceptClient);
        }
        if (rejectBtn) {
            rejectBtn.addEventListener('click', rejectClient);
        }

        // Store globally
        window.simpleEditors = window.simpleEditors || {};
        window.simpleEditors[uniqueId] = {
            getEditor: function() { return tinymce.get(editorId); },
            getContent: function() { var ed = tinymce.get(editorId); return ed ? ed.getContent() : ''; },
            setContent: function(html) { var ed = tinymce.get(editorId); if (ed) ed.setContent(html); },
            clearContent: function() { var ed = tinymce.get(editorId); if (ed) ed.setContent(''); }
        };
    }

    function detectClient() {
        var banner = document.getElementById('client-banner-' + uniqueId);
        var clientNameEl = document.getElementById('client-name-' + uniqueId);
        if (!banner || !editor) return;

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

        var text = editor.getContent({ format: 'text' }).toLowerCase();
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

    function acceptClient() {
        if (!detectedClient) return;
        var banner = document.getElementById('client-banner-' + uniqueId);

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
    }

    function rejectClient() {
        var banner = document.getElementById('client-banner-' + uniqueId);
        if (detectedClient) {
            dismissedClients.push(detectedClient.id);
        }
        detectedClient = null;
        banner.classList.add('hidden');
    }

    // Load TinyMCE on-demand, then initialize — no polling
    function boot() {
        loadTinyMCE().then(initEditor).catch(function(err) {
            console.error('[SimpleEditor] ' + err.message);
            // Fallback: show plain textarea if CDN fails
            var skeleton = document.getElementById('skeleton-' + uniqueId);
            if (skeleton) skeleton.remove();
            var textarea = document.getElementById(editorId);
            if (textarea) {
                textarea.style.display = '';
                textarea.style.width = '100%';
                textarea.style.minHeight = editorHeight + 'px';
                textarea.style.border = '1px solid #e2e8f0';
                textarea.style.borderRadius = '0.5rem';
                textarea.style.padding = '12px';
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
