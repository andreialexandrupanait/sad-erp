@props(['spaces' => []])

<!-- Space Modal -->
<div
    x-show="$store.taskModals.spaceModal.open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    @keydown.escape.window="$store.taskModals.closeSpaceModal()"
>
    <!-- Backdrop -->
    <div
        x-show="$store.taskModals.spaceModal.open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/50"
        @click="$store.taskModals.closeSpaceModal()"
    ></div>

    <!-- Modal Dialog -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div
            x-show="$store.taskModals.spaceModal.open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative bg-white rounded-lg shadow-xl w-full max-w-md"
            @click.stop
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">
                    <span x-text="$store.taskModals.spaceModal.editId ? '{{ __('Edit Space') }}' : '{{ __('New Space') }}'"></span>
                </h3>
                <button
                    @click="$store.taskModals.closeSpaceModal()"
                    class="text-slate-400 hover:text-slate-600"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="$store.taskModals.submitSpace()" class="p-6 space-y-4">
                <!-- Name -->
                <div>
                    <label for="space-name" class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('Name') }}
                    </label>
                    <input
                        type="text"
                        id="space-name"
                        x-model="$store.taskModals.spaceModal.form.name"
                        required
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="{{ __('e.g., Marketing, Development, Personal') }}"
                    />
                </div>

                <!-- Icon & Color -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Icon -->
                    <div>
                        <label for="space-icon" class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('Icon') }}
                        </label>
                        <input
                            type="text"
                            id="space-icon"
                            x-model="$store.taskModals.spaceModal.form.icon"
                            maxlength="10"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="📁"
                        />
                    </div>

                    <!-- Color -->
                    <div>
                        <label for="space-color" class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('Color') }}
                        </label>
                        <input
                            type="color"
                            id="space-color"
                            x-model="$store.taskModals.spaceModal.form.color"
                            class="w-full h-10 border border-slate-300 rounded-lg cursor-pointer"
                        />
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4">
                    <button
                        type="button"
                        @click="$store.taskModals.closeSpaceModal()"
                        class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="$store.taskModals.spaceModal.submitting || !$store.taskModals.spaceModal.form.name"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!$store.taskModals.spaceModal.submitting">
                            <span x-text="$store.taskModals.spaceModal.editId ? '{{ __('Update Space') }}' : '{{ __('Create Space') }}'"></span>
                        </span>
                        <span x-show="$store.taskModals.spaceModal.submitting">{{ __('Saving...') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
