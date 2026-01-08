{{-- Add Block Buttons Grid --}}
<p class="text-xs text-slate-500 mt-4 font-medium">{{ __('Add new block') }}:</p>
<div class="grid grid-cols-2 gap-2">
    {{-- Heading Block (for templates) --}}
    @if(isset($showHeading) && $showHeading)
    <button type="button" @click="addBlock('heading')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-purple-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Heading') }}</span>
        </div>
    </button>
    @endif

    {{-- Text/Content Block --}}
    <button type="button" @click="addBlock('content')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-green-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Text') }}</span>
        </div>
    </button>

    {{-- Image Block --}}
    <button type="button" @click="addBlock('image')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-blue-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Image') }}</span>
        </div>
    </button>

    {{-- Divider Block --}}
    <button type="button" @click="addBlock('divider')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-slate-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Divider') }}</span>
        </div>
    </button>

    {{-- Terms Block --}}
    <button type="button" @click="addBlock('terms')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-amber-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Terms') }}</span>
        </div>
    </button>

    {{-- Signature Block --}}
    <button type="button" @click="addBlock('signature')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-pink-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Signature') }}</span>
        </div>
    </button>

    {{-- Spacer Block --}}
    <button type="button" @click="addBlock('spacer')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Spacer') }}</span>
        </div>
    </button>

    {{-- Table Block --}}
    <button type="button" @click="addBlock('table')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-indigo-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Table') }}</span>
        </div>
    </button>

    {{-- Quote Block --}}
    <button type="button" @click="addBlock('quote')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-violet-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Quote') }}</span>
        </div>
    </button>

    {{-- Columns Block --}}
    <button type="button" @click="addBlock('columns')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-teal-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Columns') }}</span>
        </div>
    </button>

    {{-- Page Break Block --}}
    <button type="button" @click="addBlock('page_break')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-rose-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6M4 12h16M4 7h16"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Page Break') }}</span>
        </div>
    </button>

    {{-- Paragraph Block --}}
    <button type="button" @click="addBlock('paragraph')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-emerald-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Paragraph') }}</span>
        </div>
    </button>

    {{-- List Block --}}
    <button type="button" @click="addBlock('list')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-orange-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('List') }}</span>
        </div>
    </button>

    {{-- Highlight Block --}}
    <button type="button" @click="addBlock('highlight')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-yellow-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Highlight') }}</span>
        </div>
    </button>

    {{-- Note Block --}}
    <button type="button" @click="addBlock('note')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-sky-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Note') }}</span>
        </div>
    </button>

    {{-- Optional Services Block (for offers) --}}
    @if(isset($showOptionalServices) && $showOptionalServices)
    <button type="button" @click="addBlock('optional_services')"
            class="p-2 text-left border border-slate-200 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-colors">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-cyan-100 flex items-center justify-center">
                <svg class="w-3 h-3 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </div>
            <span class="text-xs font-medium">{{ __('Optional') }}</span>
        </div>
    </button>
    @endif
</div>

