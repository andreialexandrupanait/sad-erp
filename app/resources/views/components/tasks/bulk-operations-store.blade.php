<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('taskBulk', {
        selectedTasks: [],
        showStatusPicker: false,
        showListPicker: false,

        toggleTask(taskId) {
            const index = this.selectedTasks.indexOf(taskId);
            if (index > -1) {
                this.selectedTasks.splice(index, 1);
            } else {
                this.selectedTasks.push(taskId);
            }
        },

        isSelected(taskId) {
            return this.selectedTasks.includes(taskId);
        },

        selectAll(taskIds) {
            this.selectedTasks = [...taskIds];
        },

        clearSelection() {
            this.selectedTasks = [];
            this.showStatusPicker = false;
            this.showListPicker = false;
        },

        async bulkUpdateStatus(statusId) {
            if (!confirm(`{{ __('Update status for') }} ${this.selectedTasks.length} {{ __('tasks?') }}`)) {
                return;
            }

            try {
                const promises = this.selectedTasks.map(taskId =>
                    fetch(`/tasks/${taskId}/quick-update`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status_id: statusId })
                    })
                );

                await Promise.all(promises);
                this.clearSelection();
                window.location.reload();
            } catch (error) {
                console.error('Error:', error);
                alert('{{ __('An error occurred while updating tasks') }}');
            }
        },

        async bulkMoveToList(listId) {
            if (!confirm(`{{ __('Move') }} ${this.selectedTasks.length} {{ __('tasks to this list?') }}`)) {
                return;
            }

            try {
                const promises = this.selectedTasks.map(taskId =>
                    fetch(`/tasks/${taskId}/quick-update`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ list_id: listId })
                    })
                );

                await Promise.all(promises);
                this.clearSelection();
                window.location.reload();
            } catch (error) {
                console.error('Error:', error);
                alert('{{ __('An error occurred while moving tasks') }}');
            }
        },

        async bulkDelete() {
            if (!confirm(`{{ __('Are you sure you want to delete') }} ${this.selectedTasks.length} {{ __('tasks? This action cannot be undone.') }}`)) {
                return;
            }

            try {
                const promises = this.selectedTasks.map(taskId =>
                    fetch(`/tasks/${taskId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                );

                await Promise.all(promises);
                this.clearSelection();
                window.location.reload();
            } catch (error) {
                console.error('Error:', error);
                alert('{{ __('An error occurred while deleting tasks') }}');
            }
        }
    });
});
</script>
