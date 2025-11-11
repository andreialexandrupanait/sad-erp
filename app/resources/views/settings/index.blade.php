<x-app-layout>
    <div class="flex min-h-screen bg-slate-50" x-data="{
        activeSection: '{{ request()->get('section', 'application') }}',
        editingOption: null,
        addingToGroup: null,
        isSubmitting: false,

        slugify(text) {
            return text.toString().toLowerCase().trim()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '').replace(/-+$/, '');
        },

        async saveOption(groupId, optionId = null) {
            this.isSubmitting = true;
            const form = optionId ? document.getElementById('edit-form-' + optionId) : document.getElementById('add-form-' + groupId);
            const formData = new FormData(form);

            // Use module-specific routes for client_settings
            let url, method;
            const groupKey = arguments[2]; // Third parameter is groupKey
            if (groupKey === 'client_statuses') {
                url = optionId ? `/settings/client-settings/${optionId}` : `/settings/client-settings`;
                method = optionId ? 'PATCH' : 'POST';
            } else {
                url = optionId ? `/settings/options/${optionId}` : `/settings/groups/${groupId}/options`;
                method = optionId ? 'PATCH' : 'POST';
            }

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData
                });
                if (response.ok) {
                    window.location.href = window.location.pathname + '?section=' + this.activeSection;
                } else {
                    alert('Error saving option. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error saving option. Please try again.');
            } finally {
                this.isSubmitting = false;
            }
        },

        async deleteOption(optionId) {
            if (!confirm('Are you sure you want to delete this option?')) return;
            try {
                // Use module-specific routes for client_settings
                const url = (arguments[1] === 'client_statuses')
                    ? `/settings/client-settings/${optionId}`
                    : `/settings/options/${optionId}`;

                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const row = document.querySelector(`[data-option-id=&quot;${optionId}&quot;]`);
                    if (row) {
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    }">

        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-white border-r border-slate-200 flex-shrink-0">
            <div class="sticky top-0 overflow-y-auto max-h-screen">
                <div class="p-6">
                    <h1 class="text-xl font-bold text-slate-900">Settings</h1>
                    <p class="text-sm text-slate-500 mt-1">Manage your application</p>
                </div>

                <nav class="px-3 pb-6">
                    <!-- Application Settings -->
                    <div class="mb-6">
                        <div class="px-3 mb-2">
                            <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Application</h2>
                        </div>
                        <a href="?section=application"
                           :class="activeSection === 'application' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Application Settings
                        </a>
                    </div>

                    <!-- Nomenclature -->
                    <div>
                        <div class="px-3 mb-2">
                            <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nomenclature</h2>
                        </div>
                        @foreach($categories as $category)
                            <a href="?section={{ $category->slug }}"
                               :class="activeSection === '{{ $category->slug }}' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'"
                               class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group">
                                <span>{{ $category->name }}</span>
                                <span class="text-xs text-slate-400 group-hover:text-slate-600">{{ $category->groups->count() }}</span>
                            </a>
                        @endforeach
                    </div>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <!-- Application Settings Section -->
            <div x-show="activeSection === 'application'" x-cloak>
                <div class="p-8">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-slate-900">Application Settings</h2>
                        <p class="text-slate-600 mt-1">Configure your application preferences</p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                        <form method="POST" action="{{ route('settings.application.update') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Application Name</label>
                                    <input type="text" name="app_name" value="{{ $appSettings['app_name'] }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Language</label>
                                    <select name="language" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                                        <option value="en" {{ $appSettings['language'] === 'en' ? 'selected' : '' }}>English</option>
                                        <option value="ro" {{ $appSettings['language'] === 'ro' ? 'selected' : '' }}>Română</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Nomenclature Sections -->
            @foreach($categories as $category)
                <div x-show="activeSection === '{{ $category->slug }}'" x-cloak>
                    <div class="p-8">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-slate-900">{{ $category->name }}</h2>
                            <p class="text-slate-600 mt-1">{{ $category->description ?? 'Manage ' . strtolower($category->name) . ' settings' }}</p>
                        </div>

                        <div class="space-y-6">
                            @foreach($category->groups as $group)
                                <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold text-slate-900">{{ $group->name }}</h3>
                                            @if($group->description)
                                                <p class="text-sm text-slate-500 mt-1">{{ $group->description }}</p>
                                            @endif
                                        </div>
                                        <button @click="addingToGroup = addingToGroup === {{ $group->id }} ? null : {{ $group->id }}"
                                                class="px-4 py-2 text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors">
                                            <span x-show="addingToGroup !== {{ $group->id }}">Add Option</span>
                                            <span x-show="addingToGroup === {{ $group->id }}">Cancel</span>
                                        </button>
                                    </div>

                                    <div class="p-6">
                                        <!-- Add Form -->
                                        <div x-show="addingToGroup === {{ $group->id }}" x-cloak class="mb-6 p-4 bg-slate-50 rounded-lg border border-slate-200">
                                            <form id="add-form-{{ $group->id }}" @submit.prevent="saveOption({{ $group->id }}, null, '{{ $group->key }}')">
                                                <div class="flex gap-3 items-start">
                                                    <div class="flex-1">
                                                        <input type="text" name="label" placeholder="Label" required
                                                               @input="$el.form.querySelector('[name=value]').value = slugify($el.value)"
                                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                                                    </div>
                                                    <div class="flex-1">
                                                        <input type="text" name="value" placeholder="Value (auto-generated)" required
                                                               class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                                                    </div>
                                                    @if($group->has_colors)
                                                        <div class="w-20">
                                                            <input type="color" name="color" value="#3b82f6" class="w-full h-10 border border-slate-300 rounded-lg cursor-pointer">
                                                        </div>
                                                    @endif
                                                    <div class="flex gap-2 flex-shrink-0">
                                                        <button type="submit" :disabled="isSubmitting" class="px-4 py-2 text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors disabled:opacity-50">
                                                            Save
                                                        </button>
                                                        <button type="button" @click="addingToGroup = null" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- Options Table -->
                                        <div class="overflow-x-auto">
                                            <table class="w-full">
                                                <thead>
                                                    <tr class="border-b border-slate-200">
                                                        <th class="text-left py-3 px-2 text-xs font-semibold text-slate-600 uppercase">Label</th>
                                                        <th class="text-left py-3 px-2 text-xs font-semibold text-slate-600 uppercase">Value</th>
                                                        @if($group->has_colors)
                                                            <th class="text-left py-3 px-2 text-xs font-semibold text-slate-600 uppercase w-20">Color</th>
                                                        @endif
                                                        <th class="text-right py-3 px-2 text-xs font-semibold text-slate-600 uppercase w-24">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        // Use module-specific settings for clients
                                                        $options = ($group->key === 'client_statuses') ? $clientStatuses : $group->options;
                                                    @endphp
                                                    @forelse($options as $option)
                                                        <tr class="border-b border-slate-100 hover:bg-slate-50"
                                                            x-data="{ editing: false }"
                                                            data-option-id="{{ $option->id }}">
                                                            <template x-if="!editing">
                                                                <td class="py-3 px-2 text-sm text-slate-900">{{ $option->label }}</td>
                                                            </template>
                                                            <template x-if="editing">
                                                                <td class="py-3 px-2">
                                                                    <input type="text" name="label" value="{{ $option->label }}" required
                                                                           form="edit-form-{{ $option->id }}"
                                                                           @input="$el.form.querySelector('[name=value]').value = slugify($el.value)"
                                                                           class="w-full px-2 py-1 text-sm border border-slate-300 rounded">
                                                                </td>
                                                            </template>

                                                            <template x-if="!editing">
                                                                <td class="py-3 px-2 text-sm text-slate-500 font-mono">{{ $option->value }}</td>
                                                            </template>
                                                            <template x-if="editing">
                                                                <td class="py-3 px-2">
                                                                    <input type="text" name="value" value="{{ $option->value }}" required
                                                                           form="edit-form-{{ $option->id }}"
                                                                           class="w-full px-2 py-1 text-sm border border-slate-300 rounded bg-slate-50">
                                                                </td>
                                                            </template>

                                                            @if($group->has_colors)
                                                                <template x-if="!editing">
                                                                    <td class="py-3 px-2">
                                                                        @if($option->color)
                                                                            <div class="w-8 h-8 rounded border border-slate-300" style="background-color: {{ $option->color }}"></div>
                                                                        @endif
                                                                    </td>
                                                                </template>
                                                                <template x-if="editing">
                                                                    <td class="py-3 px-2">
                                                                        <input type="color" name="color" value="{{ $option->color ?? '#3b82f6' }}"
                                                                               form="edit-form-{{ $option->id }}"
                                                                               class="w-full h-8 border border-slate-300 rounded cursor-pointer">
                                                                    </td>
                                                                </template>
                                                            @endif

                                                            <td class="py-3 px-2 text-right">
                                                                <template x-if="!editing">
                                                                    <div class="flex justify-end gap-2">
                                                                        <button @click="editing = true" class="p-1 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                            </svg>
                                                                        </button>
                                                                        <button @click="deleteOption({{ $option->id }}, '{{ $group->key }}')" class="p-1 text-red-600 hover:text-red-700 hover:bg-red-50 rounded">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </template>
                                                                <template x-if="editing">
                                                                    <div class="flex justify-end gap-2">
                                                                        <form id="edit-form-{{ $option->id }}" @submit.prevent="saveOption({{ $group->id }}, {{ $option->id }}, '{{ $group->key }}')" class="contents"></form>
                                                                        <button type="submit" form="edit-form-{{ $option->id }}" class="px-3 py-1 text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 rounded">
                                                                            Save
                                                                        </button>
                                                                        <button @click="editing = false" class="px-3 py-1 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded">
                                                                            Cancel
                                                                        </button>
                                                                    </div>
                                                                </template>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="{{ $group->has_colors ? '4' : '3' }}" class="py-8 text-center text-slate-500">
                                                                No options yet. Click "Add Option" to create one.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </main>
    </div>
</x-app-layout>
