@props([
    'name' => 'content',
    'id' => null,
    'value' => '',
    'placeholder' => 'Start typing...',
    'variables' => [],
    'minHeight' => '400px',
    'showVariablesPanel' => true,
])

@php
    $editorId = $id ?? 'quill-' . $name . '-' . uniqid();
    $toolbarId = 'toolbar-' . $editorId;
    $uniqueId = uniqid('re_');
@endphp

{{-- Store initial value in a script tag to avoid Alpine parsing issues --}}
<script id="initial-value-{{ $uniqueId }}" type="application/json">{!! json_encode($value) !!}</script>

<div
    x-data="richEditor({
        editorId: '{{ $editorId }}',
        toolbarId: '{{ $toolbarId }}',
        name: '{{ $name }}',
        initialValueId: 'initial-value-{{ $uniqueId }}',
        variables: {{ json_encode($variables) }},
        showVariablesPanel: {{ $showVariablesPanel ? 'true' : 'false' }}
    })"
    x-init="init()"
    {{ $attributes->merge(['class' => 'rich-editor-wrapper']) }}
>
    <!-- Hidden input to store content -->
    <input type="hidden" name="{{ $name }}" x-ref="hiddenInput" :value="content">

    <div class="flex gap-4">
        <!-- Main Editor Area -->
        <div class="flex-1">
            <!-- Custom Toolbar -->
            <div id="{{ $toolbarId }}" class="ql-toolbar ql-snow">
                <span class="ql-formats">
                    <select class="ql-header">
                        <option value="">{{ __('Normal') }}</option>
                        <option value="1">{{ __('Heading 1') }}</option>
                        <option value="2">{{ __('Heading 2') }}</option>
                        <option value="3">{{ __('Heading 3') }}</option>
                    </select>
                    <select class="ql-size">
                        <option value="small">{{ __('Small') }}</option>
                        <option selected>{{ __('Normal') }}</option>
                        <option value="large">{{ __('Large') }}</option>
                        <option value="huge">{{ __('Huge') }}</option>
                    </select>
                </span>
                <span class="ql-formats">
                    <button class="ql-bold" title="{{ __('Bold') }}"></button>
                    <button class="ql-italic" title="{{ __('Italic') }}"></button>
                    <button class="ql-underline" title="{{ __('Underline') }}"></button>
                    <button class="ql-strike" title="{{ __('Strikethrough') }}"></button>
                </span>
                <span class="ql-formats">
                    <button class="ql-list" value="ordered" title="{{ __('Numbered List') }}"></button>
                    <button class="ql-list" value="bullet" title="{{ __('Bullet List') }}"></button>
                    <button class="ql-indent" value="-1" title="{{ __('Decrease Indent') }}"></button>
                    <button class="ql-indent" value="+1" title="{{ __('Increase Indent') }}"></button>
                </span>
                <span class="ql-formats">
                    <select class="ql-align">
                        <option selected></option>
                        <option value="center"></option>
                        <option value="right"></option>
                        <option value="justify"></option>
                    </select>
                    <select class="ql-lineheight" title="{{ __('Line Height') }}">
                        <option value="1">1.0</option>
                        <option value="1.2">1.2</option>
                        <option value="1.5" selected>1.5</option>
                        <option value="1.8">1.8</option>
                        <option value="2">2.0</option>
                        <option value="2.5">2.5</option>
                        <option value="3">3.0</option>
                    </select>
                </span>
                <span class="ql-formats">
                    <button class="ql-link" title="{{ __('Insert Link') }}"></button>
                    <button class="ql-image" title="{{ __('Insert Image') }}"></button>
                </span>
                <span class="ql-formats">
                    <button class="ql-blockquote" title="{{ __('Quote') }}"></button>
                    <button class="ql-code-block" title="{{ __('Code Block') }}"></button>
                </span>
                <span class="ql-formats">
                    <select class="ql-color" title="{{ __('Text Color') }}"></select>
                    <select class="ql-background" title="{{ __('Background Color') }}"></select>
                </span>
                <span class="ql-formats">
                    <button class="ql-clean" title="{{ __('Clear Formatting') }}"></button>
                </span>
                @if($showVariablesPanel)
                <span class="ql-formats">
                    <button
                        type="button"
                        @click="showVariableDropdown = !showVariableDropdown"
                        class="flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded hover:bg-blue-100 transition-colors"
                        title="{{ __('Insert Variable') }}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        {{ __('Variable') }}
                    </button>
                </span>
                @endif
            </div>

            <!-- Editor Container -->
            <div
                id="{{ $editorId }}"
                x-ref="editor"
                style="min-height: {{ $minHeight }}"
                class="bg-white"
            ></div>
        </div>

        @if($showVariablesPanel)
        <!-- Variables Side Panel -->
        <div
            x-show="showVariableDropdown"
            x-transition
            @click.away="showVariableDropdown = false"
            class="w-72 bg-white border border-slate-200 rounded-lg shadow-lg overflow-hidden flex-shrink-0"
        >
            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                <h3 class="font-semibold text-sm text-slate-900">{{ __('Insert Variable') }}</h3>
                <p class="text-xs text-slate-500 mt-1">{{ __('Click to insert at cursor position') }}</p>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <template x-for="(vars, category) in groupedVariables" :key="category">
                    <div class="border-b border-slate-100 last:border-b-0">
                        <button
                            type="button"
                            @click="toggleCategory(category)"
                            class="w-full px-4 py-2 flex items-center justify-between text-left bg-slate-50 hover:bg-slate-100 transition-colors"
                        >
                            <span class="text-xs font-semibold text-slate-700 uppercase tracking-wide" x-text="category"></span>
                            <svg
                                class="w-4 h-4 text-slate-400 transition-transform"
                                :class="{ 'rotate-180': expandedCategories.includes(category) }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="expandedCategories.includes(category)" x-collapse>
                            <template x-for="variable in vars" :key="variable.key">
                                <button
                                    type="button"
                                    @click="insertVariable(variable.key)"
                                    class="w-full px-4 py-2 text-left hover:bg-blue-50 transition-colors group"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-slate-900 group-hover:text-blue-700" x-text="variable.label"></span>
                                        <code class="text-xs text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded" x-text="'{{' + variable.key + '}}'"></code>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Special Blocks Section -->
                <div class="border-t border-slate-200">
                    <button
                        type="button"
                        @click="toggleCategory('special')"
                        class="w-full px-4 py-2 flex items-center justify-between text-left bg-amber-50 hover:bg-amber-100 transition-colors"
                    >
                        <span class="text-xs font-semibold text-amber-700 uppercase tracking-wide">{{ __('Special Blocks') }}</span>
                        <svg
                            class="w-4 h-4 text-amber-400 transition-transform"
                            :class="{ 'rotate-180': expandedCategories.includes('special') }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="expandedCategories.includes('special')" x-collapse>
                        <button
                            type="button"
                            @click="insertServicesTable()"
                            class="w-full px-4 py-2 text-left hover:bg-amber-50 transition-colors group"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm text-slate-900 group-hover:text-amber-700">{{ __('Services Table') }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">{{ __('Auto-generated table of offer items') }}</p>
                        </button>
                        <button
                            type="button"
                            @click="insertVariable('SIGNATURES')"
                            class="w-full px-4 py-2 text-left hover:bg-amber-50 transition-colors group"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                <span class="text-sm text-slate-900 group-hover:text-amber-700">{{ __('Signatures Block') }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">{{ __('Signature area with date fields') }}</p>
                        </button>
                        <button
                            type="button"
                            @click="insertVariable('CURRENT_DATE')"
                            class="w-full px-4 py-2 text-left hover:bg-amber-50 transition-colors group"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm text-slate-900 group-hover:text-amber-700">{{ __('Current Date') }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">{{ __('Inserts today\'s date') }}</p>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Word Count -->
    <div class="flex items-center justify-between mt-2 text-xs text-slate-500">
        <span>{{ __('Characters') }}: <span x-text="charCount">0</span></span>
        <span>{{ __('Words') }}: <span x-text="wordCount">0</span></span>
    </div>
</div>

@pushOnce('styles')
<style>
/* Line height dropdown styling */
.ql-snow .ql-picker.ql-lineheight {
    width: 60px;
}
.ql-snow .ql-picker.ql-lineheight .ql-picker-label::before,
.ql-snow .ql-picker.ql-lineheight .ql-picker-item::before {
    content: 'LH';
}
.ql-snow .ql-picker.ql-lineheight .ql-picker-label[data-value="1"]::before,
.ql-snow .ql-picker.ql-lineheight .ql-picker-item[data-value="1"]::before {
    content: '1.0';
}
.ql-snow .ql-picker.ql-lineheight .ql-picker-label[data-value="1.2"]::before,
.ql-snow .ql-picker.ql-lineheight .ql-picker-item[data-value="1.2"]::before {
    content: '1.2';
}
.ql-snow .ql-picker.ql-lineheight .ql-picker-label[data-value="1.5"]::before,
.ql-snow .ql-picker.ql-lineheight .ql-picker-item[data-value="1.5"]::before {
    content: '1.5';
}
.ql-snow .ql-picker.ql-lineheight .ql-picker-label[data-value="1.8"]::before,
.ql-snow .ql-picker.ql-lineheight .ql-picker-item[data-value="1.8"]::before {
    content: '1.8';
}
.ql-snow .ql-picker.ql-lineheight .ql-picker-label[data-value="2"]::before,
.ql-snow .ql-picker.ql-lineheight .ql-picker-item[data-value="2"]::before {
    content: '2.0';
}
.ql-snow .ql-picker.ql-lineheight .ql-picker-label[data-value="2.5"]::before,
.ql-snow .ql-picker.ql-lineheight .ql-picker-item[data-value="2.5"]::before {
    content: '2.5';
}
.ql-snow .ql-picker.ql-lineheight .ql-picker-label[data-value="3"]::before,
.ql-snow .ql-picker.ql-lineheight .ql-picker-item[data-value="3"]::before {
    content: '3.0';
}

/* Size dropdown styling */
.ql-snow .ql-picker.ql-size {
    width: 80px;
}
.ql-snow .ql-picker.ql-size .ql-picker-label::before,
.ql-snow .ql-picker.ql-size .ql-picker-item::before {
    content: 'Normal';
}
.ql-snow .ql-picker.ql-size .ql-picker-label[data-value="small"]::before,
.ql-snow .ql-picker.ql-size .ql-picker-item[data-value="small"]::before {
    content: 'Small';
}
.ql-snow .ql-picker.ql-size .ql-picker-label[data-value="large"]::before,
.ql-snow .ql-picker.ql-size .ql-picker-item[data-value="large"]::before {
    content: 'Large';
}
.ql-snow .ql-picker.ql-size .ql-picker-label[data-value="huge"]::before,
.ql-snow .ql-picker.ql-size .ql-picker-item[data-value="huge"]::before {
    content: 'Huge';
}

/* Editor content styling is defined in app.blade.php to ensure
   consistency between editor and preview/show views */
</style>
@endPushOnce

@pushOnce('scripts')
<script>
function richEditor(config) {
    return {
        editorId: config.editorId,
        toolbarId: config.toolbarId,
        name: config.name,
        initialValueId: config.initialValueId || null,
        variables: config.variables || {},
        showVariablesPanel: config.showVariablesPanel !== false,

        quill: null,
        content: '',
        charCount: 0,
        wordCount: 0,
        showVariableDropdown: false,
        expandedCategories: ['client', 'contract', 'special'],
        isInitializing: true,

        get groupedVariables() {
            // Transform variables from {category: {key: label}} to {category: [{key, label}]}
            const result = {};
            for (const [category, vars] of Object.entries(this.variables || {})) {
                if (category === 'special') continue; // Special blocks handled separately
                result[category] = [];
                for (const [key, label] of Object.entries(vars || {})) {
                    result[category].push({ key, label });
                }
            }
            return result;
        },

        getInitialValue() {
            // Read initial value from script tag to avoid Alpine parsing issues
            if (this.initialValueId) {
                const scriptTag = document.getElementById(this.initialValueId);
                if (scriptTag) {
                    try {
                        return JSON.parse(scriptTag.textContent) || '';
                    } catch (e) {
                        console.error('Failed to parse initial value:', e);
                        return '';
                    }
                }
            }
            return '';
        },

        init() {
            this.$nextTick(() => {
                this.initQuill();
            });
        },

        initQuill() {
            const toolbarOptions = '#' + this.toolbarId;

            // Ensure Quill is available
            if (typeof Quill === 'undefined') {
                console.error('Quill library not loaded yet, retrying...');
                setTimeout(() => this.initQuill(), 100);
                return;
            }

            // Register custom line-height format
            const Parchment = Quill.import('parchment');
            const lineHeightConfig = {
                scope: Parchment.Scope.BLOCK,
                whitelist: ['1', '1.2', '1.5', '1.8', '2', '2.5', '3']
            };
            const LineHeightClass = new Parchment.Attributor.Style('lineheight', 'line-height', lineHeightConfig);
            Quill.register(LineHeightClass, true);

            this.quill = new Quill('#' + this.editorId, {
                theme: 'snow',
                modules: {
                    toolbar: toolbarOptions
                },
                placeholder: '{{ $placeholder }}'
            });

            // Setup custom line-height handler
            const toolbar = this.quill.getModule('toolbar');
            toolbar.addHandler('lineheight', (value) => {
                this.quill.format('lineheight', value);
            });

            // Store quill instance on multiple places for external access
            const container = document.querySelector('#' + this.editorId);
            if (container) {
                container.__quill = this.quill;
                const qlContainer = container.querySelector('.ql-container');
                if (qlContainer) {
                    qlContainer.__quill = this.quill;
                }
            }

            // Also store on the wrapper element for easier access
            const wrapper = this.$el;
            if (wrapper) {
                wrapper.__quill = this.quill;
            }

            // Listen for changes
            this.quill.on('text-change', () => {
                this.content = this.quill.root.innerHTML;
                this.updateCounts();
                this.$refs.hiddenInput.value = this.content;

                // Only dispatch editor-change after initialization is complete
                if (!this.isInitializing) {
                    this.$dispatch('editor-change', { content: this.content });
                }
            });

            // Set initial content after Quill is fully ready
            setTimeout(() => {
                const initialValue = this.getInitialValue();
                if (initialValue && initialValue.trim() !== '') {
                    // Decode any escaped HTML content
                    const cleanContent = this.decodeHtmlContent(initialValue);
                    // Use clipboard API for proper HTML parsing
                    if (this.quill) {
                        this.quill.clipboard.dangerouslyPasteHTML(0, cleanContent);
                        this.content = this.quill.root.innerHTML;
                        if (this.$refs.hiddenInput) {
                            this.$refs.hiddenInput.value = this.content;
                        }
                    }
                }
                this.updateCounts();
                this.isInitializing = false;
            }, 50);
        },

        decodeHtmlContent(str) {
            if (!str) return '';
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
            const text = this.quill.getText();
            this.charCount = text.length - 1; // Subtract trailing newline
            this.wordCount = text.trim() ? text.trim().split(/\s+/).length : 0;
        },

        toggleCategory(category) {
            const index = this.expandedCategories.indexOf(category);
            if (index > -1) {
                this.expandedCategories.splice(index, 1);
            } else {
                this.expandedCategories.push(category);
            }
        },

        getQuillInstance() {
            // Try multiple ways to get the Quill instance
            if (this.quill) return this.quill;

            // Try to find via container
            const container = document.querySelector('#' + this.editorId);
            if (container && container.__quill) return container.__quill;

            // Try via ql-container
            const qlContainer = document.querySelector('.ql-container');
            if (qlContainer && qlContainer.__quill) return qlContainer.__quill;

            // Try via wrapper
            const wrapper = document.querySelector('.rich-editor-wrapper');
            if (wrapper && wrapper.__quill) return wrapper.__quill;

            return null;
        },

        insertVariable(key) {
            const quill = this.getQuillInstance();
            if (!quill) {
                console.error('Quill instance not found');
                return;
            }

            // Focus the editor first
            quill.focus();

            const range = quill.getSelection(true);
            // Use string concatenation to avoid Blade parsing
            const variableText = String.fromCharCode(123, 123) + key + String.fromCharCode(125, 125);

            quill.insertText(range.index, variableText, {
                'color': '#1e40af',
                'background': '#dbeafe'
            });

            // Move cursor after the variable
            quill.setSelection(range.index + variableText.length);

            this.showVariableDropdown = false;
        },

        insertServicesTable() {
            const quill = this.getQuillInstance();
            if (!quill) {
                console.error('Quill instance not found');
                return;
            }

            // Focus the editor first
            quill.focus();

            const range = quill.getSelection(true);

            // Insert a placeholder block - use charCode to avoid Blade parsing
            const placeholder = '\n' + String.fromCharCode(123, 123) + 'SERVICES_TABLE' + String.fromCharCode(125, 125) + '\n';
            quill.insertText(range.index, placeholder, {
                'color': '#92400e',
                'background': '#fef3c7'
            });

            quill.setSelection(range.index + placeholder.length);
            this.showVariableDropdown = false;
        },

        getContent() {
            return this.quill ? this.quill.root.innerHTML : '';
        },

        setContent(html) {
            if (this.quill) {
                this.quill.root.innerHTML = html;
                this.content = html;
                this.$refs.hiddenInput.value = html;
            }
        },

        clearContent() {
            if (this.quill) {
                this.quill.setText('');
                this.content = '';
                this.$refs.hiddenInput.value = '';
            }
        }
    };
}
</script>
@endPushOnce
