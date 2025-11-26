<x-app-layout>
    <x-slot name="pageTitle">{{ __('Settings') }}</x-slot>

    @push('head')
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    @endpush

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto" x-data="{
            showForm: false,
            editingId: null,
            isSubmitting: false,
            formData: {
                id: null,
                label: '',
                value: '',
                color: '#3b82f6',
                parent_id: ''
            },

            slugify(text) {
                return text.toString().toLowerCase().trim()
                    .replace(/\s+/g, '-')
                    .replace(/[^\w\-]+/g, '')
                    .replace(/\-\-+/g, '-')
                    .replace(/^-+/, '')
                    .replace(/-+$/, '');
            },

            autoGenerateValue() {
                if (!this.formData.id) {
                    this.formData.value = this.slugify(this.formData.label);
                }
            },

            openAddForm(parentId = null) {
                this.editingId = null;
                this.formData = {
                    id: null,
                    label: '',
                    value: '',
                    color: '#3b82f6',
                    parent_id: parentId || ''
                };
                this.showForm = true;
            },

            openEditForm(id, label, color, parentId, isHierarchical = false) {
                if (isHierarchical) {
                    // For hierarchical views, use inline editing
                    this.showForm = false;
                    this.editingId = id;
                } else {
                    // For non-hierarchical views, use top form
                    this.editingId = null;
                    this.showForm = true;
                }
                this.formData = {
                    id: id,
                    label: label,
                    value: this.slugify(label), // Auto-generate from label
                    color: color || '#3b82f6',
                    parent_id: parentId || ''
                };
            },

            closeForm() {
                this.showForm = false;
                this.editingId = null;
                this.formData = {
                    id: null,
                    label: '',
                    value: '',
                    color: '#3b82f6',
                    parent_id: ''
                };
            },

            async saveOption() {
                this.isSubmitting = true;

                const data = {
                    category: '{{ $category }}',
                    label: this.formData.label,
                    value: this.formData.value,
                    color: this.formData.color,
                    parent_id: this.formData.parent_id || null
                };

                const url = this.formData.id
                    ? '/settings/nomenclature/' + this.formData.id
                    : '/settings/nomenclature';
                const method = this.formData.id ? 'PATCH' : 'POST';

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const responseData = await response.json();

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        const errorMsg = responseData.message || 'Eroare la salvare. Va rugam incercati din nou.';
                        alert(errorMsg);
                        console.error('Error response:', responseData);
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
                    const response = await fetch('/settings/nomenclature/' + optionId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const responseData = await response.json();

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        const errorMsg = responseData.message || 'Eroare la stergere. Va rugam incercati din nou.';
                        alert(errorMsg);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Eroare la stergere. Va rugam incercati din nou.');
                }
            }
        }">
            <div class="p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">{{ $title }}</h2>
                        <p class="text-sm text-slate-500 mt-1">{{ $data->count() }} {{ isset($isHierarchical) && $isHierarchical ? 'categorii' : 'optiuni' }}</p>
                    </div>
                    <button @click="openAddForm()" class="px-4 py-2 text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Adauga {{ isset($isHierarchical) && $isHierarchical ? 'categorie' : 'optiune' }}</span>
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                    <div class="p-6">
                        <!-- Add Form (Compact) -->
                        <div x-show="showForm" x-cloak class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <form @submit.prevent="saveOption()">
                                @if(isset($isHierarchical) && $isHierarchical)
                                    <!-- Parent Category Selection -->
                                    <div class="mb-2">
                                        <select x-model="formData.parent_id" class="w-full px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">-- Categorie principala --</option>
                                            @foreach($data as $parentOption)
                                                <option value="{{ $parentOption->id }}">{{ $parentOption->label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="flex items-center gap-2">
                                    <!-- Name -->
                                    <input type="text" x-model="formData.label" @input="autoGenerateValue()" required
                                           class="flex-1 px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="Nume">

                                    @if($hasColors)
                                        <!-- Color (only for parent categories) -->
                                        <template x-if="!formData.parent_id || formData.parent_id === ''">
                                            <input type="color" x-model="formData.color" class="w-12 h-8 border border-slate-300 rounded cursor-pointer">
                                        </template>
                                        <template x-if="formData.parent_id && formData.parent_id !== ''">
                                            <div class="w-12"></div>
                                        </template>
                                    @endif

                                    <!-- Actions -->
                                    <button type="submit" :disabled="isSubmitting" class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition-colors disabled:opacity-50 whitespace-nowrap">
                                        <span x-show="!isSubmitting">Salveaza</span>
                                        <span x-show="isSubmitting">...</span>
                                    </button>
                                    <button type="button" @click="closeForm()" class="px-3 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded transition-colors">
                                        Anuleaza
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Options Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase">Nume</th>
                                        @if($hasColors)
                                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase w-20">Culoare</th>
                                        @endif
                                        <th class="text-left py-3 px-4 text-xs font-semibold text-slate-600 uppercase w-20">Status</th>
                                        <th class="text-right py-3 px-4 text-xs font-semibold text-slate-600 uppercase w-32">Actiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($isHierarchical) && $isHierarchical)
                                        {{-- HIERARCHICAL VIEW --}}
                                        @forelse($data as $parent)
                                            {{-- Parent Row --}}
                                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors {{ $parent->children->count() > 0 ? 'bg-blue-50/50' : '' }}">
                                                <td class="py-3 px-4">
                                                    <div class="flex items-center gap-2">
                                                        @if($parent->children->count() > 0)
                                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                            </svg>
                                                        @endif
                                                        <span class="font-semibold text-slate-900">{{ $parent->label }}</span>
                                                        @if($parent->children->count() > 0)
                                                            <span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">
                                                                {{ $parent->children->count() }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                                @if($hasColors)
                                                    <td class="py-3 px-4">
                                                        @if($parent->color_class)
                                                            <div class="w-8 h-8 rounded border border-slate-300" style="background-color: {{ $parent->color_class }}"></div>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td class="py-3 px-4">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $parent->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $parent->is_active ? 'Activ' : 'Inactiv' }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 text-right">
                                                    <div class="flex justify-end gap-1">
                                                        <button @click="openAddForm({{ $parent->id }})" class="p-1.5 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded" title="Adauga subcategorie">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        </button>
                                                        <button @click="openEditForm({{ $parent->id }}, '{{ addslashes($parent->label) }}', '{{ $parent->color_class ?? '#3b82f6' }}', {{ $parent->parent_id ?? 'null' }}, true)"
                                                                class="p-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded" title="Editeaza">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </button>
                                                        <button @click="deleteOption({{ $parent->id }})" class="p-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded" title="Sterge">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            {{-- Inline Edit Form for Parent --}}
                                            <tr x-show="editingId === {{ $parent->id }}" x-cloak class="bg-yellow-50 border-l-4 border-l-yellow-400">
                                                <td colspan="{{ $hasColors ? '4' : '3' }}" class="py-2 px-4">
                                                    <form @submit.prevent="saveOption()" class="flex items-center gap-2">
                                                        <!-- Parent Selector -->
                                                        <select x-model="formData.parent_id" class="px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500">
                                                            <option value="">-- Principal --</option>
                                                            @foreach($data as $parentOption)
                                                                @if($parentOption->id != $parent->id)
                                                                    <option value="{{ $parentOption->id }}">{{ $parentOption->label }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>

                                                        <!-- Name -->
                                                        <input type="text" x-model="formData.label" @input="autoGenerateValue()" required
                                                               class="flex-1 px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500"
                                                               placeholder="Nume">

                                                        @if($hasColors)
                                                            <!-- Color (only for parent categories) -->
                                                            <input type="color" x-show="!formData.parent_id || formData.parent_id === ''" x-model="formData.color" class="w-12 h-8 border border-slate-300 rounded cursor-pointer">
                                                            <div x-show="formData.parent_id && formData.parent_id !== ''" class="w-12"></div>
                                                        @endif

                                                        <!-- Actions -->
                                                        <button type="submit" :disabled="isSubmitting" class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded transition-colors disabled:opacity-50 whitespace-nowrap">
                                                            Salveaza
                                                        </button>
                                                        <button type="button" @click="closeForm()" class="px-3 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded transition-colors">
                                                            Anuleaza
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>

                                            {{-- Child Rows --}}
                                            @foreach($parent->children as $child)
                                                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors bg-green-50/30">
                                                    <td class="py-3 px-4 pl-12">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-green-500 font-bold">└─</span>
                                                            <span class="text-slate-700">{{ $child->label }}</span>
                                                        </div>
                                                    </td>
                                                    @if($hasColors)
                                                        <td class="py-3 px-4">
                                                            @if($child->color_class)
                                                                <div class="w-8 h-8 rounded border border-slate-300" style="background-color: {{ $child->color_class }}"></div>
                                                            @endif
                                                        </td>
                                                    @endif
                                                    <td class="py-3 px-4">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $child->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $child->is_active ? 'Activ' : 'Inactiv' }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 px-4 text-right">
                                                        <div class="flex justify-end gap-1">
                                                            <button @click="openEditForm({{ $child->id }}, '{{ addslashes($child->label) }}', '{{ $child->color_class ?? '#3b82f6' }}', {{ $child->parent_id ?? 'null' }}, true)"
                                                                    class="p-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded" title="Editeaza">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                            </button>
                                                            <button @click="deleteOption({{ $child->id }})" class="p-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded" title="Sterge">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>

                                                {{-- Inline Edit Form for Child --}}
                                                <tr x-show="editingId === {{ $child->id }}" x-cloak class="bg-yellow-50 border-l-4 border-l-yellow-400">
                                                    <td colspan="{{ $hasColors ? '4' : '3' }}" class="py-2 px-4 pl-12">
                                                        <form @submit.prevent="saveOption()" class="flex items-center gap-2">
                                                            <!-- Parent Selector -->
                                                            <select x-model="formData.parent_id" class="px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500">
                                                                <option value="">-- Principal --</option>
                                                                @foreach($data as $parentOption)
                                                                    <option value="{{ $parentOption->id }}">{{ $parentOption->label }}</option>
                                                                @endforeach
                                                            </select>

                                                            <!-- Name -->
                                                            <input type="text" x-model="formData.label" @input="autoGenerateValue()" required
                                                                   class="flex-1 px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500"
                                                                   placeholder="Nume">

                                                            @if($hasColors)
                                                                <!-- Color (only for parent categories) -->
                                                                <input type="color" x-show="!formData.parent_id || formData.parent_id === ''" x-model="formData.color" class="w-12 h-8 border border-slate-300 rounded cursor-pointer">
                                                                <div x-show="formData.parent_id && formData.parent_id !== ''" class="w-12"></div>
                                                            @endif

                                                            <!-- Actions -->
                                                            <button type="submit" :disabled="isSubmitting" class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded transition-colors disabled:opacity-50 whitespace-nowrap">
                                                                Salveaza
                                                            </button>
                                                            <button type="button" @click="closeForm()" class="px-3 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded transition-colors">
                                                                Anuleaza
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="{{ $hasColors ? '4' : '3' }}" class="py-8 text-center text-slate-500">
                                                    Nu exista categorii disponibile.
                                                </td>
                                            </tr>
                                        @endforelse
                                    @else
                                        {{-- NON-HIERARCHICAL VIEW --}}
                                        @forelse($data as $option)
                                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                                <td class="py-3 px-4 text-sm text-slate-900">{{ $option->label }}</td>
                                                @if($hasColors)
                                                    <td class="py-3 px-4">
                                                        @if($option->color_class)
                                                            <div class="w-8 h-8 rounded border border-slate-300" style="background-color: {{ $option->color_class }}"></div>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td class="py-3 px-4">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $option->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $option->is_active ? 'Activ' : 'Inactiv' }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 text-right">
                                                    <div class="flex justify-end gap-1">
                                                        <button @click="openEditForm({{ $option->id }}, '{{ addslashes($option->label) }}', '{{ $option->color_class ?? '#3b82f6' }}', null, true)"
                                                                class="p-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded" title="Editeaza">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </button>
                                                        <button @click="deleteOption({{ $option->id }})" class="p-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded" title="Sterge">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            {{-- Inline Edit Form --}}
                                            <tr x-show="editingId === {{ $option->id }}" x-cloak class="bg-yellow-50 border-l-4 border-l-yellow-400">
                                                <td colspan="{{ $hasColors ? '4' : '3' }}" class="py-2 px-4">
                                                    <form @submit.prevent="saveOption()" class="flex items-center gap-2">
                                                        <!-- Name -->
                                                        <input type="text" x-model="formData.label" @input="autoGenerateValue()" required
                                                               class="flex-1 px-2 py-1.5 text-sm border border-slate-300 rounded focus:ring-1 focus:ring-blue-500"
                                                               placeholder="Nume">

                                                        @if($hasColors)
                                                            <!-- Color -->
                                                            <input type="color" x-model="formData.color" class="w-12 h-8 border border-slate-300 rounded cursor-pointer">
                                                        @endif

                                                        <!-- Actions -->
                                                        <button type="submit" :disabled="isSubmitting" class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded transition-colors disabled:opacity-50 whitespace-nowrap">
                                                            Salveaza
                                                        </button>
                                                        <button type="button" @click="closeForm()" class="px-3 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded transition-colors">
                                                            Anuleaza
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $hasColors ? '4' : '3' }}" class="py-8 text-center text-slate-500">
                                                    Nu exista optiuni disponibile.
                                                </td>
                                            </tr>
                                        @endforelse
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
