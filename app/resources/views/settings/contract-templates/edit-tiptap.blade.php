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

    <div class="h-[calc(100vh-4rem)] flex" x-data="templateEditor({ blocks: @json($template->blocks), html: @json($template->content) })" x-cloak>
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

                    {{-- Hidden content field - stores JSON blocks --}}
                    <input type="hidden" name="blocks" id="blocks-input" :value="JSON.stringify(content)">
                    {{-- Legacy HTML content for backward compatibility --}}
                    <input type="hidden" name="content" id="content-input" :value="getHTML()">
                </form>
            </div>

            {{-- Editor --}}
            <div class="flex-1 bg-slate-100 overflow-hidden p-6">
                <div class="h-full bg-white rounded-xl shadow-lg overflow-hidden flex flex-col">
                    {{-- TipTap Toolbar --}}
                    <div class="editor-toolbar border-b border-slate-200 bg-slate-50 px-3 py-2 flex flex-wrap items-center gap-1">
                        {{-- Heading Select --}}
                        <select @change="setHeading(parseInt($event.target.value))"
                                class="h-8 px-2 text-sm border border-slate-200 rounded-md bg-white">
                            <option value="0">{{ __('Normal') }}</option>
                            <option value="1">{{ __('Heading 1') }}</option>
                            <option value="2">{{ __('Heading 2') }}</option>
                            <option value="3">{{ __('Heading 3') }}</option>
                        </select>

                        <div class="separator"></div>

                        {{-- Text Formatting --}}
                        <button type="button" @click="toggleBold()" :class="{ 'is-active': isActive('bold') }" title="{{ __('Bold') }} (Ctrl+B)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"/></svg>
                        </button>
                        <button type="button" @click="toggleItalic()" :class="{ 'is-active': isActive('italic') }" title="{{ __('Italic') }} (Ctrl+I)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h4m-2 0v16m-4 0h8"/></svg>
                        </button>
                        <button type="button" @click="toggleUnderline()" :class="{ 'is-active': isActive('underline') }" title="{{ __('Underline') }} (Ctrl+U)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7v5a5 5 0 0010 0V7M5 21h14"/></svg>
                        </button>
                        <button type="button" @click="toggleStrike()" :class="{ 'is-active': isActive('strike') }" title="{{ __('Strikethrough') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h12M6 6h12M6 18h12"/></svg>
                        </button>

                        <div class="separator"></div>

                        {{-- Text Color --}}
                        <div class="relative" x-data="{ showColors: false }">
                            <button type="button" @click="showColors = !showColors" title="{{ __('Text Color') }}" class="relative">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10M12 3v14M5 10l7-7 7 7"/></svg>
                                <span class="absolute bottom-0 left-1 right-1 h-0.5 bg-current"></span>
                            </button>
                            <div x-show="showColors" @click.away="showColors = false" class="absolute top-full left-0 mt-1 p-2 bg-white rounded-lg shadow-lg border border-slate-200 grid grid-cols-5 gap-1 z-50">
                                @foreach(['#000000', '#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#8b5cf6', '#ec4899', '#6b7280', '#1e293b'] as $color)
                                <button type="button" @click="setColor('{{ $color }}'); showColors = false" class="w-6 h-6 rounded" style="background-color: {{ $color }}"></button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Highlight --}}
                        <div class="relative" x-data="{ showHighlight: false }">
                            <button type="button" @click="showHighlight = !showHighlight" title="{{ __('Highlight') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <div x-show="showHighlight" @click.away="showHighlight = false" class="absolute top-full left-0 mt-1 p-2 bg-white rounded-lg shadow-lg border border-slate-200 grid grid-cols-5 gap-1 z-50">
                                @foreach(['#fef08a', '#bbf7d0', '#bfdbfe', '#ddd6fe', '#fecaca'] as $color)
                                <button type="button" @click="setHighlight('{{ $color }}'); showHighlight = false" class="w-6 h-6 rounded border border-slate-200" style="background-color: {{ $color }}"></button>
                                @endforeach
                            </div>
                        </div>

                        <div class="separator"></div>

                        {{-- Alignment --}}
                        <button type="button" @click="setTextAlign('left')" :class="{ 'is-active': isActive({ textAlign: 'left' }) }" title="{{ __('Align Left') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h14"/></svg>
                        </button>
                        <button type="button" @click="setTextAlign('center')" :class="{ 'is-active': isActive({ textAlign: 'center' }) }" title="{{ __('Align Center') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M5 18h14"/></svg>
                        </button>
                        <button type="button" @click="setTextAlign('right')" :class="{ 'is-active': isActive({ textAlign: 'right' }) }" title="{{ __('Align Right') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M6 18h14"/></svg>
                        </button>

                        <div class="separator"></div>

                        {{-- Lists --}}
                        <button type="button" @click="toggleBulletList()" :class="{ 'is-active': isActive('bulletList') }" title="{{ __('Bullet List') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16M2 6h.01M2 12h.01M2 18h.01"/></svg>
                        </button>
                        <button type="button" @click="toggleOrderedList()" :class="{ 'is-active': isActive('orderedList') }" title="{{ __('Numbered List') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6h13M7 12h13M7 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                        </button>

                        <div class="separator"></div>

                        {{-- Link --}}
                        <button type="button" @click="openLinkModal()" :class="{ 'is-active': isActive('link') }" title="{{ __('Link') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </button>

                        {{-- Table --}}
                        <button type="button" @click="openTableModal()" title="{{ __('Insert Table') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </button>

                        <div class="separator"></div>

                        {{-- Special Blocks --}}
                        <button type="button" @click="insertServicesBlock()" title="{{ __('Insert Services Table') }}" class="px-2 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded">
                            <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            {{ __('Services') }}
                        </button>
                        <button type="button" @click="insertSignatureBlock()" title="{{ __('Insert Signature Block') }}" class="px-2 text-xs font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 rounded">
                            <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            {{ __('Signatures') }}
                        </button>
                    </div>

                    {{-- Editor Container --}}
                    <div class="flex-1 overflow-y-auto">
                        <div x-ref="editor" class="min-h-full"></div>
                    </div>

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
                <p class="text-xs text-slate-500 mt-1">{{ __('Click to insert protected variable') }}</p>
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
                                @click="insertVariable('{{ $key }}', { required: {{ ($config['required'] ?? false) ? 'true' : 'false' }} })"
                                class="w-full px-4 py-2.5 text-left hover:bg-blue-50 transition-colors flex items-center justify-between group border-b border-slate-100 last:border-0">
                            <div class="flex-1 min-w-0">
                                <span class="text-sm text-slate-700 group-hover:text-blue-700 block truncate">
                                    {{ $config['label'] ?? $config['label_en'] ?? $key }}
                                </span>
                                @if($config['required'] ?? false)
                                    <span class="text-xs text-red-500">{{ __('Required') }}</span>
                                @endif
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
                        <span>{{ __('Variables are protected and cannot be edited') }}</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span>{{ __('Use Ctrl+S to save quickly') }}</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span>{{ __('Insert Services block for dynamic table') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Link Modal -->
        <div x-show="showLinkModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
             @click.self="showLinkModal = false"
             @keydown.escape.window="showLinkModal = false">
            <div class="bg-white rounded-lg shadow-xl w-96 p-5" @click.stop>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Insert Link') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('URL') }}</label>
                        <input type="url" x-model="linkUrl" placeholder="https://"
                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                               @keydown.enter="setLink()">
                    </div>
                </div>
                <div class="flex justify-between mt-6">
                    <button type="button" @click="removeLink()" x-show="isActive('link')"
                            class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-700">
                        {{ __('Remove Link') }}
                    </button>
                    <div class="flex gap-3">
                        <button type="button" @click="showLinkModal = false"
                                class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" @click="setLink()"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            {{ __('Insert') }}
                        </button>
                    </div>
                </div>
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

    {{-- Load TipTap Editor CSS --}}
    @vite(['resources/css/editor.css'])

    @push('scripts')
    {{-- Load TipTap Editor JS before Alpine initializes --}}
    @vite(['resources/js/app.js'])
    <script>
    // Register templateEditor with Alpine before it starts
    document.addEventListener('alpine:init', () => {
        Alpine.data('templateEditor', window.templateEditor);
    });

    // Keyboard shortcut for save
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            document.getElementById('template-form').submit();
        }
    });
    </script>
    @endpush
</x-app-layout>
