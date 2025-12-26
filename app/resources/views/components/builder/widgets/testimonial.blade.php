{{-- Testimonial Widget (as block) --}}
<div class="testimonial-widget p-4 m-3 bg-slate-50 rounded-lg border border-slate-200">
    {{-- Quote icon --}}
    <svg class="w-8 h-8 text-slate-300 mb-2" fill="currentColor" viewBox="0 0 24 24">
        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
    </svg>

    {{-- Edit Mode --}}
    <div x-show="!previewMode">
        <textarea x-model="block.data.quote"
                  x-init="$nextTick(() => { if($el.scrollHeight > 60) $el.style.height = $el.scrollHeight + 'px' })"
                  @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                  placeholder="{{ __('Enter testimonial quote...') }}"
                  rows="2"
                  class="w-full text-base text-slate-700 italic bg-white border border-slate-200 rounded p-2 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 placeholder:text-slate-400 resize-none overflow-hidden"></textarea>

        <div class="mt-4 flex items-center gap-3">
            {{-- Avatar --}}
            <div class="relative group">
                <div x-show="!block.data.avatar"
                     class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center cursor-pointer hover:bg-slate-300 transition-colors">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <input type="file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                           @change="handleBlockImageUpload($event, block)">
                </div>
                <div x-show="block.data.avatar" class="relative">
                    <img :src="block.data.avatar" class="w-10 h-10 rounded-full object-cover">
                    <button type="button" @click="block.data.avatar = ''"
                            class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Author info --}}
            <div class="flex-1">
                <input type="text" x-model="block.data.author"
                       placeholder="{{ __('Author name') }}"
                       class="w-full text-sm font-medium text-slate-900 bg-white border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 placeholder:text-slate-400">
                <input type="text" x-model="block.data.role"
                       placeholder="{{ __('Role / Company') }}"
                       class="w-full text-xs text-slate-500 bg-white border border-slate-200 rounded px-2 py-1 mt-1 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 placeholder:text-slate-400">
            </div>
        </div>
    </div>

    {{-- Preview Mode --}}
    <div x-show="previewMode">
        <p class="text-base text-slate-700 italic" x-text="block.data.quote"></p>

        <div class="mt-4 flex items-center gap-3">
            <div x-show="block.data.avatar">
                <img :src="block.data.avatar" class="w-10 h-10 rounded-full object-cover">
            </div>
            <div x-show="!block.data.avatar"
                 class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-900" x-text="block.data.author"></p>
                <p class="text-xs text-slate-500" x-text="block.data.role"></p>
            </div>
        </div>
    </div>
</div>
