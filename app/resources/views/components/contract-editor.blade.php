@props([
    'name' => 'content',
    'content' => '',
    'height' => 600,
    'variables' => [],
    'showVariables' => false
])

@php
    $editorId = 'contract-editor-' . $name . '-' . uniqid();
@endphp

<div class="flex gap-4">
    {{-- Editor --}}
    <div class="{{ $showVariables && count($variables) > 0 ? 'flex-1' : 'w-full' }}">
        <textarea name="{{ $name }}" id="{{ $editorId }}" class="w-full">{{ $content }}</textarea>
    </div>

    {{-- Variables Sidebar --}}
    @if($showVariables && count($variables) > 0)
        <div class="w-64 flex-shrink-0">
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm sticky top-4">
                <div class="p-3 border-b border-slate-200 bg-slate-50 rounded-t-lg">
                    <h3 class="font-semibold text-sm text-slate-900">{{ __('Variables') }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('Click to insert') }}</p>
                </div>
                <div class="max-h-96 overflow-y-auto p-2">
                    @foreach($variables as $category => $vars)
                        <div class="mb-2" x-data="{ open: {{ in_array($category, ['client', 'contract']) ? 'true' : 'false' }} }">
                            <button
                                type="button"
                                @click="open = !open"
                                class="w-full px-2 py-1.5 text-left flex items-center justify-between bg-slate-50 rounded hover:bg-slate-100 text-xs font-semibold text-slate-600 uppercase"
                            >
                                <span>
                                    @if($category === 'client') {{ __('Client') }}
                                    @elseif($category === 'contract') {{ __('Contract') }}
                                    @elseif($category === 'organization') {{ __('Organization') }}
                                    @elseif($category === 'special') {{ __('Special') }}
                                    @else {{ ucfirst($category) }}
                                    @endif
                                </span>
                                <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-collapse class="mt-1 space-y-0.5">
                                @foreach($vars as $key => $label)
                                    <button
                                        type="button"
                                        onclick="insertVariable_{{ str_replace('-', '_', $editorId) }}('{{ $key }}')"
                                        class="w-full px-2 py-1 text-left text-xs hover:bg-blue-50 rounded transition-colors flex justify-between items-center group"
                                    >
                                        <span class="text-slate-700 group-hover:text-blue-700 truncate">{{ $label }}</span>
                                        <code class="text-[10px] text-slate-400 bg-slate-100 px-1 rounded ml-1 flex-shrink-0 group-hover:bg-blue-100">{!! sprintf('{{%s}}', $key) !!}</code>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
(function() {
    const editorId = '{{ $editorId }}';
    const height = {{ $height }};

    /**
     * Load TinyMCE on-demand via a shared Promise.
     * Reuses the same loader as simple-editor component.
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
        const existing = tinymce.get(editorId);
        if (existing) {
            existing.remove();
        }

        tinymce.init({
            selector: '#' + editorId,
            height: height,
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
                    editor.save(); // Sync to textarea
                });
            }
        });
    }

    // Variable insertion function
    window['insertVariable_{{ str_replace('-', '_', $editorId) }}'] = function(varName) {
        const editor = tinymce.get(editorId);
        if (editor) {
            editor.insertContent('{{' + varName + '}}');
            editor.focus();
        }
    };

    // Load TinyMCE on-demand, then initialize
    function boot() {
        loadTinyMCE().then(initEditor).catch(function(err) {
            console.error('[ContractEditor] ' + err.message);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
@endpush
