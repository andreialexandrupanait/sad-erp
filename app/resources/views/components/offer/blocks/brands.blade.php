{{-- Brands Block - Builder View --}}
{{-- Contains: Editable title + logo gallery for trusted partners/clients --}}

<div class="offer-brands-block px-6 py-6">
    {{-- Section Title --}}
    <div class="mb-6">
        <input type="text" x-model="block.data.title"
               class="text-xl font-bold text-slate-900 bg-transparent border-none focus:ring-0 p-0 w-full"
               placeholder="{{ __('Trusted Partners') }}">
        <div class="h-1 w-20 bg-amber-500 mt-2 rounded"></div>
    </div>

    {{-- Logo Grid --}}
    <div class="grid gap-4" :class="{
        'grid-cols-2': block.data.columns === 2,
        'grid-cols-3': block.data.columns === 3,
        'grid-cols-4': block.data.columns === 4 || !block.data.columns,
        'grid-cols-5': block.data.columns === 5,
        'grid-cols-6': block.data.columns === 6
    }">
        <template x-for="(logo, logoIndex) in (block.data.logos || [])" :key="logo._key || logoIndex">
            <div class="relative group bg-white border border-slate-200 rounded-xl p-4 flex items-center justify-center min-h-[100px] hover:border-slate-300 transition-all">
                {{-- Logo Image --}}
                <template x-if="logo.src">
                    <img :src="logo.src" :alt="logo.alt || ''"
                         class="max-h-16 max-w-full object-contain grayscale hover:grayscale-0 transition-all">
                </template>

                {{-- Placeholder --}}
                <template x-if="!logo.src">
                    <div class="text-center">
                        <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-xs text-slate-400">{{ __('Upload logo') }}</span>
                    </div>
                </template>

                {{-- Overlay Actions --}}
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl flex items-center justify-center gap-2">
                    {{-- Upload Button --}}
                    <label class="p-2 bg-white rounded-lg cursor-pointer hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <input type="file" class="hidden" accept="image/*"
                               @change="uploadBrandLogo($event, logoIndex)">
                    </label>

                    {{-- Edit Alt Text --}}
                    <button type="button"
                            @click="logo.alt = prompt('{{ __('Alt text for logo:') }}', logo.alt || '')"
                            class="p-2 bg-white rounded-lg hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>

                    {{-- Delete Button --}}
                    <button type="button"
                            @click="block.data.logos.splice(logoIndex, 1)"
                            class="p-2 bg-white rounded-lg hover:bg-red-50 transition-colors">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        {{-- Add Logo Button --}}
        <button type="button"
                @click="if (!block.data.logos) block.data.logos = []; block.data.logos.push({_key: Date.now(), src: '', alt: ''})"
                class="border-2 border-dashed border-slate-300 rounded-xl p-4 min-h-[100px] flex flex-col items-center justify-center text-slate-400 hover:text-slate-600 hover:border-slate-400 transition-all">
            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-sm font-medium">{{ __('Add Logo') }}</span>
        </button>
    </div>

    {{-- Layout Options --}}
    <div class="mt-4 flex items-center gap-4">
        <label class="text-sm text-slate-600">{{ __('Columns') }}:</label>
        <select x-model="block.data.columns"
                class="border border-slate-200 rounded-lg px-3 py-1.5 text-sm focus:border-amber-400">
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
        </select>
    </div>
</div>
