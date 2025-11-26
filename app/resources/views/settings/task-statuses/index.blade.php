<x-app-layout>
    <x-slot name="pageTitle">{{ __('Task Statuses') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto p-8" x-data="statusManager()">
            {{-- Header --}}
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">{{ __('Task Statuses') }}</h1>
                        <p class="mt-1 text-sm text-slate-600">{{ __('Manage your task statuses and customize their colors.') }}</p>
            </div>
            <button @click="openCreateForm()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Add Status') }}
            </button>
        </div>
    </div>

    {{-- Statuses List --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <div class="space-y-2">
                <template x-for="(status, index) in statuses" :key="status.id">
                    <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 group">
                        {{-- Drag Handle --}}
                        <button class="text-gray-400 hover:text-gray-600 cursor-move">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                            </svg>
                        </button>

                        {{-- Color Preview --}}
                        <div class="w-10 h-10 rounded-lg border-2 border-gray-200"
                             :style="`background-color: ${status.color_class}`">
                        </div>

                        {{-- Label --}}
                        <div class="flex-1">
                            <div class="font-medium text-gray-900" x-text="status.label"></div>
                            <div class="text-sm text-gray-500" x-text="status.value"></div>
                        </div>

                        {{-- Status Badge Preview --}}
                        <div class="px-3 py-1 rounded-full text-xs font-medium"
                             :style="`background-color: ${status.color_class}20; color: ${status.color_class}`"
                             x-text="status.label">
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="openEditForm(status)"
                                    class="p-2 text-gray-400 hover:text-blue-600 rounded">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button @click="deleteStatus(status)"
                                    class="p-2 text-gray-400 hover:text-red-600 rounded">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Empty State --}}
                <div x-show="statuses.length === 0" class="text-center py-12">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-600 font-medium">{{ __('No statuses yet') }}</p>
                    <p class="text-gray-500 text-sm mt-1">{{ __('Click "Add Status" to create your first task status.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <div x-show="showForm"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="closeForm()">
        <div class="flex items-center justify-center min-h-screen px-4">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black opacity-30" @click="closeForm()"></div>

            {{-- Modal --}}
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900"
                        x-text="editingStatus ? '{{ __('Edit Status') }}' : '{{ __('Create Status') }}'">
                    </h3>
                    <button @click="closeForm()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Label --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Label') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               x-model="form.label"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="{{ __('To Do') }}">
                    </div>

                    {{-- Value (slug) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Value (slug)') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               x-model="form.value"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="{{ __('to-do') }}">
                        <p class="text-xs text-gray-500 mt-1">{{ __('Lowercase, no spaces, use hyphens') }}</p>
                    </div>

                    {{-- Color --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Color') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="color"
                                   x-model="form.color"
                                   class="h-10 w-20 rounded border border-gray-300 cursor-pointer">
                            <input type="text"
                                   x-model="form.color"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="#3B82F6">
                        </div>

                        {{-- Color Preview --}}
                        <div class="mt-2 p-3 rounded-lg flex items-center gap-2"
                             :style="`background-color: ${form.color}20`">
                            <span class="px-3 py-1 rounded-full text-sm font-medium"
                                  :style="`background-color: ${form.color}; color: white`"
                                  x-text="form.label || 'Preview'">
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 mt-6 pt-4 border-t">
                    <button @click="closeForm()"
                            class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        {{ __('Cancel') }}
                    </button>
                    <button @click="saveStatus()"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function statusManager() {
    return {
        statuses: @json($statuses),
        showForm: false,
        editingStatus: null,
        form: {
            label: '',
            value: '',
            color: '#94a3b8',
        },

        openCreateForm() {
            this.editingStatus = null;
            this.form = {
                label: '',
                value: '',
                color: '#94a3b8',
            };
            this.showForm = true;
        },

        openEditForm(status) {
            this.editingStatus = status;
            this.form = {
                label: status.label,
                value: status.value,
                color: status.color_class,
            };
            this.showForm = true;
        },

        closeForm() {
            this.showForm = false;
            this.editingStatus = null;
        },

        async saveStatus() {
            const url = this.editingStatus
                ? `/settings/task-statuses/${this.editingStatus.id}`
                : '/settings/task-statuses';
            const method = this.editingStatus ? 'PATCH' : 'POST';

            console.log('Saving status:', this.form);
            console.log('URL:', url, 'Method:', method);

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify(this.form)
                });

                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Response data:', data);

                if (data.success) {
                    this.closeForm();
                    showToast(data.message, 'success');
                    // Reload the page to show updated colors
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    // Show validation errors if available
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join(', ');
                        showToast(errorMessages, 'error');
                    } else {
                        showToast(data.message || 'Failed to save status', 'error');
                    }
                }
            } catch (error) {
                console.error('Failed to save status:', error);
                showToast('Failed to save status', 'error');
            }
        },

        async deleteStatus(status) {
            if (!confirm('{{ __('Are you sure you want to delete this status?') }}')) {
                return;
            }

            try {
                const response = await fetch(`/settings/task-statuses/${status.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.statuses = this.statuses.filter(s => s.id !== status.id);
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Failed to delete status:', error);
                showToast('Failed to delete status', 'error');
            }
        }
    };
}

function showToast(message, type = 'success') {
    const bgColors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };

    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${bgColors[type]} text-white px-4 py-2 rounded shadow-lg z-50 transition-opacity duration-300`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}
</script>
@endpush
        </div>
    </div>
</x-app-layout>
