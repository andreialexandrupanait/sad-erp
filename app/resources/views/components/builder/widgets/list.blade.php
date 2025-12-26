{{-- List Widget (as block) --}}
<div class="list-widget px-4 py-3" x-data="{ initItems() { if (!block.data.items) block.data.items = ['']; } }" x-init="initItems()">
    {{-- Edit Mode --}}
    <div x-show="!previewMode">
        <div class="flex items-center gap-2 mb-2">
            <select x-model="block.data.type" class="text-xs border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-0 bg-slate-50">
                <option value="bullet">{{ __('Bullet') }}</option>
                <option value="numbered">{{ __('Numbered') }}</option>
                <option value="check">{{ __('Checklist') }}</option>
            </select>
        </div>
        <ul class="space-y-1">
            <template x-for="(item, idx) in block.data.items" :key="'wlist_'+block.id+'_'+idx">
                <li class="flex items-start gap-2 group">
                    <span class="flex-shrink-0 mt-2 text-slate-400 text-sm"
                          x-text="block.data.type === 'numbered' ? (idx + 1) + '.' : (block.data.type === 'check' ? '☐' : '•')"></span>
                    <input type="text"
                           x-model="block.data.items[idx]"
                           @keydown.enter.prevent="block.data.items.splice(idx + 1, 0, ''); $nextTick(() => $el.parentElement.nextElementSibling?.querySelector('input')?.focus())"
                           @keydown.backspace="if($el.value === '' && block.data.items.length > 1) { $event.preventDefault(); block.data.items.splice(idx, 1); }"
                           placeholder="{{ __('List item...') }}"
                           class="flex-1 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 placeholder:text-slate-400">
                    <button type="button"
                            @click="block.data.items.splice(idx, 1)"
                            x-show="block.data.items.length > 1"
                            class="flex-shrink-0 opacity-0 group-hover:opacity-100 p-1 text-slate-400 hover:text-red-500 transition-opacity">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </li>
            </template>
        </ul>
        <button type="button"
                @click="block.data.items.push('')"
                class="mt-2 text-xs text-slate-500 hover:text-slate-700">
            + {{ __('Add item') }}
        </button>
    </div>

    {{-- Preview Mode --}}
    <div x-show="previewMode">
        <ul :class="block.data.type === 'numbered' ? 'list-decimal' : 'list-disc'" class="ml-5 space-y-1">
            <template x-for="(item, idx) in block.data.items" :key="'wpreview_'+block.id+'_'+idx">
                <li x-show="item.trim()" class="text-sm text-slate-700">
                    <span x-show="block.data.type === 'check'" class="mr-1">☐</span>
                    <span x-text="item"></span>
                </li>
            </template>
        </ul>
    </div>
</div>
