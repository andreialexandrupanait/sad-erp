@props([
    "actions" => [],
    "resource" => "items",
])

<div
    x-show="hasSelection"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] max-w-4xl w-full mx-auto px-4"
    x-cloak
>
    <div class="bg-slate-900 text-white rounded-xl shadow-2xl border border-slate-700 px-6 py-4">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-sm font-bold">
                    <span x-text="selectedCount"></span>
                </div>
                <span class="text-sm font-medium">
                    <span x-text="selectedCount"></span>
                    <span x-text="selectedCount === 1 ? '{{ Str::singular($resource) }}' : '{{ $resource}}'" ></span>
                    <span x-text="selectedCount === 1 ? '{{ __('selected') }}' : '{{ __('selectați') }}'"></span>
                </span>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                {{ $slot }}

                <button
                    @click="clearSelection()"
                    class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors"
                    :disabled="isLoading"
                >
                    {{ __('Clear') }}
                </button>
            </div>
        </div>

        <div
            x-show="isLoading"
            class="absolute inset-0 bg-slate-900/80 rounded-xl flex items-center justify-center"
        >
            <svg class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>
</div>
