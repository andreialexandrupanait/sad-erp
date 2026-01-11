<x-app-layout>
    <x-slot name="pageTitle">Setari</x-slot>

    <div class="flex min-h-screen bg-slate-50" x-data="{
        activeSection: '{{ request()->get('section', 'application') }}',
        editingOption: null,
        addingToGroup: null,
        isSubmitting: false,

        slugify(text) {
            return text.toString().toLowerCase().trim()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '').replace(/-+$/, '');
        },

        async saveOption(category, optionId = null) {
            this.isSubmitting = true;
            const form = optionId ? document.getElementById('edit-form-' + optionId) : document.getElementById('add-form-' + category);
            const formData = new FormData(form);

            // Add category to form data for create operations
            if (!optionId) {
                formData.append('category', category);
            }

            let url = optionId
                ? `/settings/nomenclature/${optionId}`
                : `/settings/nomenclature`;
            let method = optionId ? 'PATCH' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData
                });
                if (response.ok) {
                    window.location.href = window.location.pathname + '?section=' + this.activeSection;
                } else {
                    alert('Eroare la salvare. Va rugam incercati din nou.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Eroare la salvare. Va rugam incercati din nou.');
            } finally {
                this.isSubmitting = false;
            }
        },

        async deleteOption(optionId) {
            if (!confirm('Sunteti sigur ca doriti sa stergeti aceasta optiune?')) return;
            try {
                const response = await fetch(`/settings/nomenclature/${optionId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                if (response.ok) {
                    window.location.href = window.location.pathname + '?section=' + this.activeSection;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    }">

        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-white border-r border-slate-200 flex-shrink-0">
            <div class="sticky top-0 overflow-y-auto max-h-screen">
                <div class="p-4 md:p-6">
                    <h1 class="text-xl font-bold text-slate-900">Settings</h1>
                    <p class="text-sm text-slate-500 mt-1">Manage your application</p>
                </div>

                <nav class="px-3 pb-6">
                    <!-- Application Settings -->
                    <div class="mb-6">
                        <div class="px-3 mb-2">
                            <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Application</h2>
                        </div>
                        <a href="?section=application"
                           :class="activeSection === 'application' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Application Settings
                        </a>
                    </div>

                    <!-- Nomenclatoare -->
                    <div>
                        <div class="px-3 mb-2">
                            <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nomenclatoare</h2>
                        </div>

                        <a href="?section=client_statuses"
                           :class="activeSection === 'client_statuses' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'"
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group">
                            <span>Status clienti</span>
                            <span class="text-xs text-slate-400 group-hover:text-slate-600">{{ $clientStatuses->count() }}</span>
                        </a>

                        <a href="?section=domain_statuses"
                           :class="activeSection === 'domain_statuses' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'"
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group">
                            <span>Status domenii</span>
                            <span class="text-xs text-slate-400 group-hover:text-slate-600">{{ $domainStatuses->count() }}</span>
                        </a>

                        <a href="?section=subscription_statuses"
                           :class="activeSection === 'subscription_statuses' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'"
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group">
                            <span>Status abonamente</span>
                            <span class="text-xs text-slate-400 group-hover:text-slate-600">{{ $subscriptionStatuses->count() }}</span>
                        </a>

                        <a href="?section=access_platforms"
                           :class="activeSection === 'access_platforms' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'"
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group">
                            <span>Categorii platforme</span>
                            <span class="text-xs text-slate-400 group-hover:text-slate-600">{{ $accessPlatforms->count() }}</span>
                        </a>

                        <a href="?section=expense_categories"
                           :class="activeSection === 'expense_categories' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'"
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group">
                            <span>Categorii cheltuieli</span>
                            <span class="text-xs text-slate-400 group-hover:text-slate-600">{{ $expenseCategories->count() }}</span>
                        </a>

                        <a href="?section=payment_methods"
                           :class="activeSection === 'payment_methods' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'"
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group">
                            <span>Metode de plata</span>
                            <span class="text-xs text-slate-400 group-hover:text-slate-600">{{ $paymentMethods->count() }}</span>
                        </a>

                        <a href="?section=billing_cycles"
                           :class="activeSection === 'billing_cycles' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'"
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group">
                            <span>Cicluri de facturare</span>
                            <span class="text-xs text-slate-400 group-hover:text-slate-600">{{ $subscriptionBillingCycles->count() }}</span>
                        </a>

                        <a href="?section=domain_registrars"
                           :class="activeSection === 'domain_registrars' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'"
                           class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group">
                            <span>Registratori de domenii</span>
                            <span class="text-xs text-slate-400 group-hover:text-slate-600">{{ $domainRegistrars->count() }}</span>
                        </a>
                    </div>

                    <!-- Integrations -->
                    <div class="mt-6">
                        <div class="px-3 mb-2">
                            <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Integrari</h2>
                        </div>
                        <a href="{{ route('settings.smartbill.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            SmartBill Import
                        </a>
                    </div>

                    <!-- Data Management -->
                    <div class="mt-6">
                        <div class="px-3 mb-2">
                            <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Gestionare Date</h2>
                        </div>
                        <a href="{{ route('settings.backup') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Backup / Import
                        </a>
                    </div>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <!-- Application Settings Section -->
            <div x-show="activeSection === 'application'" x-cloak>
                <div class="p-4 md:p-6">
                    <div class="mb-4">
                        <h2 class="text-xl font-bold text-slate-900">Setari aplicatie</h2>
                        <p class="text-sm text-slate-500 mt-1">Configureaza preferintele aplicatiei</p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                        <form method="POST" action="{{ route('settings.application.update') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Nume aplicatie</label>
                                    <input type="text" name="app_name" value="{{ $appSettings['app_name'] }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Limba</label>
                                    <select name="language" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                                        <option value="en" {{ $appSettings['language'] === 'en' ? 'selected' : '' }}>English</option>
                                        <option value="ro" {{ $appSettings['language'] === 'ro' ? 'selected' : '' }}>Română</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors">
                                    Salveaza modificari
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @php
            $nomenclature = [
                'client_statuses' => [
                    'title' => 'Status clienti',
                    'description' => 'Gestioneaza statusurile clientilor folosite in aplicatie',
                    'data' => $clientStatuses,
                    'has_colors' => true,
                ],
                'domain_statuses' => [
                    'title' => 'Status domenii',
                    'description' => 'Gestioneaza statusurile domeniilor',
                    'data' => $domainStatuses,
                    'has_colors' => false,
                ],
                'subscription_statuses' => [
                    'title' => 'Status abonamente',
                    'description' => 'Gestioneaza statusurile abonamentelor',
                    'data' => $subscriptionStatuses,
                    'has_colors' => false,
                ],
                'access_platforms' => [
                    'title' => 'Categorii platforme',
                    'description' => 'Gestioneaza tipurile de platforme de acces',
                    'data' => $accessPlatforms,
                    'has_colors' => false,
                ],
                'expense_categories' => [
                    'title' => 'Categorii cheltuieli',
                    'description' => 'Gestioneaza categoriile de cheltuieli',
                    'data' => $expenseCategories,
                    'has_colors' => true,
                ],
                'payment_methods' => [
                    'title' => 'Metode de plata',
                    'description' => 'Gestioneaza metodele de plata disponibile',
                    'data' => $paymentMethods,
                    'has_colors' => false,
                ],
                'billing_cycles' => [
                    'title' => 'Cicluri de facturare',
                    'description' => 'Gestioneaza ciclurile de facturare pentru abonamente',
                    'data' => $subscriptionBillingCycles,
                    'has_colors' => false,
                ],
                'domain_registrars' => [
                    'title' => 'Registratori de domenii',
                    'description' => 'Gestioneaza registratorii de domenii',
                    'data' => $domainRegistrars,
                    'has_colors' => false,
                ],
            ];
            @endphp

            <!-- Nomenclature Sections -->
            @foreach($nomenclature as $key => $section)
                <div x-show="activeSection === '{{ $key }}'" x-cloak>
                    <div class="p-4 md:p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">{{ $section['title'] }}</h2>
                                <p class="text-sm text-slate-500 mt-1">{{ $section['data']->count() }} optiuni</p>
                            </div>
                            <button @click="addingToGroup = addingToGroup === '{{ $key }}' ? null : '{{ $key }}'"
                                    class="px-4 py-2 text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="addingToGroup !== '{{ $key }}'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span x-show="addingToGroup !== '{{ $key }}'">Adauga optiune</span>
                                <span x-show="addingToGroup === '{{ $key }}'">Anuleaza</span>
                            </button>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                            <div class="p-4 md:p-6">
                                <!-- Add Form (For all nomenclature types) -->
                                <div x-show="addingToGroup === '{{ $key }}'" x-cloak class="mb-6 p-4 bg-slate-50 rounded-lg border border-slate-200">
                                    <form id="add-form-{{ $key }}" @submit.prevent="saveOption('{{ $key }}')">
                                        <div class="flex gap-3 items-start">
                                            <div class="flex-1">
                                                <input type="text" name="label" placeholder="Nume (ex: Activ)" required
                                                       @input="$el.form.querySelector('[name=value]').value = slugify($el.value)"
                                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                                            </div>
                                            <div class="flex-1">
                                                <input type="text" name="value" placeholder="Valoare (auto-generata)" required
                                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                                            </div>
                                            @if($section['has_colors'])
                                                <div class="w-20">
                                                    <input type="color" name="color" value="#3b82f6" class="w-full h-10 border border-slate-300 rounded-lg cursor-pointer">
                                                </div>
                                            @endif
                                            <div class="flex gap-2 flex-shrink-0">
                                                <button type="submit" :disabled="isSubmitting" class="px-4 py-2 text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors disabled:opacity-50">
                                                    Salveaza
                                                </button>
                                                <button type="button" @click="addingToGroup = null" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors">
                                                    Anuleaza
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- Options Table -->
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="border-b border-slate-200">
                                                <th class="text-left py-3 px-2 text-xs font-semibold text-slate-600 uppercase">Nume</th>
                                                <th class="text-left py-3 px-2 text-xs font-semibold text-slate-600 uppercase">Valoare</th>
                                                @if($section['has_colors'])
                                                    <th class="text-left py-3 px-2 text-xs font-semibold text-slate-600 uppercase w-20">Culoare</th>
                                                @endif
                                                <th class="text-left py-3 px-2 text-xs font-semibold text-slate-600 uppercase w-20">Status</th>
                                                <th class="text-right py-3 px-2 text-xs font-semibold text-slate-600 uppercase w-24">Actiuni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($section['data'] as $option)
                                                <tr class="border-b border-slate-100 hover:bg-slate-50"
                                                    x-data="{ editing: false }"
                                                    data-option-id="{{ $option->id }}">
                                                    <template x-if="!editing">
                                                        <td class="py-3 px-2 text-sm text-slate-900">{{ $option->label }}</td>
                                                    </template>
                                                    <template x-if="editing">
                                                        <td class="py-3 px-2">
                                                            <input type="text" name="label" value="{{ $option->label }}" required
                                                                   form="edit-form-{{ $option->id }}"
                                                                   @input="$el.form.querySelector('[name=value]').value = slugify($el.value)"
                                                                   class="w-full px-2 py-1 text-sm border border-slate-300 rounded">
                                                        </td>
                                                    </template>

                                                    <template x-if="!editing">
                                                        <td class="py-3 px-2 text-sm text-slate-500 font-mono">{{ $option->value }}</td>
                                                    </template>
                                                    <template x-if="editing">
                                                        <td class="py-3 px-2">
                                                            <input type="text" name="value" value="{{ $option->value }}" required
                                                                   form="edit-form-{{ $option->id }}"
                                                                   class="w-full px-2 py-1 text-sm border border-slate-300 rounded bg-slate-50">
                                                        </td>
                                                    </template>

                                                    @if($section['has_colors'])
                                                        <template x-if="!editing">
                                                            <td class="py-3 px-2">
                                                                @if($option->color_class)
                                                                    <div class="w-8 h-8 rounded border border-slate-300" style="background-color: {{ $option->color_class }}"></div>
                                                                @endif
                                                            </td>
                                                        </template>
                                                        <template x-if="editing">
                                                            <td class="py-3 px-2">
                                                                <input type="color" name="color" value="{{ $option->color_class ?? '#3b82f6' }}"
                                                                       form="edit-form-{{ $option->id }}"
                                                                       class="w-full h-8 border border-slate-300 rounded cursor-pointer">
                                                            </td>
                                                        </template>
                                                    @endif

                                                    <td class="py-3 px-2">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                            {{ $option->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $option->is_active ? 'Activ' : 'Inactiv' }}
                                                        </span>
                                                    </td>

                                                    <td class="py-3 px-2 text-right">
                                                        <template x-if="!editing">
                                                            <div class="flex justify-end gap-2">
                                                                <button @click="editing = true" class="p-1 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                    </svg>
                                                                </button>
                                                                <button @click="deleteOption({{ $option->id }})" class="p-1 text-red-600 hover:text-red-700 hover:bg-red-50 rounded">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </template>
                                                        <template x-if="editing">
                                                            <div class="flex justify-end gap-2">
                                                                <form id="edit-form-{{ $option->id }}" @submit.prevent="saveOption('{{ $key }}', {{ $option->id }})" class="contents"></form>
                                                                <button type="submit" form="edit-form-{{ $option->id }}" class="px-3 py-1 text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 rounded">
                                                                    Salveaza
                                                                </button>
                                                                <button @click="editing = false" class="px-3 py-1 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded">
                                                                    Anuleaza
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $section['has_colors'] ? '5' : '4' }}" class="py-8 text-center text-slate-500">
                                                        Nu exista optiuni disponibile.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </main>
    </div>
</x-app-layout>
