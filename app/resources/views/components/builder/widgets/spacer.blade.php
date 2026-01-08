{{-- Spacer Widget (as block) --}}
<div class="spacer-widget px-4 py-3">
    {{-- Edit Mode --}}
    <div x-show="!previewMode" class="flex items-center gap-3 py-2">
        <span class="text-xs text-slate-500">{{ __('Height:') }}</span>
        <input type="range" x-model="block.data.height" min="8" max="80" step="4"
               class="flex-1 h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer">
        <span class="text-xs text-slate-500 w-10 text-right" x-text="(block.data.height || 24) + 'px'"></span>
    </div>

    {{-- Spacer preview (both modes) --}}
    <div class="bg-slate-100/50 rounded border border-dashed border-slate-200"
         x-show="!previewMode"
         :style="'height: ' + (block.data.height || 24) + 'px'">
    </div>

    {{-- Preview mode - just the space --}}
    <div x-show="previewMode" :style="'height: ' + (block.data.height || 24) + 'px'"></div>
</div>
