@props(['checklist', 'task'])

<div x-data="checklistComponent({{ $checklist->id }}, {{ $task->id }})"
     class="border border-[#e6e6e6] rounded-lg mb-3 bg-white">

    <!-- Checklist Header -->
    <div class="flex items-center justify-between p-3 border-b border-[#e6e6e6] bg-[#fafafa]">
        <div class="flex items-center gap-2 flex-1">
            <!-- Collapse/Expand Toggle -->
            <button @click="collapsed = !collapsed"
                    type="button"
                    class="text-slate-500 hover:text-slate-700 transition-colors">
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': !collapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <!-- Checklist Name (Editable) -->
            <div class="flex-1" x-show="!editingName">
                <button @click="startEditingName"
                        type="button"
                        class="font-medium text-slate-900 hover:text-primary-600 text-left">
                    <span x-text="name">{{ $checklist->name }}</span>
                </button>
            </div>
            <div class="flex-1" x-show="editingName" x-cloak>
                <input type="text"
                       x-model="name"
                       @keydown.enter="updateName"
                       @keydown.escape="cancelEditingName"
                       @blur="updateName"
                       class="w-full px-2 py-1 text-sm border border-primary-300 rounded focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <!-- Progress -->
            <div class="flex items-center gap-2">
                <div class="text-xs text-slate-600">
                    <span x-text="completedCount"></span>/<span x-text="totalCount"></span>
                </div>
                <div class="w-20 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                    <div class="h-full bg-green-500 transition-all"
                         :style="`width: ${progressPercentage}%`"></div>
                </div>
            </div>
        </div>

        <!-- Delete Checklist Button -->
        <button @click="deleteChecklist"
                type="button"
                class="ml-2 text-slate-400 hover:text-red-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </button>
    </div>

    <!-- Checklist Items -->
    <div x-show="!collapsed" x-collapse class="p-2">
        <template x-for="item in items" :key="item.id">
            <x-tasks.checklist-item :item="item" />
        </template>

        <!-- Add New Item -->
        <div class="mt-2">
            <div x-show="!addingItem" class="px-2">
                <button @click="startAddingItem"
                        type="button"
                        class="text-sm text-slate-500 hover:text-primary-600 transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add item
                </button>
            </div>
            <div x-show="addingItem" x-cloak class="px-2">
                <input type="text"
                       x-model="newItemText"
                       @keydown.enter="addItem"
                       @keydown.escape="cancelAddingItem"
                       @blur="cancelAddingItem"
                       placeholder="Enter item text..."
                       class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>
    </div>
</div>

<script>
function checklistComponent(checklistId, taskId) {
    return {
        checklistId: checklistId,
        taskId: taskId,
        name: '{{ $checklist->name }}',
        collapsed: false,
        editingName: false,
        addingItem: false,
        newItemText: '',
        items: @json($checklist->items),

        get totalCount() {
            return this.items.length;
        },

        get completedCount() {
            return this.items.filter(item => item.is_completed).length;
        },

        get progressPercentage() {
            if (this.totalCount === 0) return 0;
            return Math.round((this.completedCount / this.totalCount) * 100);
        },

        startEditingName() {
            this.editingName = true;
            this.$nextTick(() => {
                this.$el.querySelector('input[x-model="name"]').focus();
            });
        },

        cancelEditingName() {
            this.editingName = false;
            this.name = '{{ $checklist->name }}';
        },

        async updateName() {
            if (!this.name.trim()) {
                this.cancelEditingName();
                return;
            }

            try {
                const response = await fetch(`/tasks/${this.taskId}/checklists/${this.checklistId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ name: this.name })
                });

                if (response.ok) {
                    this.editingName = false;
                } else {
                    this.cancelEditingName();
                }
            } catch (error) {
                console.error('Error updating checklist name:', error);
                this.cancelEditingName();
            }
        },

        startAddingItem() {
            this.addingItem = true;
            this.$nextTick(() => {
                this.$el.querySelector('input[x-model="newItemText"]').focus();
            });
        },

        cancelAddingItem() {
            setTimeout(() => {
                this.addingItem = false;
                this.newItemText = '';
            }, 200);
        },

        async addItem() {
            if (!this.newItemText.trim()) {
                this.cancelAddingItem();
                return;
            }

            try {
                const response = await fetch(`/tasks/${this.taskId}/checklists/${this.checklistId}/items`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ text: this.newItemText })
                });

                if (response.ok) {
                    const data = await response.json();
                    this.items.push(data.item);
                    this.newItemText = '';
                    this.addingItem = false;
                }
            } catch (error) {
                console.error('Error adding checklist item:', error);
            }
        },

        async deleteChecklist() {
            if (!confirm('Are you sure you want to delete this checklist?')) {
                return;
            }

            try {
                const response = await fetch(`/tasks/${this.taskId}/checklists/${this.checklistId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    this.$el.remove();
                }
            } catch (error) {
                console.error('Error deleting checklist:', error);
            }
        }
    }
}
</script>
