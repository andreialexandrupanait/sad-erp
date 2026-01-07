<x-app-layout>
    <x-slot name="pageTitle">{{ __('Contract Builder') }} - {{ $contract->contract_number }}</x-slot>

    <x-slot name="headerActions">
        <div class="flex items-center gap-2">
            <x-ui.button variant="outline" onclick="window.location.href='{{ route('contracts.show', $contract) }}'">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Back to Contract') }}
            </x-ui.button>
            <x-ui.button variant="default" @click="$dispatch('contract-save')">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('Save') }}
            </x-ui.button>
            <x-ui.button variant="outline" @click="$dispatch('contract-preview')">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                {{ __('Preview PDF') }}
            </x-ui.button>
            <x-ui.button variant="primary" @click="$dispatch('contract-save-pdf')">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('Save & Generate PDF') }}
            </x-ui.button>
            <x-ui.button variant="outline" @click="showSaveAsTemplateModal = true">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                {{ __('Save as Template') }}
            </x-ui.button>
        </div>
    </x-slot>

    <div
        class="h-[calc(100vh-4rem)]"
        x-data="contractBuilder({
            contractId: {{ $contract->id }},
            saveUrl: '{{ route('contracts.update-content', $contract) }}',
            generatePdfUrl: '{{ route('contracts.generate-pdf', $contract) }}',
            previewUrl: '{{ route('contracts.preview', $contract) }}',
            applyTemplateUrl: '{{ route('contracts.apply-template', $contract) }}',
            saveAsTemplateUrl: '{{ route('contracts.save-as-template', $contract) }}',
            initialContent: {{ json_encode($contract->content ?? '') }},
            templates: {{ json_encode($templates) }},
            variables: {{ json_encode($variables) }},
            servicesHtml: {{ json_encode($servicesHtml) }}
        })"
        @keydown.ctrl.s.prevent="saveContent()"
        @keydown.meta.s.prevent="saveContent()"
        @contract-save.window="saveContent()"
        @contract-save-pdf.window="saveAndGeneratePdf()"
        @contract-preview.window="saveAndPreview()"
    >
        <div x-show="message" x-transition class="fixed top-20 right-4 z-50">
            <div
                :class="{
                    'bg-green-100 border-green-400 text-green-700': messageType === 'success',
                    'bg-red-100 border-red-400 text-red-700': messageType === 'error',
                    'bg-blue-100 border-blue-400 text-blue-700': messageType === 'info'
                }"
                class="px-4 py-3 rounded-lg border shadow-lg flex items-center gap-2"
            >
                <span x-text="message"></span>
                <button @click="message = ''" class="ml-2 hover:opacity-75">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex h-full">
            <div class="w-64 bg-white border-r border-slate-200 flex flex-col flex-shrink-0 overflow-hidden">
                <div class="p-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-900">{{ __('Templates') }}</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ __('Select a template to apply') }}</p>
                </div>
                <div class="flex-1 overflow-y-auto p-4">
                    <div class="space-y-2">
                        <template x-for="template in templates" :key="template.id">
                            <button
                                @click="applyTemplate(template.id)"
                                :disabled="loading"
                                class="w-full text-left p-3 rounded-lg border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-colors group"
                                :class="{ 'ring-2 ring-blue-500': selectedTemplateId === template.id }"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-sm text-slate-900 group-hover:text-blue-700" x-text="template.name"></span>
                                    <span x-show="template.is_default" class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">{{ __('Default') }}</span>
                                </div>
                                <span class="text-xs text-slate-500" x-text="template.category"></span>
                            </button>
                        </template>
                        <div x-show="templates.length === 0" class="text-center py-8 text-slate-500">
                            <p class="mt-2 text-sm">{{ __('No templates available') }}</p>
                            <a href="{{ route('settings.contract-templates.create') }}" class="text-blue-600 hover:underline text-sm">{{ __('Create a template') }}</a>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-t border-slate-200 bg-slate-50">
                    <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">{{ __('Contract Info') }}</h4>
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">{{ __('Number') }}</dt>
                            <dd class="font-medium text-slate-900">{{ $contract->contract_number }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">{{ __('Status') }}</dt>
                            <dd><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium @if($contract->status === 'draft') bg-slate-100 text-slate-700 @elseif($contract->status === 'active') bg-green-100 text-green-700 @else bg-slate-100 text-slate-700 @endif">{{ $contract->status_label }}</span></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">{{ __('Value') }}</dt>
                            <dd class="font-medium text-slate-900">{{ number_format($contract->total_value, 2) }} {{ $contract->currency }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="flex-1 flex flex-col min-w-0 bg-slate-100">
                <div class="bg-white border-b border-slate-200 px-6 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-slate-500">
                                <span x-show="!hasUnsavedChanges" class="text-green-600">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    {{ __('Saved') }}
                                </span>
                                <span x-show="hasUnsavedChanges" class="text-amber-600">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    {{ __('Unsaved changes') }}
                                </span>
                            </span>
                            <span x-show="loading" class="text-blue-600">
                                <svg class="w-4 h-4 inline animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ __('Saving...') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="insertServicesTable()" class="px-3 py-1.5 text-sm font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors">
                                <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                {{ __('Insert Services Table') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm">
                        <x-ui.rich-editor name="content" id="contract-editor" :value="$contract->content ?? ''" :variables="[]" min-height="600px" :show-variables-panel="false" x-ref="richEditor" @editor-change="markUnsaved()" />
                    </div>
                </div>
            </div>

            <div class="w-80 bg-white border-l border-slate-200 flex flex-col flex-shrink-0 overflow-hidden">
                <!-- Variables Section -->
                <div class="border-b border-slate-200">
                    <div class="p-4 border-b border-slate-100 bg-blue-50">
                        <h3 class="font-semibold text-slate-900">{{ __('Variables') }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ __('Click to insert. Format:') }} <code class="bg-blue-100 px-1 rounded">@{{var}}</code></p>
                    </div>
                    <div class="max-h-80 overflow-y-auto p-2">
                        @foreach($variables as $category => $vars)
                            <div class="mb-3" x-data="{ open: {{ $category === 'client' || $category === 'contract' ? 'true' : 'false' }} }">
                                <button
                                    @click="open = !open"
                                    class="w-full px-2 py-1.5 text-left flex items-center justify-between bg-slate-50 rounded hover:bg-slate-100"
                                >
                                    <span class="text-xs font-semibold text-slate-600 uppercase">
                                        @if($category === 'client') {{ __('Client') }}
                                        @elseif($category === 'contract') {{ __('Contract') }}
                                        @elseif($category === 'organization') {{ __('Organization') }}
                                        @elseif($category === 'special') {{ __('Special') }}
                                        @else {{ ucfirst($category) }}
                                        @endif
                                    </span>
                                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-collapse class="mt-1">
                                    @foreach($vars as $key => $label)
                                    <button
                                        type="button"
                                        onclick="insertVariableText('{{ $key }}')"
                                        class="w-full px-2 py-1.5 text-left text-sm hover:bg-blue-100 rounded transition-colors flex justify-between items-center group"
                                    >
                                        <span class="text-slate-700 group-hover:text-blue-700 truncate">{{ $label }}</span>
                                        <code class="text-xs text-slate-400 bg-slate-100 px-1 rounded ml-1 flex-shrink-0">{!! sprintf('{{%s}}', $key) !!}</code>
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Offer Services Section -->
                <div class="p-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-900">{{ __('Offer Services') }}</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ __('Services from the original offer') }}</p>
                </div>
                <div class="flex-1 overflow-y-auto">
                    @if($contract->offer && $contract->offer->items->count() > 0)
                        <div class="p-4">
                            <table class="w-full text-sm">
                                <thead><tr class="text-left text-xs text-slate-500 uppercase"><th class="pb-2">{{ __('Service') }}</th><th class="pb-2 text-right">{{ __('Total') }}</th></tr></thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($contract->offer->items as $item)
                                        <tr>
                                            <td class="py-2"><div class="font-medium text-slate-900">{{ $item->title }}</div>@if($item->description)<div class="text-xs text-slate-500 line-clamp-2">{{ $item->description }}</div>@endif<div class="text-xs text-slate-400">{{ $item->quantity }} x {{ number_format($item->unit_price, 2) }}</div></td>
                                            <td class="py-2 text-right font-medium text-slate-900 whitespace-nowrap">{{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="border-t border-slate-200"><tr><td class="pt-3 font-semibold text-slate-900">{{ __('Total') }}</td><td class="pt-3 text-right font-bold text-slate-900">{{ number_format($contract->offer->total, 2) }} {{ $contract->offer->currency }}</td></tr></tfoot>
                            </table>
                        </div>
                    @else
                        <div class="p-8 text-center text-slate-500"><p class="mt-2 text-sm">{{ __('No services in offer') }}</p></div>
                    @endif
                </div>
                @if($contract->client)
                    <div class="p-4 border-t border-slate-200 bg-slate-50">
                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">{{ __('Client') }}</h4>
                        <div class="text-sm">
                            <div class="font-medium text-slate-900">{{ $contract->client->display_name }}</div>
                            @if($contract->client->company_name)<div class="text-slate-600">{{ $contract->client->company_name }}</div>@endif
                            @if($contract->client->email)<div class="text-slate-500">{{ $contract->client->email }}</div>@endif
                        </div>
                    </div>
                @elseif($contract->temp_client_name || $contract->offer?->temp_client_name)
                    <div class="p-4 border-t border-slate-200 bg-slate-50">
                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">{{ __('Client') }}</h4>
                        <div class="text-sm">
                            <div class="font-medium text-slate-900">{{ $contract->temp_client_name ?? $contract->offer?->temp_client_name }}</div>
                            @if($contract->temp_client_company ?? $contract->offer?->temp_client_company)<div class="text-slate-600">{{ $contract->temp_client_company ?? $contract->offer?->temp_client_company }}</div>@endif
                            @if($contract->temp_client_email ?? $contract->offer?->temp_client_email)<div class="text-slate-500">{{ $contract->temp_client_email ?? $contract->offer?->temp_client_email }}</div>@endif
                            <div class="text-xs text-amber-600 mt-1">{{ __('Temporary client') }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Save as Template Modal --}}
        <div x-show="showSaveAsTemplateModal" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="showSaveAsTemplateModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showSaveAsTemplateModal = false"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Save as Template') }}</h3>
                    <p class="text-sm text-slate-600 mb-4">{{ __('Create a new template from this contract. Variable values will be automatically replaced with placeholders.') }}</p>
                    <form @submit.prevent="saveAsTemplate()">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Template Name') }} <span class="text-red-500">*</span></label>
                            <input type="text" x-model="newTemplateName" required
                                   class="w-full border-slate-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="{{ __('e.g., Standard Service Contract') }}">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Category') }}</label>
                            <select x-model="newTemplateCategory"
                                    class="w-full border-slate-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="General">{{ __('General') }}</option>
                                <option value="Service">{{ __('Service') }}</option>
                                <option value="Consulting">{{ __('Consulting') }}</option>
                                <option value="Development">{{ __('Development') }}</option>
                                <option value="Maintenance">{{ __('Maintenance') }}</option>
                            </select>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showSaveAsTemplateModal = false"
                                    class="flex-1 px-4 py-2 border border-slate-300 rounded-md text-slate-700 hover:bg-slate-50">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" :disabled="loading || !newTemplateName.trim()"
                                    :class="loading || !newTemplateName.trim() ? 'bg-blue-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                                    class="flex-1 px-4 py-2 text-white rounded-md">
                                <span x-show="!loading">{{ __('Create Template') }}</span>
                                <span x-show="loading">{{ __('Creating...') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    /**
     * Contract Builder - Quill Editor Utilities
     * Centralized helpers for finding and manipulating Quill editor instances
     */
    const QuillHelper = {
        /**
         * Find the Quill editor instance using multiple fallback methods
         */
        findInstance() {
            // Method 1: Try the specific editor by ID
            const editorById = document.getElementById('contract-editor');
            if (editorById?.__quill) return editorById.__quill;

            // Method 2: Try wrapper.__quill
            const wrapper = document.querySelector('.rich-editor-wrapper');
            if (wrapper?.__quill) return wrapper.__quill;

            // Method 3: Try Alpine data on wrapper
            if (wrapper) {
                try {
                    const alpineData = Alpine.$data(wrapper);
                    if (alpineData?.quill) return alpineData.quill;
                } catch (e) { /* ignore */ }
            }

            // Method 4: Try container with dynamic ID pattern
            const containers = document.querySelectorAll('[id^="quill-"]');
            for (const container of containers) {
                if (container.__quill) return container.__quill;
            }

            // Method 5: Try .ql-container inside the editor
            if (editorById) {
                const qlContainer = editorById.querySelector('.ql-container');
                if (qlContainer?.__quill) return qlContainer.__quill;
            }

            // Method 6: Try global .ql-container as last resort
            const qlContainer = document.querySelector('.ql-container');
            if (qlContainer?.__quill) return qlContainer.__quill;

            return null;
        },

        /**
         * Update the hidden input that stores content
         */
        updateHiddenInput(content) {
            const hiddenInput = document.querySelector('input[name="content"]');
            if (hiddenInput) hiddenInput.value = content;
        },

        /**
         * Decode HTML entities and fix escaped characters from JSON
         */
        decodeHtml(str) {
            if (!str) return '';
            // Fix JSON-escaped forward slashes
            str = str.replace(/\\\//g, '/');
            // Fix unicode escapes like \u0102 (Ă), \u201c ("), etc.
            str = str.replace(/\\u([0-9a-fA-F]{4})/g, (match, hex) => {
                return String.fromCharCode(parseInt(hex, 16));
            });
            // Decode HTML entities
            const textarea = document.createElement('textarea');
            textarea.innerHTML = str;
            return textarea.value;
        },

        /**
         * Set content in the editor
         */
        setContent(html, retryCount = 0) {
            const cleanHtml = this.decodeHtml(html);
            const quill = this.findInstance();

            if (quill) {
                quill.setText('');
                quill.clipboard.dangerouslyPasteHTML(0, cleanHtml);
                this.updateHiddenInput(quill.root.innerHTML);

                // Update Alpine data
                const wrapper = document.querySelector('.rich-editor-wrapper');
                if (wrapper) {
                    try {
                        const alpineData = Alpine.$data(wrapper);
                        if (alpineData) alpineData.content = quill.root.innerHTML;
                    } catch (e) { /* ignore */ }
                }
                return true;
            }

            // Fallback: direct DOM manipulation
            const qlEditor = document.querySelector('.ql-editor');
            if (qlEditor) {
                qlEditor.innerHTML = cleanHtml;
                this.updateHiddenInput(cleanHtml);
                qlEditor.dispatchEvent(new Event('input', { bubbles: true }));
                return true;
            }

            // Retry with delay if not found
            if (retryCount < 3) {
                const delay = retryCount === 0 ? 300 : 500;
                setTimeout(() => this.setContent(html, retryCount + 1), delay);
            }
            return false;
        },

        /**
         * Get current editor content
         */
        getContent() {
            const input = document.querySelector('input[name="content"]');
            if (input?.value) return input.value;

            const qlEditor = document.querySelector('.ql-editor');
            if (qlEditor) return qlEditor.innerHTML;

            const wrapper = document.querySelector('.rich-editor-wrapper');
            if (wrapper) {
                try {
                    const ad = Alpine.$data(wrapper);
                    if (ad?.content) return ad.content;
                } catch (e) { /* ignore */ }
            }
            return '';
        },

        /**
         * Insert HTML at cursor position
         */
        insertHtml(html) {
            const quill = this.findInstance();
            if (quill) {
                const range = quill.getSelection(true);
                quill.clipboard.dangerouslyPasteHTML(range.index, html);
                this.updateHiddenInput(quill.root.innerHTML);
                return true;
            }
            return false;
        },

        /**
         * Insert styled text at cursor position
         */
        insertStyledText(text, styles = {}) {
            const quill = this.findInstance();
            if (quill) {
                quill.focus();
                const range = quill.getSelection(true);
                const index = range ? range.index : quill.getLength() - 1;
                quill.insertText(index, text, styles);
                quill.setSelection(index + text.length);
                this.updateHiddenInput(quill.root.innerHTML);
                return true;
            }

            // Fallback for DOM-only insertion
            const qlEditor = document.querySelector('.ql-editor');
            if (qlEditor) {
                const span = document.createElement('span');
                Object.assign(span.style, {
                    color: styles.color || '#1e40af',
                    backgroundColor: styles.background || '#dbeafe',
                    padding: '0 2px',
                    borderRadius: '2px'
                });
                span.textContent = text;

                const selection = window.getSelection();
                if (selection.rangeCount > 0 && qlEditor.contains(selection.anchorNode)) {
                    const range = selection.getRangeAt(0);
                    range.deleteContents();
                    range.insertNode(span);
                    range.setStartAfter(span);
                    range.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(range);
                } else {
                    qlEditor.appendChild(span);
                }
                this.updateHiddenInput(qlEditor.innerHTML);
                return true;
            }
            return false;
        }
    };

    /**
     * Contract Builder Alpine.js Component
     */
    function contractBuilder(config) {
        return {
            contractId: config.contractId,
            saveUrl: config.saveUrl,
            generatePdfUrl: config.generatePdfUrl,
            applyTemplateUrl: config.applyTemplateUrl,
            saveAsTemplateUrl: config.saveAsTemplateUrl,
            templates: config.templates || [],
            variables: config.variables || {},
            servicesHtml: config.servicesHtml || '',
            initialContent: config.initialContent || '',
            previewUrl: config.previewUrl || '',
            selectedTemplateId: null,
            hasUnsavedChanges: false,
            loading: false,
            message: '',
            messageType: 'info',
            showSaveAsTemplateModal: false,
            newTemplateName: '',
            newTemplateCategory: 'General',

            init() {
                this.$nextTick(() => this.loadInitialContent());

                // Auto-save every 30 seconds
                setInterval(() => {
                    if (this.hasUnsavedChanges && !this.loading) {
                        this.saveContent(true);
                    }
                }, 30000);

                // Warn before leaving with unsaved changes
                window.addEventListener('beforeunload', (e) => {
                    if (this.hasUnsavedChanges) {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });
            },

            loadInitialContent() {
                if (!this.initialContent?.trim()) return;

                const checkAndLoad = () => {
                    const qlEditor = document.querySelector('.ql-editor');
                    if (qlEditor) {
                        qlEditor.innerHTML = this.initialContent;
                        QuillHelper.updateHiddenInput(this.initialContent);
                    } else {
                        setTimeout(checkAndLoad, 100);
                    }
                };
                setTimeout(checkAndLoad, 200);
            },

            markUnsaved() {
                this.hasUnsavedChanges = true;
            },

            showMessage(msg, type = 'info', duration = 3000) {
                this.message = msg;
                this.messageType = type;
                if (duration > 0) {
                    setTimeout(() => { this.message = ''; }, duration);
                }
            },

            getEditorContent() {
                // Try multiple sources to ensure we get the content
                // 1. First try the Quill editor directly (most reliable)
                const quill = QuillHelper.findInstance();
                if (quill && quill.root) {
                    return quill.root.innerHTML;
                }

                // 2. Try the .ql-editor element
                const qlEditor = document.querySelector('.ql-editor');
                if (qlEditor) {
                    return qlEditor.innerHTML;
                }

                // 3. Fall back to hidden input
                const hiddenInput = document.querySelector('input[name="content"]');
                if (hiddenInput && hiddenInput.value) {
                    return hiddenInput.value;
                }

                // 4. Last resort - try QuillHelper
                return QuillHelper.getContent();
            },

            async saveContent(silent = false) {
                if (this.loading) return;
                this.loading = true;

                const content = this.getEditorContent();
                console.log('Saving content, length:', content?.length || 0);

                try {
                    const response = await fetch(this.saveUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ content: content })
                    });

                    const data = await response.json();
                    console.log('Save response:', data);

                    if (response.ok && data.success) {
                        this.hasUnsavedChanges = false;
                        if (!silent) this.showMessage('{{ __('Contract saved successfully') }}', 'success');
                    } else {
                        throw new Error(data.message || data.error || 'Save failed');
                    }
                } catch (error) {
                    console.error('Save error:', error);
                    this.showMessage(error.message || '{{ __('Failed to save contract') }}', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async saveAndGeneratePdf() {
                await this.saveContent();
                if (this.hasUnsavedChanges) return;

                this.loading = true;
                try {
                    const response = await fetch(this.generatePdfUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        this.showMessage('{{ __('PDF generated successfully') }}', 'success');
                        if (data.redirect) {
                            setTimeout(() => { window.location.href = data.redirect; }, 1500);
                        }
                    } else {
                        throw new Error(data.message || 'PDF generation failed');
                    }
                } catch (error) {
                    this.showMessage(error.message || '{{ __('Failed to generate PDF') }}', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async saveAndPreview() {
                // Save first, then open preview in new tab
                await this.saveContent();
                if (this.hasUnsavedChanges) {
                    this.showMessage('{{ __('Please save successfully before previewing') }}', 'error');
                    return;
                }

                // Open PDF preview in new tab
                window.open(this.previewUrl, '_blank');
                this.showMessage('{{ __('PDF preview opened in new tab') }}', 'success');
            },

            async applyTemplate(templateId) {
                if (this.loading) return;

                if (this.hasUnsavedChanges) {
                    if (!confirm('{{ __('You have unsaved changes. Applying a template will replace the current content. Continue?') }}')) {
                        return;
                    }
                }

                this.loading = true;
                this.selectedTemplateId = templateId;

                try {
                    const response = await fetch(this.applyTemplateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ template_id: templateId })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        if (data.content?.trim()) {
                            QuillHelper.setContent(data.content);
                            this.hasUnsavedChanges = true;
                            this.showMessage('{{ __('Template applied successfully') }}', 'success');
                        } else {
                            this.showMessage('{{ __('Template is empty') }}', 'error');
                        }
                    } else {
                        throw new Error(data.message || 'Failed to apply template');
                    }
                } catch (error) {
                    this.showMessage(error.message || '{{ __('Failed to apply template') }}', 'error');
                    this.selectedTemplateId = null;
                } finally {
                    this.loading = false;
                }
            },

            async saveAsTemplate() {
                if (this.loading || !this.newTemplateName.trim()) return;

                this.loading = true;

                try {
                    // First save the current content
                    await this.saveContent(true);

                    const response = await fetch(this.saveAsTemplateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name: this.newTemplateName,
                            category: this.newTemplateCategory
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.showSaveAsTemplateModal = false;
                        this.newTemplateName = '';
                        this.newTemplateCategory = 'General';
                        this.showMessage('{{ __('Template created successfully') }}', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to create template');
                    }
                } catch (error) {
                    this.showMessage(error.message || '{{ __('Failed to create template') }}', 'error');
                } finally {
                    this.loading = false;
                }
            },

            setEditorContent(html) {
                QuillHelper.setContent(html);
            },

            insertServicesTable() {
                if (QuillHelper.insertHtml(this.servicesHtml)) {
                    this.hasUnsavedChanges = true;
                    this.showMessage('{{ __('Services table inserted') }}', 'success');
                }
            }
        };
    }

    /**
     * Insert variable text into the editor
     * Uses double curly braces format to match ContractVariableRegistry
     */
    function insertVariableText(variableKey) {
        const qlEditor = document.querySelector('.ql-editor');
        if (!qlEditor) {
            alert('{{ __('Editor not found. Please click inside the editor first.') }}');
            return;
        }

        // Create variable text with double curly braces
        const variableText = '{{' + variableKey + '}}';

        // Insert styled text
        QuillHelper.insertStyledText(variableText, {
            color: '#1e40af',
            background: '#dbeafe'
        });

        // Mark as unsaved in Alpine data
        try {
            const builderWrapper = document.querySelector('[x-data*="contractBuilder"]');
            if (builderWrapper && window.Alpine) {
                const data = Alpine.$data(builderWrapper);
                if (data?.hasUnsavedChanges !== undefined) {
                    data.hasUnsavedChanges = true;
                }
            }
        } catch (e) { /* ignore */ }
    }
    </script>
    @endpush
</x-app-layout>
