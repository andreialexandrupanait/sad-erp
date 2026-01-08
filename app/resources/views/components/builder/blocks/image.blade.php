@props(['isTemplate' => false])

<div class="text-center px-4 py-4">
    <template x-if="block.data.src">
        <div class="relative group">
            <img :src="block.data.src" :alt="block.data.alt || ''" class="max-w-full h-auto rounded-xl mx-auto shadow-sm">
            <label class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded-xl">
                <span class="text-white text-sm font-medium">{{ __('Change image') }}</span>
                <input type="file" accept="image/*" class="hidden" @change="handleBlockImageUpload($event, block)">
            </label>
        </div>
    </template>
    <template x-if="!block.data.src">
        <label class="block cursor-pointer">
            <div class="border-2 border-dashed border-slate-200 rounded-xl p-10 hover:border-blue-300 hover:bg-blue-50/50 transition-all">
                <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-slate-500 font-medium">{{ __('Click to upload image') }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ __('PNG, JPG up to 10MB') }}</p>
            </div>
            <input type="file" accept="image/*" class="hidden" @change="handleBlockImageUpload($event, block)">
        </label>
    </template>
</div>
