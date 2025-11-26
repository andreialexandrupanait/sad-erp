<x-app-layout>
    <x-slot name="pageTitle">{{ __('Task Tags') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto p-8" x-data="tagManager()">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">{{ __('Task Tags') }}</h1>
                        <p class="mt-1 text-sm text-slate-600">{{ __('Manage tags for organizing your tasks') }}</p>
                    </div>
                    <button @click="openAddForm()"
                            type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('Add Tag') }}
                    </button>
                </div>
            </div>

            <!-- Add/Edit Form -->
            <div x-show="showForm"
                 x-transition
                 x-cloak
                 class="mb-6 bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4" x-text="formData.id ? '{{ __('Edit Tag') }}' : '{{ __('Add New Tag') }}'"></h3>

                <form @submit.prevent="submitForm()" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Tag Name -->
                        <div>
                            <label for="tag-name" class="block text-sm font-medium text-slate-700 mb-1">
                                {{ __('Tag Name') }}
                            </label>
                            <input type="text"
                                   id="tag-name"
                                   x-model="formData.name"
                                   required
                                   maxlength="100"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="{{ __('Enter tag name') }}">
                        </div>

                        <!-- Color Picker -->
                        <div>
                            <label for="tag-color" class="block text-sm font-medium text-slate-700 mb-1">
                                {{ __('Color') }}
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="color"
                                       id="tag-color"
                                       x-model="formData.color"
                                       class="h-10 w-20 rounded-lg border border-slate-300 cursor-pointer">
                                <input type="text"
                                       x-model="formData.color"
                                       pattern="^#[0-9A-F]{6}$"
                                       maxlength="7"
                                       class="flex-1 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono uppercase"
                                       placeholder="#3B82F6">
                                <!-- Preview -->
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium"
                                     :style="`background-color: ${formData.color}20; color: ${formData.color}`">
                                    <span>Preview</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                :disabled="isSubmitting"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span x-show="!isSubmitting" x-text="formData.id ? '{{ __('Update Tag') }}' : '{{ __('Create Tag') }}'"></span>
                            <span x-show="isSubmitting">{{ __('Saving...') }}</span>
                        </button>
                        <button @click="closeForm()"
                                type="button"
                                class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tags List -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                <div class="p-6 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('All Tags') }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ count($tags) }} {{ __('tags') }}</p>
                </div>

                @if($tags->isEmpty())
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-slate-900 mb-1">{{ __('No tags yet') }}</h3>
                        <p class="text-slate-600 mb-4">{{ __('Get started by creating your first tag') }}</p>
                        <button @click="openAddForm()"
                                type="button"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Add Your First Tag') }}
                        </button>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        {{ __('Tag') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        {{ __('Color') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        {{ __('Tasks') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        {{ __('Created') }}
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($tags as $tag)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium"
                                                     style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                                    </svg>
                                                    {{ $tag->name }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded border border-slate-200" style="background-color: {{ $tag->color }}"></div>
                                                <span class="text-sm font-mono text-slate-600">{{ $tag->color }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-slate-900">{{ $tag->tasks_count ?? 0 }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                            {{ $tag->created_at->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="openEditForm({{ $tag->id }}, '{{ $tag->name }}', '{{ $tag->color }}')"
                                                        type="button"
                                                        class="text-blue-600 hover:text-blue-900">
                                                    {{ __('Edit') }}
                                                </button>
                                                <button @click="deleteTag({{ $tag->id }}, '{{ $tag->name }}')"
                                                        type="button"
                                                        class="text-red-600 hover:text-red-900">
                                                    {{ __('Delete') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function tagManager() {
        return {
            showForm: false,
            editingId: null,
            isSubmitting: false,
            formData: {
                id: null,
                name: '',
                color: '#3B82F6'
            },

            openAddForm() {
                this.editingId = null;
                this.formData = {
                    id: null,
                    name: '',
                    color: '#3B82F6'
                };
                this.showForm = true;
            },

            openEditForm(id, name, color) {
                this.editingId = id;
                this.formData = {
                    id: id,
                    name: name,
                    color: color
                };
                this.showForm = true;
            },

            closeForm() {
                this.showForm = false;
                this.editingId = null;
                this.formData = {
                    id: null,
                    name: '',
                    color: '#3B82F6'
                };
            },

            async submitForm() {
                if (this.isSubmitting) return;
                this.isSubmitting = true;

                const url = this.formData.id
                    ? `/settings/task-tags/${this.formData.id}`
                    : '/settings/task-tags';
                const method = this.formData.id ? 'PATCH' : 'POST';

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({
                            name: this.formData.name,
                            color: this.formData.color
                        })
                    });

                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('Server error:', response.status, errorText);
                        alert(`Error ${response.status}: ${errorText.substring(0, 100)}`);
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'An error occurred');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred: ' + error.message);
                } finally {
                    this.isSubmitting = false;
                }
            },

            async deleteTag(id, name) {
                if (!confirm(`Are you sure you want to delete the tag "${name}"?`)) {
                    return;
                }

                try {
                    const response = await fetch(`/settings/task-tags/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'An error occurred');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
