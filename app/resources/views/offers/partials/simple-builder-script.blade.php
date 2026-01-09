<script>
function simpleOfferBuilder() {
    const existingOffer = @json($offer ?? null);
    const existingItems = @json($existingItems ?? []);
    const predefinedServicesData = @json($predefinedServices ?? []);
    const clientsData = @json($clients ?? []);
    const offerDefaults = @json($offerDefaults ?? []);

    // Fallback specifications (used when no organization defaults are set)
    const fallbackSpecifications = [
        {
            id: 'spec_' + Date.now(),
            title: '',
            type: 'list',
            content: '',
            items: [
                '{{ __("Găzduirea este un serviciu care se achită lunar, semestrial sau anual și care nu este inclus în prețul site-ului. Putem găzdui site-ul la un cost de 50 RON + TVA pe lună (600 RON + TVA pe an).") }}',
                '{{ __("Mentenanța este un serviciu lunar care asigură actualizarea, siguranța și funcționarea optimă a site-ului. Costul este de 100 RON + TVA pe lună sau 1.000 RON + TVA pe an.") }}',
                '{{ __("Planificarea este absolut esențială în dezvoltarea unui site web de calitate. Aceasta include analiza cerințelor, structura paginilor și fluxul utilizatorului.") }}',
                '{{ __("Toate site-urile sunt responsive și optimizate pentru dispozitive mobile, tablete și desktop-uri.") }}',
                '{{ __("După mutare, site-ul intră într-o perioadă de garanție de 30 de zile în care remediem orice problemă fără costuri suplimentare.") }}',
                '{{ __("După perioada de 30 de zile, este necesar un abonament de mentenanță pentru a beneficia de suport tehnic și actualizări.") }}'
            ]
        },
        {
            id: 'portfolio_' + Date.now(),
            title: '{{ __("Website-uri realizate de noi") }}',
            type: 'paragraph',
            content: '{{ __("Vă invităm să vizualizați câteva dintre proiectele noastre recente pentru a vedea calitatea serviciilor oferite.") }}',
            items: []
        }
    ];

    // Use organization defaults if available, otherwise use fallback
    const defaultSpecifications = (offerDefaults.specifications && offerDefaults.specifications.length > 0)
        ? offerDefaults.specifications.map((spec, idx) => ({
            id: 'spec_' + Date.now() + '_' + idx,
            title: spec.title || '',
            type: spec.type || 'list',
            content: spec.content || '',
            items: spec.items || []
        }))
        : fallbackSpecifications;

    // Get VAT defaults from organization settings
    const defaultShowVAT = offerDefaults.show_vat ?? false;
    const defaultVatPercent = offerDefaults.vat_percent ?? 19;
    const defaultCurrency = offerDefaults.currency || '{{ $defaultCurrency ?? "RON" }}';

    const defaultBlocks = [
        { id: 'services', type: 'services', visible: true, data: { heading: '{{ __("Oferta include următoarele servicii:") }}', cardsHeading: '{{ __("Servicii extra disponibile") }}', showDiscount: false } },
        { id: 'specifications', type: 'specifications', visible: true, data: { heading: '{{ __("Precizări") }}', sections: defaultSpecifications } },
        { id: 'summary', type: 'summary', visible: true, data: { heading: '{{ __("Sumar servicii selectate") }}', showSubtotal: true, showDiscount: true, showGrandTotal: true } },
        { id: 'brands', type: 'brands', visible: true, data: { heading: offerDefaults.brands_heading || '', mode: 'image', image: offerDefaults.brands_image || '', logos: [], columns: 4 } },
        { id: 'acceptance', type: 'acceptance', visible: true, data: { heading: '', paragraph: offerDefaults.acceptance_paragraph || '', acceptButtonText: offerDefaults.accept_button_text || '{{ __("Accept Offer") }}', rejectButtonText: offerDefaults.decline_button_text || '{{ __("Decline") }}' } }
    ];

    // Template data for loading
    const templatesData = @json($templates ?? []);

    return {
        // UI State
        previewMode: false,
        isSaving: false,
        isSavingDefaults: false,
        showPredefinedModal: false,
        showSaveTemplateModal: false,
        isSavingTemplate: false,
        templateName: '',
        templateSetDefault: false,
        sidebarTab: 'settings', // 'settings' or 'defaults'
        selectedTemplateId: '', // Template selector

        // Defaults (editable in Defaults tab, saved to organization settings)
        defaults: {
            header_intro_title: offerDefaults.header_intro_title || '',
            header_intro_text: offerDefaults.header_intro_text || '',
            acceptance_paragraph: offerDefaults.acceptance_paragraph || '',
            accept_button_text: offerDefaults.accept_button_text || '',
            decline_button_text: offerDefaults.decline_button_text || '',
            brands_heading: offerDefaults.brands_heading || '',
            brands_image: offerDefaults.brands_image || '',
            specifications: offerDefaults.specifications || [],
            default_services: offerDefaults.default_services || [],
            validity_days: offerDefaults.validity_days || 30,
            currency: offerDefaults.currency || 'RON',
            vat_percent: offerDefaults.vat_percent || 19,
            show_vat: offerDefaults.show_vat || false,
        },

        // Logo editing state (for brands block grid mode)
        editingLogo: null,

        // Header editable data - use organization defaults for new offers
        headerData: {
            introTitle: existingOffer?.header_data?.introTitle || offerDefaults.header_intro_title || '',
            introText: existingOffer?.header_data?.introText || offerDefaults.header_intro_text || ''
        },

        // Client Data
        clients: clientsData,
        selectedClient: null,
        // Initialize newClient with temp client data from existing offer (if any)
        newClient: {
            company_name: existingOffer?.temp_client_company || '',
            contact_person: existingOffer?.temp_client_name || '',
            email: existingOffer?.temp_client_email || '',
            phone: existingOffer?.temp_client_phone || '',
            address: existingOffer?.temp_client_address || '',
            tax_id: existingOffer?.temp_client_tax_id || '',
            registration_number: existingOffer?.temp_client_registration_number || '',
            bank_account: existingOffer?.temp_client_bank_account || ''
        },

        // Offer Metadata - use organization defaults for new offers
        offer: {
            id: existingOffer?.id || null,
            offer_number: existingOffer?.offer_number || null,
            // If offer has temp client (no client_id), set to 'new' to show temp client form
            client_id: existingOffer?.id
                ? (existingOffer?.client_id || (existingOffer?.temp_client_name ? 'new' : ''))
                : '{{ $selectedClientId ?? '' }}',
            title: existingOffer?.title || '',
            valid_until: existingOffer?.valid_until?.split('T')[0] || '{{ $defaultValidUntil }}',
            currency: existingOffer?.currency || defaultCurrency,
            discount_percent: existingOffer?.discount_percent || 0,
            parent_contract_id: existingOffer?.parent_contract_id || "{{ $parentContractId ?? "" }}"
        },

        // Services - TWO TYPES: 'custom' (checkbox list) and 'card' (Plutio cards)
        // For new offers, load default services from organization settings
        items: existingItems.length > 0
            ? existingItems.map((item, idx) => ({
                _key: item._key || (Date.now() + idx),
                _type: item._type || item.type || 'custom',
                _selected: item._selected === true ? true : (item.is_selected === true ? true : (item._selected ?? item.is_selected ?? true)),
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
            : (offerDefaults.default_services || []).map((svc, idx) => ({
                _key: Date.now() + idx,
                _type: svc.type || 'custom', // 'custom' for checkbox list, 'card' for extra service cards
                _selected: svc.selected ?? (svc.type === 'card' ? false : true),
                id: null,
                service_id: svc.service_id || null,
                title: svc.title || '',
                description: svc.description || '',
                features: svc.features || [],
                quantity: parseFloat(svc.quantity) || 1,
                unit: svc.unit || 'proiect',
                unit_price: parseFloat(svc.unit_price) || 0,
                discount_percent: parseFloat(svc.discount_percent) || 0,
                total: parseFloat(svc.unit_price) || 0
            })),

        // Predefined Services (card type for client to add)
        predefinedServices: predefinedServicesData,

        // Block Structure
        blocks: existingOffer?.blocks || defaultBlocks,

        // Computed - filter by type
        get customServices() {
            return this.items.filter(item => item._type === 'custom');
        },
        get cardServices() {
            return this.items.filter(item => item._type === 'card');
        },
        get selectedItems() {
            return this.items.filter(item => item._selected);
        },
        get unselectedItems() {
            return this.items.filter(item => !item._selected);
        },
        get subtotal() {
            return this.selectedItems.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
        },
        get discountAmount() {
            return this.subtotal * (parseFloat(this.offer.discount_percent) || 0) / 100;
        },
        get vatPercent() {
            const summary = this.blocks.find(b => b.type === 'summary');
            return summary?.data?.vatPercent || 19;
        },
        get vatAmount() {
            const summary = this.blocks.find(b => b.type === 'summary');
            if (!summary?.data?.showVAT) return 0;
            return (this.subtotal - this.discountAmount) * (this.vatPercent / 100);
        },
        get grandTotal() {
            return this.subtotal - this.discountAmount + this.vatAmount;
        },

        // Init
        init() {
            if (this.offer.client_id && this.offer.client_id !== 'new') {
                this.selectedClient = this.clients.find(c => c.id == this.offer.client_id) || null;
            }

            // Ensure totals are calculated for all items on init
            this.items.forEach((item, index) => {
                this.updateServiceTotal(index);
            });
        },

        // Client
        onClientChange() {
            if (this.offer.client_id && this.offer.client_id !== 'new') {
                this.selectedClient = this.clients.find(c => c.id == this.offer.client_id) || null;
            } else {
                this.selectedClient = null;
            }
        },

        // Load template data into the offer builder
        async loadTemplate() {
            if (!this.selectedTemplateId) return;

            try {
                const response = await fetch(`/settings/document-templates/${this.selectedTemplateId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('{{ __("Failed to load template") }}');
                }

                const data = await response.json();
                const templateContent = data.content || {};

                // Load blocks from template
                if (templateContent.blocks && templateContent.blocks.length > 0) {
                    this.blocks = templateContent.blocks.map(b => ({
                        ...b,
                        id: b.id || ('block_' + Math.random().toString(36).substr(2, 9)),
                        data: b.data || {}
                    }));
                }

                // Load services from template
                if (templateContent.services && templateContent.services.length > 0) {
                    this.items = templateContent.services.map((item, idx) => ({
                        _key: Date.now() + idx,
                        _type: item._type || item.type || 'custom',
                        _selected: item._selected ?? item.is_selected ?? true,
                        id: null,
                        service_id: item.service_id,
                        title: item.title || '',
                        description: item.description || '',
                        features: item.features || [],
                        quantity: parseFloat(item.quantity) || 1,
                        unit: item.unit || 'buc',
                        unit_price: parseFloat(item.unit_price) || 0,
                        discount_percent: parseFloat(item.discount_percent) || 0,
                        total: 0
                    }));

                    // Calculate totals
                    this.items.forEach((item, index) => {
                        this.updateServiceTotal(index);
                    });
                }

            } catch (error) {
                alert(error.message || '{{ __("Failed to load template. Please try again.") }}');
            }
        },

        // Add Custom Service (checkbox type - admin adds for client to select)
        addCustomService() {
            this.items.push({
                _key: Date.now(),
                _type: 'custom',
                _selected: true, // Pre-selected for admin-added services
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

        // Add Card Service (Plutio style - client can add/remove, no quantity)
        addCardService() {
            this.items.push({
                _key: Date.now(),
                _type: 'card',
                _selected: false,
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

        // Add from predefined service catalog (as custom/checkbox type - from sidebar)
        addFromPredefined(service) {
            this.items.push({
                _key: Date.now(),
                _type: 'custom',
                _selected: true, // Pre-selected when added from sidebar
                service_id: service.id,
                title: service.name || '',
                description: service.description || '',
                features: service.features || [],
                quantity: 1,
                unit: service.unit || 'buc',
                unit_price: parseFloat(service.default_rate) || 0,
                discount_percent: 0,
                total: parseFloat(service.default_rate) || 0
            });
        },

        // Add from predefined service catalog (as card type - from services block tags)
        addCardFromPredefined(service) {
            this.items.push({
                _key: Date.now(),
                _type: 'card',
                _selected: false,
                service_id: service.id,
                title: service.name || '',
                description: service.description || '',
                features: service.features || [],
                quantity: 1,
                unit: service.unit || 'buc',
                unit_price: parseFloat(service.default_rate) || 0,
                discount_percent: 0,
                total: parseFloat(service.default_rate) || 0
            });
        },

        removeService(index) {
            this.items.splice(index, 1);
        },

        updateServiceTotal(index) {
            const item = this.items[index];
            // Card services always have quantity 1
            if (item._type === 'card') {
                item.quantity = 1;
            }
            const subtotal = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
            const discountAmount = subtotal * (parseFloat(item.discount_percent) || 0) / 100;
            item.total = subtotal - discountAmount;
        },

        // Client selection methods
        selectService(index) {
            this.items[index]._selected = true;
            this.updateServiceTotal(index);
        },

        deselectService(index) {
            this.items[index]._selected = false;
            if (this.items[index]._type === 'custom') {
                this.items[index].quantity = 1;
            }
            this.updateServiceTotal(index);
        },

        // Blocks
        moveBlockUp(index) {
            if (index > 0) {
                [this.blocks[index - 1], this.blocks[index]] = [this.blocks[index], this.blocks[index - 1]];
            }
        },
        moveBlockDown(index) {
            if (index < this.blocks.length - 1) {
                [this.blocks[index], this.blocks[index + 1]] = [this.blocks[index + 1], this.blocks[index]];
            }
        },
        addBlock(type) {
            const newBlock = {
                id: type + '_' + Date.now(),
                type: type,
                visible: true,
                data: this.getDefaultBlockData(type)
            };
            this.blocks.push(newBlock);
        },
        getDefaultBlockData(type) {
            switch(type) {
                case 'text': return { heading: '', content: '' };
                case 'specifications': return { heading: '{{ __("Specifications") }}', sections: [] };
                case 'image': return { url: '', caption: '' };
                case 'signature': return { heading: '' };
                default: return {};
            }
        },
        getBlockLabel(type) {
            return {
                'text': '{{ __("Text") }}',
                'services': '{{ __("Services") }}',
                'summary': '{{ __("Summary") }}',
                'specifications': '{{ __("Specifications") }}',
                'acceptance': '{{ __("Acceptance") }}',
                'brands': '{{ __("Brands") }}',
                'image': '{{ __("Image") }}',
                'signature': '{{ __("Signature") }}'
            }[type] || type;
        },
        getBlockIndex(block) {
            return this.blocks.findIndex(b => b.id === block.id);
        },

        // Logo grid helpers for brands block
        editLogo(blockIndex, logoIndex) {
            this.editingLogo = { blockIndex, logoIndex };
        },

        // Handle brands image upload
        async handleBrandsImageUpload(event, block) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('{{ __("File size must be less than 5MB") }}');
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('{{ __("Please upload an image file") }}');
                return;
            }

            // Upload to server
            const formData = new FormData();
            formData.append('image', file);
            formData.append('type', 'brands');

            try {
                const response = await fetch('/offers/upload-image', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await response.json();
                if (response.ok && data.url) {
                    block.data.image = data.url;
                } else {
                    alert(data.message || '{{ __("Failed to upload image") }}');
                }
            } catch (error) {
                console.error('Upload failed:', error);
                alert('{{ __("Failed to upload image") }}');
            }

            // Clear the input
            event.target.value = '';
        },

        // Handle brands image upload for defaults tab
        async handleDefaultBrandsImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('{{ __("File size must be less than 5MB") }}');
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('{{ __("Please upload an image file") }}');
                return;
            }

            // Upload to server
            const formData = new FormData();
            formData.append('image', file);
            formData.append('type', 'brands');

            try {
                const response = await fetch('/offers/upload-image', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await response.json();
                if (response.ok && data.url) {
                    this.defaults.brands_image = data.url;
                } else {
                    alert(data.message || '{{ __("Failed to upload image") }}');
                }
            } catch (error) {
                console.error('Upload failed:', error);
                alert('{{ __("Failed to upload image") }}');
            }

            // Clear the input
            event.target.value = '';
        },

        // Formatting - use browser locale for international support
        formatCurrency(amount) {
            const locale = document.documentElement.lang || navigator.language || 'en';
            return new Intl.NumberFormat(locale, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                .format(amount || 0) + ' ' + (this.offer.currency || 'RON');
        },
        formatDate(date) {
            const locale = document.documentElement.lang || navigator.language || 'en';
            return date ? new Date(date).toLocaleDateString(locale) : '-';
        },

        // Save
        async saveOffer() {
            if (!this.validateOffer()) return;
            this.isSaving = true;

            try {
                const url = this.offer.id ? `/offers/${this.offer.id}` : '/offers';
                const method = this.offer.id ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        client_id: this.offer.client_id === 'new' ? null : this.offer.client_id,
                        // Temp client fields when creating new client
                        temp_client_name: this.offer.client_id === 'new' ? (this.newClient.contact_person || this.newClient.company_name) : null,
                        temp_client_email: this.offer.client_id === 'new' ? this.newClient.email : null,
                        temp_client_phone: this.offer.client_id === 'new' ? this.newClient.phone : null,
                        temp_client_company: this.offer.client_id === 'new' ? this.newClient.company_name : null,
                        temp_client_address: this.offer.client_id === 'new' ? this.newClient.address : null,
                        temp_client_tax_id: this.offer.client_id === 'new' ? this.newClient.tax_id : null,
                        temp_client_registration_number: this.offer.client_id === 'new' ? this.newClient.registration_number : null,
                        temp_client_bank_account: this.offer.client_id === 'new' ? this.newClient.bank_account : null,
                        title: this.offer.title,
                        valid_until: this.offer.valid_until,
                        currency: this.offer.currency,
                        discount_percent: this.offer.discount_percent,
                        parent_contract_id: this.offer.parent_contract_id || null,
                        header_data: this.headerData,
                        blocks: this.blocks,
                        items: this.items.map(item => ({
                            type: item._type || 'custom',
                            is_selected: item._selected !== false,
                            service_id: item.service_id,
                            title: item.title,
                            description: item.description,
                            features: item.features,
                            quantity: item.quantity,
                            unit: item.unit,
                            unit_price: item.unit_price,
                            discount_percent: item.discount_percent
                        }))
                    })
                });

                const data = await response.json();
                if (response.ok) {
                    // Update local offer data with server response
                    if (data.offer) {
                        this.offer.id = data.offer.id;
                        this.offer.offer_number = data.offer.offer_number;
                        window.history.replaceState({}, '', `/offers/${data.offer.id}/edit`);
                    }
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else {
                    alert(data.message || '{{ __("Failed to save offer") }}');
                }
            } catch (error) {
                console.error('Save failed:', error);
                alert('{{ __("Failed to save offer") }}');
            } finally {
                this.isSaving = false;
            }
        },

        // Save as Template
        async saveAsTemplate() {
            if (!this.templateName) {
                alert('{{ __("Please enter a template name") }}');
                return;
            }

            this.isSavingTemplate = true;

            try {
                const response = await fetch('/offers/save-as-template', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: this.templateName,
                        blocks: this.blocks,
                        is_default: this.templateSetDefault,
                        // Include services structure for template
                        services: this.items.map(item => ({
                            _type: item._type,
                            service_id: item.service_id,
                            title: item.title,
                            description: item.description,
                            features: item.features,
                            unit: item.unit,
                            unit_price: item.unit_price
                        }))
                    })
                });

                const data = await response.json();
                if (response.ok) {
                    this.showSaveTemplateModal = false;
                    this.templateName = '';
                    this.templateSetDefault = false;
                    alert('{{ __("Template saved successfully") }}');
                } else {
                    alert(data.message || '{{ __("Failed to save template") }}');
                }
            } catch (error) {
                console.error('Save template failed:', error);
                alert('{{ __("Failed to save template") }}');
            } finally {
                this.isSavingTemplate = false;
            }
        },

        // Save Defaults to organization settings
        async saveDefaults() {
            this.isSavingDefaults = true;

            try {
                const response = await fetch('/settings/offer-defaults', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.defaults)
                });

                const data = await response.json();
                if (response.ok) {
                    // Show success message
                    const toast = document.createElement('div');
                    toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-fade-in';
                    toast.textContent = '{{ __("Defaults saved successfully") }}';
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                } else {
                    alert(data.message || '{{ __("Failed to save defaults") }}');
                }
            } catch (error) {
                console.error('Save defaults failed:', error);
                alert('{{ __("Failed to save defaults") }}');
            } finally {
                this.isSavingDefaults = false;
            }
        },

        validateOffer() {
            if (!this.offer.client_id) {
                alert('{{ __("Please select a client") }}');
                return false;
            }
            if (this.offer.client_id === 'new') {
                // For new clients, require at least a name (contact person or company)
                if (!this.newClient.contact_person && !this.newClient.company_name) {
                    alert('{{ __("Please enter client name or company name") }}');
                    return false;
                }
            }
            return true;
        },

        // Save a custom service as predefined service in the catalog
        async saveAsPredefined(index) {
            const item = this.items[index];
            if (!item.title) {
                alert('{{ __("Please enter a service name first") }}');
                return;
            }

            try {
                const response = await fetch('/settings/services', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: item.title,
                        description: item.description || '',
                        default_rate: item.unit_price || 0,
                        currency: this.offer.currency || 'RON',
                        unit: item.unit || 'proiect',
                        is_active: true
                    })
                });

                const data = await response.json();
                if (response.ok && data.service) {
                    // Update the item to reference the new predefined service
                    item.service_id = data.service.id;

                    // Add to predefinedServices list
                    this.predefinedServices.push({
                        id: data.service.id,
                        name: data.service.name,
                        description: data.service.description,
                        default_rate: data.service.default_rate,
                        unit: data.service.unit
                    });

                    // Show success toast
                    const toast = document.createElement('div');
                    toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                    toast.textContent = '{{ __("Service saved to catalog") }}';
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                } else {
                    alert(data.message || '{{ __("Failed to save service") }}');
                }
            } catch (error) {
                console.error('Save service failed:', error);
                alert('{{ __("Failed to save service") }}');
            }
        },

        // ========================================
        // Real-time sync with customer selections
        // ========================================
        lastSyncTimestamp: existingOffer?.updated_at ? Math.floor(new Date(existingOffer.updated_at).getTime() / 1000) : 0,
        customerSyncInterval: null,
        showCustomerUpdateNotification: false,
        pendingCustomerUpdate: null,

        initCustomerSync() {
            // Only start sync if we have an existing offer with a public token
            if (this.offer.id && existingOffer?.public_token) {
                this.startCustomerSync();

                // Clean up interval on page unload to prevent memory leaks
                window.addEventListener('beforeunload', () => this.stopCustomerSync());
            }
        },

        startCustomerSync() {
            // Poll every 3 seconds
            this.customerSyncInterval = setInterval(() => {
                this.checkCustomerUpdates();
            }, 3000);
        },

        stopCustomerSync() {
            if (this.customerSyncInterval) {
                clearInterval(this.customerSyncInterval);
                this.customerSyncInterval = null;
            }
        },

        async checkCustomerUpdates() {
            if (!existingOffer?.public_token) return;

            try {
                const response = await fetch(`/offers/view/${existingOffer.public_token}/state`);
                const data = await response.json();

                if (data.success && data.updated_at > this.lastSyncTimestamp) {
                    this.pendingCustomerUpdate = data;
                    this.showCustomerUpdateNotification = true;
                }
            } catch (error) {
                // Silently ignore sync errors - non-critical functionality
            }
        },

        applyCustomerUpdate() {
            if (!this.pendingCustomerUpdate) return;

            const data = this.pendingCustomerUpdate;

            // Update items based on customer selections
            // Custom items - check which ones customer deselected
            data.custom_items.forEach(serverItem => {
                const localItem = this.items.find(i => i.id === serverItem.id);
                if (localItem) {
                    // Server returns is_selected, we use _selected
                    localItem._selected = serverItem.is_selected !== false;
                }
            });

            // Card items
            data.card_items.forEach(serverItem => {
                const localItem = this.items.find(i => i.id === serverItem.id);
                if (localItem) {
                    localItem._selected = serverItem.is_selected === true;
                }
            });

            // Update timestamp
            this.lastSyncTimestamp = data.updated_at;
            this.showCustomerUpdateNotification = false;
            this.pendingCustomerUpdate = null;

            // Show toast
            this.showToast('{{ __("Customer selections updated") }}', 'info');
        },

        dismissCustomerUpdate() {
            if (this.pendingCustomerUpdate) {
                this.lastSyncTimestamp = this.pendingCustomerUpdate.updated_at;
            }
            this.showCustomerUpdateNotification = false;
            this.pendingCustomerUpdate = null;
        },

        showToast(message, type = 'success') {
            const bgColor = type === 'success' ? 'bg-green-600' : (type === 'info' ? 'bg-blue-600' : 'bg-red-600');
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-4 py-2 rounded-lg shadow-lg z-50`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    };
}
</script>
