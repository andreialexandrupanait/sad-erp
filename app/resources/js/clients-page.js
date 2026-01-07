/**
 * Clients Page Alpine.js Component
 *
 * ClickUp-style reactive UI with:
 * - No page reloads when filtering/sorting
 * - Clean URLs (status slugs, combined sort params)
 * - localStorage for UI preferences (view mode, grouping, per-page)
 * - Multi-status filter support
 */

function clientsPage(initialData = {}) {
    return {
        // === URL State (synced with pushState) ===
        filters: {
            status: initialData.filters?.status || [],
            q: initialData.filters?.q || '',
            sort: initialData.filters?.sort || 'activity:desc',
            page: initialData.filters?.page || 1
        },

        // === UI State (localStorage) ===
        ui: {
            viewMode: 'table',
            grouped: false,
            perPage: 25,
            collapsedGroups: {}
        },

        // === Data State ===
        clients: [],
        statuses: initialData.statuses || [],
        statusCounts: { total: 0, by_status: {} },
        pagination: {
            total: 0,
            per_page: 25,
            current_page: 1,
            last_page: 1,
            from: 0,
            to: 0
        },

        // === Loading & Selection State ===
        loading: false,
        initialLoad: false,
        selectedIds: [],
        selectAll: false,
        savingStatus: {},
        openStatusDropdown: null, // Track which client's dropdown is open

        // === Computed Properties ===
        get selectedCount() {
            return this.selectedIds.length;
        },

        get hasSelection() {
            return this.selectedIds.length > 0;
        },

        get sortColumn() {
            return this.filters.sort.split(':')[0];
        },

        get sortDirection() {
            const parts = this.filters.sort.split(':');
            return parts[1] || 'asc';
        },

        get pages() {
            const pages = [];
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;

            if (last <= 7) {
                for (let i = 1; i <= last; i++) pages.push(i);
            } else {
                pages.push(1);
                if (current > 3) pages.push('...');

                const start = Math.max(2, current - 1);
                const end = Math.min(last - 1, current + 1);

                for (let i = start; i <= end; i++) pages.push(i);

                if (current < last - 2) pages.push('...');
                pages.push(last);
            }
            return pages;
        },

        // === Lifecycle ===
        init() {
            this.loadUiPreferences();
            this.parseUrlFilters();
            this.loadClients();
            this.setupUrlListener();
        },

        loadUiPreferences() {
            const stored = localStorage.getItem('clients_ui_prefs');
            if (stored) {
                try {
                    const prefs = JSON.parse(stored);

                    // Security: Validate schema to prevent localStorage poisoning
                    const validViewModes = ['table', 'grid', 'list'];
                    const validPerPage = [10, 25, 50, 100];

                    // Only apply viewMode if it's a valid option
                    if (validViewModes.includes(prefs.viewMode)) {
                        this.ui.viewMode = prefs.viewMode;
                    }

                    // Only apply perPage if it's a valid option
                    if (validPerPage.includes(prefs.perPage)) {
                        this.ui.perPage = prefs.perPage;
                    }

                    // Boolean validation for grouped
                    if (typeof prefs.grouped === 'boolean') {
                        this.ui.grouped = prefs.grouped;
                    }

                    // Object validation for collapsedGroups
                    if (prefs.collapsedGroups && typeof prefs.collapsedGroups === 'object' && !Array.isArray(prefs.collapsedGroups)) {
                        this.ui.collapsedGroups = prefs.collapsedGroups;
                    }
                } catch (e) {
                    // Failed to parse UI preferences, clear corrupted data and use defaults
                    console.warn('Invalid UI preferences in localStorage, resetting to defaults');
                    localStorage.removeItem('clients_ui_prefs');
                }
            }
        },

        saveUiPreferences() {
            localStorage.setItem('clients_ui_prefs', JSON.stringify(this.ui));
        },

        parseUrlFilters() {
            const params = new URLSearchParams(window.location.search);

            if (params.has('status')) {
                this.filters.status = params.get('status').split(',').filter(Boolean);
            }
            if (params.has('q')) {
                this.filters.q = params.get('q');
            }
            if (params.has('sort')) {
                this.filters.sort = params.get('sort');
            }
            if (params.has('page')) {
                this.filters.page = parseInt(params.get('page')) || 1;
            }
        },

        setupUrlListener() {
            window.addEventListener('popstate', () => {
                this.parseUrlFilters();
                this.loadClients();
            });
        },

        // === Data Loading ===
        async loadClients() {
            this.loading = true;

            try {
                const params = new URLSearchParams();

                if (this.filters.status.length) {
                    params.set('status', this.filters.status.join(','));
                }
                if (this.filters.q) {
                    params.set('q', this.filters.q);
                }
                if (this.filters.sort && this.filters.sort !== 'activity:desc') {
                    params.set('sort', this.filters.sort);
                }
                if (this.filters.page > 1) {
                    params.set('page', this.filters.page);
                }
                params.set('limit', this.ui.perPage);

                const url = `/clients${params.toString() ? '?' + params.toString() : ''}`;

                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                this.clients = data.clients || [];
                this.pagination = data.pagination || this.pagination;
                this.statusCounts = data.status_counts || this.statusCounts;

                // Clear selection on data change
                this.clearSelection();

            } catch (error) {
                this.showToast('Failed to load clients', 'error');
            } finally {
                this.loading = false;
                this.initialLoad = false;
            }
        },

        // === URL Management ===
        updateUrl() {
            const params = new URLSearchParams();

            if (this.filters.status.length) {
                params.set('status', this.filters.status.join(','));
            }
            if (this.filters.q) {
                params.set('q', this.filters.q);
            }
            if (this.filters.sort && this.filters.sort !== 'activity:desc') {
                params.set('sort', this.filters.sort);
            }
            if (this.filters.page > 1) {
                params.set('page', this.filters.page);
            }

            const url = params.toString() ? `?${params.toString()}` : window.location.pathname;
            history.pushState({}, '', url);
        },

        // === Filter Actions ===
        toggleStatus(slug) {
            const idx = this.filters.status.indexOf(slug);
            if (idx === -1) {
                this.filters.status.push(slug);
            } else {
                this.filters.status.splice(idx, 1);
            }
            this.filters.page = 1;
            this.updateUrl();
            this.loadClients();
        },

        clearStatusFilter() {
            this.filters.status = [];
            this.filters.page = 1;
            this.updateUrl();
            this.loadClients();
        },

        setSort(column) {
            const [currentCol, currentDir] = this.filters.sort.split(':');

            if (currentCol === column) {
                // Toggle direction
                const newDir = currentDir === 'asc' ? 'desc' : 'asc';
                this.filters.sort = `${column}:${newDir}`;
            } else {
                // Default direction for new column
                const defaultDesc = ['revenue', 'total_incomes', 'created', 'created_at', 'activity', 'last_invoice_at'];
                const dir = defaultDesc.includes(column) ? 'desc' : 'asc';
                this.filters.sort = `${column}:${dir}`;
            }

            this.filters.page = 1;
            this.updateUrl();
            this.loadClients();
        },

        search(query) {
            this.filters.q = query;
            this.filters.page = 1;
            this.updateUrl();
            this.loadClients();
        },

        // Debounced search for input
        searchDebounced: null,
        onSearchInput(query) {
            if (this.searchDebounced) {
                clearTimeout(this.searchDebounced);
            }
            this.searchDebounced = setTimeout(() => {
                this.search(query);
            }, 300);
        },

        goToPage(page) {
            if (page < 1 || page > this.pagination.last_page || page === '...') return;
            this.filters.page = page;
            this.updateUrl();
            this.loadClients();
            // Scroll to top of table
            document.querySelector('[x-data]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        // === UI State Changes ===
        setViewMode(mode) {
            this.ui.viewMode = mode;
            this.saveUiPreferences();
        },

        toggleGrouped() {
            this.ui.grouped = !this.ui.grouped;
            this.saveUiPreferences();
        },

        setPerPage(perPage) {
            this.ui.perPage = parseInt(perPage);
            this.filters.page = 1;
            this.saveUiPreferences();
            this.loadClients();
        },

        toggleGroupCollapse(statusId) {
            const key = statusId || 'null';
            this.ui.collapsedGroups[key] = !this.ui.collapsedGroups[key];
            this.saveUiPreferences();
        },

        isGroupCollapsed(statusId) {
            const key = statusId || 'null';
            return this.ui.collapsedGroups[key] || false;
        },

        // === Selection ===
        toggleItem(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx === -1) {
                this.selectedIds.push(id);
            } else {
                this.selectedIds.splice(idx, 1);
            }
            this.updateSelectAllState();
        },

        isSelected(id) {
            return this.selectedIds.includes(id);
        },

        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedIds = this.clients.map(c => c.id);
            } else {
                this.selectedIds = [];
            }
        },

        selectAllVisible() {
            this.selectedIds = this.clients.map(c => c.id);
            this.selectAll = true;
        },

        clearSelection() {
            this.selectedIds = [];
            this.selectAll = false;
        },

        updateSelectAllState() {
            this.selectAll = this.clients.length > 0 &&
                             this.selectedIds.length === this.clients.length;
        },

        // === Status Dropdown Management ===
        toggleStatusDropdown(clientId) {
            this.openStatusDropdown = this.openStatusDropdown === clientId ? null : clientId;
        },

        closeStatusDropdown() {
            this.openStatusDropdown = null;
        },

        isStatusDropdownOpen(clientId) {
            return this.openStatusDropdown === clientId;
        },

        // === Status Updates ===
        async updateClientStatus(client, newStatusId) {
            if (this.savingStatus[client.id]) return;

            const slug = client.slug || client.id;
            this.savingStatus[client.id] = true;

            try {
                const response = await fetch(`/clients/${slug}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status_id: newStatusId })
                });

                const data = await response.json();

                if (data.success) {
                    // Close the dropdown
                    this.openStatusDropdown = null;

                    // Find and update the client in our local data
                    const clientIndex = this.clients.findIndex(c => c.id === client.id);
                    if (clientIndex !== -1) {
                        const newStatus = this.statuses.find(s => s.id === newStatusId);
                        this.clients[clientIndex].status_id = newStatusId;
                        this.clients[clientIndex].status = newStatus ? {
                            id: newStatus.id,
                            name: newStatus.name,
                            slug: newStatus.slug,
                            color_class: newStatus.color_class,
                            color_background: newStatus.color_background,
                            color_text: newStatus.color_text
                        } : null;
                    }

                    // Update status counts
                    this.recalculateStatusCounts(client.status_id, newStatusId);

                    const newStatusName = this.statuses.find(s => s.id === newStatusId)?.name || 'Updated';
                    this.showToast(`Status changed to "${newStatusName}"`, 'success');
                } else {
                    throw new Error(data.message || 'Failed to update status');
                }
            } catch (error) {
                this.showToast('Error updating status', 'error');
            } finally {
                this.savingStatus[client.id] = false;
            }
        },

        recalculateStatusCounts(oldStatusId, newStatusId) {
            if (oldStatusId && this.statusCounts.by_status[oldStatusId]) {
                this.statusCounts.by_status[oldStatusId]--;
            }
            if (!this.statusCounts.by_status[newStatusId]) {
                this.statusCounts.by_status[newStatusId] = 0;
            }
            this.statusCounts.by_status[newStatusId]++;
        },

        getStatusCount(statusId) {
            return this.statusCounts.by_status[statusId] || 0;
        },

        // === Bulk Actions ===
        async bulkUpdateStatus(newStatusId) {
            if (!this.hasSelection) return;

            const confirmed = await this.confirmAction({
                title: 'Update Status',
                message: `Update status for ${this.selectedCount} client(s)?`
            });
            if (!confirmed) return;

            this.loading = true;

            try {
                const response = await fetch('/clients/bulk-update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ids: this.selectedIds,
                        action: 'update_status',
                        status_id: newStatusId
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    // Update local state
                    const newStatus = this.statuses.find(s => s.id === newStatusId);
                    this.clients.forEach(client => {
                        if (this.selectedIds.includes(client.id)) {
                            client.status_id = newStatusId;
                            client.status = newStatus ? {
                                id: newStatus.id,
                                name: newStatus.name,
                                slug: newStatus.slug,
                                color_background: newStatus.color_background,
                                color_text: newStatus.color_text
                            } : null;
                        }
                    });

                    this.showToast(result.message || 'Status updated successfully', 'success');
                    this.clearSelection();

                    // Reload to get fresh status counts
                    await this.loadClients();
                } else {
                    throw new Error(result.message || 'Action failed');
                }
            } catch (error) {
                this.showToast('An error occurred. Please try again.', 'error');
            } finally {
                this.loading = false;
            }
        },

        async bulkExport() {
            if (!this.hasSelection) return;

            const confirmed = await this.confirmAction({
                title: 'Export Clients',
                message: `Export ${this.selectedCount} client(s) to CSV?`
            });
            if (!confirmed) return;

            try {
                const response = await fetch('/clients/bulk-export', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        ids: this.selectedIds,
                        action: 'export'
                    })
                });

                if (response.ok) {
                    const contentDisposition = response.headers.get('Content-Disposition');
                    let filename = 'clients_export.csv';
                    if (contentDisposition) {
                        const match = contentDisposition.match(/filename="?(.+)"?/i);
                        if (match) filename = match[1];
                    }

                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    this.showToast('Export completed successfully', 'success');
                    this.clearSelection();
                } else {
                    throw new Error('Export failed');
                }
            } catch (error) {
                this.showToast('Export failed. Please try again.', 'error');
            }
        },

        async bulkDelete() {
            if (!this.hasSelection) return;

            const confirmed = await this.confirmAction({
                title: 'Delete Clients',
                message: `Are you sure you want to delete ${this.selectedCount} client(s)? This action cannot be undone.`,
                variant: 'destructive'
            });
            if (!confirmed) return;

            this.loading = true;

            try {
                const response = await fetch('/clients/bulk-update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ids: this.selectedIds,
                        action: 'delete'
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    this.showToast(result.message || 'Clients deleted successfully', 'success');
                    this.clearSelection();
                    await this.loadClients();
                } else {
                    throw new Error(result.message || 'Delete failed');
                }
            } catch (error) {
                this.showToast('An error occurred. Please try again.', 'error');
            } finally {
                this.loading = false;
            }
        },

        // === Helpers ===
        confirmAction(options) {
            return new Promise((resolve) => {
                window.dispatchEvent(new CustomEvent('confirm-dialog', {
                    detail: {
                        title: options.title || 'Confirm Action',
                        message: options.message || 'Are you sure?',
                        confirmText: options.confirmText || 'Proceed',
                        variant: options.variant || 'default',
                        onConfirm: () => resolve(true)
                    }
                }));

                // Fallback to native confirm if no dialog handler
                setTimeout(() => {
                    const hasDialog = document.querySelector('[x-data*="confirmDialog"]');
                    if (!hasDialog) {
                        resolve(window.confirm(options.message));
                    }
                }, 100);
            });
        },

        showToast(message, type = 'info') {
            const container = document.getElementById('toast-container') || this.createToastContainer();
            const toast = document.createElement('div');

            const bgColor = type === 'success' ? 'bg-green-600' :
                           type === 'error' ? 'bg-red-600' : 'bg-blue-600';

            toast.className = `px-4 py-3 rounded-lg shadow-lg text-white transform transition-all duration-300 ${bgColor}`;

            // Build toast content safely to prevent XSS
            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-center gap-2';

            // Icon (hardcoded HTML - safe)
            const iconWrapper = document.createElement('span');
            iconWrapper.innerHTML = type === 'success'
                ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                : type === 'error'
                ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
                : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

            // Message text (use textContent to prevent XSS)
            const messageSpan = document.createElement('span');
            messageSpan.textContent = message;

            wrapper.appendChild(iconWrapper);
            wrapper.appendChild(messageSpan);
            toast.appendChild(wrapper);

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        },

        createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
            return container;
        },

        formatCurrency(value, currency = 'RON') {
            return new Intl.NumberFormat('ro-RO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0) + ' ' + (currency || 'RON');
        },

        copyToClipboard(text, event) {
            navigator.clipboard.writeText(text).then(() => {
                const element = event.currentTarget;
                const originalHTML = element.innerHTML;
                element.innerHTML = '<span class="flex items-center gap-1 text-green-600"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!</span>';
                setTimeout(() => {
                    element.innerHTML = originalHTML;
                }, 2000);
            });
        },

        // === Grouped View Helpers ===
        getClientsForStatus(statusId) {
            return this.clients.filter(c => c.status_id === statusId);
        },

        getClientsWithoutStatus() {
            return this.clients.filter(c => !c.status_id);
        }
    };
}

// Export for global access
window.clientsPage = clientsPage;
