{{-- Heading Widget (as block) --}}
<div class="heading-widget px-4 py-3">
    {{-- Edit Mode --}}
    <div x-show="!previewMode" class="flex items-center gap-2">
        <select x-model="block.data.level"
                class="w-16 h-8 text-xs border border-slate-200 rounded px-1 focus:border-slate-400 focus:ring-0 bg-slate-50">
            <option value="h2">H2</option>
            <option value="h3">H3</option>
            <option value="h4">H4</option>
        </select>
        <input type="text"
               x-model="block.data.text"
               placeholder="{{ __('Heading text...') }}"
               class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 placeholder:text-slate-400"
               :class="{
                   'text-2xl font-bold': block.data.level === 'h2',
                   'text-xl font-semibold': block.data.level === 'h3',
                   'text-lg font-medium': block.data.level === 'h4'
               }">
    </div>

    {{-- Preview Mode --}}
    <div x-show="previewMode" class="p-3">
        <h2 x-show="block.data.level === 'h2'" class="text-2xl font-bold text-slate-900" x-text="block.data.text"></h2>
        <h3 x-show="block.data.level === 'h3' || !block.data.level" class="text-xl font-semibold text-slate-900" x-text="block.data.text"></h3>
        <h4 x-show="block.data.level === 'h4'" class="text-lg font-medium text-slate-900" x-text="block.data.text"></h4>
    </div>
</div>
