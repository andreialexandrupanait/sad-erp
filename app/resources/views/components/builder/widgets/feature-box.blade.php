{{-- Feature Box Widget (as block) --}}
<div class="feature-box-widget p-4 m-3 rounded-lg border border-slate-200 bg-white">
    <div class="flex items-start gap-3">
        {{-- Icon --}}
        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
             :class="'bg-' + (block.data.color || 'blue') + '-100'">
            {{-- Star --}}
            <svg x-show="block.data.icon === 'star' || !block.data.icon"
                 class="w-5 h-5" :class="'text-' + (block.data.color || 'blue') + '-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            {{-- Light Bulb --}}
            <svg x-show="block.data.icon === 'light-bulb'"
                 class="w-5 h-5" :class="'text-' + (block.data.color || 'blue') + '-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            {{-- Shield Check --}}
            <svg x-show="block.data.icon === 'shield-check'"
                 class="w-5 h-5" :class="'text-' + (block.data.color || 'blue') + '-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            {{-- Cog --}}
            <svg x-show="block.data.icon === 'cog'"
                 class="w-5 h-5" :class="'text-' + (block.data.color || 'blue') + '-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            {{-- Sparkles --}}
            <svg x-show="block.data.icon === 'sparkles'"
                 class="w-5 h-5" :class="'text-' + (block.data.color || 'blue') + '-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            {{-- Globe --}}
            <svg x-show="block.data.icon === 'globe'"
                 class="w-5 h-5" :class="'text-' + (block.data.color || 'blue') + '-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
            {{-- Edit Mode --}}
            <template x-if="!previewMode">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <select x-model="block.data.icon" class="text-xs border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-0 bg-slate-50">
                            <option value="star">{{ __('Star') }}</option>
                            <option value="light-bulb">{{ __('Idea') }}</option>
                            <option value="shield-check">{{ __('Security') }}</option>
                            <option value="cog">{{ __('Settings') }}</option>
                            <option value="sparkles">{{ __('Magic') }}</option>
                            <option value="globe">{{ __('Global') }}</option>
                        </select>
                        <select x-model="block.data.color" class="text-xs border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-0 bg-slate-50">
                            <option value="blue">{{ __('Blue') }}</option>
                            <option value="green">{{ __('Green') }}</option>
                            <option value="purple">{{ __('Purple') }}</option>
                            <option value="amber">{{ __('Amber') }}</option>
                            <option value="red">{{ __('Red') }}</option>
                            <option value="slate">{{ __('Gray') }}</option>
                        </select>
                    </div>
                    <input type="text" x-model="block.data.title"
                           placeholder="{{ __('Feature title...') }}"
                           class="w-full text-base font-semibold text-slate-900 bg-slate-50 border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 placeholder:text-slate-400">
                    <textarea x-model="block.data.description"
                              x-init="$nextTick(() => { if($el.scrollHeight > 24) $el.style.height = $el.scrollHeight + 'px' })"
                              @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                              placeholder="{{ __('Feature description...') }}"
                              rows="1"
                              class="w-full text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded px-2 py-1 mt-2 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 placeholder:text-slate-400 resize-none overflow-hidden"></textarea>
                </div>
            </template>

            {{-- Preview Mode --}}
            <template x-if="previewMode">
                <div>
                    <h4 class="text-base font-semibold text-slate-900" x-text="block.data.title"></h4>
                    <p class="text-sm text-slate-600 mt-1" x-text="block.data.description"></p>
                </div>
            </template>
        </div>
    </div>
</div>
