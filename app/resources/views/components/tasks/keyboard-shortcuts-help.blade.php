<!-- Keyboard Shortcuts Help Modal -->
<div
    x-data="{ open: false }"
    @keydown.window="if (e.key === '?' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) { e.preventDefault(); open = !open }"
    x-show="open"
    x-cloak
   
    class="fixed inset-0 z-50 overflow-y-auto"
    @keydown.escape.window="open = false"
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm"
        @click="open = false"
    ></div>

    <!-- Modal Dialog -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white rounded-xl shadow-2xl w-full max-w-3xl overflow-hidden"
            @click.stop
        >
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">{{ __('Keyboard Shortcuts') }}</h2>
                    <button
                        @click="open = false"
                        class="text-slate-400 hover:text-slate-600"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- General -->
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-3">
                            {{ __('General') }}
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">{{ __('Quick Switcher') }}</span>
                                <kbd class="px-2 py-1 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded">⌘K</kbd>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">{{ __('Show Shortcuts') }}</span>
                                <kbd class="px-2 py-1 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded">?</kbd>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">{{ __('New Task') }}</span>
                                <kbd class="px-2 py-1 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded">⌘N</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-3">
                            {{ __('Navigation') }}
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">{{ __('Navigate Up/Down') }}</span>
                                <div class="flex gap-1">
                                    <kbd class="px-2 py-1 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded">↑</kbd>
                                    <kbd class="px-2 py-1 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded">↓</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">{{ __('Select Item') }}</span>
                                <kbd class="px-2 py-1 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded">↵</kbd>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">{{ __('Close/Escape') }}</span>
                                <kbd class="px-2 py-1 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded">ESC</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Task Actions -->
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-3">
                            {{ __('Task Actions') }}
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">{{ __('Clear Selection') }}</span>
                                <kbd class="px-2 py-1 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded">ESC</kbd>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">{{ __('Select All') }}</span>
                                <kbd class="px-2 py-1 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded">⌘A</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Switcher -->
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-3">
                            {{ __('Quick Switcher') }}
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">{{ __('Commands') }}</span>
                                <kbd class="px-2 py-1 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded">&gt;</kbd>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">{{ __('Search Lists') }}</span>
                                <kbd class="px-2 py-1 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded">{{ __('type to search') }}</kbd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                <p class="text-xs text-slate-500 text-center">
                    {{ __('Press') }} <kbd class="px-2 py-1 font-semibold bg-white border border-slate-200 rounded">?</kbd> {{ __('anytime to view shortcuts') }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Floating Help Button -->
<button
    @click="$event.target.closest('[x-data]').__x.$data.open = true"
    class="fixed bottom-6 right-6 z-30 w-12 h-12 rounded-full bg-slate-900 text-white shadow-lg hover:bg-slate-800 transition-all hover:scale-110 flex items-center justify-center"
    title="{{ __('Keyboard Shortcuts') }} (?)"
>
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
</button>
