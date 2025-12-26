/**
 * Builder Core - Shared JavaScript utilities for document builders
 *
 * This module provides common functionality for the offer and template builders.
 * To use: include this file and call builderCore.init(config) with your specific options.
 */

const builderCore = {
    /**
     * Block type definitions with icons and labels
     */
    blockTypes: {
        header: { label: 'Header', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/></svg>', bg: 'bg-slate-200 text-slate-700' },
        services: { label: 'Services', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>', bg: 'bg-blue-100 text-blue-600' },
        summary: { label: 'Summary', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>', bg: 'bg-purple-100 text-purple-600' },
        content: { label: 'Text', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>', bg: 'bg-green-100 text-green-600' },
        paragraph: { label: 'Paragraph', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10"/></svg>', bg: 'bg-emerald-100 text-emerald-600' },
        terms: { label: 'Terms', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', bg: 'bg-amber-100 text-amber-600' },
        signature: { label: 'Signature', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>', bg: 'bg-pink-100 text-pink-600' },
        image: { label: 'Image', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>', bg: 'bg-blue-100 text-blue-600' },
        divider: { label: 'Divider', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>', bg: 'bg-slate-100 text-slate-600' },
        spacer: { label: 'Spacer', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>', bg: 'bg-gray-100 text-gray-600' },
        table: { label: 'Table', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>', bg: 'bg-indigo-100 text-indigo-600' },
        quote: { label: 'Quote', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>', bg: 'bg-violet-100 text-violet-600' },
        columns: { label: 'Columns', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>', bg: 'bg-teal-100 text-teal-600' },
        list: { label: 'List', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>', bg: 'bg-orange-100 text-orange-600' },
        page_break: { label: 'Page Break', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6M4 12h16M4 7h16"/></svg>', bg: 'bg-rose-100 text-rose-600' },
        highlight: { label: 'Highlight', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>', bg: 'bg-yellow-100 text-yellow-600' },
        note: { label: 'Note', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', bg: 'bg-sky-100 text-sky-600' },
        optional_services: { label: 'Optional Services', icon: '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>', bg: 'bg-cyan-100 text-cyan-600' },
    },

    /**
     * Get block icon info
     */
    getBlockIcon(type) {
        return this.blockTypes[type] || { icon: '?', bg: 'bg-slate-100' };
    },

    /**
     * Get block label
     */
    getBlockLabel(type) {
        return this.blockTypes[type]?.label || type;
    },

    /**
     * Generate unique block ID
     */
    generateBlockId(type) {
        return type + '_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
    },

    /**
     * Get default data for a block type
     */
    getDefaultBlockData(type) {
        const defaults = {
            header: { show_logo: true, show_offer_number: true, show_client_info: true, show_date: true, introTitle: '', introText: '' },
            services: { title: 'Services', show_descriptions: true, show_prices: true },
            summary: { title: 'Investment Summary', show_subtotal: true, show_discount: true, show_total: true },
            content: { title: '', content: '' },
            paragraph: { content: '' },
            terms: { title: 'Terms & Conditions', content: '' },
            signature: { title: 'Agreement', content: '', show_signature_field: true, signatureText: '' },
            image: { url: '', alt: '', caption: '', alignment: 'center' },
            divider: { style: 'solid' },
            spacer: { height: 'md' },
            table: { columns: ['Column 1', 'Column 2', 'Column 3'], rows: [['', '', ''], ['', '', '']] },
            quote: { content: '', author: '' },
            columns: { columns: [{ content: '' }, { content: '' }] },
            list: { items: ['', ''], ordered: false },
            page_break: {},
            highlight: { content: '', type: 'info' },
            note: { content: '', type: 'info' },
            optional_services: { title: 'Optional Services', services: [] },
        };
        return defaults[type] || {};
    },

    /**
     * Create a new block
     */
    createBlock(type) {
        return {
            id: this.generateBlockId(type),
            type: type,
            visible: true,
            data: this.getDefaultBlockData(type),
        };
    },

    /**
     * Block management methods
     */
    removeBlock(blocks, index) {
        blocks.splice(index, 1);
    },

    moveBlockUp(blocks, index) {
        if (index > 0) {
            [blocks[index - 1], blocks[index]] = [blocks[index], blocks[index - 1]];
        }
    },

    moveBlockDown(blocks, index) {
        if (index < blocks.length - 1) {
            [blocks[index], blocks[index + 1]] = [blocks[index + 1], blocks[index]];
        }
    },

    duplicateBlock(blocks, index) {
        const block = blocks[index];
        const newBlock = JSON.parse(JSON.stringify(block));
        newBlock.id = this.generateBlockId(block.type);
        blocks.splice(index + 1, 0, newBlock);
        return newBlock;
    },

    /**
     * Table block helpers
     */
    addTableColumn(block) {
        if (!block.data.columns) block.data.columns = [];
        block.data.columns.push('New Column');
        if (block.data.rows) {
            block.data.rows.forEach(row => row.push(''));
        }
    },

    addTableRow(block) {
        if (!block.data.rows) block.data.rows = [];
        const colCount = block.data.columns?.length || 3;
        block.data.rows.push(Array(colCount).fill(''));
    },

    removeTableRow(block, rowIndex) {
        if (block.data.rows && block.data.rows.length > 1) {
            block.data.rows.splice(rowIndex, 1);
        }
    },

    removeTableColumn(block, colIndex) {
        if (block.data.columns && block.data.columns.length > 1) {
            block.data.columns.splice(colIndex, 1);
            if (block.data.rows) {
                block.data.rows.forEach(row => row.splice(colIndex, 1));
            }
        }
    },

    /**
     * List block helpers
     */
    addListItem(block, afterIndex = null) {
        if (!block.data.items) block.data.items = [];
        if (afterIndex !== null) {
            block.data.items.splice(afterIndex + 1, 0, '');
        } else {
            block.data.items.push('');
        }
    },

    removeListItem(block, itemIndex) {
        if (block.data.items && block.data.items.length > 1) {
            block.data.items.splice(itemIndex, 1);
        }
    },

    /**
     * Style helpers
     */
    styleClasses: {
        marginTop: { 'none': 'mt-0', 'sm': 'mt-2', 'md': 'mt-4', 'lg': 'mt-8', 'xl': 'mt-12' },
        marginBottom: { 'none': 'mb-0', 'sm': 'mb-2', 'md': 'mb-4', 'lg': 'mb-8', 'xl': 'mb-12' },
        background: { 'white': 'bg-white', 'slate-50': 'bg-slate-50', 'blue-50': 'bg-blue-50', 'green-50': 'bg-green-50', 'amber-50': 'bg-amber-50' },
        textAlign: { 'left': 'text-left', 'center': 'text-center', 'right': 'text-right' },
        border: { 'none': '', 'subtle': 'border border-slate-200 rounded-lg', 'prominent': 'border-2 border-slate-400 rounded-lg' },
        padding: { 'none': 'p-0', 'sm': 'p-2', 'md': 'p-4', 'lg': 'p-6' },
    },

    getBlockStyleClasses(block) {
        const style = block?.data?.style || {};
        const classes = [];

        Object.keys(this.styleClasses).forEach(property => {
            if (style[property]) {
                const value = this.styleClasses[property][style[property]];
                if (value) classes.push(value);
            }
        });

        return classes.join(' ');
    },

    /**
     * Formatting utilities
     */
    formatCurrency(amount, currency = 'RON') {
        const formatted = new Intl.NumberFormat('ro-RO', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount || 0);
        return formatted + ' ' + currency;
    },

    formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('ro-RO', { day: '2-digit', month: '2-digit', year: 'numeric' });
    },

    /**
     * Initialize SortableJS on blocks container
     */
    initSortable(container, blocks, onUpdate) {
        if (typeof Sortable === 'undefined') {
            console.warn('SortableJS not loaded');
            return null;
        }

        return new Sortable(container, {
            animation: 150,
            handle: '.block-drag-handle',
            ghostClass: 'bg-blue-50',
            onEnd: (evt) => {
                const item = blocks.splice(evt.oldIndex, 1)[0];
                blocks.splice(evt.newIndex, 0, item);
                if (onUpdate) onUpdate(blocks);
            }
        });
    },
};

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = builderCore;
}
