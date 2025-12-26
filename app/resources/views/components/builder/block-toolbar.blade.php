{{-- Block hover toolbar - Plutio style at top-right --}}
@props(['blockIndex' => 'blockIndex'])

<div class="absolute -top-3 right-4 opacity-0 group-hover:opacity-100 transition-all duration-200 z-20">
    <div class="flex items-center gap-0.5 bg-slate-800 rounded-lg shadow-lg px-1 py-1">
        {{-- Drag handle --}}
        <div class="block-drag-handle w-7 h-7 rounded hover:bg-slate-700 flex items-center justify-center cursor-grab" title="{{ __('Drag to reorder') }}">
            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
            </svg>
        </div>
        {{-- Up button --}}
        <button type="button" @click.stop="moveBlockUp({{ $blockIndex }})" :disabled="{{ $blockIndex }} === 0"
                class="w-7 h-7 rounded hover:bg-slate-700 disabled:opacity-30 flex items-center justify-center" title="{{ __('Move up') }}">
            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
        </button>
        {{-- Down button --}}
        <button type="button" @click.stop="moveBlockDown({{ $blockIndex }})" :disabled="{{ $blockIndex }} === blocks.length - 1"
                class="w-7 h-7 rounded hover:bg-slate-700 disabled:opacity-30 flex items-center justify-center" title="{{ __('Move down') }}">
            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        {{-- Divider --}}
        <div class="w-px h-5 bg-slate-600 mx-0.5"></div>
        {{-- Settings/Style button --}}
        <button type="button" @click.stop="selectedBlockId = selectedBlockId === block.id ? null : block.id; activeTab = 'blocks'"
                class="w-7 h-7 rounded hover:bg-slate-700 flex items-center justify-center"
                :class="selectedBlockId === block.id ? 'bg-blue-600' : ''"
                title="{{ __('Block settings') }}">
            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </button>
        {{-- Duplicate button --}}
        <button type="button" @click.stop="duplicateBlock({{ $blockIndex }})"
                class="w-7 h-7 rounded hover:bg-slate-700 flex items-center justify-center" title="{{ __('Duplicate') }}">
            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
        </button>
        {{-- Delete button --}}
        <button type="button" @click.stop="removeBlock({{ $blockIndex }})"
                class="w-7 h-7 rounded hover:bg-red-600 flex items-center justify-center" title="{{ __('Delete') }}">
            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </button>
    </div>
</div>
