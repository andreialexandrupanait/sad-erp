{{-- Stat Card Widget (as block) --}}
<div class="stat-card-widget p-4 m-3 rounded-lg border"
     :class="'bg-' + (block.data.color || 'green') + '-50 border-' + (block.data.color || 'green') + '-200'">
    <div class="flex items-center gap-3">
        {{-- Icon --}}
        <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center"
             :class="'bg-' + (block.data.color || 'green') + '-100'">
            {{-- Trending Up --}}
            <svg x-show="block.data.icon === 'trending-up' || !block.data.icon"
                 class="w-6 h-6" :class="'text-' + (block.data.color || 'green') + '-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            {{-- Chart Bar --}}
            <svg x-show="block.data.icon === 'chart-bar'"
                 class="w-6 h-6" :class="'text-' + (block.data.color || 'green') + '-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            {{-- Users --}}
            <svg x-show="block.data.icon === 'users'"
                 class="w-6 h-6" :class="'text-' + (block.data.color || 'green') + '-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            {{-- Currency Dollar --}}
            <svg x-show="block.data.icon === 'currency-dollar'"
                 class="w-6 h-6" :class="'text-' + (block.data.color || 'green') + '-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{-- Clock --}}
            <svg x-show="block.data.icon === 'clock'"
                 class="w-6 h-6" :class="'text-' + (block.data.color || 'green') + '-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{-- Star --}}
            <svg x-show="block.data.icon === 'star'"
                 class="w-6 h-6" :class="'text-' + (block.data.color || 'green') + '-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
            {{-- Edit Mode --}}
            <template x-if="!previewMode">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <select x-model="block.data.icon" class="text-xs border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-0 bg-white/50">
                            <option value="trending-up">{{ __('Trending') }}</option>
                            <option value="chart-bar">{{ __('Chart') }}</option>
                            <option value="users">{{ __('Users') }}</option>
                            <option value="currency-dollar">{{ __('Money') }}</option>
                            <option value="clock">{{ __('Time') }}</option>
                            <option value="star">{{ __('Star') }}</option>
                        </select>
                        <select x-model="block.data.color" class="text-xs border border-slate-200 rounded px-2 py-1 focus:border-slate-400 focus:ring-0 bg-white/50">
                            <option value="green">{{ __('Green') }}</option>
                            <option value="blue">{{ __('Blue') }}</option>
                            <option value="purple">{{ __('Purple') }}</option>
                            <option value="amber">{{ __('Amber') }}</option>
                            <option value="red">{{ __('Red') }}</option>
                            <option value="slate">{{ __('Gray') }}</option>
                        </select>
                    </div>
                    <input type="text" x-model="block.data.value"
                           placeholder="99%"
                           class="w-full text-2xl font-bold bg-transparent border-none p-0 focus:ring-0"
                           :class="'text-' + (block.data.color || 'green') + '-700'">
                    <input type="text" x-model="block.data.label"
                           placeholder="{{ __('Metric label...') }}"
                           class="w-full text-sm bg-transparent border-none p-0 focus:ring-0"
                           :class="'text-' + (block.data.color || 'green') + '-600 placeholder:text-' + (block.data.color || 'green') + '-400'">
                </div>
            </template>

            {{-- Preview Mode --}}
            <template x-if="previewMode">
                <div>
                    <p class="text-2xl font-bold" :class="'text-' + (block.data.color || 'green') + '-700'" x-text="block.data.value"></p>
                    <p class="text-sm" :class="'text-' + (block.data.color || 'green') + '-600'" x-text="block.data.label"></p>
                </div>
            </template>
        </div>
    </div>
</div>
