{{-- Divider Widget (as block) --}}
<div class="divider-widget px-4 py-4">
    {{-- Edit Mode --}}
    <div x-show="!previewMode" class="flex items-center gap-3">
        <select x-model="block.data.style" class="text-xs border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-0 bg-slate-50">
            <option value="solid">{{ __('Solid') }}</option>
            <option value="dashed">{{ __('Dashed') }}</option>
            <option value="dotted">{{ __('Dotted') }}</option>
        </select>
        <select x-model="block.data.color" class="text-xs border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-0 bg-slate-50">
            <option value="gray">{{ __('Gray') }}</option>
            <option value="slate">{{ __('Dark') }}</option>
            <option value="blue">{{ __('Blue') }}</option>
            <option value="green">{{ __('Green') }}</option>
        </select>
        <div class="flex-1 border-t"
             :class="{
                 'border-solid': block.data.style === 'solid' || !block.data.style,
                 'border-dashed': block.data.style === 'dashed',
                 'border-dotted': block.data.style === 'dotted',
                 'border-gray-300': block.data.color === 'gray' || !block.data.color,
                 'border-slate-400': block.data.color === 'slate',
                 'border-blue-300': block.data.color === 'blue',
                 'border-green-300': block.data.color === 'green'
             }"></div>
    </div>

    {{-- Preview Mode --}}
    <div x-show="previewMode">
        <div class="border-t"
             :class="{
                 'border-solid': block.data.style === 'solid' || !block.data.style,
                 'border-dashed': block.data.style === 'dashed',
                 'border-dotted': block.data.style === 'dotted',
                 'border-gray-300': block.data.color === 'gray' || !block.data.color,
                 'border-slate-400': block.data.color === 'slate',
                 'border-blue-300': block.data.color === 'blue',
                 'border-green-300': block.data.color === 'green'
             }"></div>
    </div>
</div>
