@props(['taskId', 'selectedTags' => []])

<div x-data="tagSelector({{ $taskId }}, {{ json_encode($selectedTags) }})"
     class="relative">
    <!-- Tag Display / Trigger -->
    <div class="flex flex-wrap items-center gap-1">
        <!-- Existing Tags -->
        <template x-for="tag in currentTags" :key="tag.id">
            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium group cursor-pointer hover:opacity-80 transition-opacity"
                 :style="`background-color: ${tag.color}20; color: ${tag.color}`"
                 @click="removeTag(tag.id)">
                <span x-text="tag.name"></span>
                <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
        </template>

        <!-- Add Tag Button -->
        <button @click="showDropdown = !showDropdown"
                type="button"
                class="inline-flex items-center gap-1 px-2 py-0.5 text-xs text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded transition-colors">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>{{ __('Tag') }}</span>
        </button>
    </div>

    <!-- Dropdown -->
    <div x-show="showDropdown"
         @click.away="showDropdown = false"
         x-transition
         x-cloak
         class="absolute z-50 mt-1 w-64 bg-white rounded-lg shadow-lg border border-slate-200 py-2">
        <!-- Search -->
        <div class="px-3 pb-2">
            <input type="text"
                   x-model="searchQuery"
                   @input="filterTags"
                   placeholder="{{ __('Search tags...') }}"
                   class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Tags List -->
        <div class="max-h-48 overflow-y-auto">
            <template x-for="tag in filteredTags" :key="tag.id">
                <button @click="addTag(tag)"
                        type="button"
                        :disabled="isTagSelected(tag.id)"
                        class="w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <div class="flex items-center gap-2 flex-1">
                        <div class="w-3 h-3 rounded-full flex-shrink-0"
                             :style="`background-color: ${tag.color}`"></div>
                        <span x-text="tag.name"></span>
                    </div>
                    <svg x-show="isTagSelected(tag.id)" class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </template>
            <template x-if="filteredTags.length === 0">
                <div class="px-3 py-4 text-center text-sm text-slate-500">
                    {{ __('No tags found') }}
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
function tagSelector(taskId, initialTags = []) {
    return {
        taskId: taskId,
        currentTags: initialTags,
        allTags: @json(\App\Models\TaskTag::forOrganization(auth()->user()->organization_id)->ordered()->get()),
        filteredTags: [],
        searchQuery: '',
        showDropdown: false,

        init() {
            this.filterTags();
        },

        filterTags() {
            const query = this.searchQuery.toLowerCase();
            this.filteredTags = this.allTags.filter(tag =>
                tag.name.toLowerCase().includes(query)
            );
        },

        isTagSelected(tagId) {
            return this.currentTags.some(t => t.id === tagId);
        },

        async addTag(tag) {
            if (this.isTagSelected(tag.id)) return;

            try {
                const response = await fetch(`/tasks/${this.taskId}/tags`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ tag_id: tag.id })
                });

                const data = await response.json();

                if (data.success) {
                    this.currentTags.push(tag);
                    this.showDropdown = false;
                    this.searchQuery = '';
                    this.filterTags();
                } else {
                    alert(data.message || 'Failed to add tag');
                }
            } catch (error) {
                console.error('Error adding tag:', error);
                alert('An error occurred. Please try again.');
            }
        },

        async removeTag(tagId) {
            if (!confirm('Remove this tag?')) return;

            try {
                const response = await fetch(`/tasks/${this.taskId}/tags/${tagId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.currentTags = this.currentTags.filter(t => t.id !== tagId);
                } else {
                    alert(data.message || 'Failed to remove tag');
                }
            } catch (error) {
                console.error('Error removing tag:', error);
                alert('An error occurred. Please try again.');
            }
        }
    };
}
</script>
@endpush
