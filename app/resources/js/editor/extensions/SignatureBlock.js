import { Node, mergeAttributes } from '@tiptap/core'

/**
 * SignatureBlock Extension for TipTap
 *
 * A block-level node that represents signature areas for provider and client.
 * In the editor, it shows a placeholder. When rendered, it displays
 * the signature blocks with appropriate spacing.
 */
export const SignatureBlock = Node.create({
    name: 'signatureBlock',

    group: 'block',

    // Non-editable block
    atom: true,

    // Allow dragging to reposition
    draggable: true,

    // Selectable
    selectable: true,

    addAttributes() {
        return {
            showProviderSignature: {
                default: true,
                parseHTML: element => element.getAttribute('data-show-provider') !== 'false',
                renderHTML: attributes => ({
                    'data-show-provider': attributes.showProviderSignature ? 'true' : 'false',
                }),
            },
            showClientSignature: {
                default: true,
                parseHTML: element => element.getAttribute('data-show-client') !== 'false',
                renderHTML: attributes => ({
                    'data-show-client': attributes.showClientSignature ? 'true' : 'false',
                }),
            },
            providerLabel: {
                default: 'Prestator',
                parseHTML: element => element.getAttribute('data-provider-label') || 'Prestator',
                renderHTML: attributes => ({
                    'data-provider-label': attributes.providerLabel,
                }),
            },
            clientLabel: {
                default: 'Beneficiar',
                parseHTML: element => element.getAttribute('data-client-label') || 'Beneficiar',
                renderHTML: attributes => ({
                    'data-client-label': attributes.clientLabel,
                }),
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-signature-block]',
            },
        ]
    },

    renderHTML({ node, HTMLAttributes }) {
        const showProvider = node.attrs.showProviderSignature
        const showClient = node.attrs.showClientSignature
        const providerLabel = node.attrs.providerLabel
        const clientLabel = node.attrs.clientLabel

        const children = [
            [
                'div',
                { class: 'flex items-center gap-2 text-slate-600 mb-3' },
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
                            d: 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z',
                        },
                    ],
                ],
                [
                    'span',
                    { class: 'font-medium' },
                    'Bloc semnături',
                ],
            ],
            [
                'div',
                { class: 'grid grid-cols-2 gap-4 mt-2' },
                ...(showProvider ? [[
                    'div',
                    { class: 'p-3 border border-slate-200 rounded bg-white' },
                    [
                        'p',
                        { class: 'font-medium text-slate-700' },
                        providerLabel,
                    ],
                    [
                        'div',
                        { class: 'mt-8 pt-2 border-t border-slate-300 text-xs text-slate-400' },
                        'Semnătură',
                    ],
                ]] : []),
                ...(showClient ? [[
                    'div',
                    { class: 'p-3 border border-slate-200 rounded bg-white' },
                    [
                        'p',
                        { class: 'font-medium text-slate-700' },
                        clientLabel,
                    ],
                    [
                        'div',
                        { class: 'mt-8 pt-2 border-t border-slate-300 text-xs text-slate-400' },
                        'Semnătură',
                    ],
                ]] : []),
            ],
        ]

        return [
            'div',
            mergeAttributes(HTMLAttributes, {
                'data-signature-block': '',
                class: 'signature-block my-4 p-4 border-2 border-dashed border-slate-300 rounded-lg bg-slate-50',
                contenteditable: 'false',
            }),
            ...children,
        ]
    },

    addCommands() {
        return {
            insertSignatureBlock: (options = {}) => ({ commands }) => {
                return commands.insertContent({
                    type: this.name,
                    attrs: {
                        showProviderSignature: options.showProviderSignature !== false,
                        showClientSignature: options.showClientSignature !== false,
                        providerLabel: options.providerLabel || 'Prestator',
                        clientLabel: options.clientLabel || 'Beneficiar',
                    },
                })
            },
        }
    },
})

export default SignatureBlock
