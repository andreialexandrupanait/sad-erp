{{-- Offer Builder Sidebar --}}
<div class="w-80 bg-white border-r border-slate-200 flex flex-col overflow-hidden">
    {{-- Tab Navigation --}}
    <div class="flex border-b border-slate-200 bg-slate-50">
        <button type="button" @click="sidebarTab = 'settings'"
                :class="sidebarTab === 'settings' ? 'border-b-2 border-blue-600 text-blue-600 bg-white' : 'text-slate-600 hover:text-slate-900'"
                class="flex-1 px-4 py-3 text-sm font-medium transition-colors">
            {{ __('Settings') }}
        </button>
        <button type="button" @click="sidebarTab = 'defaults'"
                :class="sidebarTab === 'defaults' ? 'border-b-2 border-blue-600 text-blue-600 bg-white' : 'text-slate-600 hover:text-slate-900'"
                class="flex-1 px-4 py-3 text-sm font-medium transition-colors">
            {{ __('Defaults') }}
        </button>
    </div>

    {{-- Settings Tab Content --}}
    <div x-show="sidebarTab === 'settings'" class="flex-1 overflow-y-auto p-4 space-y-6">
        {{-- Client Selection --}}
        <div>
            <label class="text-sm font-medium leading-none mb-2 block">{{ __('Client') }} <span class="text-red-500">*</span></label>
            <select x-model="offer.client_id" @change="onClientChange()"
                    class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                <option value="">{{ __('Select client...') }}</option>
                <option value="new">+ {{ __('New Client') }}</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->company_name ?: $client->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- New Client Form --}}
        <div x-show="offer.client_id === 'new'" x-collapse class="space-y-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
            <input type="text" x-model="newClient.company_name" placeholder="{{ __('Company Name') }}"
                   class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
            <input type="text" x-model="newClient.contact_person" placeholder="{{ __('Contact Person') }}"
                   class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
            <input type="email" x-model="newClient.email" placeholder="{{ __('Email') }} *"
                   class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
            <input type="text" x-model="newClient.phone" placeholder="{{ __('Phone') }}"
                   class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">

            {{-- Business Details (for contracts) --}}
            <div class="border-t border-slate-200 pt-3 mt-3">
                <p class="text-xs text-slate-500 mb-2">{{ __('Business Details (optional, for contracts)') }}</p>
                <input type="text" x-model="newClient.address" placeholder="{{ __('Address') }}"
                       class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 mb-3">
                <input type="text" x-model="newClient.tax_id" placeholder="{{ __('CUI / Tax ID') }}"
                       class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 mb-3">
                <input type="text" x-model="newClient.registration_number" placeholder="{{ __('Reg. Com. / Registration Number') }}"
                       class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
            </div>
        </div>

        {{-- Template Selection (only for new offers) --}}
        @if(isset($templates) && count($templates) > 0)
        <div x-show="!offer.id">
            <label class="text-sm font-medium leading-none mb-2 block">{{ __('Load from Template') }}</label>
            <select x-model="selectedTemplateId" @change="loadTemplate()"
                    class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                <option value="">{{ __('Start from blank...') }}</option>
                @foreach($templates as $template)
                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-500 mt-1">{{ __('Load pre-configured blocks and services from a template') }}</p>
        </div>
        @endif

        {{-- Offer Number (Auto-generated, read-only display) --}}
        <div>
            <label class="text-sm font-medium leading-none mb-2 block">{{ __('Offer Number') }}</label>
            <div class="h-10 flex items-center gap-2 px-3 py-2 bg-slate-50 rounded-md border border-slate-200">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                </svg>
                <span class="text-sm text-slate-500" x-text="offer.offer_number || '{{ __('Auto-generated on save') }}'"></span>
            </div>
        </div>

        {{-- Valid Until --}}
        <div>
            <label class="text-sm font-medium leading-none mb-2 block">{{ __('Valid Until') }}</label>
            <input type="date" x-model="offer.valid_until"
                   class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
        </div>

        {{-- Currency & Discount Row --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-sm font-medium leading-none mb-2 block">{{ __('Currency') }}</label>
                <select x-model="offer.currency" class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                    <option value="RON">RON</option>
                    <option value="EUR">EUR</option>
                    <option value="USD">USD</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium leading-none mb-2 block">{{ __('Discount %') }}</label>
                <input type="number" x-model.number="offer.discount_percent" min="0" max="100" step="0.01"
                       class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
            </div>
        </div>

        {{-- Predefined Services --}}
        @if(isset($predefinedServices) && count($predefinedServices) > 0)
        <div class="pt-4 border-t border-slate-200">
            <h3 class="text-sm font-semibold text-slate-700 mb-2">{{ __('Predefined Services') }}</h3>
            <p class="text-xs text-slate-500 mb-3">{{ __('Click to add to services list') }}</p>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                @foreach($predefinedServices as $service)
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

        {{-- Block Visibility --}}
        <div class="pt-4 border-t border-slate-200">
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
                <div x-show="offer.discount_percent > 0" class="flex justify-between text-red-600">
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

    {{-- Defaults Tab Content --}}
    <div x-show="sidebarTab === 'defaults'" class="flex-1 overflow-y-auto p-4 space-y-6">
        <p class="text-xs text-slate-500 mb-4">{{ __('Configure default texts that will be pre-filled in new offers. Changes are saved to your organization settings.') }}</p>

        {{-- Header Section Defaults --}}
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/>
                </svg>
                {{ __('Header Section') }}
            </h3>
            <div>
                <label class="text-xs font-medium text-slate-600 mb-1 block">{{ __('Intro Title') }}</label>
                <input type="text" x-model="defaults.header_intro_title"
                       placeholder="{{ __('Your business partner for digital solutions.') }}"
                       class="h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600 mb-1 block">{{ __('Intro Text') }}</label>
                <textarea x-model="defaults.header_intro_text" rows="2"
                          placeholder="{{ __('We deliver high-quality services tailored to your specific needs.') }}"
                          class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 resize-none"></textarea>
            </div>
        </div>

        {{-- Acceptance Section Defaults --}}
        <div class="space-y-3 pt-4 border-t border-slate-200">
            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('Acceptance Section') }}
            </h3>
            <div>
                <label class="text-xs font-medium text-slate-600 mb-1 block">{{ __('Terms Paragraph') }}</label>
                <textarea x-model="defaults.acceptance_paragraph" rows="3"
                          placeholder="{{ __('By accepting this offer, you agree to the terms and conditions outlined above.') }}"
                          class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 resize-none"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">{{ __('Accept Button') }}</label>
                    <input type="text" x-model="defaults.accept_button_text"
                           placeholder="{{ __('Accept Offer') }}"
                           class="h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">{{ __('Decline Button') }}</label>
                    <input type="text" x-model="defaults.decline_button_text"
                           placeholder="{{ __('Decline') }}"
                           class="h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                </div>
            </div>
        </div>

        {{-- Specifications Section Defaults --}}
        <div class="space-y-3 pt-4 border-t border-slate-200">
            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                {{ __('Specifications (Precizări)') }}
            </h3>
            <p class="text-xs text-slate-500">{{ __('Default items that appear in the Precizări section of every new offer.') }}</p>

            {{-- Existing Specifications --}}
            <div class="space-y-2">
                <template x-for="(spec, specIndex) in defaults.specifications" :key="specIndex">
                    <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <input type="text" x-model="spec.title"
                                   placeholder="{{ __('Section title (optional)') }}"
                                   class="flex-1 h-8 rounded border border-slate-200 bg-white px-2 text-xs placeholder:text-slate-400 focus:border-blue-400">
                            <select x-model="spec.type" class="h-8 rounded border border-slate-200 bg-white px-2 text-xs focus:border-blue-400">
                                <option value="list">{{ __('List') }}</option>
                                <option value="paragraph">{{ __('Paragraph') }}</option>
                            </select>
                            <button type="button" @click="defaults.specifications.splice(specIndex, 1)"
                                    class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Paragraph type --}}
                        <template x-if="spec.type === 'paragraph'">
                            <textarea x-model="spec.content" rows="2"
                                      placeholder="{{ __('Paragraph content...') }}"
                                      class="w-full rounded border border-slate-200 bg-white px-2 py-1.5 text-xs placeholder:text-slate-400 focus:border-blue-400 resize-none"></textarea>
                        </template>

                        {{-- List type --}}
                        <template x-if="spec.type === 'list'">
                            <div class="space-y-1.5">
                                <template x-for="(item, itemIndex) in spec.items" :key="itemIndex">
                                    <div class="flex items-start gap-1.5">
                                        <span class="text-green-500 mt-1.5 flex-shrink-0">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                        <textarea x-model="spec.items[itemIndex]" rows="2"
                                                  placeholder="{{ __('List item...') }}"
                                                  class="flex-1 rounded border border-slate-200 bg-white px-2 py-1 text-xs placeholder:text-slate-400 focus:border-blue-400 resize-none"></textarea>
                                        <button type="button" @click="spec.items.splice(itemIndex, 1)"
                                                class="p-1 text-slate-400 hover:text-red-500 rounded transition-colors mt-0.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="spec.items.push('')"
                                        class="w-full py-1.5 text-xs text-slate-500 hover:text-blue-600 hover:bg-blue-50 border border-dashed border-slate-300 hover:border-blue-400 rounded transition-colors flex items-center justify-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('Add item') }}
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Add New Section Button --}}
            <button type="button" @click="defaults.specifications.push({ title: '', type: 'list', content: '', items: [''] })"
                    class="w-full py-2 text-xs text-slate-600 hover:text-blue-600 hover:bg-blue-50 border-2 border-dashed border-slate-300 hover:border-blue-400 rounded-lg transition-colors flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Add specification section') }}
            </button>
        </div>

        {{-- Default Services Section --}}
        <div class="space-y-3 pt-4 border-t border-slate-200">
            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                {{ __('Default Services') }}
            </h3>
            <p class="text-xs text-slate-500">{{ __('Services that will be pre-added to every new offer.') }}</p>

            {{-- Existing Default Services --}}
            <div class="space-y-2">
                <template x-for="(svc, svcIndex) in defaults.default_services" :key="svcIndex">
                    <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <input type="text" x-model="svc.title"
                                   placeholder="{{ __('Service name') }}"
                                   class="flex-1 h-8 rounded border border-slate-200 bg-white px-2 text-xs font-medium placeholder:text-slate-400 focus:border-blue-400">
                            <button type="button" @click="defaults.default_services.splice(svcIndex, 1)"
                                    class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        <textarea x-model="svc.description" rows="2"
                                  placeholder="{{ __('Description (optional)') }}"
                                  class="w-full rounded border border-slate-200 bg-white px-2 py-1.5 text-xs placeholder:text-slate-400 focus:border-blue-400 resize-none mb-2"></textarea>
                        <div class="flex items-center gap-2 flex-wrap">
                            <input type="number" x-model.number="svc.unit_price" min="0" step="0.01"
                                   placeholder="{{ __('Price') }}"
                                   class="w-20 h-7 rounded border border-slate-200 bg-white px-2 text-xs placeholder:text-slate-400 focus:border-blue-400">
                            <select x-model="svc.unit" class="h-7 rounded border border-slate-200 bg-white px-2 text-xs focus:border-blue-400">
                                <option value="proiect">proiect</option>
                                <option value="buc">buc</option>
                                <option value="ora">oră</option>
                                <option value="luna">lună</option>
                                <option value="an">an</option>
                            </select>
                            <select x-model="svc.type" class="h-7 rounded border border-slate-200 bg-white px-2 text-xs focus:border-blue-400">
                                <option value="custom">{{ __('Standard') }}</option>
                                <option value="card">{{ __('Extra (card)') }}</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-3 mt-2">
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" x-model="svc.selected" class="h-3.5 w-3.5 rounded border-slate-300 text-green-600">
                                <span class="text-xs text-slate-500" x-text="svc.type === 'card' ? '{{ __('Pre-added to offer') }}' : '{{ __('Pre-selected') }}'"></span>
                            </label>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Add New Service Button --}}
            <button type="button" @click="defaults.default_services.push({ title: '', description: '', unit_price: 0, unit: 'proiect', selected: true, type: 'card' })"
                    class="w-full py-2 text-xs text-slate-600 hover:text-blue-600 hover:bg-blue-50 border-2 border-dashed border-slate-300 hover:border-blue-400 rounded-lg transition-colors flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Add default service') }}
            </button>

            {{-- Quick add from predefined services --}}
            @if(isset($predefinedServices) && count($predefinedServices) > 0)
            <div class="mt-2">
                <p class="text-xs text-slate-500 mb-1.5">{{ __('Or add from catalog:') }}</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($predefinedServices as $service)
                    <button type="button"
                            @click="defaults.default_services.push({ title: '{{ addslashes($service->name) }}', description: '{{ addslashes($service->description ?? '') }}', unit_price: {{ $service->default_rate ?? 0 }}, unit: '{{ $service->unit ?? 'proiect' }}', service_id: {{ $service->id }}, selected: true, type: 'card' })"
                            class="px-2 py-1 text-xs bg-white border border-slate-200 hover:border-blue-400 hover:bg-blue-50 rounded-full transition-colors flex items-center gap-1">
                        <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ $service->name }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Brands Section Defaults --}}
        <div class="space-y-3 pt-4 border-t border-slate-200">
            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ __('Brands Section') }}
            </h3>
            <div>
                <label class="text-xs font-medium text-slate-600 mb-1 block">{{ __('Brands Heading') }}</label>
                <input type="text" x-model="defaults.brands_heading"
                       placeholder="{{ __('Branduri care au ales să se bazeze pe experiența noastră') }}"
                       class="h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600 mb-1 block">{{ __('Default Brands Image') }}</label>
                {{-- Image Preview --}}
                <div x-show="defaults.brands_image" class="mb-2 relative group">
                    <img :src="defaults.brands_image" class="w-full rounded-lg border border-slate-200" alt="Brands preview">
                    <button type="button" @click="defaults.brands_image = ''"
                            class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                {{-- Upload Button --}}
                <div x-show="!defaults.brands_image" @click="$refs.defaultBrandsImageInput.click()"
                     class="border-2 border-dashed border-slate-300 hover:border-blue-400 rounded-lg p-4 text-center cursor-pointer transition-colors group">
                    <svg class="w-6 h-6 mx-auto mb-1 text-slate-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-xs text-slate-500 group-hover:text-blue-600">{{ __('Click to upload') }}</p>
                </div>
                <input type="file" x-ref="defaultBrandsImageInput" @change="handleDefaultBrandsImageUpload($event)" accept="image/*" class="hidden">
                {{-- Or paste URL --}}
                <div class="mt-2">
                    <input type="text" x-model="defaults.brands_image"
                           placeholder="{{ __('Or paste image URL...') }}"
                           class="h-8 w-full rounded-md border border-slate-200 bg-white px-3 py-1 text-xs placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                </div>
                <p class="text-xs text-slate-400 mt-1">{{ __('This image will be pre-loaded in all new offers') }}</p>
            </div>
        </div>

        {{-- General Defaults --}}
        <div class="space-y-3 pt-4 border-t border-slate-200">
            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ __('General Defaults') }}
            </h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">{{ __('Validity (days)') }}</label>
                    <input type="number" x-model.number="defaults.validity_days" min="1" max="365"
                           placeholder="30"
                           class="h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">{{ __('Currency') }}</label>
                    <select x-model="defaults.currency"
                            class="h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                        <option value="RON">RON</option>
                        <option value="EUR">EUR</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-slate-600 mb-1 block">{{ __('VAT %') }}</label>
                    <input type="number" x-model.number="defaults.vat_percent" min="0" max="100"
                           placeholder="19"
                           class="h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="defaults.show_vat" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950">
                        <span class="text-xs text-slate-600">{{ __('Show VAT') }}</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Save Defaults Button --}}
        <div class="pt-4">
            <button type="button" @click="saveDefaults()" :disabled="isSavingDefaults"
                    class="w-full py-2.5 bg-orange-600 text-white rounded-md font-medium text-sm hover:bg-orange-700 disabled:opacity-50 flex items-center justify-center gap-2 transition-colors shadow-sm">
                <svg x-show="!isSavingDefaults" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg x-show="isSavingDefaults" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="isSavingDefaults ? '{{ __('Saving...') }}' : '{{ __('Save Defaults') }}'"></span>
            </button>
            <p class="text-xs text-slate-400 text-center mt-2">{{ __('Saved to organization settings') }}</p>
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="p-4 border-t border-slate-200 bg-slate-50 space-y-2">
        {{-- Preview Toggle --}}
        <button type="button" @click="previewMode = !previewMode"
                class="w-full py-2.5 rounded-md font-medium text-sm flex items-center justify-center gap-2 transition-colors border"
                :class="previewMode ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'">
            <svg x-show="!previewMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <svg x-show="previewMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <span x-text="previewMode ? '{{ __('Edit Mode') }}' : '{{ __('Preview') }}'"></span>
        </button>

        {{-- Save as Template --}}
        <button type="button" @click="showSaveTemplateModal = true"
                class="w-full py-2.5 bg-white text-slate-700 rounded-md font-medium text-sm border border-slate-300 hover:bg-slate-50 flex items-center justify-center gap-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            <span>{{ __('Save as Template') }}</span>
        </button>

        {{-- Save Offer --}}
        <button type="button" @click="saveOffer()" :disabled="isSaving"
                class="w-full py-2.5 bg-blue-600 text-white rounded-md font-medium text-sm hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2 transition-colors shadow-sm">
            <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <svg x-show="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span x-text="isSaving ? '{{ __('Saving...') }}' : '{{ __('Save Offer') }}'"></span>
        </button>
    </div>
