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
            <x-ui.button variant="outline" onclick="previewTemplate()">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                {{ __('Preview') }}
            </x-ui.button>
            <x-ui.button type="submit" form="template-form" variant="primary">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('Save') }}
            </x-ui.button>
        </div>
    </x-slot>

    <div class="flex flex-col lg:flex-row min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{-- Success/Error Messages --}}
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="template-form" action="{{ route('settings.contract-templates.update', $template) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Header Section --}}
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
                        <div class="p-6 bg-slate-50">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {{-- Name --}}
                                <div>
                                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                                        {{ __('Template Name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           name="name"
                                           id="name"
                                           value="{{ old('name', $template->name) }}"
                                           required
                                           class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                </div>

                                {{-- Category --}}
                                <div>
                                    <label for="category" class="block text-sm font-medium text-slate-700 mb-1">
                                        {{ __('Category') }}
                                    </label>
                                    <select name="category"
                                            id="category"
                                            class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        @foreach($categories as $key => $label)
                                            <option value="{{ $key }}" {{ old('category', $template->category) === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Flags --}}
                                <div class="flex items-end gap-6">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"
                                               name="is_default"
                                               value="1"
                                               {{ old('is_default', $template->is_default) ? 'checked' : '' }}
                                               class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                        <span class="text-sm text-slate-600">{{ __('Default') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"
                                               name="is_active"
                                               value="1"
                                               {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                                               class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                                        <span class="text-sm text-slate-600">{{ __('Active') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Editor + Variables Sidebar --}}
                    <div class="flex gap-4">
                        {{-- Editor (Left - Main) --}}
                        <div class="flex-1 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="p-4">
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    {{ __('Template Content') }}
                                </label>
                                <textarea name="content" id="content-editor" class="w-full">{{ old('content', $template->content) }}</textarea>
                            </div>
                        </div>

                        {{-- Variables Sidebar (Right) --}}
                        <div class="w-72 flex-shrink-0">
                            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden sticky top-4">
                                <div class="p-4 bg-slate-50 border-b border-slate-200">
                                    <h3 class="font-medium text-slate-900 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        {{ __('Variables') }}
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-1">{{ __('Click to insert at cursor') }}</p>
                                </div>

                                <div x-data="{ openCategory: 'client' }" class="divide-y divide-slate-200 max-h-[550px] overflow-y-auto">
                                    {{-- Client Variables --}}
                                    <div>
                                        <button type="button" 
                                                @click="openCategory = openCategory === 'client' ? '' : 'client'"
                                                class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-slate-50 transition-colors">
                                            <h4 class="text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ __('Client') }}</h4>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                                 :class="{ 'rotate-180': openCategory === 'client' }"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <div x-show="openCategory === 'client'" 
                                             x-collapse
                                             class="px-4 pb-3 space-y-1">
                                            <button type="button" onclick="insertVariable('client_company_name')" class="variable-btn">{{ __('Company Name') }}</button>
                                            <button type="button" onclick="insertVariable('client_address')" class="variable-btn">{{ __('Address') }}</button>
                                            <button type="button" onclick="insertVariable('client_tax_id')" class="variable-btn">{{ __('Tax ID (CUI)') }}</button>
                                            <button type="button" onclick="insertVariable('client_trade_register_number')" class="variable-btn">{{ __('Trade Register No.') }}</button>
                                            <button type="button" onclick="insertVariable('client_bank_account')" class="variable-btn">{{ __('Bank Account') }}</button>
                                            <button type="button" onclick="insertVariable('client_representative')" class="variable-btn">{{ __('Representative') }}</button>
                                            <button type="button" onclick="insertVariable('client_email')" class="variable-btn">{{ __('Email') }}</button>
                                            <button type="button" onclick="insertVariable('client_phone')" class="variable-btn">{{ __('Phone') }}</button>
                                        </div>
                                    </div>

                                    {{-- Contract Variables --}}
                                    <div>
                                        <button type="button" 
                                                @click="openCategory = openCategory === 'contract' ? '' : 'contract'"
                                                class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-slate-50 transition-colors">
                                            <h4 class="text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ __('Contract') }}</h4>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                                 :class="{ 'rotate-180': openCategory === 'contract' }"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <div x-show="openCategory === 'contract'" 
                                             x-collapse
                                             class="px-4 pb-3 space-y-1">
                                            <button type="button" onclick="insertVariable('contract_number')" class="variable-btn">{{ __('Contract Number') }}</button>
                                            <button type="button" onclick="insertVariable('contract_date')" class="variable-btn">{{ __('Contract Date') }}</button>
                                            <button type="button" onclick="insertVariable('contract_start_date')" class="variable-btn">{{ __('Start Date') }}</button>
                                            <button type="button" onclick="insertVariable('contract_end_date')" class="variable-btn">{{ __('End Date') }}</button>
                                            <button type="button" onclick="insertVariable('contract_total')" class="variable-btn">{{ __('Total Value') }}</button>
                                            <button type="button" onclick="insertVariable('contract_currency')" class="variable-btn">{{ __('Currency') }}</button>
                                        </div>
                                    </div>

                                    {{-- Organization Variables --}}
                                    <div>
                                        <button type="button" 
                                                @click="openCategory = openCategory === 'organization' ? '' : 'organization'"
                                                class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-slate-50 transition-colors">
                                            <h4 class="text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ __('Your Company') }}</h4>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                                 :class="{ 'rotate-180': openCategory === 'organization' }"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <div x-show="openCategory === 'organization'" 
                                             x-collapse
                                             class="px-4 pb-3 space-y-1">
                                            <button type="button" onclick="insertVariable('org_name')" class="variable-btn">{{ __('Company Name') }}</button>
                                            <button type="button" onclick="insertVariable('org_address')" class="variable-btn">{{ __('Address') }}</button>
                                            <button type="button" onclick="insertVariable('org_tax_id')" class="variable-btn">{{ __('Tax ID (CUI)') }}</button>
                                            <button type="button" onclick="insertVariable('org_trade_register')" class="variable-btn">{{ __('Trade Register No.') }}</button>
                                            <button type="button" onclick="insertVariable('org_bank_account')" class="variable-btn">{{ __('Bank Account') }}</button>
                                            <button type="button" onclick="insertVariable('org_representative')" class="variable-btn">{{ __('Representative') }}</button>
                                            <button type="button" onclick="insertVariable('org_email')" class="variable-btn">{{ __('Email') }}</button>
                                            <button type="button" onclick="insertVariable('org_phone')" class="variable-btn">{{ __('Phone') }}</button>
                                        </div>
                                    </div>

                                    {{-- Annex Variables --}}
                                    <div id="annex-variables">
                                        <button type="button" 
                                                @click="openCategory = openCategory === 'annex' ? '' : 'annex'"
                                                class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-purple-50 transition-colors">
                                            <h4 class="text-xs font-semibold text-purple-600 uppercase tracking-wider">{{ __('Annex') }}</h4>
                                            <svg class="w-4 h-4 text-purple-400 transition-transform duration-200"
                                                 :class="{ 'rotate-180': openCategory === 'annex' }"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <div x-show="openCategory === 'annex'" 
                                             x-collapse
                                             class="px-4 pb-3 space-y-1 bg-purple-50/50">
                                            <button type="button" onclick="insertVariable('annex_number')" class="variable-btn variable-btn-purple">{{ __('Annex Number') }}</button>
                                            <button type="button" onclick="insertVariable('annex_code')" class="variable-btn variable-btn-purple">{{ __('Annex Code') }}</button>
                                            <button type="button" onclick="insertVariable('annex_date')" class="variable-btn variable-btn-purple">{{ __('Annex Date') }}</button>
                                            <button type="button" onclick="insertVariable('annex_title')" class="variable-btn variable-btn-purple">{{ __('Annex Title') }}</button>
                                            <button type="button" onclick="insertVariable('annex_value')" class="variable-btn variable-btn-purple">{{ __('Annex Value') }}</button>
                                            <button type="button" onclick="insertVariable('parent_contract_number')" class="variable-btn variable-btn-purple">{{ __('Parent Contract Number') }}</button>
                                            <button type="button" onclick="insertVariable('parent_contract_date')" class="variable-btn variable-btn-purple">{{ __('Parent Contract Date') }}</button>
                                            <button type="button" onclick="insertVariable('new_contract_total')" class="variable-btn variable-btn-purple">{{ __('New Contract Total') }}</button>
                                            <button type="button" onclick="insertVariable('annex_services_list')" class="variable-btn variable-btn-purple">{{ __('Annex Services List') }}</button>
                                        </div>
                                    </div>

                                    {{-- Special Variables --}}
                                    <div>
                                        <button type="button" 
                                                @click="openCategory = openCategory === 'special' ? '' : 'special'"
                                                class="w-full px-4 py-3 flex items-center justify-between text-left hover:bg-slate-50 transition-colors">
                                            <h4 class="text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ __('Special') }}</h4>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                                 :class="{ 'rotate-180': openCategory === 'special' }"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <div x-show="openCategory === 'special'" 
                                             x-collapse
                                             class="px-4 pb-3 space-y-1">
                                            <button type="button" onclick="insertVariable('services_list')" class="variable-btn">{{ __('Services List') }}</button>
                                            <button type="button" onclick="insertVariable('current_date')" class="variable-btn">{{ __('Current Date') }}</button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Help Text --}}
                                <div class="p-3 bg-blue-50 border-t border-blue-100">
                                    <p class="text-xs text-blue-700">
                                        <strong>{{ __('Tip:') }}</strong> {{ __('Variables will be replaced with actual values when a contract is generated from this template.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .variable-btn {
            display: block;
            width: 100%;
            text-align: left;
            padding: 0.5rem 0.75rem;
            font-size: 0.8125rem;
            color: #334155;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .variable-btn:hover {
            background: #e0f2fe;
            border-color: #7dd3fc;
            color: #0369a1;
        }
        .variable-btn:active {
            background: #bae6fd;
        }
        .variable-btn-purple {
            background: #faf5ff;
            border-color: #e9d5ff;
        }
        .variable-btn-purple:hover {
            background: #f3e8ff;
            border-color: #c084fc;
            color: #7c3aed;
        }
        .variable-btn-purple:active {
            background: #e9d5ff;
        }
    </style>

    @push('scripts')
    <script>
        // Preview template function
        function previewTemplate() {
            var editor = tinymce.get('content-editor');
            if (!editor) {
                alert('{{ __("Editor not initialized") }}');
                return;
            }
            
            // Save current content
            editor.save();
            
            // Create a form and submit it to open PDF in new tab
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("settings.contract-templates.preview", $template) }}';
            form.target = '_blank';
            
            // Add CSRF token
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            // Add content
            var contentInput = document.createElement('input');
            contentInput.type = 'hidden';
            contentInput.name = 'content';
            contentInput.value = editor.getContent();
            form.appendChild(contentInput);
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        // Insert variable at cursor position in TinyMCE
        function insertVariable(key) {
            var editor = tinymce.get('content-editor');
            if (editor) {
                // Build mustache-style token using char codes
                var token = String.fromCharCode(123, 123) + key + String.fromCharCode(125, 125);
                editor.insertContent(token);
                editor.save();
                editor.focus();
            }
        }

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

        // Initialize TinyMCE
        document.addEventListener('DOMContentLoaded', function() {
            loadTinyMCE().then(function() {

            tinymce.init({
                selector: '#content-editor',
                height: 550,
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
                        editor.save();
                    });
                }
            });

            // Keyboard shortcut: Ctrl+S to save
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    if (tinymce.activeEditor) {
                        tinymce.activeEditor.save();
                    }
                    document.getElementById('template-form').submit();
                }
            });
            }).catch(function(e) { console.error('[ContractTemplate]', e); });
        });
    </script>
    @endpush
</x-app-layout>
