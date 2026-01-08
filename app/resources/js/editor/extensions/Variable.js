import { Node, mergeAttributes } from '@tiptap/core'

/**
 * Variable Extension for TipTap
 *
 * Creates protected, non-editable inline nodes for template variables.
 * Variables are displayed as styled badges and cannot be accidentally edited.
 */
export const Variable = Node.create({
    name: 'variable',

    group: 'inline',

    inline: true,

    // Makes it non-editable as a whole unit
    atom: true,

    // Allow selection
    selectable: true,

    // Allow dragging
    draggable: true,

    addAttributes() {
        return {
            name: {
                default: null,
                parseHTML: element => element.getAttribute('data-variable'),
                renderHTML: attributes => ({
                    'data-variable': attributes.name,
                }),
            },
            required: {
                default: false,
                parseHTML: element => element.getAttribute('data-required') === 'true',
                renderHTML: attributes => ({
                    'data-required': attributes.required ? 'true' : 'false',
                }),
            },
            fallback: {
                default: '',
                parseHTML: element => element.getAttribute('data-fallback') || '',
                renderHTML: attributes => ({
                    'data-fallback': attributes.fallback,
                }),
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-variable]',
            },
        ]
    },

    renderHTML({ node, HTMLAttributes }) {
        const name = node.attrs.name || 'unknown'
        const required = node.attrs.required

        return [
            'span',
            mergeAttributes(HTMLAttributes, {
                class: `variable-node inline-flex items-center px-1.5 py-0.5 rounded text-sm font-mono cursor-default select-none ${
                    required
                        ? 'bg-blue-100 text-blue-800 border border-blue-200'
                        : 'bg-slate-100 text-slate-700 border border-slate-200'
                }`,
                contenteditable: 'false',
            }),
            `{{${name}}}`,
        ]
    },

    addCommands() {
        return {
            insertVariable: (name, options = {}) => ({ commands }) => {
                return commands.insertContent({
                    type: this.name,
                    attrs: {
                        name,
                        required: options.required || false,
                        fallback: options.fallback || '',
                    },
                })
            },
        }
    },

    addKeyboardShortcuts() {
        return {
            // Delete variable on backspace when selected
            Backspace: () => {
                const { selection } = this.editor.state
                const node = selection.$anchor.nodeAfter

                if (node?.type.name === 'variable') {
                    return this.editor.commands.deleteSelection()
                }

                return false
            },
        }
    },
})

export default Variable
