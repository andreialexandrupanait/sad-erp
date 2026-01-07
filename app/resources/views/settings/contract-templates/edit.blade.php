<x-app-layout>
    <x-slot name="pageTitle">{{ __('Edit Contract Template') }}: {{ $template->name }}</x-slot>

    <x-slot name="headerActions">
        <div class="flex items-center gap-2">
            <x-ui.button variant="outline" onclick="window.location.href='{{ route('settings.document-templates.index') }}'">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Back') }}
            </x-ui.button>
            <x-ui.button type="submit" form="template-form" variant="primary">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('Save') }}
            </x-ui.button>
        </div>
    </x-slot>

    <div class="h-[calc(100vh-4rem)] flex" x-data="contractTemplateEditor()" x-cloak>
        {{-- Main Editor Area --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- Template Details Bar --}}
            <div class="bg-white border-b border-slate-200 px-6 py-4">
                <form id="template-form" action="{{ route('settings.contract-templates.update', $template) }}" method="POST" class="flex items-center gap-4">
                    @csrf
                    @method('PUT')

                    <div class="flex-1 flex items-center gap-4">
                        <div class="flex-1 max-w-xs">
                            <label for="name" class="block text-xs font-medium text-slate-500 mb-1">{{ __('Template Name') }} *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $template->name) }}" required
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>

                        <div class="w-40">
                            <label for="category" class="block text-xs font-medium text-slate-500 mb-1">{{ __('Category') }} *</label>
                            <select name="category" id="category" required
                                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category', $template->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-4 ml-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_default" value="1" {{ old('is_default', $template->is_default) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="text-sm text-slate-600">{{ __('Default') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                                <span class="text-sm text-slate-600">{{ __('Active') }}</span>
                            </label>
                        </div>
                    </div>

                    {{-- Hidden content field - updated by Quill --}}
                    <input type="hidden" name="content" id="content-input" :value="content">
                </form>
            </div>

            {{-- Editor --}}
            <div class="flex-1 bg-slate-100 overflow-hidden p-6">
                <div class="h-full bg-white rounded-xl shadow-lg overflow-hidden flex flex-col">
                    {{-- Quill Toolbar --}}
                    <div id="toolbar" class="border-b border-slate-200 bg-slate-50">
                        <span class="ql-formats">
                            <select class="ql-header">
                                <option value="1">{{ __('Heading 1') }}</option>
                                <option value="2">{{ __('Heading 2') }}</option>
                                <option value="3">{{ __('Heading 3') }}</option>
                                <option selected>{{ __('Normal') }}</option>
                            </select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-bold" title="{{ __('Bold') }}"></button>
                            <button class="ql-italic" title="{{ __('Italic') }}"></button>
                            <button class="ql-underline" title="{{ __('Underline') }}"></button>
                            <button class="ql-strike" title="{{ __('Strikethrough') }}"></button>
                        </span>
                        <span class="ql-formats">
                            <select class="ql-color" title="{{ __('Text Color') }}"></select>
                            <select class="ql-background" title="{{ __('Background Color') }}"></select>
                        </span>
                        <span class="ql-formats">
                            <select class="ql-align" title="{{ __('Alignment') }}"></select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-list" value="ordered" title="{{ __('Numbered List') }}"></button>
                            <button class="ql-list" value="bullet" title="{{ __('Bullet List') }}"></button>
                            <button class="ql-indent" value="-1" title="{{ __('Decrease Indent') }}"></button>
                            <button class="ql-indent" value="+1" title="{{ __('Increase Indent') }}"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-link" title="{{ __('Link') }}"></button>
                            <button class="ql-image" title="{{ __('Image') }}"></button>
                        </span>
                        <span class="ql-formats">
                            <button type="button" @click="showTableModal = true" class="ql-table-insert" title="{{ __('Insert Table') }}">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-clean" title="{{ __('Clear Formatting') }}"></button>
                        </span>
                    </div>

                    {{-- Editor Container --}}
                    <div id="editor" class="flex-1 overflow-y-auto"></div>

                    {{-- Status Bar --}}
                    <div class="border-t border-slate-200 bg-slate-50 px-4 py-2 flex items-center justify-between text-xs text-slate-500">
                        <span>{{ __('Characters') }}: <span x-text="charCount">0</span></span>
                        <span>{{ __('Words') }}: <span x-text="wordCount">0</span></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Sidebar - Variables Panel --}}
        <div class="w-80 bg-white border-l border-slate-200 flex flex-col overflow-hidden">
            {{-- Variables Header --}}
            <div class="p-4 border-b border-slate-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    {{ __('Variables') }}
                </h3>
                <p class="text-xs text-slate-500 mt-1">{{ __('Click to insert at cursor position') }}</p>
            </div>

            {{-- Variables List --}}
            <div class="flex-1 overflow-y-auto">
                @foreach($variables as $category => $vars)
                <div class="border-b border-slate-100" x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
                    {{-- Category Header --}}
                    <button @click="open = !open"
                            class="w-full px-4 py-3 flex items-center justify-between hover:bg-slate-50 transition-colors">
                        <span class="flex items-center gap-2">
                            @if($category === 'client')
                                <span class="w-6 h-6 rounded-lg bg-purple-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                                <span class="font-medium text-slate-700">{{ __('Client') }}</span>
                            @elseif($category === 'contract')
                                <span class="w-6 h-6 rounded-lg bg-blue-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </span>
                                <span class="font-medium text-slate-700">{{ __('Contract') }}</span>
                            @elseif($category === 'organization')
                                <span class="w-6 h-6 rounded-lg bg-emerald-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </span>
                                <span class="font-medium text-slate-700">{{ __('Organization') }}</span>
                            @elseif($category === 'special')
                                <span class="w-6 h-6 rounded-lg bg-amber-100 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </span>
                                <span class="font-medium text-slate-700">{{ __('Special') }}</span>
                            @endif
                        </span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{ 'rotate-180': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Variables in Category --}}
                    <div x-show="open" x-collapse class="bg-slate-50/50">
                        @foreach($vars as $key => $config)
                        <button type="button"
                                @click="insertVariable('{{ $key }}')"
                                class="w-full px-4 py-2.5 text-left hover:bg-blue-50 transition-colors flex items-center justify-between group border-b border-slate-100 last:border-0">
                            <div class="flex-1 min-w-0">
                                <span class="text-sm text-slate-700 group-hover:text-blue-700 block truncate">
                                    {{ $config['label'] ?? $config['label_en'] ?? $key }}
                                </span>
                            </div>
                            <code class="text-xs bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded ml-2 font-mono flex-shrink-0 group-hover:bg-blue-100 group-hover:text-blue-700">{!! '{{' . $key . '}}' !!}</code>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Help Section --}}
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                <h4 class="text-xs font-semibold text-slate-500 uppercase mb-2">{{ __('Tips') }}</h4>
                <ul class="text-xs text-slate-600 space-y-1">
                    <li class="flex items-start gap-1.5">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span>{{ __('Variables are replaced when generating PDF') }}</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span>{{ __('Use Ctrl+S to save quickly') }}</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span>{{ __('Services list generates bullet points') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Table Insert Modal -->
        <div x-show="showTableModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
             @click.self="showTableModal = false"
             @keydown.escape.window="showTableModal = false">
            <div class="bg-white rounded-lg shadow-xl w-80 p-5" @click.stop>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Insert Table') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Rows') }}</label>
                        <input type="number" x-model.number="tableRows" min="1" max="20"
                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Columns') }}</label>
                        <input type="number" x-model.number="tableCols" min="1" max="10"
                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="tableWithHeader"
                               class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="text-sm text-slate-600">{{ __('Include header row') }}</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="showTableModal = false"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" @click="insertTable()"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        {{ __('Insert') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <style>
        #editor {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        #editor .ql-editor {
            font-size: 14px;
            line-height: 1.7;
            padding: 2rem;
            min-height: 100%;
        }
        #editor .ql-editor h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1e293b;
        }
        #editor .ql-editor h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #334155;
        }
        #editor .ql-editor h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #475569;
        }
        #editor .ql-editor p {
            margin-bottom: 0.75rem;
        }
        #editor .ql-editor ul, #editor .ql-editor ol {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }
        #toolbar {
            padding: 8px 12px;
            background: #f8fafc;
        }
        #toolbar .ql-formats {
            margin-right: 12px;
        }
        .ql-snow .ql-picker.ql-header {
            width: 120px;
        }
        /* Style variables in editor */
        #editor .ql-editor code {
            background: #dbeafe;
            color: #1e40af;
            padding: 2px 4px;
            border-radius: 4px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        /* Table button styling */
        .ql-table-insert {
            display: flex !important;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 24px;
            padding: 0;
            cursor: pointer;
            color: #444;
        }
        .ql-table-insert:hover {
            color: #06c;
        }
        .ql-table-insert svg {
            stroke: currentColor;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
    function contractTemplateEditor() {
        return {
            quill: null,
            content: @json(old('content', $template->content ?? '')),
            charCount: 0,
            wordCount: 0,

            // Table insertion state
            showTableModal: false,
            tableRows: 3,
            tableCols: 3,
            tableWithHeader: true,

            init() {
                this.$nextTick(() => {
                    this.initQuill();
                    this.setupKeyboardShortcuts();
                });
            },

            initQuill() {
                this.quill = new Quill('#editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: '#toolbar'
                    },
                    placeholder: '{{ __("Start writing your contract template...") }}'
                });

                // Load existing content - decode any escaped HTML first
                if (this.content) {
                    const cleanContent = this.decodeHtmlContent(this.content);
                    this.quill.clipboard.dangerouslyPasteHTML(0, cleanContent);
                    this.content = this.quill.root.innerHTML;
                }

                // Update counts and hidden input on change
                this.quill.on('text-change', () => {
                    this.content = this.quill.root.innerHTML;
                    this.updateCounts();
                });

                this.updateCounts();

                // Store quill instance for variable insertion
                window.quillEditor = this.quill;
            },

            decodeHtmlContent(str) {
                // Fix JSON-escaped forward slashes
                str = str.replace(/\\\//g, '/');
                // Fix unicode escapes like \u0102 (Ă), \u201c ("), etc.
                str = str.replace(/\\u([0-9a-fA-F]{4})/g, (match, hex) => {
                    return String.fromCharCode(parseInt(hex, 16));
                });
                // Decode HTML entities using textarea trick
                const textarea = document.createElement('textarea');
                textarea.innerHTML = str;
                return textarea.value;
            },

            updateCounts() {
                const text = this.quill.getText().trim();
                this.charCount = text.length;
                this.wordCount = text ? text.split(/\s+/).filter(w => w.length > 0).length : 0;
            },

            insertVariable(key) {
                // Create variable text with double curly braces
                const variableText = String.fromCharCode(123, 123) + key + String.fromCharCode(125, 125);

                // Always use DOM manipulation - it's more reliable with Quill v2
                const editor = document.querySelector('#editor .ql-editor');
                if (!editor) {
                    console.error('Editor not found');
                    return;
                }

                // Create styled span for the variable
                const span = document.createElement('span');
                span.style.color = '#1e40af';
                span.style.backgroundColor = '#dbeafe';
                span.style.padding = '1px 3px';
                span.style.borderRadius = '3px';
                span.textContent = variableText;

                // Try to insert at cursor position
                const selection = window.getSelection();
                if (selection.rangeCount > 0 && editor.contains(selection.anchorNode)) {
                    const range = selection.getRangeAt(0);
                    range.deleteContents();
                    range.insertNode(span);
                    // Move cursor after the span
                    range.setStartAfter(span);
                    range.setEndAfter(span);
                    selection.removeAllRanges();
                    selection.addRange(range);
                } else {
                    // No selection in editor - append at end
                    editor.appendChild(span);
                }

                // Add a space after for easier continued typing
                const space = document.createTextNode(' ');
                span.after(space);

                // Update content
                this.content = editor.innerHTML;

                // Sync to hidden input
                const hiddenInput = document.getElementById('content-input');
                if (hiddenInput) {
                    hiddenInput.value = this.content;
                }
            },

            setupKeyboardShortcuts() {
                document.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                        e.preventDefault();
                        document.getElementById('template-form').submit();
                    }
                });
            },

            insertTable() {
                if (!this.quill) {
                    console.error('Quill instance not found');
                    return;
                }

                const rows = Math.max(1, Math.min(20, this.tableRows || 3));
                const cols = Math.max(1, Math.min(10, this.tableCols || 3));
                const hasHeader = this.tableWithHeader;

                // Build HTML table with styling for PDF export
                let html = '<table style="width: 100%; border-collapse: collapse; margin: 16px 0;">';

                for (let r = 0; r < rows; r++) {
                    html += '<tr>';
                    for (let c = 0; c < cols; c++) {
                        const isHeader = hasHeader && r === 0;
                        const tag = isHeader ? 'th' : 'td';
                        const headerStyle = isHeader
                            ? 'background-color: #f1f5f9; font-weight: 600; text-align: left;'
                            : '';
                        const cellStyle = `border: 1px solid #cbd5e1; padding: 8px 12px; ${headerStyle}`;
                        const content = isHeader ? `{{ __('Header') }} ${c + 1}` : '&nbsp;';
                        html += `<${tag} style="${cellStyle}">${content}</${tag}>`;
                    }
                    html += '</tr>';
                }
                html += '</table><p><br></p>';

                // Focus and insert at cursor position
                this.quill.focus();
                const range = this.quill.getSelection(true);
                this.quill.clipboard.dangerouslyPasteHTML(range.index, html);

                // Update content
                this.content = this.quill.root.innerHTML;

                // Close modal
                this.showTableModal = false;
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
