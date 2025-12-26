{{-- Icon + Text Widget (as block) --}}
<div class="icon-text-widget flex items-start gap-3 px-4 py-3">
    {{-- Icon --}}
    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
         :class="'bg-' + (block.data.iconColor || 'green') + '-100'">
        {{-- Check Circle (default) --}}
        <svg x-show="block.data.icon === 'check-circle' || !block.data.icon"
             class="w-5 h-5" :class="'text-' + (block.data.iconColor || 'green') + '-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{-- Star --}}
        <svg x-show="block.data.icon === 'star'"
             class="w-5 h-5" :class="'text-' + (block.data.iconColor || 'green') + '-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
        </svg>
        {{-- Light Bulb --}}
        <svg x-show="block.data.icon === 'light-bulb'"
             class="w-5 h-5" :class="'text-' + (block.data.iconColor || 'green') + '-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
        </svg>
        {{-- Shield Check --}}
        <svg x-show="block.data.icon === 'shield-check'"
             class="w-5 h-5" :class="'text-' + (block.data.iconColor || 'green') + '-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        {{-- Clock --}}
        <svg x-show="block.data.icon === 'clock'"
             class="w-5 h-5" :class="'text-' + (block.data.iconColor || 'green') + '-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{-- Trending Up --}}
        <svg x-show="block.data.icon === 'trending-up'"
             class="w-5 h-5" :class="'text-' + (block.data.iconColor || 'green') + '-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
        </svg>
        {{-- Heart --}}
        <svg x-show="block.data.icon === 'heart'"
             class="w-5 h-5" :class="'text-' + (block.data.iconColor || 'green') + '-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
    </div>

    {{-- Text --}}
    <div class="flex-1 min-w-0">
        {{-- Edit Mode --}}
        <div x-show="!previewMode" class="space-y-1">
            <div class="flex items-center gap-2 mb-2">
                <select x-model="block.data.icon" class="text-xs border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-0 bg-slate-50">
                    <option value="check-circle">{{ __('Check') }}</option>
                    <option value="star">{{ __('Star') }}</option>
                    <option value="light-bulb">{{ __('Idea') }}</option>
                    <option value="shield-check">{{ __('Security') }}</option>
                    <option value="clock">{{ __('Time') }}</option>
                    <option value="trending-up">{{ __('Growth') }}</option>
                    <option value="heart">{{ __('Heart') }}</option>
                </select>
                <select x-model="block.data.iconColor" class="text-xs border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-0 bg-slate-50">
                    <option value="green">{{ __('Green') }}</option>
                    <option value="blue">{{ __('Blue') }}</option>
                    <option value="purple">{{ __('Purple') }}</option>
                    <option value="amber">{{ __('Amber') }}</option>
                    <option value="red">{{ __('Red') }}</option>
                    <option value="slate">{{ __('Gray') }}</option>
                </select>
            </div>
            <textarea x-model="block.data.text"
                      x-init="$nextTick(() => { if($el.scrollHeight > 24) $el.style.height = $el.scrollHeight + 'px' })"
                      @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                      placeholder="{{ __('Enter text...') }}"
                      rows="1"
                      class="w-full text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg p-2 focus:border-slate-400 focus:ring-1 focus:ring-slate-400 placeholder:text-slate-400 resize-none overflow-hidden"></textarea>
        </div>

        {{-- Preview Mode --}}
        <p x-show="previewMode" class="text-sm text-slate-700" x-text="block.data.text"></p>
    </div>
</div>
