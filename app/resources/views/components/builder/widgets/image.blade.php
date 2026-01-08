{{-- Image Widget (as block) --}}
<div class="image-widget px-4 py-3">
    {{-- Edit Mode --}}
    <div x-show="!previewMode">
        {{-- No image yet --}}
        <div x-show="!block.data.src" class="border-2 border-dashed border-slate-200 rounded-lg p-6 text-center hover:border-slate-300 transition-colors">
            <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm text-slate-500 mb-2">{{ __('Upload an image') }}</p>
            <label class="inline-block px-3 py-1.5 text-xs bg-slate-100 hover:bg-slate-200 rounded cursor-pointer transition-colors">
                {{ __('Choose File') }}
                <input type="file" accept="image/*" class="hidden" @change="handleBlockImageUpload($event, block)">
            </label>
            <p class="text-xs text-slate-400 mt-2">{{ __('Or paste an image URL:') }}</p>
            <input type="text" x-model="block.data.src" placeholder="https://..."
                   class="mt-1 w-full max-w-xs text-xs border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-0">
        </div>

        {{-- Image preview --}}
        <div x-show="block.data.src" class="relative group">
            <img :src="block.data.src" :alt="block.data.alt" class="max-w-full h-auto rounded-lg" :style="'width: ' + (block.data.width || '100%')">
            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                <label class="p-1.5 bg-white/90 hover:bg-white rounded shadow cursor-pointer" title="{{ __('Replace') }}">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="file" accept="image/*" class="hidden" @change="handleBlockImageUpload($event, block)">
                </label>
                <button type="button" @click="block.data.src = ''" class="p-1.5 bg-white/90 hover:bg-white rounded shadow" title="{{ __('Remove') }}">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
            <input type="text" x-model="block.data.caption" placeholder="{{ __('Add a caption...') }}"
                   class="mt-2 w-full text-xs text-center text-slate-500 bg-transparent border-none focus:ring-0 placeholder:text-slate-400">
        </div>
    </div>

    {{-- Preview Mode --}}
    <div x-show="previewMode && block.data.src" class="text-center">
        <img :src="block.data.src" :alt="block.data.alt" class="max-w-full h-auto rounded-lg mx-auto" :style="'width: ' + (block.data.width || '100%')">
        <p x-show="block.data.caption" class="mt-2 text-xs text-slate-500 italic" x-text="block.data.caption"></p>
    </div>
</div>
