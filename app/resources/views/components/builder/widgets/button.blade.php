{{-- Button Widget (as block) --}}
<div class="button-widget px-4 py-3"
     :class="{
         'text-left': block.data.align === 'left' || !block.data.align,
         'text-center': block.data.align === 'center',
         'text-right': block.data.align === 'right'
     }">
    {{-- Edit Mode --}}
    <div x-show="!previewMode">
        <div class="flex items-center gap-2 mb-2 flex-wrap">
            <select x-model="block.data.style" class="text-xs border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-0 bg-slate-50">
                <option value="primary">{{ __('Primary') }}</option>
                <option value="secondary">{{ __('Secondary') }}</option>
                <option value="outline">{{ __('Outline') }}</option>
            </select>
            <select x-model="block.data.align" class="text-xs border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-0 bg-slate-50">
                <option value="left">{{ __('Left') }}</option>
                <option value="center">{{ __('Center') }}</option>
                <option value="right">{{ __('Right') }}</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <input type="text" x-model="block.data.text"
                   placeholder="{{ __('Button text') }}"
                   class="flex-1 text-sm border border-slate-200 rounded px-3 py-1.5 focus:border-slate-400 focus:ring-0">
            <input type="text" x-model="block.data.url"
                   placeholder="{{ __('URL (optional)') }}"
                   class="flex-1 text-sm border border-slate-200 rounded px-3 py-1.5 focus:border-slate-400 focus:ring-0">
        </div>

        {{-- Button Preview --}}
        <div class="mt-3"
             :class="{
                 'text-left': block.data.align === 'left' || !block.data.align,
                 'text-center': block.data.align === 'center',
                 'text-right': block.data.align === 'right'
             }">
            <span class="inline-block px-4 py-2 rounded-lg font-medium text-sm cursor-default"
                  :class="{
                      'bg-blue-600 text-white': block.data.style === 'primary' || !block.data.style,
                      'bg-slate-600 text-white': block.data.style === 'secondary',
                      'border-2 border-blue-600 text-blue-600': block.data.style === 'outline'
                  }"
                  x-text="block.data.text || '{{ __('Button') }}'"></span>
        </div>
    </div>

    {{-- Preview Mode --}}
    <div x-show="previewMode">
        <a :href="block.data.url || '#'"
           class="inline-block px-4 py-2 rounded-lg font-medium text-sm transition-colors"
           :class="{
               'bg-blue-600 text-white hover:bg-blue-700': block.data.style === 'primary' || !block.data.style,
               'bg-slate-600 text-white hover:bg-slate-700': block.data.style === 'secondary',
               'border-2 border-blue-600 text-blue-600 hover:bg-blue-50': block.data.style === 'outline'
           }"
           x-text="block.data.text || '{{ __('Button') }}'"></a>
    </div>
</div>
