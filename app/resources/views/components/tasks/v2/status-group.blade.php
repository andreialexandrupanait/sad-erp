{{-- Status Group Component with Lazy Loading --}}
@props(['status', 'taskCount', 'initialTasks' => [], 'organizationId'])

<div class="mb-6"
     x-data="{
         expanded: localStorage.getItem('status_{{ $status->id }}_expanded') === 'true',
         loading: false,
         page: 1,
         hasMore: {{ $taskCount > 50 ? 'true' : 'false' }},
         tasksContainer: null,

         init() {
             this.tasksContainer = this.$refs.tasksContainer;
             // Restore expanded state from localStorage
             if (this.expanded && this.tasksContainer.children.length === 0) {
                 this.loadTasks();
             }
         },

         toggleExpanded() {
             this.expanded = !this.expanded;
             localStorage.setItem('status_{{ $status->id }}_expanded', this.expanded);

             if (this.expanded && this.tasksContainer.children.length === 0) {
                 this.loadTasks();
             }
         },

         loadTasks() {
             if (this.loading) return;

             this.loading = true;

             fetch(`/api/tasks/status/{{ $status->id }}?page=${this.page}&organization_id={{ $organizationId }}`)
                 .then(response => response.json())
                 .then(data => {
                     this.tasksContainer.insertAdjacentHTML('beforeend', data.html);
                     this.page++;
                     this.hasMore = data.has_more;
                     this.loading = false;
                 })
                 .catch(error => {
                     console.error('Error loading tasks:', error);
                     this.loading = false;
                 });
         }
     }">

    {{-- ClickUp-Style Status Header --}}
    <div class="flex items-center justify-between h-10 px-4 bg-white border-b border-[#e6e9ef] hover:bg-[#fafbfc] group cursor-pointer"
         @click="toggleExpanded">
        <div class="flex items-center gap-2">
            {{-- Expand/Collapse Button --}}
            <button class="text-[#8b9bac] hover:text-[#3b4c5c] transition-transform p-0.5"
                    :class="{ 'rotate-90': expanded }">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
            </button>

            {{-- Status Badge with ClickUp styling --}}
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold uppercase tracking-wide"
                      style="background-color: {{ $status->color_class }}; color: white;">
                    {{ $status->label }}
                </span>

                {{-- Task Count --}}
                <span class="text-xs text-[#8b9bac] font-medium">
                    {{ $taskCount }}
                </span>
            </div>
        </div>

        {{-- Quick Actions (ClickUp style) --}}
        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button class="p-1 text-[#8b9bac] hover:text-[#3b4c5c] hover:bg-[#e6e9ef] rounded"
                    title="Add task"
                    @click.stop>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
            <button class="p-1 text-[#8b9bac] hover:text-[#3b4c5c] hover:bg-[#e6e9ef] rounded"
                    title="More options"
                    @click.stop>
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Tasks Container --}}
    <div x-show="expanded"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="bg-white">

        {{-- Task Rows Container --}}
        <div x-ref="tasksContainer" class="divide-y divide-slate-100">
            {{-- Initially empty, filled by AJAX --}}
        </div>

        {{-- Load More Button --}}
        <div x-show="hasMore" class="py-4 text-center border-t border-slate-100">
            <button @click="loadTasks"
                    :disabled="loading"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!loading">Load More Tasks</span>
                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading...
                </span>
            </button>
        </div>

        {{-- Empty State --}}
        <div x-show="!loading && !hasMore && tasksContainer && tasksContainer.children.length === 0"
             class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="mt-4 text-sm text-slate-500">No tasks in this status</p>
        </div>
    </div>
</div>
