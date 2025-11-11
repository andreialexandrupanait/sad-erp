<x-app-layout>
    <x-slot name="pageTitle">Venituri</x-slot>

    <x-slot name="headerActions">
        <button @click="$dispatch('open-slide-panel', 'revenue-create')" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition-colors">
            + Adaugă venit
        </button>
    </x-slot>

    <div class="p-6" x-data>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <form method="GET" class="mb-6 flex gap-2 flex-wrap">
            <select name="year" class="rounded-lg border-slate-300">
                @foreach($availableYears as $availableYear)
                    <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                @endforeach
            </select>
            <select name="month" class="rounded-lg border-slate-300">
                <option value="">Toate lunile</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endfor
            </select>
            <select name="currency" class="rounded-lg border-slate-300">
                <option value="">Toate valutele</option>
                <option value="RON" {{ $currency == 'RON' ? 'selected' : '' }}>RON</option>
                <option value="EUR" {{ $currency == 'EUR' ? 'selected' : '' }}>EUR</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700">Filtrează</button>
        </form>

        <!-- Totals -->
        <div class="mb-4 flex gap-4">
            @foreach($totals as $curr => $total)
                <div class="px-4 py-2 bg-green-50 rounded-lg">
                    <span class="text-sm text-slate-600">Total {{ $curr }}:</span>
                    <span class="ml-2 font-bold text-green-700">{{ number_format($total, 2) }}</span>
                </div>
            @endforeach
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Dată</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Document</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Sumă</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Acțiuni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($revenues as $revenue)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $revenue->occurred_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-sm font-medium">{{ $revenue->document_name }}</td>
                            <td class="px-6 py-4 text-sm">{{ $revenue->client?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-green-600">{{ number_format($revenue->amount, 2) }} {{ $revenue->currency }}</td>
                            <td class="px-6 py-4 text-right text-sm space-x-2">
                                <button @click="$dispatch('open-slide-panel', 'revenue-edit-{{ $revenue->id }}')" class="text-blue-600 hover:text-blue-900">
                                    Editează
                                </button>
                                <form method="POST" action="{{ route('financial.revenues.destroy', $revenue) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Ești sigur?')" class="text-red-600 hover:text-red-900">Șterge</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-slate-500">Nu există venituri</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $revenues->links() }}
        </div>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

    <!-- Create Revenue Slide Panel -->
    <x-slide-panel name="revenue-create" :show="false" maxWidth="2xl">
        <!-- Header -->
        <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
            <h2 class="text-2xl font-bold text-slate-900">Adaugă venit nou</h2>
            <button type="button" @click="$dispatch('close-slide-panel', 'revenue-create')" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto px-8 py-6">
            <form id="revenue-create-form"
                x-data="{
                    loading: false,
                    async submit(event) {
                        event.preventDefault();
                        this.loading = true;

                        // Clear previous errors
                        document.querySelectorAll('#revenue-create-form .error-message').forEach(el => el.remove());

                        const formData = new FormData(event.target);

                        try {
                            const response = await fetch('{{ route('financial.revenues.store') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: formData
                            });

                            const data = await response.json();

                            if (response.ok) {
                                $dispatch('close-slide-panel', 'revenue-create');
                                $dispatch('toast', { message: 'Revenue created successfully!', type: 'success' });
                                setTimeout(() => window.location.reload(), 500);
                            } else {
                                if (data.errors) {
                                    Object.keys(data.errors).forEach(key => {
                                        const input = document.querySelector(`#revenue-create-form [name='${key}']`);
                                        if (input) {
                                            const wrapper = input.closest('.field-wrapper');
                                            if (wrapper) {
                                                const existingError = wrapper.querySelector('.error-message');
                                                if (existingError) existingError.remove();
                                                const errorDiv = document.createElement('p');
                                                errorDiv.className = 'error-message mt-2 text-sm text-red-600';
                                                errorDiv.textContent = data.errors[key][0];
                                                wrapper.appendChild(errorDiv);
                                            }
                                        }
                                    });
                                    $dispatch('toast', { message: 'Please correct the errors in the form.', type: 'error' });
                                }
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            $dispatch('toast', { message: 'An error occurred. Please try again.', type: 'error' });
                        } finally {
                            this.loading = false;
                        }
                    }
                }"
                @submit="submit"
            >
                @csrf
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="document_name_create">Nume document <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="text" name="document_name" id="document_name_create" required/>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="amount_create">Sumă <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="number" step="0.01" name="amount" id="amount_create" required/>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="currency_create">Valută <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.select name="currency" id="currency_create" required>
                                <option value="RON">RON</option>
                                <option value="EUR">EUR</option>
                            </x-ui.select>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="occurred_at_create">Dată <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="date" name="occurred_at" id="occurred_at_create" value="{{ now()->format('Y-m-d') }}" required/>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="client_id_create">Client</x-ui.label>
                        <div class="mt-2">
                            <x-ui.select name="client_id" id="client_id_create">
                                <option value="">Selectează client (opțional)</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    </div>

                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="note_create">Notă</x-ui.label>
                        <div class="mt-2">
                            <x-ui.textarea name="note" id="note_create" rows="3"></x-ui.textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
            <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel', 'revenue-create')">
                Anulează
            </x-ui.button>
            <x-ui.button type="submit" form="revenue-create-form" variant="default">
                Salvează venit
            </x-ui.button>
        </div>
    </x-slide-panel>

    <!-- Edit Revenue Slide Panels -->
    @foreach($revenues as $revenue)
        <x-slide-panel name="revenue-edit-{{ $revenue->id }}" :show="false" maxWidth="2xl">
            <!-- Header -->
            <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
                <h2 class="text-2xl font-bold text-slate-900">Editează venit</h2>
                <button type="button" @click="$dispatch('close-slide-panel', 'revenue-edit-{{ $revenue->id }}')" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto px-8 py-6">
                <form id="revenue-edit-form-{{ $revenue->id }}"
                    x-data="{
                        loading: false,
                        async submit(event) {
                            event.preventDefault();
                            this.loading = true;

                            // Clear previous errors
                            document.querySelectorAll('#revenue-edit-form-{{ $revenue->id }} .error-message').forEach(el => el.remove());

                            const formData = new FormData(event.target);

                            try {
                                const response = await fetch('{{ route('financial.revenues.update', $revenue) }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    },
                                    body: formData
                                });

                                const data = await response.json();

                                if (response.ok) {
                                    $dispatch('close-slide-panel', 'revenue-edit-{{ $revenue->id }}');
                                    $dispatch('toast', { message: 'Revenue updated successfully!', type: 'success' });
                                    setTimeout(() => window.location.reload(), 500);
                                } else {
                                    if (data.errors) {
                                        Object.keys(data.errors).forEach(key => {
                                            const input = document.querySelector(`#revenue-edit-form-{{ $revenue->id }} [name='${key}']`);
                                            if (input) {
                                                const wrapper = input.closest('.field-wrapper');
                                                if (wrapper) {
                                                    const existingError = wrapper.querySelector('.error-message');
                                                    if (existingError) existingError.remove();
                                                    const errorDiv = document.createElement('p');
                                                    errorDiv.className = 'error-message mt-2 text-sm text-red-600';
                                                    errorDiv.textContent = data.errors[key][0];
                                                    wrapper.appendChild(errorDiv);
                                                }
                                            }
                                        });
                                        $dispatch('toast', { message: 'Please correct the errors in the form.', type: 'error' });
                                    }
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                $dispatch('toast', { message: 'An error occurred. Please try again.', type: 'error' });
                            } finally {
                                this.loading = false;
                            }
                        }
                    }"
                    @submit="submit"
                >
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                        <div class="sm:col-span-6 field-wrapper">
                            <x-ui.label for="document_name_edit_{{ $revenue->id }}">Nume document <span class="text-red-500">*</span></x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="text" name="document_name" id="document_name_edit_{{ $revenue->id }}" value="{{ $revenue->document_name }}" required/>
                            </div>
                        </div>

                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="amount_edit_{{ $revenue->id }}">Sumă <span class="text-red-500">*</span></x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="number" step="0.01" name="amount" id="amount_edit_{{ $revenue->id }}" value="{{ $revenue->amount }}" required/>
                            </div>
                        </div>

                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="currency_edit_{{ $revenue->id }}">Valută <span class="text-red-500">*</span></x-ui.label>
                            <div class="mt-2">
                                <x-ui.select name="currency" id="currency_edit_{{ $revenue->id }}" required>
                                    <option value="RON" {{ $revenue->currency == 'RON' ? 'selected' : '' }}>RON</option>
                                    <option value="EUR" {{ $revenue->currency == 'EUR' ? 'selected' : '' }}>EUR</option>
                                </x-ui.select>
                            </div>
                        </div>

                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="occurred_at_edit_{{ $revenue->id }}">Dată <span class="text-red-500">*</span></x-ui.label>
                            <div class="mt-2">
                                <x-ui.input type="date" name="occurred_at" id="occurred_at_edit_{{ $revenue->id }}" value="{{ $revenue->occurred_at->format('Y-m-d') }}" required/>
                            </div>
                        </div>

                        <div class="sm:col-span-3 field-wrapper">
                            <x-ui.label for="client_id_edit_{{ $revenue->id }}">Client</x-ui.label>
                            <div class="mt-2">
                                <x-ui.select name="client_id" id="client_id_edit_{{ $revenue->id }}">
                                    <option value="">Selectează client (opțional)</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ $revenue->client_id == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                        </div>

                        <div class="sm:col-span-6 field-wrapper">
                            <x-ui.label for="note_edit_{{ $revenue->id }}">Notă</x-ui.label>
                            <div class="mt-2">
                                <x-ui.textarea name="note" id="note_edit_{{ $revenue->id }}" rows="3">{{ $revenue->note }}</x-ui.textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
                <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel', 'revenue-edit-{{ $revenue->id }}')">
                    Anulează
                </x-ui.button>
                <x-ui.button type="submit" form="revenue-edit-form-{{ $revenue->id }}" variant="default">
                    Actualizează venit
                </x-ui.button>
            </div>
        </x-slide-panel>
    @endforeach
</x-app-layout>
