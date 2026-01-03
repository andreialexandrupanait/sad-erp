<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $globalAppSettings['app_name'] ?? config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        @if(isset($globalAppSettings['app_favicon']) && $globalAppSettings['app_favicon'])
            <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $globalAppSettings['app_favicon']) }}">
        @endif

        <!-- Fonts - Inter for modern clean look -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS CDN with custom config -->
        <script src="https://cdn.tailwindcss.com"></script>

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
                        console.error('Bulk action error:', error);
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
                        console.error('Export error:', error);
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
                        } else {
                            // Debug: log if item not found
                            console.log('Category not found for ID:', this.selectedId, 'Available:', this.flatList.map(i => i.id));
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
                        console.error('Error creating category:', error);
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

        <!-- Alpine.js x-cloak - Hide elements until Alpine is ready -->
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        <!-- Fix for cards with colored headers - ensure border radius clips properly -->
        <style>
            /* Cards with rounded corners and internal colored backgrounds need overflow:hidden */
            .bg-white.rounded-lg,
            .bg-white.rounded-xl {
                overflow: hidden;
            }

            /* Card utility classes */
            .card {
                background: white;
                border-radius: 0.75rem;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
                border: 1px solid #e2e8f0;
                overflow: hidden;
            }

            .card-header {
                padding: 1rem 1.5rem;
                border-bottom: 1px solid #e2e8f0;
                background-color: #f8fafc;
            }

            .card-body {
                padding: 1.5rem;
            }
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

        <!-- Trix WYSIWYG Editor -->
        <link rel="stylesheet" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
        <script src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>

        <!-- Quill.js Rich Text Editor (for Contract Builder) -->
        <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
        <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

        <!-- TinyMCE Rich Text Editor (for Notes) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>

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
                ring: 2px;
                ring-color: #0f172a;
                ring-offset: 2px;
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

            /* Quill Editor Customizations */
            .ql-editor {
                min-height: 400px;
                font-size: 14px;
                line-height: 1.6;
            }
            .ql-container {
                font-family: 'Inter', sans-serif;
            }
            .ql-toolbar.ql-snow {
                border: 1px solid #e2e8f0;
                border-radius: 0.5rem 0.5rem 0 0;
                background: #f8fafc;
            }
            .ql-container.ql-snow {
                border: 1px solid #e2e8f0;
                border-top: none;
                border-radius: 0 0 0.5rem 0.5rem;
            }
            .ql-editor:focus {
                outline: none;
            }
            .ql-snow .ql-picker.ql-header {
                width: 110px;
            }
            /* Variable placeholder styling */
            .ql-editor .variable-placeholder {
                background-color: #dbeafe;
                border: 1px solid #3b82f6;
                border-radius: 4px;
                padding: 2px 6px;
                font-family: monospace;
                font-size: 12px;
                color: #1e40af;
                white-space: nowrap;
            }
            .ql-editor .services-block {
                background-color: #fef3c7;
                border: 2px dashed #f59e0b;
                border-radius: 8px;
                padding: 16px;
                margin: 16px 0;
                text-align: center;
                color: #92400e;
                font-weight: 500;
            }
            /* Custom variable button */
            .ql-variable {
                width: auto !important;
                padding: 0 8px !important;
            }
            .ql-variable::before {
                content: '{{}}';
                font-family: monospace;
                font-size: 12px;
            }

            /* Shared typography for Editor and Preview (no duplication) */
            /* Using !important to override Quill's default CDN styles */
            .ql-editor,
            .contract-content {
                font-size: 14px !important;
                line-height: 1.5 !important;
                color: #1e293b;
            }
            .ql-editor h1,
            .contract-content h1 {
                font-size: 1.875rem !important;
                font-weight: 700 !important;
                line-height: 1.3 !important;
                margin-top: 1.5rem !important;
                margin-bottom: 0.25rem !important;
                color: #1e293b;
            }
            .ql-editor h2,
            .contract-content h2 {
                font-size: 1.5rem !important;
                font-weight: 600 !important;
                line-height: 1.35 !important;
                margin-top: 1.25rem !important;
                margin-bottom: 0.2rem !important;
                color: #1e293b;
            }
            .ql-editor h3,
            .contract-content h3 {
                font-size: 1.25rem !important;
                font-weight: 600 !important;
                line-height: 1.4 !important;
                margin-top: 1rem !important;
                margin-bottom: 0.15rem !important;
                color: #334155;
            }
            .ql-editor p,
            .contract-content p {
                margin-bottom: 0.5rem !important;
                line-height: 1.5 !important;
            }
            .ql-editor ul,
            .ql-editor ol,
            .contract-content ul,
            .contract-content ol {
                margin-bottom: 0.75rem !important;
                padding-left: 1.5rem !important;
            }
            .ql-editor li,
            .contract-content li {
                margin-bottom: 0.1rem !important;
                line-height: 1.4 !important;
            }
            .ql-editor li:last-child,
            .contract-content li:last-child {
                margin-bottom: 0.5rem !important;
            }
            .ql-editor blockquote,
            .contract-content blockquote {
                border-left: 4px solid #3b82f6 !important;
                padding-left: 1rem !important;
                margin: 0.75rem 0 !important;
                color: #475569;
                font-style: italic;
            }
            .ql-editor hr,
            .contract-content hr {
                margin: 1rem 0 !important;
                border-color: #e2e8f0;
            }
            /* First element should not have top margin */
            .ql-editor > *:first-child,
            .contract-content > *:first-child {
                margin-top: 0 !important;
            }
            /* Preserve empty paragraphs for spacing */
            .ql-editor p:empty::before,
            .contract-content p:empty::before {
                content: '\00a0';
            }
            /* Quill alignment classes */
            .ql-editor .ql-align-center,
            .contract-content .ql-align-center {
                text-align: center;
            }
            .ql-editor .ql-align-right,
            .contract-content .ql-align-right {
                text-align: right;
            }
            .ql-editor .ql-align-justify,
            .contract-content .ql-align-justify {
                text-align: justify;
            }
            /* Quill indent classes */
            .ql-editor .ql-indent-1,
            .contract-content .ql-indent-1 {
                padding-left: 3em;
            }
            .ql-editor .ql-indent-2,
            .contract-content .ql-indent-2 {
                padding-left: 6em;
            }
            .ql-editor .ql-indent-3,
            .contract-content .ql-indent-3 {
                padding-left: 9em;
            }
            /* Quill size classes */
            .ql-editor .ql-size-small,
            .contract-content .ql-size-small {
                font-size: 0.75em;
            }
            .ql-editor .ql-size-large,
            .contract-content .ql-size-large {
                font-size: 1.5em;
            }
            .ql-editor .ql-size-huge,
            .contract-content .ql-size-huge {
                font-size: 2.5em;
            }
            /* Sticky toolbar for contract builder */
            .ql-toolbar.ql-snow {
                position: sticky;
                top: 0;
                z-index: 10;
                background: white;
                border-top-left-radius: 0.5rem;
                border-top-right-radius: 0.5rem;
            }
        </style>
        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-slate-50" x-data="{
        sidebarOpen: true,
        touchStartX: 0,
        touchCurrentX: 0,
        isDragging: false
    }">
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar - toggleable on mobile, always visible on desktop -->
            <x-sidebar
                class="md:flex"
                x-show="sidebarOpen"
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
                }"
                x-transition:enter="transition-transform ease-out duration-200 md:transition-none"
                x-transition:enter-start="-translate-x-full md:translate-x-0"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition-transform ease-in duration-150 md:transition-none"
                x-transition:leave-start="translate-x-0 md:translate-x-0"
                x-transition:leave-end="-translate-x-full md:translate-x-0" />

            <!-- Overlay for mobile -->
            <div
                x-show="sidebarOpen"
                @click="sidebarOpen = false"
                x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/50 z-40 md:hidden"
                style="display: none;"
            ></div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden">
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
                <main class="flex-1 overflow-y-auto bg-slate-50">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Toast Notifications -->
        <x-toast />

        <!-- Global Confirm Dialog -->
        <x-ui.confirm-dialog />

        <!-- Livewire Scripts (MUST load before Alpine.js) -->
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

        <!-- Clients Page Component -->
        <script>
        function clientsPage(initialData = {}) {
            return {
                filters: {
                    status: initialData.filters?.status || [],
                    q: initialData.filters?.q || '',
                    sort: initialData.filters?.sort || 'name:asc',
                    page: initialData.filters?.page || 1
                },
                ui: { viewMode: 'table', grouped: false, perPage: 25, collapsedGroups: {} },
                clients: initialData.initialClients || [],
                statuses: initialData.statuses || [],
                statusCounts: initialData.initialStatusCounts || { total: 0, by_status: {} },
                pagination: initialData.initialPagination || { total: 0, per_page: 25, current_page: 1, last_page: 1, from: 0, to: 0 },
                loading: false,
                initialLoad: false,
                selectedIds: [],
                selectAll: false,
                savingStatus: {},

                get selectedCount() { return this.selectedIds.length; },
                get hasSelection() { return this.selectedIds.length > 0; },
                get sortColumn() { return this.filters.sort.split(':')[0]; },
                get sortDirection() { return this.filters.sort.split(':')[1] || 'asc'; },
                get pages() {
                    const pages = [], current = this.pagination.current_page, last = this.pagination.last_page;
                    if (last <= 7) { for (let i = 1; i <= last; i++) pages.push(i); }
                    else {
                        pages.push(1);
                        if (current > 3) pages.push('...');
                        for (let i = Math.max(2, current - 1); i <= Math.min(last - 1, current + 1); i++) pages.push(i);
                        if (current < last - 2) pages.push('...');
                        pages.push(last);
                    }
                    return pages;
                },

                init() {
                    this.loadUiPreferences();
                    this.parseUrlFilters();
                    // Skip initial load if we already have server-rendered data
                    if (this.clients.length === 0) {
                        this.loadClients();
                    }
                    window.addEventListener('popstate', () => { this.parseUrlFilters(); this.loadClients(); });
                },

                loadUiPreferences() {
                    const stored = localStorage.getItem('clients_ui_prefs');
                    if (stored) {
                        try {
                            const prefs = JSON.parse(stored);
                            this.ui.viewMode = prefs.viewMode || 'table';
                            this.ui.grouped = prefs.grouped || false;
                            this.ui.perPage = prefs.perPage || 25;
                            this.ui.collapsedGroups = prefs.collapsedGroups || {};
                        } catch (e) { console.error('Failed to parse UI preferences:', e); }
                    }
                },

                saveUiPreferences() { localStorage.setItem('clients_ui_prefs', JSON.stringify(this.ui)); },

                parseUrlFilters() {
                    const params = new URLSearchParams(window.location.search);
                    if (params.has('status')) this.filters.status = params.get('status').split(',').filter(Boolean);
                    if (params.has('q')) this.filters.q = params.get('q');
                    if (params.has('sort')) this.filters.sort = params.get('sort');
                    if (params.has('page')) this.filters.page = parseInt(params.get('page')) || 1;
                },

                async loadClients() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.filters.status.length) params.set('status', this.filters.status.join(','));
                        if (this.filters.q) params.set('q', this.filters.q);
                        if (this.filters.sort && this.filters.sort !== 'name:asc') params.set('sort', this.filters.sort);
                        if (this.filters.page > 1) params.set('page', this.filters.page);
                        params.set('limit', this.ui.perPage);

                        const response = await fetch(`/clients${params.toString() ? '?' + params.toString() : ''}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        const data = await response.json();
                        this.clients = data.clients || [];
                        this.pagination = data.pagination || this.pagination;
                        this.statusCounts = data.status_counts || this.statusCounts;
                        this.clearSelection();
                    } catch (error) {
                        console.error('Failed to load clients:', error);
                        this.showToast('Failed to load clients', 'error');
                    } finally {
                        this.loading = false;
                        this.initialLoad = false;
                    }
                },

                updateUrl() {
                    const params = new URLSearchParams();
                    if (this.filters.status.length) params.set('status', this.filters.status.join(','));
                    if (this.filters.q) params.set('q', this.filters.q);
                    if (this.filters.sort && this.filters.sort !== 'name:asc') params.set('sort', this.filters.sort);
                    if (this.filters.page > 1) params.set('page', this.filters.page);
                    history.pushState({}, '', params.toString() ? `?${params.toString()}` : window.location.pathname);
                },

                toggleStatus(slug) {
                    const idx = this.filters.status.indexOf(slug);
                    if (idx === -1) this.filters.status.push(slug);
                    else this.filters.status.splice(idx, 1);
                    this.filters.page = 1;
                    this.updateUrl();
                    this.loadClients();
                },

                clearStatusFilter() { this.filters.status = []; this.filters.page = 1; this.updateUrl(); this.loadClients(); },

                setSort(column) {
                    const [currentCol, currentDir] = this.filters.sort.split(':');
                    if (currentCol === column) {
                        this.filters.sort = `${column}:${currentDir === 'asc' ? 'desc' : 'asc'}`;
                    } else {
                        const defaultDesc = ['revenue', 'total_incomes', 'created', 'created_at'];
                        this.filters.sort = `${column}:${defaultDesc.includes(column) ? 'desc' : 'asc'}`;
                    }
                    this.filters.page = 1;
                    this.updateUrl();
                    this.loadClients();
                },

                search(query) { this.filters.q = query; this.filters.page = 1; this.updateUrl(); this.loadClients(); },

                goToPage(page) {
                    if (page < 1 || page > this.pagination.last_page || page === '...') return;
                    this.filters.page = page;
                    this.updateUrl();
                    this.loadClients();
                },

                setViewMode(mode) { this.ui.viewMode = mode; this.saveUiPreferences(); },
                toggleGrouped() { this.ui.grouped = !this.ui.grouped; this.saveUiPreferences(); },
                setPerPage(perPage) { this.ui.perPage = parseInt(perPage); this.filters.page = 1; this.saveUiPreferences(); this.loadClients(); },
                toggleGroupCollapse(statusId) { const key = statusId || 'null'; this.ui.collapsedGroups[key] = !this.ui.collapsedGroups[key]; this.saveUiPreferences(); },
                isGroupCollapsed(statusId) { return this.ui.collapsedGroups[statusId || 'null'] || false; },

                toggleItem(id) {
                    const idx = this.selectedIds.indexOf(id);
                    if (idx === -1) this.selectedIds.push(id);
                    else this.selectedIds.splice(idx, 1);
                    this.updateSelectAllState();
                },
                isSelected(id) { return this.selectedIds.includes(id); },
                toggleSelectAll() { this.selectedIds = this.selectAll ? this.clients.map(c => c.id) : []; },
                clearSelection() { this.selectedIds = []; this.selectAll = false; },
                updateSelectAllState() { this.selectAll = this.clients.length > 0 && this.selectedIds.length === this.clients.length; },

                async updateClientStatus(client, newStatusId) {
                    if (this.savingStatus[client.id]) return;
                    this.savingStatus[client.id] = true;
                    try {
                        const response = await fetch(`/clients/${client.slug || client.id}/status`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ status_id: newStatusId })
                        });
                        const data = await response.json();
                        if (data.success) {
                            const clientIndex = this.clients.findIndex(c => c.id === client.id);
                            if (clientIndex !== -1) {
                                const newStatus = this.statuses.find(s => s.id === newStatusId);
                                this.clients[clientIndex].status_id = newStatusId;
                                this.clients[clientIndex].status = newStatus ? { id: newStatus.id, name: newStatus.name, slug: newStatus.slug, color_background: newStatus.color_background, color_text: newStatus.color_text } : null;
                            }
                            this.showToast(`Status changed to "${this.statuses.find(s => s.id === newStatusId)?.name || 'Updated'}"`, 'success');
                        } else throw new Error(data.message || 'Failed');
                    } catch (error) { console.error('Error updating status:', error); this.showToast('Error updating status', 'error'); }
                    finally { this.savingStatus[client.id] = false; }
                },

                getStatusCount(statusId) { return this.statusCounts.by_status[statusId] || 0; },
                getClientsForStatus(statusId) { return this.clients.filter(c => c.status_id === statusId); },
                getClientsWithoutStatus() { return this.clients.filter(c => !c.status_id); },

                async bulkUpdateStatus(newStatusId) {
                    if (!this.hasSelection || !confirm(`Update status for ${this.selectedCount} client(s)?`)) return;
                    this.loading = true;
                    try {
                        const response = await fetch('/clients/bulk-update', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ ids: this.selectedIds, action: 'update_status', status_id: newStatusId })
                        });
                        if (response.ok) { this.showToast('Status updated successfully', 'success'); this.clearSelection(); await this.loadClients(); }
                        else throw new Error('Failed');
                    } catch (error) { this.showToast('An error occurred', 'error'); }
                    finally { this.loading = false; }
                },

                async bulkExport() {
                    if (!this.hasSelection || !confirm(`Export ${this.selectedCount} client(s) to CSV?`)) return;
                    try {
                        const response = await fetch('/clients/bulk-export', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify({ ids: this.selectedIds, action: 'export' })
                        });
                        if (response.ok) {
                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a'); a.href = url; a.download = 'clients_export.csv';
                            document.body.appendChild(a); a.click(); window.URL.revokeObjectURL(url); document.body.removeChild(a);
                            this.showToast('Export completed', 'success'); this.clearSelection();
                        }
                    } catch (error) { this.showToast('Export failed', 'error'); }
                },

                async bulkDelete() {
                    if (!this.hasSelection || !confirm(`Delete ${this.selectedCount} client(s)? This cannot be undone.`)) return;
                    this.loading = true;
                    try {
                        const response = await fetch('/clients/bulk-update', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ ids: this.selectedIds, action: 'delete' })
                        });
                        if (response.ok) { this.showToast('Clients deleted', 'success'); this.clearSelection(); await this.loadClients(); }
                        else throw new Error('Failed');
                    } catch (error) { this.showToast('An error occurred', 'error'); }
                    finally { this.loading = false; }
                },

                showToast(message, type = 'info') {
                    let container = document.getElementById('toast-container');
                    if (!container) { container = document.createElement('div'); container.id = 'toast-container'; container.className = 'fixed top-4 right-4 z-50 space-y-2'; document.body.appendChild(container); }
                    const toast = document.createElement('div');
                    toast.className = `px-4 py-3 rounded-lg shadow-lg text-white transition-all duration-300 ${type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600'}`;
                    // Use DOM methods instead of innerHTML to prevent XSS
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex items-center gap-2';
                    const icon = document.createElement('span');
                    icon.innerHTML = type === 'success' ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                    const text = document.createElement('span');
                    text.textContent = message;
                    wrapper.appendChild(icon);
                    wrapper.appendChild(text);
                    toast.appendChild(wrapper);
                    container.appendChild(toast);
                    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
                },

                formatCurrency(value) { return new Intl.NumberFormat('ro-RO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0) + ' RON'; },

                copyToClipboard(text, event) {
                    navigator.clipboard.writeText(text).then(() => {
                        const el = event.currentTarget;
                        const orig = el.innerHTML;
                        el.innerHTML = '<span class="flex items-center gap-1 text-green-600"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!</span>';
                        setTimeout(() => { el.innerHTML = orig; }, 2000);
                    });
                }
            };
        }
        </script>

        <!-- Page-specific Scripts (must load before Alpine.js) -->
        @stack('scripts')

        <!-- Alpine Collapse Plugin (must load before Alpine.js) -->
        <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>

        <!-- Alpine.js for interactivity (loads AFTER Livewire) -->
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    </body>
</html>
