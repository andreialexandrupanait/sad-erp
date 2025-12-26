<x-app-layout>
    <x-slot name="pageTitle">{{ __("Catalog servicii") }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
                        <a href="{{ route('settings.business') }}" class="hover:text-slate-700">{{ __('Setări afacere') }}</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span>{{ __('Catalog servicii') }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ __('Catalog servicii') }}</h1>
                    <p class="text-slate-500 mt-1">{{ __('Gestionează serviciile oferite de organizația ta') }}</p>
                </div>

                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                @endif

                <div class="bg-white rounded-[10px] border border-slate-200 overflow-hidden">
                    <!-- Section Header -->
                    <div class="px-6 py-4 bg-slate-100 border-b border-slate-200 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Adaugă serviciu nou') }}</h3>
                    </div>

                    <!-- Add new service form -->
                    <div class="p-6">
                        <form id="addServiceForm" action="{{ route('settings.services.store') }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Nume serviciu') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" required
                                           class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                           placeholder="{{ __('ex: Dezvoltare web') }}">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Descriere') }}</label>
                                    <input type="text" name="description"
                                           class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                           placeholder="{{ __('Descriere opțională') }}">
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Tarif') }}</label>
                                    <input type="number" name="default_rate" step="0.01" min="0"
                                           class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                           placeholder="0.00">
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Moneda') }}</label>
                                    <select name="currency" class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                        <option value="RON">RON</option>
                                        <option value="EUR">EUR</option>
                                        <option value="USD">USD</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Unitate') }}</label>
                                    <select name="unit" class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                        @foreach(\App\Models\Service::UNITS as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <button type="submit" class="w-full h-10 px-4 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 whitespace-nowrap">
                                        {{ __('Adaugă serviciu') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Services List Header -->
                    <div class="px-6 py-4 bg-slate-100 border-t border-b border-slate-200 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Lista servicii') }}</h3>
                    </div>

                    <!-- Services table -->
                    @if($services->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full" id="servicesTable">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-xs font-medium text-slate-500 uppercase tracking-wider bg-slate-100">
                                        <th class="px-6 py-4">{{ __('Serviciu') }}</th>
                                        <th class="px-6 py-4 w-32">{{ __('Tarif') }}</th>
                                        <th class="px-6 py-4 w-24">{{ __('Moneda') }}</th>
                                        <th class="px-6 py-4 w-28">{{ __('Unitate') }}</th>
                                        <th class="px-6 py-4 text-center w-24">{{ __('Status') }}</th>
                                        <th class="px-6 py-4 w-28"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($services as $service)
                                    <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50 service-row" data-id="{{ $service->id }}">
                                        <!-- View Mode -->
                                        <td class="px-6 py-4 view-mode">
                                            <div class="text-sm font-medium text-slate-900">{{ $service->name }}</div>
                                            @if($service->description)
                                                <div class="text-xs text-slate-500 mt-0.5">{{ Str::limit($service->description, 60) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 view-mode">
                                            <div class="text-sm text-slate-900">{{ $service->default_rate ? number_format($service->default_rate, 2, ',', '.') : '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 view-mode">
                                            <div class="text-sm text-slate-700">{{ $service->currency }}</div>
                                        </td>
                                        <td class="px-6 py-4 view-mode">
                                            <div class="text-sm text-slate-700">{{ $service->unit_label }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center view-mode">
                                            @if($service->is_active)
                                                <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">{{ __("Activ") }}</span>
                                            @else
                                                <span class="px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-500 rounded-full">{{ __("Inactiv") }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 view-mode">
                                            <div class="flex items-center justify-end gap-1">
                                                <button type="button" onclick="startEdit({{ $service->id }})" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded" title="{{ __('Editează') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <form method="POST" action="{{ route('settings.services.destroy', $service) }}" class="inline" onsubmit="return confirm('{{ __("Sigur doriți să ștergeți acest serviciu?") }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded" title="{{ __('Șterge') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>

                                        <!-- Edit Mode (hidden by default) -->
                                        <td class="px-6 py-4 edit-mode hidden" colspan="6">
                                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                                                <div class="md:col-span-3">
                                                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Nume') }}</label>
                                                    <input type="text" name="name" value="{{ $service->name }}" required
                                                           class="w-full h-9 border border-slate-300 rounded px-2 text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                                </div>
                                                <div class="md:col-span-3">
                                                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Descriere') }}</label>
                                                    <input type="text" name="description" value="{{ $service->description }}"
                                                           class="w-full h-9 border border-slate-300 rounded px-2 text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500"
                                                           placeholder="{{ __('Descriere opțională') }}">
                                                </div>
                                                <div class="md:col-span-1">
                                                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Tarif') }}</label>
                                                    <input type="number" name="default_rate" value="{{ $service->default_rate }}" step="0.01" min="0"
                                                           class="w-full h-9 border border-slate-300 rounded px-2 text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                                </div>
                                                <div class="md:col-span-1">
                                                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Moneda') }}</label>
                                                    <select name="currency" class="w-full h-9 border border-slate-300 rounded px-2 text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                                        <option value="RON" {{ $service->currency === 'RON' ? 'selected' : '' }}>RON</option>
                                                        <option value="EUR" {{ $service->currency === 'EUR' ? 'selected' : '' }}>EUR</option>
                                                        <option value="USD" {{ $service->currency === 'USD' ? 'selected' : '' }}>USD</option>
                                                    </select>
                                                </div>
                                                <div class="md:col-span-1">
                                                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Unitate') }}</label>
                                                    <select name="unit" class="w-full h-9 border border-slate-300 rounded px-2 text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                                        @foreach(\App\Models\Service::UNITS as $value => $label)
                                                            <option value="{{ $value }}" {{ $service->unit === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="md:col-span-1">
                                                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Activ') }}</label>
                                                    <label class="inline-flex items-center cursor-pointer mt-1.5">
                                                        <input type="checkbox" name="is_active" {{ $service->is_active ? 'checked' : '' }} class="sr-only peer">
                                                        <div class="relative w-9 h-5 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-slate-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                                                    </label>
                                                </div>
                                                <div class="md:col-span-2 flex items-end gap-1 pb-0.5">
                                                    <button type="button" onclick="saveEdit({{ $service->id }})" class="h-9 px-3 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        {{ __('Salvează') }}
                                                    </button>
                                                    <button type="button" onclick="cancelEdit({{ $service->id }})" class="h-9 px-3 bg-slate-100 text-slate-700 text-sm font-medium rounded hover:bg-slate-200 flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        {{ __('Anulează') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12 text-sm text-slate-500">
                            {{ __('Nu ai definit încă niciun serviciu. Adaugă primul serviciu folosind formularul de mai sus.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let currentEditingId = null;

        function startEdit(id) {
            // Cancel any existing edit
            if (currentEditingId && currentEditingId !== id) {
                cancelEdit(currentEditingId);
            }

            const row = document.querySelector(`.service-row[data-id="${id}"]`);
            if (!row) return;

            // Hide view mode cells, show edit mode cell
            row.querySelectorAll('.view-mode').forEach(el => el.classList.add('hidden'));
            row.querySelectorAll('.edit-mode').forEach(el => el.classList.remove('hidden'));

            currentEditingId = id;
        }

        function cancelEdit(id) {
            const row = document.querySelector(`.service-row[data-id="${id}"]`);
            if (!row) return;

            // Show view mode, hide edit mode
            row.querySelectorAll('.view-mode').forEach(el => el.classList.remove('hidden'));
            row.querySelectorAll('.edit-mode').forEach(el => el.classList.add('hidden'));

            currentEditingId = null;
        }

        function saveEdit(id) {
            const row = document.querySelector(`.service-row[data-id="${id}"]`);
            if (!row) return;

            // Get values from edit inputs
            const name = row.querySelector('.edit-mode input[name="name"]').value;
            const description = row.querySelector('.edit-mode input[name="description"]').value;
            const defaultRate = row.querySelector('.edit-mode input[name="default_rate"]').value;
            const currency = row.querySelector('.edit-mode select[name="currency"]').value;
            const unit = row.querySelector('.edit-mode select[name="unit"]').value;
            const isActive = row.querySelector('.edit-mode input[name="is_active"]').checked;

            // Validate
            if (!name.trim()) {
                alert('{{ __("Numele serviciului este obligatoriu") }}');
                return;
            }

            // Send AJAX request
            fetch(`{{ url('settings/services') }}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    description: description || null,
                    default_rate: defaultRate || null,
                    currency: currency,
                    unit: unit,
                    is_active: isActive
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to show updated data
                    window.location.reload();
                } else {
                    alert(data.message || '{{ __("A apărut o eroare") }}');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("A apărut o eroare la salvare") }}');
            });
        }

        // Handle Escape key to cancel edit
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && currentEditingId) {
                cancelEdit(currentEditingId);
            }
        });
    </script>
    @endpush
</x-app-layout>
