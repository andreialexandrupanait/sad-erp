{{-- Specifications Block - Title, Lists, Paragraphs --}}
<div class="px-6 py-6">
    {{-- Block Heading --}}
    <div class="mb-6">
        <input x-show="!previewMode" type="text" x-model="block.data.heading"
               placeholder="{{ __('Specifications heading...') }}"
               class="w-full text-xl font-bold text-slate-800 bg-transparent border-none p-0 focus:ring-0">
        <h2 x-show="previewMode" class="text-xl font-bold text-slate-800">
            <span x-text="block.data.heading || '{{ __('Specificații') }}'"></span>
            <div class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
        </h2>
        <div x-show="!previewMode" class="h-1 w-16 bg-green-500 mt-2 rounded"></div>
    </div>

    {{-- Sections --}}
    <div class="space-y-6">
        <template x-for="(section, sectionIndex) in (block.data.sections || [])" :key="sectionIndex">
            <div class="relative group/section">
                {{-- Section Controls (Edit Mode) --}}
                <div x-show="!previewMode" class="absolute -right-2 -top-2 flex items-center gap-1 opacity-0 group-hover/section:opacity-100 transition-opacity z-10">
                    <button type="button" @click="block.data.sections.splice(sectionIndex, 1)"
                            class="p-1.5 bg-red-500 text-white rounded-full shadow hover:bg-red-600 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="bg-slate-50 rounded-lg p-4" :class="!previewMode ? 'border-2 border-dashed border-slate-200 hover:border-blue-300' : ''">
                    {{-- Section Title --}}
                    <input x-show="!previewMode" type="text" x-model="section.title"
                           placeholder="{{ __('Section title...') }}"
                           class="w-full text-lg font-semibold text-slate-800 bg-transparent border-none p-0 focus:ring-0 mb-3">
                    <h3 x-show="previewMode && section.title" class="text-lg font-semibold text-slate-800 mb-3" x-text="section.title"></h3>

                    {{-- Section Content (Paragraph) --}}
                    <template x-if="section.type === 'paragraph'">
                        <div>
                            <textarea x-show="!previewMode" x-model="section.content" rows="4"
                                      placeholder="{{ __('Enter paragraph text...') }}"
                                      class="w-full text-sm text-slate-600 bg-white border border-slate-200 rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                            <p x-show="previewMode" class="text-sm text-slate-600 whitespace-pre-line" x-text="section.content"></p>
                        </div>
                    </template>

                    {{-- Section Content (List) --}}
                    <template x-if="section.type === 'list'">
                        <div>
                            {{-- Edit Mode --}}
                            <div x-show="!previewMode" class="space-y-2">
                                <template x-for="(item, itemIndex) in (section.items || [])" :key="itemIndex">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <input type="text" x-model="section.items[itemIndex]"
                                               placeholder="{{ __('List item...') }}"
                                               class="flex-1 text-sm bg-white border border-slate-200 rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500">
                                        <button type="button" @click="section.items.splice(itemIndex, 1)"
                                                class="p-1 text-slate-400 hover:text-red-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="section.items = section.items || []; section.items.push('')"
                                        class="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('Add item') }}
                                </button>
                            </div>
                            {{-- Preview Mode --}}
                            <ul x-show="previewMode" class="space-y-2">
                                <template x-for="(item, itemIndex) in (section.items || [])" :key="itemIndex">
                                    <li class="flex items-start gap-2 text-sm text-slate-600">
                                        <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span x-text="item"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    {{-- Type Selector (Edit Mode) --}}
                    <div x-show="!previewMode" class="mt-3 pt-3 border-t border-slate-200">
                        <div class="flex items-center gap-4 text-xs">
                            <span class="text-slate-500">{{ __('Type') }}:</span>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" :name="'section_type_' + sectionIndex" value="paragraph" x-model="section.type"
                                       class="text-blue-600 focus:ring-blue-500">
                                <span class="text-slate-600">{{ __('Paragraph') }}</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" :name="'section_type_' + sectionIndex" value="list" x-model="section.type"
                                       class="text-blue-600 focus:ring-blue-500">
                                <span class="text-slate-600">{{ __('List') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Add Section Button (Edit Mode) --}}
    <div x-show="!previewMode" class="mt-4">
        <button type="button" @click="block.data.sections = block.data.sections || []; block.data.sections.push({ title: '', type: 'paragraph', content: '', items: [] })"
                class="w-full py-2.5 border-2 border-dashed border-slate-300 rounded-lg text-slate-500 hover:border-blue-500 hover:text-blue-600 transition-colors flex items-center justify-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('Add specification section') }}
        </button>
    </div>

    {{-- Empty State --}}
    <div x-show="(!block.data.sections || block.data.sections.length === 0) && previewMode" class="text-center py-8 text-slate-400">
        <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-sm">{{ __('No specifications added') }}</p>
    </div>
</div>
