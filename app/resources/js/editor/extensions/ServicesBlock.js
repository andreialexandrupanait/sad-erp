import { Node, mergeAttributes } from '@tiptap/core'

/**
 * ServicesBlock Extension for TipTap
 *
 * A block-level node that represents a dynamic services table.
 * In the editor, it shows a placeholder. When rendered, it displays
 * the actual services from the document.
 */
export const ServicesBlock = Node.create({
    name: 'servicesBlock',

    group: 'block',

    // Non-editable block
    atom: true,

    // Allow dragging to reposition
    draggable: true,

    // Selectable
    selectable: true,

    addAttributes() {
        return {
            title: {
                default: 'Servicii',
                parseHTML: element => element.getAttribute('data-title') || 'Servicii',
                renderHTML: attributes => ({
                    'data-title': attributes.title,
                }),
            },
            showPrices: {
                default: true,
                parseHTML: element => element.getAttribute('data-show-prices') !== 'false',
                renderHTML: attributes => ({
                    'data-show-prices': attributes.showPrices ? 'true' : 'false',
                }),
            },
            showDescriptions: {
                default: true,
                parseHTML: element => element.getAttribute('data-show-descriptions') !== 'false',
                renderHTML: attributes => ({
                    'data-show-descriptions': attributes.showDescriptions ? 'true' : 'false',
                }),
            },
            showQuantity: {
                default: true,
                parseHTML: element => element.getAttribute('data-show-quantity') !== 'false',
                renderHTML: attributes => ({
                    'data-show-quantity': attributes.showQuantity ? 'true' : 'false',
                }),
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-services-block]',
            },
        ]
    },

    renderHTML({ node, HTMLAttributes }) {
        const title = node.attrs.title || 'Servicii'

        return [
            'div',
            mergeAttributes(HTMLAttributes, {
                'data-services-block': '',
                class: 'services-block my-4 p-4 border-2 border-dashed border-slate-300 rounded-lg bg-slate-50',
                contenteditable: 'false',
            }),
            [
                'div',
                { class: 'flex items-center gap-2 text-slate-600' },
                [
                    'svg',
                    {
                        class: 'w-5 h-5',
                        fill: 'none',
                        stroke: 'currentColor',
                        viewBox: '0 0 24 24',
                    },
                    [
                        'path',
                        {
                            'stroke-linecap': 'round',
                            'stroke-linejoin': 'round',
                            'stroke-width': '2',
                            d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                        },
                    ],
                ],
                [
                    'span',
                    { class: 'font-medium' },
                    `${title} - Tabel servicii (generat automat)`,
                ],
            ],
        ]
    },

    addCommands() {
        return {
            insertServicesBlock: (options = {}) => ({ commands }) => {
                return commands.insertContent({
                    type: this.name,
                    attrs: {
                        title: options.title || 'Servicii',
                        showPrices: options.showPrices !== false,
                        showDescriptions: options.showDescriptions !== false,
                        showQuantity: options.showQuantity !== false,
                    },
                })
            },

            updateServicesBlock: (options) => ({ tr, state, dispatch }) => {
                const { selection } = state
                const node = selection.$anchor.nodeAfter

                if (node?.type.name === 'servicesBlock') {
                    const newAttrs = { ...node.attrs, ...options }
                    if (dispatch) {
                        tr.setNodeMarkup(selection.from, undefined, newAttrs)
                        dispatch(tr)
                    }
                    return true
                }

                return false
            },
        }
    },
})

export default ServicesBlock
