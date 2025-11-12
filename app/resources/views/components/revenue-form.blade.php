@props(['revenue' => null, 'clients' => [], 'currencies' => [], 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-ui.card-content>
            <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <!-- Document Name (Required) -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="document_name">
                        Nume document <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="document_name"
                            id="document_name"
                            required
                            placeholder="Enter revenue description"
                            value="{{ old('document_name', $revenue->document_name ?? '') }}"
                        />
                    </div>
                </div>

                <!-- Amount -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="amount">
                        Sumă <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="number"
                            step="0.01"
                            name="amount"
                            id="amount"
                            required
                            placeholder="0.00"
                            value="{{ old('amount', $revenue->amount ?? '') }}"
                        />
                    </div>
                </div>

                <!-- Currency -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="currency">
                        Valută <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="currency" id="currency" required>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->value }}" {{ old('currency', $revenue->currency ?? 'RON') == $currency->value ? 'selected' : '' }}>
                                    {{ $currency->label }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </div>

                <!-- Date -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="occurred_at">
                        Dată <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="date"
                            name="occurred_at"
                            id="occurred_at"
                            required
                            placeholder="YYYY-MM-DD"
                            value="{{ old('occurred_at', $revenue ? $revenue->occurred_at?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                        />
                    </div>
                </div>

                <!-- Client -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="client_id">Client</x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="client_id" id="client_id">
                            <option value="">Selectează client (opțional)</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $revenue->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                    {{ $client->display_name }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </div>

                <!-- Notes -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="note">Notă</x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea name="note" id="note" rows="3" placeholder="Additional notes about this revenue (optional)">{{ old('note', $revenue->note ?? '') }}</x-ui.textarea>
                    </div>
                </div>
            </div>
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-6 border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('financial.revenues.index') }}'">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $revenue ? 'Update Revenue' : 'Create Revenue' }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
