<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('taskModals', {
        // Space Modal
        spaceModal: {
            open: false,
            editId: null,
            submitting: false,
            form: {
                name: '',
                icon: '📁',
                color: '#3b82f6'
            }
        },

        // Folder Modal
        folderModal: {
            open: false,
            editId: null,
            submitting: false,
            form: {
                space_id: '',
                name: '',
                icon: '📂',
                color: '#8b5cf6'
            }
        },

        // List Modal
        listModal: {
            open: false,
            editId: null,
            submitting: false,
            form: {
                folder_id: '',
                client_id: '',
                name: '',
                icon: '',
                color: '#10b981'
            }
        },

        // Space Methods
        openSpaceModal(spaceId = null, data = {}) {
            if (spaceId) {
                this.spaceModal.editId = spaceId;
                this.spaceModal.form = {
                    name: data.name || '',
                    icon: data.icon || '📁',
                    color: data.color || '#3b82f6'
                };
            } else {
                this.resetSpaceForm();
            }
            this.spaceModal.open = true;
        },

        closeSpaceModal() {
            this.spaceModal.open = false;
            setTimeout(() => {
                this.resetSpaceForm();
            }, 300);
        },

        resetSpaceForm() {
            this.spaceModal.editId = null;
            this.spaceModal.form = {
                name: '',
                icon: '📁',
                color: '#3b82f6'
            };
        },

        async submitSpace() {
            this.spaceModal.submitting = true;

            const url = this.spaceModal.editId
                ? `/task-spaces/${this.spaceModal.editId}`
                : '/task-spaces';

            const method = this.spaceModal.editId ? 'PATCH' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.spaceModal.form)
                });

                const data = await response.json();

                if (data.success) {
                    this.closeSpaceModal();
                    window.location.reload();
                } else {
                    alert(data.message || 'An error occurred');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            } finally {
                this.spaceModal.submitting = false;
            }
        },

        // Folder Methods
        openFolderModal(spaceId = null, folderId = null, data = {}) {
            if (folderId) {
                this.folderModal.editId = folderId;
                this.folderModal.form = {
                    space_id: data.space_id || spaceId || '',
                    name: data.name || '',
                    icon: data.icon || '📂',
                    color: data.color || '#8b5cf6'
                };
            } else {
                this.resetFolderForm();
                if (spaceId) {
                    this.folderModal.form.space_id = spaceId;
                }
            }
            this.folderModal.open = true;
        },

        closeFolderModal() {
            this.folderModal.open = false;
            setTimeout(() => {
                this.resetFolderForm();
            }, 300);
        },

        resetFolderForm() {
            this.folderModal.editId = null;
            this.folderModal.form = {
                space_id: '',
                name: '',
                icon: '📂',
                color: '#8b5cf6'
            };
        },

        async submitFolder() {
            this.folderModal.submitting = true;

            const url = this.folderModal.editId
                ? `/task-folders/${this.folderModal.editId}`
                : '/task-folders';

            const method = this.folderModal.editId ? 'PATCH' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.folderModal.form)
                });

                const data = await response.json();

                if (data.success) {
                    this.closeFolderModal();
                    window.location.reload();
                } else {
                    alert(data.message || 'An error occurred');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            } finally {
                this.folderModal.submitting = false;
            }
        },

        // List Methods
        openListModal(folderId = null, listId = null, data = {}) {
            if (listId) {
                this.listModal.editId = listId;
                this.listModal.form = {
                    folder_id: data.folder_id || folderId || '',
                    client_id: data.client_id || '',
                    name: data.name || '',
                    icon: data.icon || '',
                    color: data.color || '#10b981'
                };
            } else {
                this.resetListForm();
                if (folderId) {
                    this.listModal.form.folder_id = folderId;
                }
            }
            this.listModal.open = true;
        },

        closeListModal() {
            this.listModal.open = false;
            setTimeout(() => {
                this.resetListForm();
            }, 300);
        },

        resetListForm() {
            this.listModal.editId = null;
            this.listModal.form = {
                folder_id: '',
                client_id: '',
                name: '',
                icon: '',
                color: '#10b981'
            };
        },

        async submitList() {
            this.listModal.submitting = true;

            const url = this.listModal.editId
                ? `/task-lists/${this.listModal.editId}`
                : '/task-lists';

            const method = this.listModal.editId ? 'PATCH' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.listModal.form)
                });

                const data = await response.json();

                if (data.success) {
                    this.closeListModal();
                    window.location.reload();
                } else {
                    alert(data.message || 'An error occurred');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            } finally {
                this.listModal.submitting = false;
            }
        }
    });
});
</script>
