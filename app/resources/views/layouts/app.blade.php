<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Critical CSS - Prevents FOUC (must be first) -->
        <style>
            [x-cloak] { display: none !important; }
            .no-fouc { opacity: 0; }
            .no-fouc.ready { opacity: 1; transition: opacity 0.1s ease-in; }
        </style>

        <!-- User/Organization data for JS -->
        @auth
        <script>
            window.userId = {{ auth()->id() }};
            window.organizationId = {{ auth()->user()->organization_id ?? "null" }};
        </script>
        @endauth

        <title>{{ $globalAppSettings['app_name'] ?? config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        @if(isset($globalAppSettings['app_favicon']) && $globalAppSettings['app_favicon'])
            <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $globalAppSettings['app_favicon']) }}">
        @endif

        <!-- Fonts - Inter for modern clean look -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Custom Primary Color CSS -->
        <style>
            :root {
                --primary-color: {{ $globalAppSettings['primary_color'] ?? '#3b82f6' }};
            }

            /* Override primary color classes */
            .bg-primary-600 { background-color: var(--primary-color) !important; }
            .hover\:bg-primary-700:hover { filter: brightness(0.9); background-color: var(--primary-color) !important; }
            .text-primary-600 { color: var(--primary-color) !important; }
            .hover\:text-primary-700:hover { filter: brightness(0.9); color: var(--primary-color) !important; }
            .border-primary-600 { border-color: var(--primary-color) !important; }
            .ring-primary-500 { --tw-ring-color: var(--primary-color) !important; }
            .focus\:ring-primary-500:focus { --tw-ring-color: var(--primary-color) !important; }
        </style>

        <!-- Livewire Styles (must be in head) -->
        @livewireStyles

        <!-- Vite CSS (must be in head to prevent FOUC) -->
        @vite(['resources/css/app.css'])

        <!-- Bulk Selection Component - Must load before Alpine.js evaluates x-data -->
        <script>
        function bulkSelection(options = {}) {
            return {
                selectedIds: [],
                selectAll: false,
                groupSelections: {},
                isLoading: false,
                idAttribute: options.idAttribute || 'data-id',
                rowSelector: options.rowSelector || '[data-selectable]',

                init() {
                    this.$watch('selectAll', value => {
                        if (value) {
                            this.selectAllVisible();
                        } else {
                            if (this.selectedIds.length > 0) {
                                this.clearSelection();
                            }
                        }
                    });
                },

                get selectedCount() {
                    return this.selectedIds.length;
                },

                get hasSelection() {
                    return this.selectedIds.length > 0;
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

                selectAllVisible() {
                    const rows = document.querySelectorAll(this.rowSelector);
                    this.selectedIds = Array.from(rows).map(row => {
                        const id = row.dataset[this.idAttribute.replace('data-', '').replace(/-([a-z])/g, (g) => g[1].toUpperCase())];
                        return parseInt(id);
                    });
                },

                toggleAll() {
                    if (this.selectAll) {
                        this.selectAllVisible();
                    } else {
                        this.selectedIds = [];
                    }
                },

                toggleGroup(groupName) {
                    const groupRows = Array.from(document.querySelectorAll(`[data-group="${groupName}"]`));
                    const groupIds = groupRows.map(row => {
                        const id = row.dataset[this.idAttribute.replace('data-', '').replace(/-([a-z])/g, (g) => g[1].toUpperCase())];
                        return parseInt(id);
                    });

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

                updateSelectAllState() {
                    const rows = document.querySelectorAll(this.rowSelector);
                    const totalRows = rows.length;
                    this.selectAll = totalRows > 0 && this.selectedIds.length === totalRows;
                },

                clearSelection() {
                    this.selectedIds = [];
                    this.selectAll = false;
                },

                async performBulkAction(action, endpoint, options = {}) {
                    if (this.selectedIds.length === 0) {
                        this.showToast('Please select at least one item', 'warning');
                        return;
                    }

                    const confirmMessage = options.confirmMessage || `Are you sure you want to perform this action on ${this.selectedIds.length} item(s)?`;
                    if (!confirm(confirmMessage)) {
                        return;
                    }

                    this.isLoading = true;

                    try {
                        if (action === 'export') {
                            await this.handleExport(endpoint, options);
                            return;
                        }

                        const response = await fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                ids: this.selectedIds,
                                action: action,
                                ...options.data
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.showToast(options.successMessage || data.message || 'Action completed successfully', 'success');
                            this.clearSelection();
                            setTimeout(() => { window.location.reload(); }, 1000);
                        } else {
                            this.showToast(data.message || 'Action failed', 'error');
                        }
                    } catch (error) {
                        this.showToast('An error occurred. Please try again.', 'error');
                    } finally {
                        this.isLoading = false;
                    }
                },

                async handleExport(endpoint, options = {}) {
                    try {
                        const response = await fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                ids: this.selectedIds,
                                action: 'export'
                            })
                        });

                        if (response.ok) {
                            const contentDisposition = response.headers.get('Content-Disposition');
                            let filename = 'export.csv';
                            if (contentDisposition) {
                                const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
                                if (filenameMatch) {
                                    filename = filenameMatch[1];
                                }
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

                            this.showToast(options.successMessage || 'Export completed successfully', 'success');
                            this.clearSelection();
                        } else {
                            const data = await response.json().catch(() => ({ message: 'Export failed' }));
                            this.showToast(data.message || 'Export failed', 'error');
                        }
                    } catch (error) {
                        this.showToast('An error occurred during export. Please try again.', 'error');
                    } finally {
                        this.isLoading = false;
                    }
                },

                showToast(message, type = 'info') {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { message, type }
                    }));
                }
            };
        }
        window.bulkSelection = bulkSelection;

        // Category Combobox Component - Searchable hierarchical dropdown
        window.categoryComboboxConfig = {
            storeUrl: '{{ route("settings.nomenclature.store") }}'
        };

        window.categoryCombobox = function(config = {}) {
            return {
                categories: config.categories || [],
                name: config.name || 'category_id',
                allowCreate: config.allowCreate !== false,
                allowEmpty: config.allowEmpty !== false,
                placeholder: config.placeholder || 'Select category...',

                selectedId: config.selected || null,
                selectedLabel: '',
                searchQuery: '',
                open: false,
                highlightedIndex: -1,

                showCreateForm: false,
                createMode: 'category',
                newCategoryName: '',
                selectedParentId: null,
                saving: false,

                init() {
                    // Convert selectedId to number if it's a string
                    if (this.selectedId && typeof this.selectedId === 'string') {
                        this.selectedId = parseInt(this.selectedId, 10);
                    }
                    // Use nextTick to ensure flatList is computed
                    this.$nextTick(() => {
                        this.updateSelectedLabel();
                    });

                    // Reposition on scroll/resize while open
                    window.addEventListener('scroll', () => {
                        if (this.open) this.positionDropdown();
                    }, true);
                    window.addEventListener('resize', () => {
                        if (this.open) this.positionDropdown();
                    });
                },

                updateSelectedLabel() {
                    if (this.selectedId) {
                        // Use == for loose comparison to handle string/number mismatch
                        const item = this.flatList.find(i => String(i.id) === String(this.selectedId));
                        if (item) {
                            this.selectedLabel = item.isParent
                                ? item.label
                                : `${item.parentLabel || this.getParentLabel(item.parentId)} > ${item.label}`;
                        }
                    } else {
                        this.selectedLabel = '';
                    }
                },

                get flatList() {
                    const flat = [];
                    (this.categories || []).forEach(cat => {
                        flat.push({
                            id: cat.id,
                            label: cat.label,
                            value: cat.value,
                            isParent: true,
                            depth: 0,
                            hasChildren: cat.children && cat.children.length > 0
                        });
                        if (cat.children) {
                            cat.children.forEach(child => {
                                flat.push({
                                    id: child.id,
                                    label: child.label,
                                    value: child.value,
                                    isParent: false,
                                    depth: 1,
                                    parentId: cat.id,
                                    parentLabel: cat.label
                                });
                            });
                        }
                    });
                    return flat;
                },

                get filteredList() {
                    if (!this.searchQuery.trim()) return this.flatList;
                    const q = this.searchQuery.toLowerCase().trim();
                    return this.flatList.filter(item => item.label.toLowerCase().includes(q));
                },

                getParentLabel(parentId) {
                    const parent = this.flatList.find(i => i.id == parentId);
                    return parent ? parent.label : '';
                },

                toggle() {
                    this.open ? this.close() : this.openDropdown();
                },

                openDropdown() {
                    this.open = true;
                    this.searchQuery = '';
                    this.highlightedIndex = this.selectedId
                        ? this.filteredList.findIndex(i => i.id == this.selectedId)
                        : 0;
                    this.$nextTick(() => {
                        const input = this.$refs.searchInput;
                        if (input) input.focus();
                        this.positionDropdown();
                    });
                },

                positionDropdown() {
                    const trigger = this.$refs.trigger;
                    const dropdown = this.$refs.dropdown;
                    if (!trigger || !dropdown) return;

                    const rect = trigger.getBoundingClientRect();
                    const viewportHeight = window.innerHeight;
                    const viewportWidth = window.innerWidth;

                    // Vertical positioning - check if dropdown fits below
                    const spaceBelow = viewportHeight - rect.bottom;
                    const spaceAbove = rect.top;
                    const dropdownHeight = dropdown.offsetHeight || 350;

                    let top;
                    if (spaceBelow >= dropdownHeight || spaceBelow >= spaceAbove) {
                        // Position below
                        top = rect.bottom + 4;
                    } else {
                        // Position above
                        top = rect.top - dropdownHeight - 4;
                    }

                    // Horizontal positioning - align to trigger left
                    let left = rect.left;

                    // Ensure dropdown stays within viewport horizontally
                    const dropdownWidth = dropdown.offsetWidth || 280;
                    if (left + dropdownWidth > viewportWidth - 8) {
                        left = viewportWidth - dropdownWidth - 8;
                    }
                    if (left < 8) {
                        left = 8;
                    }

                    dropdown.style.top = top + 'px';
                    dropdown.style.left = left + 'px';
                },

                close() {
                    this.open = false;
                    this.searchQuery = '';
                    this.showCreateForm = false;
                    this.highlightedIndex = -1;
                },

                select(item) {
                    this.selectedId = item.id;
                    this.selectedLabel = item.isParent
                        ? item.label
                        : `${this.getParentLabel(item.parentId)} > ${item.label}`;
                    this.close();
                    this.$dispatch('category-selected', { id: item.id, label: item.label });
                },

                clear() {
                    this.selectedId = null;
                    this.selectedLabel = '';
                    this.$dispatch('category-selected', { id: null, label: '' });
                },

                onSearch(query) {
                    this.searchQuery = query;
                    this.highlightedIndex = 0;
                },

                onKeydown(e) {
                    if (!this.open) {
                        if (['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(e.key)) {
                            e.preventDefault();
                            this.openDropdown();
                        }
                        return;
                    }

                    switch(e.key) {
                        case 'ArrowDown':
                            e.preventDefault();
                            this.highlightedIndex = Math.min(this.highlightedIndex + 1, this.filteredList.length - 1);
                            this.scrollToHighlighted();
                            break;
                        case 'ArrowUp':
                            e.preventDefault();
                            this.highlightedIndex = Math.max(this.highlightedIndex - 1, 0);
                            this.scrollToHighlighted();
                            break;
                        case 'Enter':
                            e.preventDefault();
                            if (this.showCreateForm) {
                                this.createCategory();
                            } else if (this.highlightedIndex >= 0 && this.filteredList[this.highlightedIndex]) {
                                this.select(this.filteredList[this.highlightedIndex]);
                            }
                            break;
                        case 'Escape':
                            e.preventDefault();
                            this.showCreateForm ? (this.showCreateForm = false) : this.close();
                            break;
                        case 'Tab':
                            this.close();
                            break;
                    }
                },

                scrollToHighlighted() {
                    this.$nextTick(() => {
                        const list = this.$refs.optionsList;
                        const highlighted = list?.querySelector('[data-highlighted="true"]');
                        if (highlighted && list) {
                            const listRect = list.getBoundingClientRect();
                            const itemRect = highlighted.getBoundingClientRect();
                            if (itemRect.bottom > listRect.bottom) {
                                list.scrollTop += itemRect.bottom - listRect.bottom + 4;
                            } else if (itemRect.top < listRect.top) {
                                list.scrollTop -= listRect.top - itemRect.top + 4;
                            }
                        }
                    });
                },

                showCreate(mode = 'category') {
                    this.createMode = mode;
                    this.showCreateForm = true;
                    this.newCategoryName = '';
                    if (mode === 'subcategory' && this.highlightedIndex >= 0) {
                        const item = this.filteredList[this.highlightedIndex];
                        if (item) this.selectedParentId = item.isParent ? item.id : item.parentId;
                    } else {
                        this.selectedParentId = null;
                    }
                    this.$nextTick(() => {
                        const input = this.$refs.newCategoryInput;
                        if (input) input.focus();
                    });
                },

                async createCategory() {
                    const name = this.newCategoryName.trim();
                    if (!name || name.length < 2 || this.saving) return;

                    this.saving = true;
                    try {
                        const response = await fetch(window.categoryComboboxConfig?.storeUrl || '/settings/nomenclature', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                category: 'expense_categories',
                                label: name,
                                parent_id: this.createMode === 'subcategory' ? this.selectedParentId : null
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            const newItem = {
                                id: data.setting.id,
                                label: data.setting.label,
                                value: data.setting.value,
                                isParent: !data.setting.parent_id,
                                parentId: data.setting.parent_id,
                                depth: data.setting.parent_id ? 1 : 0
                            };

                            if (newItem.isParent) {
                                this.categories.push({ id: newItem.id, label: newItem.label, value: newItem.value, children: [] });
                            } else {
                                const parent = this.categories.find(c => c.id == newItem.parentId);
                                if (parent) {
                                    if (!parent.children) parent.children = [];
                                    parent.children.push({ id: newItem.id, label: newItem.label, value: newItem.value });
                                }
                            }

                            this.select(newItem);
                            this.showCreateForm = false;
                            this.newCategoryName = '';
                            this.selectedParentId = null;

                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: `Category "${name}" created`, type: 'success' } }));
                            window.dispatchEvent(new CustomEvent('category-created', { detail: { category: data.setting } }));
                        } else {
                            alert(data.message || 'Failed to create category');
                        }
                    } catch (error) {
                        alert('Failed to create category. Please try again.');
                    } finally {
                        this.saving = false;
                    }
                },

                onCategoryCreated(event) {
                    const newCat = event.detail.category;
                    if (!newCat) return;
                    const exists = this.flatList.some(i => i.id == newCat.id);
                    if (exists) return;

                    if (!newCat.parent_id) {
                        this.categories.push({ id: newCat.id, label: newCat.label, value: newCat.value, children: [] });
                    } else {
                        const parent = this.categories.find(c => c.id == newCat.parent_id);
                        if (parent) {
                            if (!parent.children) parent.children = [];
                            parent.children.push({ id: newCat.id, label: newCat.label, value: newCat.value });
                        }
                    }
                },

                isHighlighted(index) { return this.highlightedIndex === index; },
                isSelected(item) { return this.selectedId == item.id; }
            };
        };
        </script>

        <!-- Sidebar responsive styles -->
        <style>
            /* Mobile: closed by default */
            #sidebar-wrapper {
                transform: translateX(-100%);
                transition: transform 300ms ease-in-out;
            }
            #sidebar-wrapper.sidebar-open {
                transform: translateX(0);
            }
            /* Desktop: open by default, use sidebar-closed to hide */
            @media (min-width: 768px) {
                #sidebar-wrapper {
                    transform: translateX(0);
                }
                #sidebar-wrapper.sidebar-closed {
                    transform: translateX(-100%);
                }
                #content-wrapper {
                    margin-left: 16rem;
                    transition: margin-left 300ms ease-in-out;
                }
                #content-wrapper.sidebar-closed {
                    margin-left: 0;
                }
            }
        </style>

        <!-- Fix for cards with colored headers - ensure border radius clips properly -->
        <style>
            /* Cards with rounded corners and internal colored backgrounds need overflow:hidden */
            .bg-white.rounded-lg,
            .bg-white.rounded-xl {
                overflow: hidden;
            }
            /* Card utility classes defined in resources/css/app.css via @layer components */
        </style>

        <style>
            * {
                scroll-behavior: smooth;
            }

            /* Custom scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }

            ::-webkit-scrollbar-track {
                background: #f1f5f9;
            }

            ::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }
        </style>

        <!-- Trix WYSIWYG Editor (deferred — non-render-blocking) -->
        <link rel="stylesheet" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
        <script defer src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>

        <!-- TinyMCE Rich Text Editor (deferred — non-render-blocking) -->
        <script defer src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>

        <style>
            /* TinyMCE Customizations */
            .tox-tinymce {
                border: 1px solid #e2e8f0 !important;
                border-radius: 0.5rem !important;
                box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important;
            }
            .tox-tinymce:focus-within {
                border-color: #0f172a !important;
                box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.1) !important;
            }
            .tox-editor-header {
                border-bottom: 1px solid #e2e8f0 !important;
                box-shadow: none !important;
            }
            .tox:not(.tox-tinymce-inline) .tox-editor-header {
                padding: 0 !important;
            }
            .tox-toolbar__primary {
                background: #f8fafc !important;
                border-radius: 0.5rem 0.5rem 0 0 !important;
            }
            .tox-toolbar__group {
                border: none !important;
                padding: 0 4px !important;
            }
            .tox-toolbar__group:not(:last-of-type) {
                border-right: 1px solid #e2e8f0 !important;
            }
            .tox-tbtn {
                border-radius: 0.25rem !important;
                margin: 2px !important;
            }
            .tox-tbtn:hover {
                background: #e2e8f0 !important;
            }
            .tox-tbtn--enabled,
            .tox-tbtn--enabled:hover {
                background: #e2e8f0 !important;
            }
            .tox-tbtn--select {
                border-radius: 0.25rem !important;
            }
            .tox-split-button {
                border-radius: 0.25rem !important;
                overflow: hidden;
            }
            .tox-split-button:hover {
                box-shadow: none !important;
            }
            .tox-statusbar {
                border-top: 1px solid #e2e8f0 !important;
                background: #f8fafc !important;
                border-radius: 0 0 0.5rem 0.5rem !important;
                padding: 8px 12px !important;
            }
            .tox-statusbar__text-container {
                font-size: 12px !important;
                color: #64748b !important;
            }
            .tox-edit-area__iframe {
                background: #fff !important;
            }
            /* Dropdown menus */
            .tox-menu {
                border: 1px solid #e2e8f0 !important;
                border-radius: 0.375rem !important;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
            }
            .tox-collection__item--active {
                background: #f1f5f9 !important;
            }
            .tox-collection__item-label {
                font-size: 13px !important;
            }
            /* Dialog styling */
            .tox-dialog {
                border-radius: 0.5rem !important;
                border: 1px solid #e2e8f0 !important;
            }
            .tox-dialog__header {
                background: #f8fafc !important;
                border-bottom: 1px solid #e2e8f0 !important;
            }
            .tox-dialog__footer {
                background: #f8fafc !important;
                border-top: 1px solid #e2e8f0 !important;
            }
            .tox-button {
                border-radius: 0.375rem !important;
            }
            .tox-button--secondary {
                background: #fff !important;
                border: 1px solid #e2e8f0 !important;
            }
            .tox-textfield, .tox-selectfield select {
                border-radius: 0.375rem !important;
                border: 1px solid #e2e8f0 !important;
            }
            .tox-textfield:focus, .tox-selectfield select:focus {
                border-color: #0f172a !important;
                box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.1) !important;
            }
        </style>

        <style>
            /* Trix Customizations */
            trix-toolbar .trix-button-group--file-tools { display: none; }
            trix-toolbar [data-trix-action="attachFiles"] { display: none; }
            trix-editor {
                min-height: 120px;
                border: 1px solid #e2e8f0;
                border-radius: 0.375rem;
                padding: 0.75rem;
            }
            trix-editor:focus {
                outline: none;
                border-color: #0f172a;
            }
            trix-toolbar {
                border: 1px solid #e2e8f0;
                border-bottom: none;
                border-radius: 0.375rem 0.375rem 0 0;
                background: #f8fafc;
                padding: 0.5rem;
            }
            trix-editor {
                border-radius: 0 0 0.375rem 0.375rem;
            }
            trix-toolbar .trix-button {
                border-radius: 0.25rem;
            }
            trix-toolbar .trix-button.trix-active {
                background: #e2e8f0;
            }
        </style>

        <style>
            /* Contract content display styles */
            .contract-content {
                font-size: 14px !important;
                line-height: 1.5 !important;
                color: #1e293b;
            }
            .contract-content h1 {
                font-size: 1.875rem !important;
                font-weight: 700 !important;
                line-height: 1.3 !important;
                margin-top: 1.5rem !important;
                margin-bottom: 0.25rem !important;
                color: #1e293b;
            }
            .contract-content h2 {
                font-size: 1.5rem !important;
                font-weight: 600 !important;
                line-height: 1.35 !important;
                margin-top: 1.25rem !important;
                margin-bottom: 0.2rem !important;
                color: #1e293b;
            }
            .contract-content h3 {
                font-size: 1.25rem !important;
                font-weight: 600 !important;
                line-height: 1.4 !important;
                margin-top: 1rem !important;
                margin-bottom: 0.15rem !important;
                color: #334155;
            }
            .contract-content p {
                margin-bottom: 0.5rem !important;
                line-height: 1.5 !important;
            }
            .contract-content ul,
            .contract-content ol {
                margin-bottom: 0.75rem !important;
                padding-left: 1.5rem !important;
            }
            .contract-content li {
                margin-bottom: 0.1rem !important;
                line-height: 1.4 !important;
            }
            .contract-content li:last-child {
                margin-bottom: 0.5rem !important;
            }
            .contract-content blockquote {
                border-left: 4px solid #3b82f6 !important;
                padding-left: 1rem !important;
                margin: 0.75rem 0 !important;
                color: #475569;
                font-style: italic;
            }
            .contract-content hr {
                margin: 1rem 0 !important;
                border-color: #e2e8f0;
            }
            .contract-content > *:first-child {
                margin-top: 0 !important;
            }
            .contract-content p:empty::before {
                content: '\00a0';
            }
            .contract-content .ql-align-center { text-align: center; }
            .contract-content .ql-align-right { text-align: right; }
            .contract-content .ql-align-justify { text-align: justify; }
            .contract-content .ql-indent-1 { padding-left: 3em; }
            .contract-content .ql-indent-2 { padding-left: 6em; }
            .contract-content .ql-indent-3 { padding-left: 9em; }
            .contract-content .ql-size-small { font-size: 0.75em; }
            .contract-content .ql-size-large { font-size: 1.5em; }
            .contract-content .ql-size-huge { font-size: 2.5em; }
        </style>
        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-slate-50" x-data="{
        sidebarOpen: window.innerWidth >= 768 ? localStorage.getItem('sidebarOpen') !== 'false' : false,
        touchStartX: 0,
        touchCurrentX: 0,
        isDragging: false,
        init() {
            this.$watch('sidebarOpen', (value) => {
                if (window.innerWidth >= 768) {
                    localStorage.setItem('sidebarOpen', value);
                }
            });
        }
    }">
        <!-- Skip to main content link for accessibility -->
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-slate-900 focus:text-white focus:rounded-md focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
            Skip to main content
        </a>

        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar - toggleable on mobile, always visible on desktop -->
            <div
                id="sidebar-wrapper"
                class="fixed inset-y-0 left-0 z-50"
                :class="sidebarOpen ? 'sidebar-open' : 'sidebar-closed'"
                @touchstart="if (window.innerWidth < 768) {
                    touchStartX = $event.touches[0].clientX;
                    isDragging = true;
                }"
                @touchmove="if (isDragging && window.innerWidth < 768) {
                    touchCurrentX = $event.touches[0].clientX;
                    const diff = touchCurrentX - touchStartX;
                    if (diff < 0) {
                        $el.style.transition = 'none';
                        $el.style.transform = 'translateX(' + diff + 'px)';
                    }
                }"
                @touchend="if (isDragging && window.innerWidth < 768) {
                    const diff = touchCurrentX - touchStartX;
                    isDragging = false;
                    $el.style.transition = 'transform 150ms ease-out';
                    $el.style.transform = '';
                    if (diff < -80) {
                        setTimeout(function() { sidebarOpen = false; }, 150);
                    }
                    touchStartX = 0;
                    touchCurrentX = 0;
                }">
                <x-sidebar class="h-full" />
            </div>

            <!-- Overlay for mobile -->
            <div
                x-cloak
                x-show="sidebarOpen"
                x-transition:enter="transition-opacity ease-linear duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="sidebarOpen = false"
                class="fixed inset-0 bg-slate-900/50 z-40 md:hidden"
            ></div>

            <!-- Main Content Area -->
            <div id="content-wrapper" class="flex-1 flex flex-col overflow-hidden" :class="!sidebarOpen && 'sidebar-closed'">
                <!-- Global Header -->
                <header class="bg-white border-b border-slate-200 sticky top-0 z-30 flex-shrink-0 h-16">
                    <div class="flex items-center justify-between h-full px-4 md:px-6 gap-4">
                        <!-- Left: Toggle + Breadcrumb/Title -->
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <!-- Sidebar Toggle -->
                            <button
                                @click="sidebarOpen = !sidebarOpen"
                                class="p-2 rounded-lg hover:bg-slate-100 transition-colors flex-shrink-0"
                                aria-label="Toggle sidebar"
                            >
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>

                            <!-- Page Title + Breadcrumb -->
                            <div class="min-w-0 flex-1">
                                @isset($pageTitle)
                                    <div class="flex flex-col">
                                        <h1 class="text-sm font-bold text-slate-900 truncate uppercase tracking-wide">{{ $pageTitle }}</h1>
                                        @if(!isset($hideBreadcrumb) || !$hideBreadcrumb)
                                            @isset($breadcrumb)
                                                <div class="mt-0.5">
                                                    {{ $breadcrumb }}
                                                </div>
                                            @else
                                                <div class="mt-0.5">
                                                    <x-breadcrumb />
                                                </div>
                                            @endisset
                                        @endif
                                    </div>
                                @else
                                    @isset($breadcrumb)
                                        {{ $breadcrumb }}
                                    @else
                                        @isset($header)
                                            {{ $header }}
                                        @else
                                            <x-breadcrumb />
                                        @endisset
                                    @endisset
                                @endisset
                            </div>
                        </div>

                        <!-- Right: Action Buttons -->
                        @isset($headerActions)
                            <div class="flex-shrink-0">
                                {{ $headerActions }}
                            </div>
                        @endisset
                    </div>
                </header>

                <!-- Page Content -->
                <main id="main-content" class="flex-1 overflow-y-auto bg-slate-50" role="main" tabindex="-1">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Toast Notifications -->
        <x-toast />

        <!-- Global Confirm Dialog -->
        <x-ui.confirm-dialog />

        <!-- Command Palette (Cmd+K) -->
        <x-command-palette />

        <!-- Load Vite JS bundle (registers Alpine components via alpine:init) -->
        @vite(['resources/js/app.js'])

        <!-- Livewire Scripts (includes Alpine.js) - must load AFTER Vite so Alpine is available -->
        @livewireScripts

        <!-- File Uploader Component for financial forms -->
        <script>
        window.fileUploader = function(existingFilesData) {
            return {
                existingFiles: existingFilesData || [],
                newFiles: [],
                filesToDelete: [],

                handleFileSelect(event) {
                    const files = Array.from(event.target.files);
                    this.addFiles(files);
                },

                handleDrop(event) {
                    const files = Array.from(event.dataTransfer.files);
                    this.addFiles(files);
                    const input = document.getElementById('file-upload');
                    const dataTransfer = new DataTransfer();
                    files.forEach(file => dataTransfer.items.add(file));
                    input.files = dataTransfer.files;
                },

                addFiles(files) {
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    const allowedTypes = [
                        'application/pdf', 'image/jpeg', 'image/jpg', 'image/png',
                        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/zip', 'application/x-rar-compressed'
                    ];

                    files.forEach(file => {
                        if (file.size > maxSize) {
                            alert(`${file.name} is too large. Maximum size is 10MB.`);
                            return;
                        }
                        if (!allowedTypes.includes(file.type) && !file.name.match(/\.(pdf|jpe?g|png|docx?|xlsx?|zip|rar)$/i)) {
                            alert(`${file.name} has an unsupported file type.`);
                            return;
                        }
                        this.newFiles.push(file);
                    });
                },

                removeNewFile(index) {
                    this.newFiles.splice(index, 1);
                    const input = document.getElementById('file-upload');
                    const dataTransfer = new DataTransfer();
                    this.newFiles.forEach(file => dataTransfer.items.add(file));
                    input.files = dataTransfer.files;
                },

                removeExistingFile(fileId) {
                    if (confirm('Are you sure you want to delete this file?')) {
                        this.existingFiles = this.existingFiles.filter(f => f.id !== fileId);
                        this.filesToDelete.push(fileId);
                    }
                },

                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
                },

                getFileIcon(filename) {
                    const ext = filename.split('.').pop().toLowerCase();
                    const icons = {
                        'pdf': '📄',
                        'doc': '📝',
                        'docx': '📝',
                        'xls': '📊',
                        'xlsx': '📊',
                        'jpg': '🖼️',
                        'jpeg': '🖼️',
                        'png': '🖼️',
                        'zip': '🗜️',
                        'rar': '🗜️'
                    };
                    return icons[ext] || '📎';
                }
            };
        };
        </script>

        <!-- Clients Page Component is loaded via Vite from resources/js/clients-page.js -->


        <!-- Real-time Notification Toast -->
        <div id="notification-toast" 
             class="fixed bottom-4 right-4 z-50 max-w-sm transform transition-all duration-300 translate-y-full opacity-0"
             style="display: none;">
            <div class="bg-white rounded-lg shadow-xl border border-slate-200 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <div id="toast-icon" class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center">
                            <!-- Icon inserted by JS -->
                        </div>
                        <div class="flex-1 min-w-0">
                            <p id="toast-title" class="text-sm font-semibold text-slate-900"></p>
                            <p id="toast-message" class="text-sm text-slate-600 mt-1"></p>
                            <a id="toast-link" href="#" class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block">{{ __("View Details") }} →</a>
                        </div>
                        <button onclick="hideNotificationToast()" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Real-time notification listener
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof window.Echo !== "undefined" && window.organizationId) {
                window.Echo.private("organization." + window.organizationId)
                    .listen(".offer.accepted", (e) => {
                        showNotificationToast("success", e.message, e.client_name + " - " + e.total, "/offers/" + e.offer_id);
                        playNotificationSound();
                    })
                    .listen(".offer.rejected", (e) => {
                        showNotificationToast("warning", e.message, e.client_name + (e.rejection_reason ? ": " + e.rejection_reason : ""), "/offers/" + e.offer_id);
                        playNotificationSound();
                    });
            }
        });

        function showNotificationToast(type, title, message, link) {
            const toast = document.getElementById("notification-toast");
            const iconEl = document.getElementById("toast-icon");
            const titleEl = document.getElementById("toast-title");
            const messageEl = document.getElementById("toast-message");
            const linkEl = document.getElementById("toast-link");

            titleEl.textContent = title;
            messageEl.textContent = message;
            linkEl.href = link;

            if (type === "success") {
                iconEl.className = "flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center bg-green-100";
                iconEl.innerHTML = "<svg class=\"w-5 h-5 text-green-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M5 13l4 4L19 7\"/></svg>";
            } else {
                iconEl.className = "flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center bg-amber-100";
                iconEl.innerHTML = "<svg class=\"w-5 h-5 text-amber-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z\"/></svg>";
            }

            toast.style.display = "block";
            setTimeout(() => {
                toast.classList.remove("translate-y-full", "opacity-0");
            }, 10);

            // Auto-hide after 10 seconds
            setTimeout(hideNotificationToast, 10000);
        }

        function hideNotificationToast() {
            const toast = document.getElementById("notification-toast");
            toast.classList.add("translate-y-full", "opacity-0");
            setTimeout(() => {
                toast.style.display = "none";
            }, 300);
        }

        function playNotificationSound() {
            // Create a simple beep sound
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                oscillator.frequency.value = 800;
                oscillator.type = "sine";
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            } catch (e) {
                // Could not play notification sound
            }
        }

        // Push Notification Registration
        async function initPushNotifications() {
            if (!("serviceWorker" in navigator) || !("PushManager" in window)) {
                // Push notifications not supported in this browser - silent return
                return;
            }

            try {
                // Get VAPID public key first - if not configured, skip silently
                const response = await fetch("/push/vapid-key");
                const { publicKey } = await response.json();

                // Validate VAPID key - must be a non-empty base64url string (65 bytes when decoded)
                if (!publicKey || typeof publicKey !== "string" || publicKey.length < 50) {
                    // VAPID key not configured - push notifications disabled, skip silently
                    return;
                }

                const registration = await navigator.serviceWorker.register("/sw.js");

                // Check if already subscribed
                const subscription = await registration.pushManager.getSubscription();
                if (subscription) {
                    return;
                }

                // Request permission
                const permission = await Notification.requestPermission();
                if (permission !== "granted") {
                    return;
                }

                // Subscribe to push
                const newSubscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(publicKey)
                });

                // Send subscription to server
                await fetch("/push/subscribe", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content
                    },
                    body: JSON.stringify(newSubscription.toJSON())
                });
            } catch (error) {
                // Silently ignore push notification setup errors
                // Common causes: VAPID key not configured, service worker issues, etc.
            }
        }

        function urlBase64ToUint8Array(base64String) {
            const padding = "=".repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        // Initialize push notifications after page load
        if (window.userId) {
            document.addEventListener("DOMContentLoaded", initPushNotifications);
        }
        </script>

        <!-- Page-specific Scripts -->
        @stack('scripts')

        <!-- Alpine.js is bundled with Livewire v3, no CDN needed -->
    </body>
</html>
