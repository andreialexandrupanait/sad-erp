@props(['isTemplate' => false])

<div class="px-4 py-4">
    <div class="flex gap-2 mb-2">
        <button type="button" @click="block.data.listType = 'bullet'"
                :class="{'bg-slate-200': block.data.listType === 'bullet' || !block.data.listType}"
                class="px-2 py-1 text-xs rounded hover:bg-slate-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <button type="button" @click="block.data.listType = 'numbered'"
                :class="{'bg-slate-200': block.data.listType === 'numbered'}"
                class="px-2 py-1 text-xs rounded hover:bg-slate-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
            </svg>
        </button>
    </div>
    <div class="space-y-1">
        <template x-for="(item, idx) in block.data.items" :key="idx">
            <div class="flex items-center gap-2">
                <span class="text-slate-400 w-4 text-center" x-text="block.data.listType === 'numbered' ? (idx + 1) + '.' : '•'"></span>
                <input type="text" x-model="block.data.items[idx]"
                       @keydown.enter.prevent="block.data.items.splice(idx + 1, 0, ''); $nextTick(() => $event.target.parentElement.nextElementSibling?.querySelector('input')?.focus())"
                       @keydown.backspace="if (!block.data.items[idx] && block.data.items.length > 1) { block.data.items.splice(idx, 1); $event.preventDefault(); }"
                       placeholder="{{ __('List item...') }}"
                       class="flex-1 text-slate-600 bg-transparent border-none focus:ring-0 p-0">
                <button type="button" @click="block.data.items.splice(idx, 1)" x-show="block.data.items.length > 1"
                        class="text-slate-400 hover:text-red-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>
    <button type="button" @click="block.data.items.push('')"
            class="mt-2 text-sm text-slate-500 hover:text-slate-700">
        + {{ __('Add item') }}
    </button>
</div>
