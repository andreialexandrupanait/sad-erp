{{-- Widget Container - Drop zone for widgets inside blocks --}}
<div class="widgets-list mt-4 min-h-[40px] border-2 border-dashed rounded-lg p-2 transition-colors"
     :class="(block.data.widgets && block.data.widgets.length > 0) ? 'border-slate-200 bg-slate-50/50' : 'border-slate-300 hover:border-blue-400 hover:bg-blue-50/50'"
     x-ref="widgetContainer"
     x-init="$nextTick(() => {
         console.log('Widget container x-init for block:', block.id);
         if (typeof Sortable !== 'undefined') {
             const container = $el;
             const currentBlockId = block.id;
             console.log('Creating Sortable for widget container:', currentBlockId);
             Sortable.create(container, {
                 animation: 150,
                 handle: '.widget-drag-handle',
                 ghostClass: 'widget-ghost',
                 chosenClass: 'widget-chosen',
                 group: 'widgets',
                 onAdd: (evt) => {
                     const widgetType = evt.item.dataset.widgetType;
                     if (widgetType) {
                         evt.item.remove();
                         if (!block.data.widgets) block.data.widgets = [];
                         const actualType = widgetType.replace('widget_', '');
                         const defaults = {
                             'text': { content: '' },
                             'heading': { text: '', level: 'h3' },
                             'image': { src: '', alt: '', caption: '', width: '100%' },
                             'list': { type: 'bullet', items: [''] },
                             'icon_text': { icon: 'check-circle', iconColor: 'green', text: '' },
                             'stat_card': { value: '', label: '', icon: 'trending-up', color: 'green' },
                             'feature_box': { icon: 'star', title: '', description: '', color: 'blue' },
                             'testimonial': { quote: '', author: '', role: '', avatar: '' },
                             'price_box': { title: '', price: '', period: '/month', features: [''], highlighted: false },
                             'divider': { style: 'solid', color: 'gray' },
                             'spacer': { height: 24 },
                             'button': { text: 'Click here', url: '', style: 'primary', align: 'left' },
                         };
                         const newWidget = {
                             id: actualType + '_' + Date.now(),
                             type: actualType,
                             data: defaults[actualType] || {},
                         };
                         block.data.widgets.splice(evt.newIndex, 0, newWidget);
                     }
                 },
             });
         }
     })"
     x-show="!previewMode || (block.data.widgets && block.data.widgets.length > 0)">

    {{-- Header label --}}
    <div x-show="!previewMode" class="flex items-center gap-2 mb-2 pb-2 border-b border-dashed border-slate-200">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
        </svg>
        <span class="text-xs font-medium text-slate-500">{{ __('Widgets') }}</span>
    </div>

    {{-- Empty state --}}
    <div x-show="!block.data.widgets || block.data.widgets.length === 0"
         class="text-center py-4 text-xs text-slate-400">
        <svg class="w-6 h-6 mx-auto mb-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        {{ __('Drag widgets here from sidebar') }}
    </div>

    {{-- Widgets list --}}
    <template x-for="(widget, widgetIndex) in (block.data.widgets || [])" :key="widget.id">
        <div class="widget-item relative group bg-white rounded-lg border border-slate-200 mb-2 last:mb-0"
             :class="previewMode ? '' : 'hover:border-blue-300'">

            {{-- Widget toolbar --}}
            <div x-show="!previewMode"
                 class="absolute -top-2 left-1/2 -translate-x-1/2 flex items-center gap-0.5 bg-slate-700 rounded px-1 py-0.5 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                {{-- Drag handle --}}
                <div class="widget-drag-handle w-5 h-5 flex items-center justify-center cursor-grab hover:bg-slate-600 rounded">
                    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                    </svg>
                </div>
                {{-- Delete widget --}}
                <button type="button" @click="block.data.widgets.splice(widgetIndex, 1)"
                        class="w-5 h-5 flex items-center justify-center hover:bg-red-600 rounded">
                    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Widget content based on type --}}
            <div class="p-3">
                {{-- Text Widget --}}
                <template x-if="widget.type === 'text'">
                    <div>
                        <textarea x-show="!previewMode" x-model="widget.data.content" rows="2"
                                  placeholder="{{ __('Enter text...') }}"
                                  class="w-full text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded p-2 focus:border-slate-400 resize-none"></textarea>
                        <div x-show="previewMode" class="text-sm text-slate-700" x-html="widget.data.content"></div>
                    </div>
                </template>

                {{-- Heading Widget --}}
                <template x-if="widget.type === 'heading'">
                    <div>
                        <div x-show="!previewMode" class="flex items-center gap-2">
                            <select x-model="widget.data.level" class="text-xs border border-slate-200 rounded px-2 py-1">
                                <option value="h2">H2</option>
                                <option value="h3">H3</option>
                                <option value="h4">H4</option>
                            </select>
                            <input type="text" x-model="widget.data.text" placeholder="{{ __('Heading text...') }}"
                                   class="flex-1 text-base font-semibold bg-slate-50 border border-slate-200 rounded px-2 py-1">
                        </div>
                        <component x-show="previewMode" :is="widget.data.level || 'h3'" class="font-semibold text-slate-900" x-text="widget.data.text"></component>
                    </div>
                </template>

                {{-- Stat Card Widget --}}
                <template x-if="widget.type === 'stat_card'">
                    <div class="flex items-center gap-3 p-2 bg-slate-50 rounded-lg">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <input x-show="!previewMode" type="text" x-model="widget.data.value" placeholder="100%"
                                   class="w-full text-xl font-bold text-slate-900 bg-transparent border-none p-0">
                            <div x-show="previewMode" class="text-xl font-bold text-slate-900" x-text="widget.data.value"></div>
                            <input x-show="!previewMode" type="text" x-model="widget.data.label" placeholder="{{ __('Label') }}"
                                   class="w-full text-xs text-slate-500 bg-transparent border-none p-0">
                            <div x-show="previewMode" class="text-xs text-slate-500" x-text="widget.data.label"></div>
                        </div>
                    </div>
                </template>

                {{-- Feature Box Widget --}}
                <template x-if="widget.type === 'feature_box'">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <input x-show="!previewMode" type="text" x-model="widget.data.title" placeholder="{{ __('Feature title') }}"
                                   class="w-full text-sm font-medium text-slate-900 bg-transparent border-none p-0">
                            <div x-show="previewMode" class="text-sm font-medium text-slate-900" x-text="widget.data.title"></div>
                            <textarea x-show="!previewMode" x-model="widget.data.description" placeholder="{{ __('Description...') }}" rows="1"
                                      class="w-full text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded p-1 mt-1 resize-none"></textarea>
                            <div x-show="previewMode" class="text-xs text-slate-500 mt-1" x-text="widget.data.description"></div>
                        </div>
                    </div>
                </template>

                {{-- Icon + Text Widget --}}
                <template x-if="widget.type === 'icon_text'">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <input x-show="!previewMode" type="text" x-model="widget.data.text" placeholder="{{ __('Text with icon...') }}"
                               class="flex-1 text-sm text-slate-700 bg-transparent border-none p-0">
                        <span x-show="previewMode" class="text-sm text-slate-700" x-text="widget.data.text"></span>
                    </div>
                </template>

                {{-- Divider Widget --}}
                <template x-if="widget.type === 'divider'">
                    <div class="border-t border-slate-300 my-2"></div>
                </template>

                {{-- Spacer Widget --}}
                <template x-if="widget.type === 'spacer'">
                    <div :style="'height: ' + (widget.data.height || 24) + 'px'" class="bg-slate-50 rounded"></div>
                </template>

                {{-- Button Widget --}}
                <template x-if="widget.type === 'button'">
                    <div :class="{'text-center': widget.data.align === 'center', 'text-right': widget.data.align === 'right'}">
                        <span class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium"
                              x-text="widget.data.text || '{{ __('Button') }}'"></span>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
