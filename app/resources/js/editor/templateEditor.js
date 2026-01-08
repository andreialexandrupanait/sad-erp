import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import Link from '@tiptap/extension-link'
import Image from '@tiptap/extension-image'
import TextAlign from '@tiptap/extension-text-align'
import TextStyle from '@tiptap/extension-text-style'
import Color from '@tiptap/extension-color'
import Highlight from '@tiptap/extension-highlight'
import Placeholder from '@tiptap/extension-placeholder'
import Table from '@tiptap/extension-table'
import TableRow from '@tiptap/extension-table-row'
import TableCell from '@tiptap/extension-table-cell'
import TableHeader from '@tiptap/extension-table-header'

import Variable from './extensions/Variable'
import ServicesBlock from './extensions/ServicesBlock'
import SignatureBlock from './extensions/SignatureBlock'

/**
 * Create a TipTap template editor with Alpine.js integration.
 *
 * Usage in Blade:
 * <div x-data="templateEditor({ blocks: @json($blocks), html: @json($html) })">
 *     <div x-ref="editor"></div>
 * </div>
 */
export function templateEditor(initialData = {}) {
    return {
        editor: null,
        content: null,
        charCount: 0,
        wordCount: 0,
        isReady: false,

        // Modal states
        showLinkModal: false,
        linkUrl: '',
        showTableModal: false,
        tableRows: 3,
        tableCols: 3,

        init() {
            // Determine initial content - prefer blocks JSON, fall back to HTML
            if (initialData && initialData.blocks) {
                this.content = initialData.blocks
            } else if (initialData && initialData.html) {
                // Will be converted by TipTap when loading
                this.content = initialData.html
            } else if (typeof initialData === 'string') {
                // Legacy: direct HTML string
                this.content = initialData
            } else if (initialData && typeof initialData === 'object' && initialData.type === 'doc') {
                // Legacy: direct JSON object
                this.content = initialData
            }

            this.$nextTick(() => {
                this.initEditor()
            })
        },

        initEditor() {
            const editorElement = this.$refs.editor

            if (!editorElement) {
                console.error('Editor element not found')
                return
            }

            this.editor = new Editor({
                element: editorElement,
                extensions: [
                    StarterKit.configure({
                        heading: {
                            levels: [1, 2, 3],
                        },
                    }),
                    Underline,
                    Link.configure({
                        openOnClick: false,
                        HTMLAttributes: {
                            class: 'text-blue-600 underline',
                        },
                    }),
                    Image.configure({
                        HTMLAttributes: {
                            class: 'max-w-full h-auto',
                        },
                    }),
                    TextAlign.configure({
                        types: ['heading', 'paragraph'],
                    }),
                    TextStyle,
                    Color,
                    Highlight.configure({
                        multicolor: true,
                    }),
                    Placeholder.configure({
                        placeholder: 'Scrie conținutul șablonului...',
                    }),
                    Table.configure({
                        resizable: true,
                        HTMLAttributes: {
                            class: 'border-collapse border border-slate-300',
                        },
                    }),
                    TableRow,
                    TableCell.configure({
                        HTMLAttributes: {
                            class: 'border border-slate-300 p-2',
                        },
                    }),
                    TableHeader.configure({
                        HTMLAttributes: {
                            class: 'border border-slate-300 p-2 bg-slate-100 font-semibold',
                        },
                    }),
                    // Custom extensions
                    Variable,
                    ServicesBlock,
                    SignatureBlock,
                ],
                content: this.content,
                editorProps: {
                    attributes: {
                        class: 'prose prose-slate max-w-none focus:outline-none min-h-[400px] p-6',
                    },
                },
                onUpdate: ({ editor }) => {
                    this.content = editor.getJSON()
                    this.updateCounts()
                    this.$dispatch('content-changed', { content: this.content })
                },
                onSelectionUpdate: ({ editor }) => {
                    this.$dispatch('selection-changed', { editor })
                },
            })

            this.updateCounts()
            this.isReady = true

            // Expose to window for debugging
            window.templateEditor = this.editor
        },

        // =====================================================================
        // CONTENT METHODS
        // =====================================================================

        getJSON() {
            return this.editor?.getJSON() || null
        },

        getHTML() {
            return this.editor?.getHTML() || ''
        },

        setContent(content) {
            this.editor?.commands.setContent(content)
            this.content = this.editor?.getJSON()
        },

        clearContent() {
            this.editor?.commands.clearContent()
        },

        updateCounts() {
            if (!this.editor) return

            const text = this.editor.getText()
            this.charCount = text.length
            this.wordCount = text.trim() ? text.trim().split(/\s+/).length : 0
        },

        // =====================================================================
        // FORMATTING COMMANDS
        // =====================================================================

        toggleBold() {
            this.editor?.chain().focus().toggleBold().run()
        },

        toggleItalic() {
            this.editor?.chain().focus().toggleItalic().run()
        },

        toggleUnderline() {
            this.editor?.chain().focus().toggleUnderline().run()
        },

        toggleStrike() {
            this.editor?.chain().focus().toggleStrike().run()
        },

        toggleCode() {
            this.editor?.chain().focus().toggleCode().run()
        },

        setHeading(level) {
            if (level === 0) {
                this.editor?.chain().focus().setParagraph().run()
            } else {
                this.editor?.chain().focus().toggleHeading({ level }).run()
            }
        },

        setTextAlign(align) {
            this.editor?.chain().focus().setTextAlign(align).run()
        },

        setColor(color) {
            this.editor?.chain().focus().setColor(color).run()
        },

        setHighlight(color) {
            this.editor?.chain().focus().toggleHighlight({ color }).run()
        },

        // =====================================================================
        // LIST COMMANDS
        // =====================================================================

        toggleBulletList() {
            this.editor?.chain().focus().toggleBulletList().run()
        },

        toggleOrderedList() {
            this.editor?.chain().focus().toggleOrderedList().run()
        },

        // =====================================================================
        // LINK COMMANDS
        // =====================================================================

        openLinkModal() {
            const previousUrl = this.editor?.getAttributes('link').href || ''
            this.linkUrl = previousUrl
            this.showLinkModal = true
        },

        setLink() {
            if (this.linkUrl) {
                this.editor?.chain().focus().extendMarkRange('link').setLink({ href: this.linkUrl }).run()
            } else {
                this.editor?.chain().focus().unsetLink().run()
            }
            this.showLinkModal = false
            this.linkUrl = ''
        },

        removeLink() {
            this.editor?.chain().focus().unsetLink().run()
            this.showLinkModal = false
        },

        // =====================================================================
        // TABLE COMMANDS
        // =====================================================================

        openTableModal() {
            this.tableRows = 3
            this.tableCols = 3
            this.showTableModal = true
        },

        insertTable() {
            this.editor?.chain().focus().insertTable({
                rows: this.tableRows,
                cols: this.tableCols,
                withHeaderRow: true,
            }).run()
            this.showTableModal = false
        },

        addTableRowBefore() {
            this.editor?.chain().focus().addRowBefore().run()
        },

        addTableRowAfter() {
            this.editor?.chain().focus().addRowAfter().run()
        },

        deleteTableRow() {
            this.editor?.chain().focus().deleteRow().run()
        },

        addTableColumnBefore() {
            this.editor?.chain().focus().addColumnBefore().run()
        },

        addTableColumnAfter() {
            this.editor?.chain().focus().addColumnAfter().run()
        },

        deleteTableColumn() {
            this.editor?.chain().focus().deleteColumn().run()
        },

        deleteTable() {
            this.editor?.chain().focus().deleteTable().run()
        },

        // =====================================================================
        // VARIABLE COMMANDS
        // =====================================================================

        insertVariable(name, options = {}) {
            this.editor?.chain().focus().insertVariable(name, options).run()
        },

        // =====================================================================
        // SPECIAL BLOCK COMMANDS
        // =====================================================================

        insertServicesBlock(options = {}) {
            this.editor?.chain().focus().insertServicesBlock(options).run()
        },

        insertSignatureBlock(options = {}) {
            this.editor?.chain().focus().insertSignatureBlock(options).run()
        },

        // =====================================================================
        // IMAGE COMMANDS
        // =====================================================================

        insertImage(url, alt = '') {
            this.editor?.chain().focus().setImage({ src: url, alt }).run()
        },

        // =====================================================================
        // UTILITY
        // =====================================================================

        isActive(type, attrs = {}) {
            return this.editor?.isActive(type, attrs) || false
        },

        focus() {
            this.editor?.chain().focus().run()
        },

        destroy() {
            this.editor?.destroy()
        },
    }
}

// Export for global use
export default templateEditor