{{-- Widget Palette - Draggable widgets --}}
<p class="text-xs text-slate-500 mt-6 font-medium">{{ __('Widgets') }} <span class="text-slate-400">({{ __('drag to canvas') }})</span>:</p>
<div id="widgetPalette" x-ref="widgetPalette" class="grid grid-cols-3 gap-2 mt-2">
    {{-- Text Widget --}}
    <div data-widget-type="widget_text" class="widget-source p-2 border border-slate-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-grab text-center">
        <div class="w-6 h-6 mx-auto mb-1 rounded bg-slate-100 flex items-center justify-center">
            <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
            </svg>
        </div>
        <span class="text-xs font-medium text-slate-600">{{ __('Text') }}</span>
    </div>

    {{-- Heading Widget --}}
    <div data-widget-type="widget_heading" class="widget-source p-2 border border-slate-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-grab text-center">
        <div class="w-6 h-6 mx-auto mb-1 rounded bg-purple-100 flex items-center justify-center">
            <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4"/>
            </svg>
        </div>
        <span class="text-xs font-medium text-slate-600">{{ __('Heading') }}</span>
    </div>

    {{-- Image Widget --}}
    <div data-widget-type="widget_image" class="widget-source p-2 border border-slate-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-grab text-center">
        <div class="w-6 h-6 mx-auto mb-1 rounded bg-blue-100 flex items-center justify-center">
            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <span class="text-xs font-medium text-slate-600">{{ __('Image') }}</span>
    </div>

    {{-- List Widget --}}
    <div data-widget-type="widget_list" class="widget-source p-2 border border-slate-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-grab text-center">
        <div class="w-6 h-6 mx-auto mb-1 rounded bg-orange-100 flex items-center justify-center">
            <svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
        </div>
        <span class="text-xs font-medium text-slate-600">{{ __('List') }}</span>
    </div>

    {{-- Stat Card Widget --}}
    <div data-widget-type="widget_stat_card" class="widget-source p-2 border border-slate-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-grab text-center">
        <div class="w-6 h-6 mx-auto mb-1 rounded bg-green-100 flex items-center justify-center">
            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        <span class="text-xs font-medium text-slate-600">{{ __('Stat') }}</span>
    </div>

    {{-- Feature Box Widget --}}
    <div data-widget-type="widget_feature_box" class="widget-source p-2 border border-slate-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-grab text-center">
        <div class="w-6 h-6 mx-auto mb-1 rounded bg-indigo-100 flex items-center justify-center">
            <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
        </div>
        <span class="text-xs font-medium text-slate-600">{{ __('Feature') }}</span>
    </div>

    {{-- Icon + Text Widget --}}
    <div data-widget-type="widget_icon_text" class="widget-source p-2 border border-slate-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-grab text-center">
        <div class="w-6 h-6 mx-auto mb-1 rounded bg-teal-100 flex items-center justify-center">
            <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <span class="text-xs font-medium text-slate-600">{{ __('Icon+Text') }}</span>
    </div>

    {{-- Testimonial Widget --}}
    <div data-widget-type="widget_testimonial" class="widget-source p-2 border border-slate-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-grab text-center">
        <div class="w-6 h-6 mx-auto mb-1 rounded bg-pink-100 flex items-center justify-center">
            <svg class="w-3 h-3 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
        </div>
        <span class="text-xs font-medium text-slate-600">{{ __('Quote') }}</span>
    </div>

    {{-- Price Box Widget --}}
    <div data-widget-type="widget_price_box" class="widget-source p-2 border border-slate-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-grab text-center">
        <div class="w-6 h-6 mx-auto mb-1 rounded bg-amber-100 flex items-center justify-center">
            <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <span class="text-xs font-medium text-slate-600">{{ __('Price') }}</span>
    </div>

    {{-- Divider Widget --}}
    <div data-widget-type="widget_divider" class="widget-source p-2 border border-slate-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-grab text-center">
        <div class="w-6 h-6 mx-auto mb-1 rounded bg-gray-100 flex items-center justify-center">
            <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            </svg>
        </div>
        <span class="text-xs font-medium text-slate-600">{{ __('Divider') }}</span>
    </div>

    {{-- Spacer Widget --}}
    <div data-widget-type="widget_spacer" class="widget-source p-2 border border-slate-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-grab text-center">
        <div class="w-6 h-6 mx-auto mb-1 rounded bg-slate-100 flex items-center justify-center">
            <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
            </svg>
        </div>
        <span class="text-xs font-medium text-slate-600">{{ __('Spacer') }}</span>
    </div>

    {{-- Button Widget --}}
    <div data-widget-type="widget_button" class="widget-source p-2 border border-slate-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors cursor-grab text-center">
        <div class="w-6 h-6 mx-auto mb-1 rounded bg-blue-100 flex items-center justify-center">
            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
            </svg>
        </div>
        <span class="text-xs font-medium text-slate-600">{{ __('Button') }}</span>
    </div>
</div>
