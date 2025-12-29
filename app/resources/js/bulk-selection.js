function bulkSelection(options = {}) {
    return {
        selectedIds: [],
        selectAll: false,
        groupSelections: {},
        isLoading: false,
        idAttribute: options.idAttribute || "data-id",
        rowSelector: options.rowSelector || "[data-selectable]",
        
        get selectedCount() {
            return this.selectedIds.length;
        },
        
        get hasSelection() {
            return this.selectedIds.length > 0;
        },
        
        init() {
            this.$watch("selectAll", value => {
                if (value) {
                    this.selectAllVisible();
                } else {
                    if (this.selectedIds.length > 0) {
                        this.clearSelection();
                    }
                }
            });
        },
        
        toggleItem(id) {
            const index = this.selectedIds.indexOf(id);
            if (index > -1) {
                this.selectedIds.splice(index, 1);
            } else {
                this.selectedIds.push(id);
            }
            this.updateSelectAllState();
        },
        
        isSelected(id) {
            return this.selectedIds.includes(id);
        },
        
        selectAllVisible() {
            const items = document.querySelectorAll(this.rowSelector);
            this.selectedIds = Array.from(items).map(item => {
                return parseInt(item.getAttribute(this.idAttribute));
            });
        },
        
        clearSelection() {
            this.selectedIds = [];
            this.selectAll = false;
        },
        
        updateSelectAllState() {
            const items = document.querySelectorAll(this.rowSelector);
            this.selectAll = items.length > 0 && this.selectedIds.length === items.length;
        },

        toggleAll() {
            if (this.selectAll) {
                this.selectAllVisible();
            } else {
                this.clearSelection();
            }
        },

        toggleGroup(groupName) {
            const groupRows = Array.from(document.querySelectorAll(`[data-group="${groupName}"]`));
            const groupIds = groupRows.map(row => parseInt(row.getAttribute(this.idAttribute)));

            if (this.groupSelections[groupName]) {
                // Select all in group
                groupIds.forEach(id => {
                    if (!this.selectedIds.includes(id)) {
                        this.selectedIds.push(id);
                    }
                });
            } else {
                // Deselect all in group
                this.selectedIds = this.selectedIds.filter(id => !groupIds.includes(id));
            }
            this.updateSelectAllState();
        },

        async performBulkAction(action, endpoint, options = {}) {
            if (this.selectedIds.length === 0) return;

            const confirmed = await this.confirmAction(options);
            if (!confirmed) return;

            this.isLoading = true;

            try {
                // Handle export action differently (file download)
                if (action === "export") {
                    await this.handleExport(endpoint, options);
                    return;
                }

                const response = await fetch(endpoint, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content,
                        "Accept": "application/json",
                    },
                    body: JSON.stringify({
                        ids: this.selectedIds,
                        action: action,
                        ...options.data
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    this.showToast(result.message || "Action completed successfully", "success");
                    this.clearSelection();

                    if (options.reload !== false) {
                        window.location.reload();
                    }
                } else {
                    this.showToast(result.message || "Action failed", "error");
                }
            } catch (error) {
                this.showToast("An error occurred. Please try again.", "error");
            } finally {
                this.isLoading = false;
            }
        },

        async handleExport(endpoint, options = {}) {
            try {
                const response = await fetch(endpoint, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content,
                    },
                    body: JSON.stringify({
                        ids: this.selectedIds,
                        action: "export"
                    })
                });

                if (response.ok) {
                    // Get filename from Content-Disposition header or use default
                    const contentDisposition = response.headers.get("Content-Disposition");
                    let filename = "export.csv";
                    if (contentDisposition) {
                        const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
                        if (filenameMatch) {
                            filename = filenameMatch[1];
                        }
                    }

                    // Download the file
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement("a");
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    this.showToast(options.successMessage || "Export completed successfully", "success");
                    this.clearSelection();
                } else {
                    const result = await response.json().catch(() => ({ message: "Export failed" }));
                    this.showToast(result.message || "Export failed", "error");
                }
            } catch (error) {
                this.showToast("An error occurred during export. Please try again.", "error");
            } finally {
                this.isLoading = false;
            }
        },
        
        confirmAction(options) {
            return new Promise((resolve) => {
                // Support both 'title'/'message' and 'confirmTitle'/'confirmMessage' formats
                const title = options.title || options.confirmTitle || "Confirm Bulk Action";
                const message = options.message || options.confirmMessage || `This will affect ${this.selectedCount} items. Continue?`;

                window.dispatchEvent(new CustomEvent("confirm-dialog", {
                    detail: {
                        title: title,
                        message: message,
                        confirmText: options.confirmText || "Proceed",
                        variant: options.variant || "destructive",
                        onConfirm: () => resolve(true)
                    }
                }));

                setTimeout(() => {
                    const hasDialog = document.querySelector("[x-data*=confirm]");
                    if (!hasDialog) {
                        resolve(window.confirm(message || "Are you sure?"));
                    }
                }, 100);
            });
        },
        
        showToast(message, type = "info") {
            const toast = document.createElement("div");
            toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white ${
                type === "success" ? "bg-green-600" : type === "error" ? "bg-red-600" : "bg-blue-600"
            } transition-opacity duration-300`;
            toast.textContent = message;
            toast.style.opacity = "0";
            
            document.body.appendChild(toast);
            
            setTimeout(() => { toast.style.opacity = "1"; }, 10);
            
            setTimeout(() => {
                toast.style.opacity = "0";
                setTimeout(() => { document.body.removeChild(toast); }, 300);
            }, 3000);
        }
    };
}

window.bulkSelection = bulkSelection;
