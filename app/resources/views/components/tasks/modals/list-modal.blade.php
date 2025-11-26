@props(['spaces' => [], 'clients' => []])

<!-- List Modal -->
<div
    x-show="$store.taskModals.listModal.open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    @keydown.escape.window="$store.taskModals.closeListModal()"
>
    <!-- Backdrop -->
    <div
        x-show="$store.taskModals.listModal.open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/50"
        @click="$store.taskModals.closeListModal()"
    ></div>

    <!-- Modal Dialog -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div
            x-show="$store.taskModals.listModal.open"
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
                    <span x-text="$store.taskModals.listModal.editId ? '{{ __('Edit List') }}' : '{{ __('New List') }}'"></span>
                </h3>
                <button
                    @click="$store.taskModals.closeListModal()"
                    class="text-slate-400 hover:text-slate-600"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="$store.taskModals.submitList()" class="p-6 space-y-4">
                <!-- Folder Selection -->
                <div>
                    <label for="list-folder" class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('Folder') }}
                    </label>
                    <select
                        id="list-folder"
                        x-model="$store.taskModals.listModal.form.folder_id"
                        required
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">{{ __('Select a folder...') }}</option>
                        @foreach($spaces as $space)
                            @foreach($space->folders ?? [] as $folder)
                                <option value="{{ $folder->id }}">{{ $space->icon ?? '📁' }} {{ $space->name }} / {{ $folder->icon ?? '📂' }} {{ $folder->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <!-- Name -->
                <div>
                    <label for="list-name" class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('Name') }}
                    </label>
                    <input
                        type="text"
                        id="list-name"
                        x-model="$store.taskModals.listModal.form.name"
                        required
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="{{ __('e.g., Website Redesign, Bug Fixes') }}"
                    />
                </div>

                <!-- Client (Optional) -->
                <div>
                    <label for="list-client" class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('Client') }} <span class="text-xs text-slate-500">({{ __('optional') }})</span>
                    </label>
                    <select
                        id="list-client"
                        x-model="$store.taskModals.listModal.form.client_id"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">{{ __('No client') }}</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Icon & Color -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Icon -->
                    <div>
                        <label for="list-icon" class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('Icon') }}
                        </label>
                        <input
                            type="text"
                            id="list-icon"
                            x-model="$store.taskModals.listModal.form.icon"
                            maxlength="10"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="📋"
                        />
                    </div>

                    <!-- Color -->
                    <div>
                        <label for="list-color" class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('Color') }}
                        </label>
                        <input
                            type="color"
                            id="list-color"
                            x-model="$store.taskModals.listModal.form.color"
                            class="w-full h-10 border border-slate-300 rounded-lg cursor-pointer"
                        />
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4">
                    <button
                        type="button"
                        @click="$store.taskModals.closeListModal()"
                        class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="submit"
                        :disabled="$store.taskModals.listModal.submitting || !$store.taskModals.listModal.form.name || !$store.taskModals.listModal.form.folder_id"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!$store.taskModals.listModal.submitting">
                            <span x-text="$store.taskModals.listModal.editId ? '{{ __('Update List') }}' : '{{ __('Create List') }}'"></span>
                        </span>
                        <span x-show="$store.taskModals.listModal.submitting">{{ __('Saving...') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
