/**
 * Category Combobox - Alpine.js Component
 *
 * A searchable, keyboard-navigable dropdown for hierarchical categories
 * with inline category creation support.
 */

export default function categoryCombobox(config = {}) {
    return {
        // Configuration
        categories: config.categories || [],
        name: config.name || 'category_id',
        allowCreate: config.allowCreate !== false,
        allowEmpty: config.allowEmpty !== false,
        placeholder: config.placeholder || 'Select category...',

        // State
        selectedId: config.selected || null,
        selectedLabel: '',
        searchQuery: '',
        open: false,
        highlightedIndex: -1,

        // Create form state
        showCreateForm: false,
        createMode: 'category', // 'category' or 'subcategory'
        newCategoryName: '',
        selectedParentId: null,
        saving: false,

        // Virtual scroll state (for large lists)
        scrollTop: 0,
        itemHeight: 36,
        visibleCount: 10,

        /**
         * Initialize component
         */
        init() {
            // Set initial selected label
            if (this.selectedId) {
                const item = this.flatList.find(i => i.id == this.selectedId);
                if (item) {
                    this.selectedLabel = item.isParent
                        ? item.label
                        : `${this.getParentLabel(item.parentId)} > ${item.label}`;
                }
            }

            // Watch for external changes
            this.$watch('selectedId', (newId) => {
                if (newId) {
                    const item = this.flatList.find(i => i.id == newId);
                    if (item) {
                        this.selectedLabel = item.isParent
                            ? item.label
                            : `${this.getParentLabel(item.parentId)} > ${item.label}`;
                    }
                } else {
                    this.selectedLabel = '';
                }
            });
        },

        /**
         * Flatten hierarchy for easier filtering/rendering
         */
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

        /**
         * Filter list by search query
         */
        get filteredList() {
            if (!this.searchQuery.trim()) return this.flatList;
            const q = this.searchQuery.toLowerCase().trim();
            return this.flatList.filter(item =>
                item.label.toLowerCase().includes(q)
            );
        },

        /**
         * Get parent label for a child item
         */
        getParentLabel(parentId) {
            const parent = this.flatList.find(i => i.id == parentId);
            return parent ? parent.label : '';
        },

        /**
         * Toggle dropdown open/close
         */
        toggle() {
            if (this.open) {
                this.close();
            } else {
                this.openDropdown();
            }
        },

        /**
         * Open dropdown
         */
        openDropdown() {
            this.open = true;
            this.searchQuery = '';
            this.highlightedIndex = this.selectedId
                ? this.filteredList.findIndex(i => i.id == this.selectedId)
                : 0;
            this.$nextTick(() => {
                const input = this.$refs.searchInput;
                if (input) input.focus();
            });
        },

        /**
         * Close dropdown
         */
        close() {
            this.open = false;
            this.searchQuery = '';
            this.showCreateForm = false;
            this.highlightedIndex = -1;
        },

        /**
         * Select an item
         */
        select(item) {
            this.selectedId = item.id;
            this.selectedLabel = item.isParent
                ? item.label
                : `${this.getParentLabel(item.parentId)} > ${item.label}`;
            this.close();

            // Dispatch event for parent components
            this.$dispatch('category-selected', {
                id: item.id,
                label: item.label,
                isParent: item.isParent,
                parentId: item.parentId
            });
        },

        /**
         * Clear selection
         */
        clear() {
            this.selectedId = null;
            this.selectedLabel = '';
            this.$dispatch('category-selected', { id: null, label: '' });
        },

        /**
         * Handle search input with debounce
         */
        onSearch(query) {
            this.searchQuery = query;
            this.highlightedIndex = 0;
        },

        /**
         * Keyboard navigation handler
         */
        onKeydown(e) {
            // If dropdown is closed
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
                    this.highlightedIndex = Math.min(
                        this.highlightedIndex + 1,
                        this.filteredList.length - 1
                    );
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
                    if (this.showCreateForm) {
                        this.showCreateForm = false;
                    } else {
                        this.close();
                    }
                    break;

                case 'Tab':
                    this.close();
                    break;
            }
        },

        /**
         * Scroll to keep highlighted item visible
         */
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

        /**
         * Show create category form
         */
        showCreate(mode = 'category') {
            this.createMode = mode;
            this.showCreateForm = true;
            this.newCategoryName = '';

            // Pre-select parent if creating subcategory and item is highlighted
            if (mode === 'subcategory' && this.highlightedIndex >= 0) {
                const item = this.filteredList[this.highlightedIndex];
                if (item) {
                    this.selectedParentId = item.isParent ? item.id : item.parentId;
                }
            } else {
                this.selectedParentId = null;
            }

            this.$nextTick(() => {
                const input = this.$refs.newCategoryInput;
                if (input) input.focus();
            });
        },

        /**
         * Create new category via AJAX
         */
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

                    // Add to categories array
                    if (newItem.isParent) {
                        this.categories.push({
                            id: newItem.id,
                            label: newItem.label,
                            value: newItem.value,
                            children: []
                        });
                    } else {
                        const parent = this.categories.find(c => c.id == newItem.parentId);
                        if (parent) {
                            if (!parent.children) parent.children = [];
                            parent.children.push({
                                id: newItem.id,
                                label: newItem.label,
                                value: newItem.value
                            });
                        }
                    }

                    // Select the new item
                    this.select(newItem);

                    // Reset form
                    this.showCreateForm = false;
                    this.newCategoryName = '';
                    this.selectedParentId = null;

                    // Show success toast
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: `Category "${name}" created`,
                            type: 'success'
                        }
                    }));

                    // Dispatch event for other comboboxes to update
                    window.dispatchEvent(new CustomEvent('category-created', {
                        detail: { category: data.setting }
                    }));

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

        /**
         * Handle category created event from other comboboxes
         */
        onCategoryCreated(event) {
            const newCat = event.detail.category;
            if (!newCat) return;

            // Check if already exists
            const exists = this.flatList.some(i => i.id == newCat.id);
            if (exists) return;

            // Add to categories
            if (!newCat.parent_id) {
                this.categories.push({
                    id: newCat.id,
                    label: newCat.label,
                    value: newCat.value,
                    children: []
                });
            } else {
                const parent = this.categories.find(c => c.id == newCat.parent_id);
                if (parent) {
                    if (!parent.children) parent.children = [];
                    parent.children.push({
                        id: newCat.id,
                        label: newCat.label,
                        value: newCat.value
                    });
                }
            }
        },

        /**
         * Check if item is highlighted
         */
        isHighlighted(index) {
            return this.highlightedIndex === index;
        },

        /**
         * Check if item is selected
         */
        isSelected(item) {
            return this.selectedId == item.id;
        }
    };
}

// Make available globally
window.categoryCombobox = categoryCombobox;
