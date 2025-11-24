@props(['item' => null])

<div x-data="checklistItemComponent()"
     class="flex items-start gap-2 px-2 py-1.5 rounded hover:bg-[#fafafa] group">

    <!-- Checkbox -->
    <input type="checkbox"
           :checked="item.is_completed"
           @change="toggleComplete"
           class="mt-0.5 w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 cursor-pointer">

    <!-- Item Text (Editable) -->
    <div class="flex-1 min-w-0">
        <div x-show="!editing" class="flex items-center gap-2">
            <button @click="startEditing"
                    type="button"
                    class="text-sm text-left flex-1"
                    :class="item.is_completed ? 'line-through text-slate-500' : 'text-slate-900'">
                <span x-text="item.text"></span>
            </button>
        </div>
        <div x-show="editing" x-cloak>
            <input type="text"
                   x-model="itemText"
                   @keydown.enter="updateText"
                   @keydown.escape="cancelEditing"
                   @blur="updateText"
                   class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
        </div>
    </div>

    <!-- Delete Button (shown on hover) -->
    <button @click="deleteItem"
            type="button"
            class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-red-600 transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

<script>
function checklistItemComponent() {
    return {
        editing: false,
        itemText: '',

        init() {
            if (!this.item) {
                this.item = JSON.parse(this.$el.getAttribute('x-bind:item') || '{}');
            }
            this.itemText = this.item.text;
        },

        startEditing() {
            this.editing = true;
            this.$nextTick(() => {
                this.$el.querySelector('input[x-model="itemText"]').focus();
            });
        },

        cancelEditing() {
            this.editing = false;
            this.itemText = this.item.text;
        },

        async updateText() {
            if (!this.itemText.trim()) {
                this.cancelEditing();
                return;
            }

            if (this.itemText === this.item.text) {
                this.editing = false;
                return;
            }

            try {
                const response = await fetch(`/checklist-items/${this.item.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ text: this.itemText })
                });

                if (response.ok) {
                    const data = await response.json();
                    this.item.text = data.item.text;
                    this.editing = false;
                } else {
                    this.cancelEditing();
                }
            } catch (error) {
                console.error('Error updating item text:', error);
                this.cancelEditing();
            }
        },

        async toggleComplete() {
            const previousState = this.item.is_completed;
            this.item.is_completed = !this.item.is_completed;

            try {
                const response = await fetch(`/checklist-items/${this.item.id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.item = data.item;
                } else {
                    // Revert on error
                    this.item.is_completed = previousState;
                }
            } catch (error) {
                console.error('Error toggling item:', error);
                // Revert on error
                this.item.is_completed = previousState;
            }
        },

        async deleteItem() {
            if (!confirm('Delete this item?')) {
                return;
            }

            try {
                const response = await fetch(`/checklist-items/${this.item.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    // Remove from parent's items array
                    const parentComponent = this.$el.closest('[x-data*="checklistComponent"]').__x.$data;
                    const index = parentComponent.items.findIndex(i => i.id === this.item.id);
                    if (index > -1) {
                        parentComponent.items.splice(index, 1);
                    }
                }
            } catch (error) {
                console.error('Error deleting item:', error);
            }
        }
    }
}
</script>
