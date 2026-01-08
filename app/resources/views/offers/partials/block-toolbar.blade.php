{{-- Block Reorder Toolbar (Edit Mode Only) --}}
<div x-show="!previewMode" class="absolute -left-12 top-4 flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
    <button @click="moveBlockUp(blockIndex)" :disabled="blockIndex === 0"
            class="w-8 h-8 bg-white rounded-lg shadow border border-slate-200 flex items-center justify-center hover:bg-slate-50 disabled:opacity-30">
        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
        </svg>
    </button>
    <button @click="moveBlockDown(blockIndex)" :disabled="blockIndex === blocks.length - 1"
            class="w-8 h-8 bg-white rounded-lg shadow border border-slate-200 flex items-center justify-center hover:bg-slate-50 disabled:opacity-30">
        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
</div>