</div>

{{-- Save as Template Modal --}}
<div x-show="showSaveTemplateModal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
     @click.self="showSaveTemplateModal = false">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4" @click.stop>
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-900">{{ __('Save as Template') }}</h3>
            <p class="text-sm text-slate-500 mt-1">{{ __('Save the current offer structure as a reusable template.') }}</p>
        </div>
        <div class="px-6 py-4 space-y-4">
            <div>
                <label class="text-sm font-medium leading-none mb-2 block">{{ __('Template Name') }} <span class="text-red-500">*</span></label>
                <input type="text" x-model="templateName" placeholder="{{ __('e.g., Standard Web Development') }}"
                       class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" x-model="templateSetDefault" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950 focus:ring-offset-2">
                <span class="text-sm text-slate-600">{{ __('Set as default template') }}</span>
            </label>
        </div>
        <div class="px-6 py-4 bg-slate-50 rounded-b-lg flex justify-end gap-3">
            <button type="button" @click="showSaveTemplateModal = false"
                    class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50">
                {{ __('Cancel') }}
            </button>
            <button type="button" @click="saveAsTemplate()" :disabled="!templateName || isSavingTemplate"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2 shadow-sm">
                <svg x-show="isSavingTemplate" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="isSavingTemplate ? '{{ __('Saving...') }}' : '{{ __('Save Template') }}'"></span>
            </button>
        </div>
    </div>
</div>
