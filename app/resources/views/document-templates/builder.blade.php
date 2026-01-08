<x-app-layout>
    <x-slot name="pageTitle">{{ __('Edit Template') }} - {{ $template->name }}</x-slot>

    @php
        $defaultCurrency = $organization->settings['default_currency'] ?? 'RON';
        $defaultVatPercent = $organization->settings['default_vat_percent'] ?? 19;
        $companyName = $organization->name ?? 'Company';
    @endphp

    <div class="h-[calc(100vh-4rem)] flex" x-data="templateBuilder()" x-cloak>
        {{-- Left Sidebar --}}
        <div class="w-80 bg-white border-r border-slate-200 flex flex-col overflow-hidden">
            {{-- Tab Navigation --}}
            <div class="flex border-b border-slate-200 bg-slate-50">
                <button type="button" @click="sidebarTab = 'settings'"
                        :class="sidebarTab === 'settings' ? 'border-b-2 border-blue-600 text-blue-600 bg-white' : 'text-slate-600 hover:text-slate-900'"
                        class="flex-1 px-4 py-3 text-sm font-medium transition-colors">
                    {{ __('Settings') }}
                </button>
                <button type="button" @click="sidebarTab = 'blocks'"
                        :class="sidebarTab === 'blocks' ? 'border-b-2 border-blue-600 text-blue-600 bg-white' : 'text-slate-600 hover:text-slate-900'"
                        class="flex-1 px-4 py-3 text-sm font-medium transition-colors">
                    {{ __('Blocks') }}
                </button>
            </div>

            {{-- Settings Tab Content --}}
            <div x-show="sidebarTab === 'settings'" class="flex-1 overflow-y-auto p-4 space-y-6">
                {{-- Template Name --}}
                <div>
                    <label class="text-sm font-medium leading-none mb-2 block">{{ __('Template Name') }} <span class="text-red-500">*</span></label>
                    <input type="text" x-model="template.name"
                           class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                </div>

                {{-- Sample Client (Placeholder) --}}
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-sm font-medium text-amber-800">{{ __('Sample Client Data') }}</span>
                    </div>
                    <p class="text-xs text-amber-700">{{ __('This template will use placeholder client data. Actual client info will be filled when creating offers.') }}</p>
                </div>

                {{-- Currency & Discount Row --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium leading-none mb-2 block">{{ __('Currency') }}</label>
                        <select x-model="template.currency" class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                            <option value="RON">RON</option>
                            <option value="EUR">EUR</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium leading-none mb-2 block">{{ __('Discount %') }}</label>
                        <input type="number" x-model.number="template.discount_percent" min="0" max="100" step="0.01"
                               class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                    </div>
                </div>

                {{-- Predefined Services --}}
                @if(isset($services) && count($services) > 0)
                <div class="pt-4 border-t border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-700 mb-2">{{ __('Predefined Services') }}</h3>
                    <p class="text-xs text-slate-500 mb-3">{{ __('Click to add to services list') }}</p>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($services as $service)
                        <button type="button"
                                @click="addFromPredefined({{ json_encode(['id' => $service->id, 'name' => $service->name, 'description' => $service->description ?? '', 'unit' => $service->unit ?? 'buc', 'default_rate' => $service->default_rate ?? 0, 'features' => []]) }})"
                                class="w-full text-left px-3 py-2 bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 rounded-md transition-colors text-sm flex items-center justify-between group">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-slate-700 group-hover:text-blue-700">{{ $service->name }}</span>
                            </div>
                            <span class="text-slate-400 group-hover:text-blue-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </span>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Quick Summary --}}
                <div class="pt-4 border-t border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-700 mb-3">{{ __('Quick Summary') }}</h3>
                    <div class="bg-slate-50 rounded-lg p-4 space-y-2 text-sm border border-slate-200">
                        <div class="flex justify-between">
                            <span class="text-slate-600">{{ __('Services') }}:</span>
                            <span class="font-medium text-slate-900" x-text="customServices.length"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">{{ __('Extra services') }}:</span>
                            <span class="font-medium text-slate-900" x-text="cardServices.length"></span>
                        </div>
                        <div class="flex justify-between text-green-600">
                            <span>{{ __('Selected') }}:</span>
                            <span class="font-medium" x-text="selectedItems.length"></span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-slate-300">
                            <span class="text-slate-600">{{ __('Subtotal') }}:</span>
                            <span class="font-medium text-slate-900" x-text="formatCurrency(subtotal)"></span>
                        </div>
                        <div x-show="template.discount_percent > 0" class="flex justify-between text-red-600">
                            <span>{{ __('Discount') }}:</span>
                            <span x-text="'-' + formatCurrency(discountAmount)"></span>
                        </div>
                        <div class="flex justify-between font-bold text-slate-900 pt-2 border-t border-slate-300">
                            <span>{{ __('Total') }}:</span>
                            <span class="text-green-600" x-text="formatCurrency(grandTotal)"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Blocks Tab Content --}}
            <div x-show="sidebarTab === 'blocks'" class="flex-1 overflow-y-auto p-4 space-y-6">
                {{-- Block Visibility --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-700 mb-3">{{ __('Visible Sections') }}</h3>
                    <div class="space-y-2">
                        <template x-for="(block, index) in blocks" :key="block.id">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" x-model="block.visible" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950 focus:ring-offset-2">
                                <span class="text-sm text-slate-600" x-text="getBlockLabel(block.type)"></span>
                            </label>
                        </template>
                    </div>
                </div>

                {{-- Add Block --}}
                <div class="pt-4 border-t border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-700 mb-3">{{ __('Add Block') }}</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="addBlock('text')"
                                class="p-3 text-left bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-slate-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                            <span class="text-xs font-medium text-slate-700">{{ __('Text') }}</span>
                        </button>
                        <button type="button" @click="addBlock('specifications')"
                                class="p-3 text-left bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-slate-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="text-xs font-medium text-slate-700">{{ __('Specifications') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="p-4 border-t border-slate-200 bg-slate-50 space-y-2">
                {{-- Template Options --}}
                <div class="flex items-center gap-3 mb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="template.is_default" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950 focus:ring-offset-2">
                        <span class="text-sm text-slate-600">{{ __('Set as default') }}</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="template.is_active" class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500 focus:ring-offset-2">
                        <span class="text-sm text-slate-600">{{ __('Active') }}</span>
                    </label>
                </div>

                {{-- Back to List --}}
                <a href="{{ route('settings.document-templates.index') }}"
                   class="w-full py-2.5 bg-white text-slate-700 rounded-md font-medium text-sm border border-slate-300 hover:bg-slate-50 flex items-center justify-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>{{ __('Back to Templates') }}</span>
                </a>

                {{-- Save Template --}}
                <button type="button" @click="saveTemplate()" :disabled="isSaving"
                        class="w-full py-2.5 bg-blue-600 text-white rounded-md font-medium text-sm hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2 transition-colors shadow-sm">
                    <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isSaving ? '{{ __('Saving...') }}' : '{{ __('Save Template') }}'"></span>
                </button>
            </div>
        </div>

        {{-- Main Canvas --}}
        <div class="flex-1 bg-slate-100 overflow-y-auto p-8">
            <div class="max-w-4xl mx-auto">
                {{-- Document Container --}}
                <div class="bg-white shadow-lg rounded-xl">
                    {{-- Header Block (Always First) --}}
                    @include('components.offer-simple.blocks.header')

                    {{-- Dynamic Blocks --}}
                    <div>
                        <template x-for="(block, blockIndex) in blocks" :key="block.id">
                            <div x-show="block.visible" class="relative group"
                                 :class="{
                                     '': block.type === 'brands',
                                     'py-4': block.type !== 'brands',
                                     'border-b border-slate-200': block.type !== 'brands' && blocks[blockIndex + 1]?.type !== 'brands'
                                 }">
                                {{-- Block Toolbar --}}
                                @include('offers.partials.block-toolbar')

                                {{-- Block Content --}}
                                <template x-if="block.type === 'text'">
                                    @include('components.offer-simple.blocks.text')
                                </template>
                                <template x-if="block.type === 'services'">
                                    @include('components.offer-simple.blocks.services')
                                </template>
                                <template x-if="block.type === 'summary'">
                                    @include('components.offer-simple.blocks.summary')
                                </template>
                                <template x-if="block.type === 'specifications'">
                                    @include('components.offer-simple.blocks.specifications')
                                </template>
                                <template x-if="block.type === 'brands'">
                                    @include('components.offer-simple.blocks.brands')
                                </template>
                                <template x-if="block.type === 'acceptance'">
                                    @include('components.offer-simple.blocks.acceptance')
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function templateBuilder() {
        const existingTemplate = @json($template ?? null);
        const existingBlocks = @json($existingBlocks ?? []);
        const templateServices = @json($templateServices ?? []);
        const predefinedServicesData = @json($services ?? []);
        const defaultCurrency = '{{ $defaultCurrency }}';
        const defaultVatPercent = {{ $defaultVatPercent }};
        const companyName = '{{ $companyName }}';

        // Sample client data for template preview
        const sampleClient = {
            name: '{{ __("Client Name") }}',
            company_name: '{{ __("Client Company") }}',
            email: 'client@example.com',
            phone: '+40 700 000 000',
            address: '{{ __("Client Address") }}'
        };

        // Default blocks if none exist
        const defaultBlocks = existingBlocks.length > 0 ? existingBlocks : [
            { id: 'services', type: 'services', visible: true, data: { heading: '', cardsHeading: '', showDiscount: false } },
            { id: 'specifications', type: 'specifications', visible: true, data: { heading: '{{ __("Precizări") }}', sections: [] } },
            { id: 'summary', type: 'summary', visible: true, data: { heading: '', showSubtotal: true, showDiscount: true, showVAT: false, showGrandTotal: true, vatPercent: defaultVatPercent } },
            { id: 'brands', type: 'brands', visible: true, data: { heading: '', mode: 'image', image: '', logos: [], columns: 4 } },
            { id: 'acceptance', type: 'acceptance', visible: true, data: { heading: '', paragraph: '', acceptButtonText: '{{ __("Accept Offer") }}', rejectButtonText: '{{ __("Decline") }}' } }
        ];

        return {
            // UI State
            isSaving: false,
            sidebarTab: 'settings',
            editingLogo: null,

            // Template metadata
            template: {
                id: existingTemplate?.id || null,
                name: existingTemplate?.name || '',
                currency: defaultCurrency,
                discount_percent: 0,
                is_default: existingTemplate?.is_default || false,
                is_active: existingTemplate?.is_active ?? true
            },

            // Header editable data
            headerData: {
                introTitle: '',
                introText: ''
            },

            // Sample client for display
            selectedClient: sampleClient,

            // Simulated offer for compatibility with shared components
            offer: {
                id: null,
                offer_number: 'OFR-XXXXX',
                client_id: null,
                title: '',
                valid_until: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
                currency: defaultCurrency,
                discount_percent: 0
            },

            // Services
            items: templateServices.length > 0
                ? templateServices.map((item, idx) => ({
                    _key: item._key || (Date.now() + idx),
                    _type: item._type || item.type || 'custom',
                    _selected: item._selected ?? item.is_selected ?? true,
                    id: item.id,
                    service_id: item.service_id,
                    title: item.title,
                    description: item.description || '',
                    features: item.features || [],
                    quantity: parseFloat(item.quantity) || 1,
                    unit: item.unit || 'buc',
                    unit_price: parseFloat(item.unit_price) || 0,
                    discount_percent: parseFloat(item.discount_percent) || 0,
                    total: parseFloat(item.total) || parseFloat(item.total_price) || 0
                }))
                : [],

            // Blocks
            blocks: defaultBlocks.map(b => ({
                ...b,
                id: b.id || ('block_' + Math.random().toString(36).substr(2, 9)),
                data: b.data || {}
            })),

            // Computed: Custom services (checkbox list)
            get customServices() {
                return this.items.filter(i => i._type === 'custom');
            },

            // Computed: Card services (Plutio-style cards)
            get cardServices() {
                return this.items.filter(i => i._type === 'card');
            },

            // Computed: Selected items
            get selectedItems() {
                return this.items.filter(i => i._selected);
            },

            // Computed: Subtotal (only selected items)
            get subtotal() {
                return this.selectedItems.reduce((sum, item) => {
                    const itemTotal = (item.quantity || 1) * (item.unit_price || 0);
                    const discount = item.discount_percent ? itemTotal * (item.discount_percent / 100) : 0;
                    return sum + (itemTotal - discount);
                }, 0);
            },

            // Computed: Discount amount
            get discountAmount() {
                return this.subtotal * (this.template.discount_percent / 100);
            },

            // Computed: Grand total
            get grandTotal() {
                return this.subtotal - this.discountAmount;
            },

            // Format currency
            formatCurrency(amount) {
                return new Intl.NumberFormat('ro-RO', {
                    style: 'currency',
                    currency: this.template.currency || 'RON'
                }).format(amount || 0);
            },

            // Get block label
            getBlockLabel(type) {
                const labels = {
                    'services': '{{ __("Services") }}',
                    'specifications': '{{ __("Specifications") }}',
                    'summary': '{{ __("Summary") }}',
                    'brands': '{{ __("Brands") }}',
                    'acceptance': '{{ __("Acceptance") }}',
                    'text': '{{ __("Text") }}'
                };
                return labels[type] || type;
            },

            // Add block
            addBlock(type) {
                const newBlock = {
                    id: 'block_' + Math.random().toString(36).substr(2, 9),
                    type: type,
                    visible: true,
                    data: {}
                };

                if (type === 'text') {
                    newBlock.data = { content: '' };
                } else if (type === 'specifications') {
                    newBlock.data = {
                        heading: '{{ __("Precizări") }}',
                        sections: [{ id: 'spec_' + Date.now(), title: '', type: 'list', content: '', items: [''] }]
                    };
                }

                // Insert before summary block
                const summaryIndex = this.blocks.findIndex(b => b.type === 'summary');
                if (summaryIndex > -1) {
                    this.blocks.splice(summaryIndex, 0, newBlock);
                } else {
                    this.blocks.push(newBlock);
                }
            },

            // Move block
            moveBlock(index, direction) {
                const newIndex = index + direction;
                if (newIndex < 0 || newIndex >= this.blocks.length) return;

                const temp = this.blocks[index];
                this.blocks[index] = this.blocks[newIndex];
                this.blocks[newIndex] = temp;
            },

            // Remove block
            removeBlock(index) {
                if (confirm('{{ __("Are you sure you want to remove this block?") }}')) {
                    this.blocks.splice(index, 1);
                }
            },

            // Add item from predefined services
            addFromPredefined(service) {
                this.items.push({
                    _key: Date.now(),
                    _type: 'card',
                    _selected: true,
                    id: null,
                    service_id: service.id,
                    title: service.name,
                    description: service.description || '',
                    features: service.features || [],
                    quantity: 1,
                    unit: service.unit || 'buc',
                    unit_price: parseFloat(service.default_rate) || 0,
                    discount_percent: 0,
                    total: parseFloat(service.default_rate) || 0
                });
            },

            // Add custom service
            addCustomService() {
                this.items.push({
                    _key: Date.now(),
                    _type: 'custom',
                    _selected: true,
                    id: null,
                    service_id: null,
                    title: '',
                    description: '',
                    features: [],
                    quantity: 1,
                    unit: 'buc',
                    unit_price: 0,
                    discount_percent: 0,
                    total: 0
                });
            },

            // Add card service
            addCardService() {
                this.items.push({
                    _key: Date.now(),
                    _type: 'card',
                    _selected: false,
                    id: null,
                    service_id: null,
                    title: '',
                    description: '',
                    features: [],
                    quantity: 1,
                    unit: 'proiect',
                    unit_price: 0,
                    discount_percent: 0,
                    total: 0
                });
            },

            // Remove item
            removeItem(key) {
                const index = this.items.findIndex(i => i._key === key);
                if (index > -1) {
                    this.items.splice(index, 1);
                }
            },

            // Calculate item total
            calculateItemTotal(item) {
                const baseTotal = (item.quantity || 1) * (item.unit_price || 0);
                const discount = item.discount_percent ? baseTotal * (item.discount_percent / 100) : 0;
                item.total = baseTotal - discount;
                return item.total;
            },

            // Save template
            async saveTemplate() {
                if (!this.template.name) {
                    alert('{{ __("Please enter a template name") }}');
                    return;
                }

                this.isSaving = true;

                try {
                    const response = await fetch('/settings/document-templates/{{ $template->id }}/builder', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name: this.template.name,
                            blocks: this.blocks,
                            services: this.items,
                            is_default: this.template.is_default,
                            is_active: this.template.is_active
                        })
                    });

                    const result = await response.json();

                    if (result.success || response.ok) {
                        // Show success notification
                        if (typeof Alpine !== 'undefined') {
                            window.dispatchEvent(new CustomEvent('notify', {
                                detail: { type: 'success', message: result.message || '{{ __("Template saved successfully!") }}' }
                            }));
                        }
                    } else {
                        throw new Error(result.error || result.message || '{{ __("Failed to save template") }}');
                    }
                } catch (error) {
                    console.error('Error saving template:', error);
                    alert(error.message || '{{ __("Failed to save template. Please try again.") }}');
                } finally {
                    this.isSaving = false;
                }
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
