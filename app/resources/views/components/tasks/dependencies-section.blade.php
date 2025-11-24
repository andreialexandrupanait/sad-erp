<div x-data="dependenciesManager()" x-init="loadDependencies()">
    <!-- Blocked Warning -->
    <div x-show="isBlocked" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-red-800">{{ __('This task is blocked') }}</p>
                <p class="text-xs text-red-600" x-text="`${incompleteDependenciesCount} incomplete ${incompleteDependenciesCount === 1 ? 'dependency' : 'dependencies'}`"></p>
            </div>
        </div>
    </div>

    <!-- Dependencies (Tasks this task depends on) -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-semibold text-slate-900">
                {{ __('Waiting On') }}
                <span class="ml-2 text-xs text-slate-500" x-text="`(${dependencies.length})`"></span>
            </h4>
            <button @click="showAddDependency = true"
                    type="button"
                    class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                + {{ __('Add') }}
            </button>
        </div>

        <!-- Dependencies List -->
        <div class="space-y-2">
            <template x-for="dep in dependencies" :key="dep.id">
                <div class="flex items-center gap-2 p-2 bg-[#fafafa] rounded-lg hover:bg-slate-100 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full flex-shrink-0"
                                 :style="`background-color: ${dep.depends_on_task?.status?.color || '#94a3b8'}`"></div>
                            <span class="text-sm text-slate-900 truncate" x-text="dep.depends_on_task?.name"></span>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-slate-500" x-text="dep.depends_on_task?.list?.name"></span>
                            <span class="text-xs px-1.5 py-0.5 rounded"
                                  :style="`background-color: ${dep.depends_on_task?.status?.color}20; color: ${dep.depends_on_task?.status?.color}`"
                                  x-text="dep.depends_on_task?.status?.label"></span>
                        </div>
                    </div>
                    <button @click="removeDependency(dep.id)"
                            type="button"
                            class="p-1 text-slate-400 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>

            <template x-if="dependencies.length === 0">
                <div class="text-center py-6 text-slate-500 text-sm">
                    {{ __('No dependencies') }}
                </div>
            </template>
        </div>
    </div>

    <!-- Dependents (Tasks that depend on this task) -->
    <div>
        <div class="mb-3">
            <h4 class="text-sm font-semibold text-slate-900">
                {{ __('Blocking') }}
                <span class="ml-2 text-xs text-slate-500" x-text="`(${dependents.length})`"></span>
            </h4>
            <p class="text-xs text-slate-500 mt-1">{{ __('Tasks waiting for this task to be completed') }}</p>
        </div>

        <!-- Dependents List -->
        <div class="space-y-2">
            <template x-for="dep in dependents" :key="dep.id">
                <div class="flex items-center gap-2 p-2 bg-amber-50 border border-amber-100 rounded-lg">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full flex-shrink-0"
                                 :style="`background-color: ${dep.task?.status?.color || '#94a3b8'}`"></div>
                            <span class="text-sm text-slate-900 truncate" x-text="dep.task?.name"></span>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-slate-500" x-text="dep.task?.list?.name"></span>
                            <span class="text-xs px-1.5 py-0.5 rounded"
                                  :style="`background-color: ${dep.task?.status?.color}20; color: ${dep.task?.status?.color}`"
                                  x-text="dep.task?.status?.label"></span>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="dependents.length === 0">
                <div class="text-center py-6 text-slate-500 text-sm">
                    {{ __('No tasks are blocked by this one') }}
                </div>
            </template>
        </div>
    </div>

    <!-- Add Dependency Modal -->
    <div x-show="showAddDependency"
         x-cloak
         @click.away="showAddDependency = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div @click.stop class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="p-4 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Add Dependency') }}</h3>
                <p class="text-sm text-slate-600 mt-1">{{ __('Select a task that this task depends on') }}</p>
            </div>

            <div class="p-4">
                <!-- Search -->
                <input type="text"
                       x-model="searchQuery"
                       @input.debounce.300ms="searchTasks"
                       placeholder="{{ __('Search tasks...') }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">

                <!-- Search Results -->
                <div class="mt-3 max-h-64 overflow-y-auto space-y-2">
                    <template x-for="task in searchResults" :key="task.id">
                        <button @click="addDependency(task.id)"
                                type="button"
                                class="w-full flex items-center gap-2 p-2 text-left hover:bg-[#fafafa] rounded-lg transition-colors">
                            <div class="w-2 h-2 rounded-full flex-shrink-0"
                                 :style="`background-color: ${task.status?.color || '#94a3b8'}`"></div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm text-slate-900 truncate" x-text="task.name"></div>
                                <div class="text-xs text-slate-500" x-text="task.list?.name"></div>
                            </div>
                        </button>
                    </template>

                    <template x-if="searchResults.length === 0 && searchQuery.length > 0">
                        <div class="text-center py-8 text-slate-500 text-sm">
                            {{ __('No tasks found') }}
                        </div>
                    </template>

                    <template x-if="searchQuery.length === 0">
                        <div class="text-center py-8 text-slate-500 text-sm">
                            {{ __('Start typing to search for tasks') }}
                        </div>
                    </template>
                </div>
            </div>

            <div class="p-4 border-t border-slate-200 flex justify-end">
                <button @click="showAddDependency = false"
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function dependenciesManager() {
    return {
        dependencies: [],
        dependents: [],
        isBlocked: false,
        incompleteDependenciesCount: 0,
        showAddDependency: false,
        searchQuery: '',
        searchResults: [],

        async loadDependencies() {
            const taskId = Alpine.store('sidePanel').taskId;
            if (!taskId) return;

            try {
                const response = await fetch(`/tasks/${taskId}/dependencies`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.dependencies = data.dependencies || [];
                    this.dependents = data.dependents || [];
                    this.isBlocked = data.is_blocked || false;
                    this.incompleteDependenciesCount = data.incomplete_dependencies_count || 0;
                }
            } catch (error) {
                console.error('Error loading dependencies:', error);
            }
        },

        async searchTasks() {
            if (this.searchQuery.length === 0) {
                this.searchResults = [];
                return;
            }

            const taskId = Alpine.store('sidePanel').taskId;

            try {
                const response = await fetch(`/tasks/${taskId}/dependencies/search?query=${encodeURIComponent(this.searchQuery)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.searchResults = data.tasks || [];
                }
            } catch (error) {
                console.error('Error searching tasks:', error);
            }
        },

        async addDependency(dependsOnTaskId) {
            const taskId = Alpine.store('sidePanel').taskId;

            try {
                const response = await fetch(`/tasks/${taskId}/dependencies`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        depends_on_task_id: dependsOnTaskId,
                        dependency_type: 'blocks'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.dependencies = data.dependencies || [];
                    this.showAddDependency = false;
                    this.searchQuery = '';
                    this.searchResults = [];
                    await this.loadDependencies(); // Reload to update blocked status
                } else {
                    alert(data.message || 'Failed to add dependency');
                }
            } catch (error) {
                console.error('Error adding dependency:', error);
                alert('An error occurred. Please try again.');
            }
        },

        async removeDependency(dependencyId) {
            if (!confirm('Remove this dependency?')) return;

            const taskId = Alpine.store('sidePanel').taskId;

            try {
                const response = await fetch(`/tasks/${taskId}/dependencies/${dependencyId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    await this.loadDependencies();
                } else {
                    alert(data.message || 'Failed to remove dependency');
                }
            } catch (error) {
                console.error('Error removing dependency:', error);
                alert('An error occurred. Please try again.');
            }
        }
    };
}
</script>
@endpush
