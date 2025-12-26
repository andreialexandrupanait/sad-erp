{{-- Brands Block - Builder View --}}
<div class="px-6 py-8 bg-gradient-to-r from-slate-50 to-slate-100">
    {{-- Block Heading --}}
    <div class="mb-6 text-center">
        <textarea x-show="!previewMode" x-model="block.data.heading"
               placeholder="{{ __('Brands heading...') }}"
               x-ref="brandsHeading"
               x-effect="$nextTick(() => { if($refs.brandsHeading) { $refs.brandsHeading.style.height = 'auto'; $refs.brandsHeading.style.height = $refs.brandsHeading.scrollHeight + 'px'; } })"
               x-on:input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
               class="w-full text-xl font-bold text-slate-800 bg-transparent border-none p-0 focus:ring-0 text-center resize-none overflow-hidden min-h-[28px]"></textarea>
        <h2 x-show="previewMode" class="text-xl font-bold text-slate-800 text-center">
            <span x-text="block.data.heading || '{{ __('Branduri care au ales să se bazeze pe experiența noastră') }}'"></span>
        </h2>
    </div>

    {{-- Single Image Mode --}}
    <template x-if="block.data.mode !== 'logos'">
        <div class="max-w-4xl mx-auto">
            {{-- Image Display --}}
            <div x-show="block.data.image" class="relative group">
                <img :src="block.data.image" :alt="block.data.heading || 'Brands'"
                     class="w-full rounded-lg shadow-sm">

                {{-- Edit Overlay (Edit Mode) --}}
                <div x-show="!previewMode"
                     class="absolute inset-0 bg-slate-900/50 opacity-0 group-hover:opacity-100 rounded-lg flex items-center justify-center gap-3 transition-opacity">
                    <button type="button" @click="$refs.brandsImageInput.click()"
                            class="px-4 py-2 bg-white/90 hover:bg-white text-slate-700 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ __('Change Image') }}
                    </button>
                    <button type="button" @click="block.data.image = ''"
                            class="px-4 py-2 bg-red-500/90 hover:bg-red-500 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        {{ __('Remove') }}
                    </button>
                </div>
            </div>

            {{-- Upload Area (when no image) --}}
            <div x-show="!block.data.image && !previewMode"
                 @click="$refs.brandsImageInput.click()"
                 class="border-2 border-dashed border-slate-300 hover:border-blue-400 rounded-xl p-12 text-center cursor-pointer transition-colors group">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-slate-600 group-hover:text-blue-600 font-medium mb-1">{{ __('Click to upload brands image') }}</p>
                <p class="text-sm text-slate-400">{{ __('PNG, JPG up to 5MB') }}</p>
            </div>

            {{-- Empty State (Preview Mode) --}}
            <div x-show="!block.data.image && previewMode" class="text-center py-8 text-slate-400">
                <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm">{{ __('No brands image added') }}</p>
            </div>

            {{-- Hidden File Input --}}
            <input type="file" x-ref="brandsImageInput" @change="handleBrandsImageUpload($event, block)" accept="image/*" class="hidden">
        </div>
    </template>

    {{-- Logo Grid Mode (multiple individual logos) --}}
    <template x-if="block.data.mode === 'logos'">
        <div>
            <div class="grid gap-4"
                 :class="{
                     'grid-cols-2': (block.data.columns || 4) === 2,
                     'grid-cols-3': (block.data.columns || 4) === 3,
                     'grid-cols-4': (block.data.columns || 4) === 4,
                     'grid-cols-5': (block.data.columns || 4) === 5,
                     'grid-cols-6': (block.data.columns || 4) === 6
                 }">
                <template x-for="(logo, logoIndex) in (block.data.logos || [])" :key="logoIndex">
                    <div class="relative group bg-white border border-slate-200 rounded-lg p-4 flex items-center justify-center min-h-[80px]"
                         :class="previewMode ? '' : 'hover:border-blue-300'">
                        <img x-show="logo.src" :src="logo.src" :alt="logo.alt || 'Logo'"
                             class="max-h-12 max-w-full object-contain grayscale hover:grayscale-0 transition-all">
                        <div x-show="!logo.src" class="text-center text-slate-400">
                            <svg class="w-8 h-8 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-xs">{{ __('No image') }}</span>
                        </div>
                        <div x-show="!previewMode"
                             class="absolute inset-0 bg-slate-900/70 opacity-0 group-hover:opacity-100 rounded-lg flex items-center justify-center gap-2 transition-opacity">
                            <button type="button" @click="editLogo(getBlockIndex(block), logoIndex)"
                                    class="p-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </button>
                            <button type="button" @click="block.data.logos.splice(logoIndex, 1)"
                                    class="p-2 bg-red-500/50 hover:bg-red-500/70 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
                <button x-show="!previewMode" type="button"
                        @click="if(!block.data.logos) block.data.logos = []; block.data.logos.push({ src: '', alt: '' })"
                        class="border-2 border-dashed border-slate-300 hover:border-blue-400 rounded-lg p-4 flex flex-col items-center justify-center min-h-[80px] transition-colors group">
                    <svg class="w-6 h-6 text-slate-400 group-hover:text-blue-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="text-xs text-slate-400 group-hover:text-blue-500">{{ __('Add Logo') }}</span>
                </button>
            </div>

            <div x-show="previewMode && (!block.data.logos || block.data.logos.length === 0)"
                 class="text-center py-8 text-slate-400">
                <p class="text-sm">{{ __('No brand logos added') }}</p>
            </div>

            <div x-show="!previewMode" class="mt-4 pt-4 border-t border-dashed border-slate-200">
                <div class="flex items-center gap-4">
                    <label class="text-xs text-slate-600">{{ __('Columns') }}:</label>
                    <select x-model.number="block.data.columns"
                            class="text-sm text-slate-700 bg-white border border-slate-200 rounded px-2 py-1 focus:border-blue-400">
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                    </select>
                </div>
            </div>
        </div>
    </template>

    {{-- Mode Toggle (Edit Mode) --}}
    <div x-show="!previewMode" class="mt-4 pt-4 border-t border-slate-200 flex items-center justify-center gap-4">
        <span class="text-xs text-slate-500">{{ __('Display mode') }}:</span>
        <button type="button" @click="block.data.mode = 'image'"
                :class="block.data.mode !== 'logos' ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600'"
                class="px-3 py-1 text-xs rounded-full transition-colors">
            {{ __('Single Image') }}
        </button>
        <button type="button" @click="block.data.mode = 'logos'"
                :class="block.data.mode === 'logos' ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600'"
                class="px-3 py-1 text-xs rounded-full transition-colors">
            {{ __('Logo Grid') }}
        </button>
    </div>
</div>

{{-- Logo Edit Modal (for grid mode) --}}
<template x-teleport="body">
    <div x-show="editingLogo !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @click.self="editingLogo = null">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Edit Logo') }}</h3>
            <template x-if="editingLogo !== null">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Image URL') }}</label>
                        <input type="text" x-model="blocks[editingLogo.blockIndex].data.logos[editingLogo.logoIndex].src"
                               placeholder="https://example.com/logo.png"
                               class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Alt Text') }}</label>
                        <input type="text" x-model="blocks[editingLogo.blockIndex].data.logos[editingLogo.logoIndex].alt"
                               placeholder="{{ __('Company name') }}"
                               class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div x-show="blocks[editingLogo.blockIndex]?.data?.logos?.[editingLogo.logoIndex]?.src"
                         class="p-4 bg-slate-50 rounded-lg text-center">
                        <img :src="blocks[editingLogo.blockIndex]?.data?.logos?.[editingLogo.logoIndex]?.src"
                             class="max-h-16 max-w-full mx-auto object-contain">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editingLogo = null"
                                class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 transition-colors">
                            {{ __('Close') }}
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
