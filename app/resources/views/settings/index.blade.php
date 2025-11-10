<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ __('Settings') }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">Manage all dropdown options and configurations for your system</p>
            </div>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8" x-data="{
        activeTab: '{{ $categories->first()->slug ?? 'domains' }}',
        editingOption: null,
        addingOption: null,
        isSubmitting: false,

        slugify(text) {
            return text
                .toString()
                .toLowerCase()
                .trim()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
        },

        autoFillValue(groupId, event) {
            const label = event.target.value;
            const valueInput = document.getElementById('add-value-' + groupId);
            if (valueInput && !valueInput.dataset.userModified) {
                valueInput.value = this.slugify(label);
            }
        },

        markValueAsModified(groupId) {
            const valueInput = document.getElementById('add-value-' + groupId);
            if (valueInput) {
                valueInput.dataset.userModified = 'true';
            }
        },

        async saveOption(groupId, optionId = null) {
            this.isSubmitting = true;
            const form = optionId ? document.getElementById('edit-form-' + optionId) : document.getElementById('add-form-' + groupId);
            const formData = new FormData(form);
            const url = optionId
                ? `/settings/options/${optionId}`
                : `/settings/groups/${groupId}/options`;
            const method = optionId ? 'PATCH' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error saving option:', error);
            } finally {
                this.isSubmitting = false;
            }
        },

        async deleteOption(optionId) {
            if (!confirm('Are you sure you want to delete this option?')) return;

            try {
                const response = await fetch(`/settings/options/${optionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error deleting option:', error);
            }
        }
    }">
        <!-- Category Tabs -->
        <x-ui.card class="mb-6">
            <x-ui.card-content class="p-0">
                <div class="border-b border-slate-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        @foreach($categories as $category)
                            <button
                                @click="activeTab = '{{ $category->slug }}'"
                                :class="activeTab === '{{ $category->slug }}' ? 'border-primary-500 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </nav>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Tab Content -->
        @foreach($categories as $category)
            <div x-show="activeTab === '{{ $category->slug }}'" x-cloak>
                <div class="space-y-6">
                    @foreach($category->groups as $group)
                        <x-ui.card>
                            <x-ui.card-header>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900">{{ $group->name }}</h3>
                                        @if($group->description)
                                            <p class="text-sm text-slate-500 mt-1">{{ $group->description }}</p>
                                        @endif
                                    </div>
                                    <button
                                        @click="addingOption = addingOption === {{ $group->id }} ? null : {{ $group->id }}"
                                        class="px-3 py-1.5 text-sm font-medium text-primary-600 hover:text-primary-700 border border-primary-300 hover:border-primary-400 rounded-lg transition-colors">
                                        <span x-show="addingOption !== {{ $group->id }}">Add Option</span>
                                        <span x-show="addingOption === {{ $group->id }}">Cancel</span>
                                    </button>
                                </div>
                            </x-ui.card-header>

                            <x-ui.card-content>
                                <!-- Add Option Form -->
                                <div x-show="addingOption === {{ $group->id }}" x-cloak class="mb-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                                    <form id="add-form-{{ $group->id }}" @submit.prevent="saveOption({{ $group->id }})">
                                        <div class="grid grid-cols-1 md:grid-cols-{{ $group->has_colors ? '4' : '3' }} gap-3">
                                            <div>
                                                <x-ui.label for="add-label-{{ $group->id }}">Label</x-ui.label>
                                                <x-ui.input type="text" name="label" id="add-label-{{ $group->id }}" required @input="autoFillValue({{ $group->id }}, $event)" />
                                            </div>
                                            <div>
                                                <x-ui.label for="add-value-{{ $group->id }}">Value</x-ui.label>
                                                <x-ui.input type="text" name="value" id="add-value-{{ $group->id }}" required @input="markValueAsModified({{ $group->id }})" />
                                            </div>
                                            @if($group->has_colors)
                                                <div>
                                                    <x-ui.label for="add-color-{{ $group->id }}">Color</x-ui.label>
                                                    <input type="color" name="color" id="add-color-{{ $group->id }}" class="h-10 w-full rounded-lg border border-slate-300" />
                                                </div>
                                            @endif
                                            <div class="flex items-end">
                                                <x-ui.button type="submit" variant="default" class="w-full" x-bind:disabled="isSubmitting">
                                                    <span x-show="!isSubmitting">Save</span>
                                                    <span x-show="isSubmitting">Saving...</span>
                                                </x-ui.button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- Options List -->
                                <div class="space-y-2">
                                    @forelse($group->options as $option)
                                        <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors border border-slate-200">
                                            <!-- Drag Handle -->
                                            <div class="flex-shrink-0 text-slate-400 cursor-move">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                </svg>
                                            </div>

                                            <!-- Editing State -->
                                            <template x-if="editingOption === {{ $option->id }}">
                                                <form id="edit-form-{{ $option->id }}" @submit.prevent="saveOption({{ $group->id }}, {{ $option->id }})" class="flex-1 grid grid-cols-1 md:grid-cols-{{ $group->has_colors ? '4' : '3' }} gap-3">
                                                    <div>
                                                        <x-ui.input type="text" name="label" value="{{ $option->label }}" required />
                                                    </div>
                                                    <div>
                                                        <x-ui.input type="text" name="value" value="{{ $option->value }}" required />
                                                    </div>
                                                    @if($group->has_colors)
                                                        <div>
                                                            <input type="color" name="color" value="{{ $option->color ?? '#6b7280' }}" class="h-10 w-full rounded-lg border border-slate-300" />
                                                        </div>
                                                    @endif
                                                    <div class="flex items-center gap-2">
                                                        <button type="submit" class="px-3 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg">
                                                            Save
                                                        </button>
                                                        <button type="button" @click="editingOption = null" class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </form>
                                            </template>

                                            <!-- Display State -->
                                            <template x-if="editingOption !== {{ $option->id }}">
                                                <div class="flex-1 flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        @if($group->has_colors && $option->color)
                                                            <div class="w-4 h-4 rounded-full border border-slate-200" style="background-color: {{ $option->color }}"></div>
                                                        @endif
                                                        <div>
                                                            <span class="font-medium text-slate-900">{{ $option->label }}</span>
                                                            <span class="text-sm text-slate-500 ml-2">({{ $option->value }})</span>
                                                        </div>
                                                        @if($option->is_default)
                                                            <x-ui.badge variant="secondary" class="text-xs">Default</x-ui.badge>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <button @click="editingOption = {{ $option->id }}" class="p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </button>
                                                        <button @click="deleteOption({{ $option->id }})" class="p-2 text-red-600 hover:text-red-700 rounded-lg hover:bg-red-50">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    @empty
                                        <div class="text-center py-8 text-slate-500">
                                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                            </svg>
                                            <p>No options configured yet</p>
                                            <p class="text-sm mt-1">Click "Add Option" to create your first option</p>
                                        </div>
                                    @endforelse
                                </div>
                            </x-ui.card-content>
                        </x-ui.card>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
