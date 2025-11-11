@props(['revenue' => null, 'clients' => [], 'idSuffix' => ''])

<div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
    <!-- Document Name (Required) -->
    <div class="sm:col-span-6 field-wrapper">
        <x-ui.label for="document_name{{ $idSuffix }}">
            Nume document <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="text"
                name="document_name"
                id="document_name{{ $idSuffix }}"
                required
                value="{{ old('document_name', $revenue->document_name ?? '') }}"
            />
        </div>
    </div>

    <!-- Amount -->
    <div class="sm:col-span-3 field-wrapper">
        <x-ui.label for="amount{{ $idSuffix }}">
            Sumă <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="number"
                step="0.01"
                name="amount"
                id="amount{{ $idSuffix }}"
                required
                value="{{ old('amount', $revenue->amount ?? '') }}"
            />
        </div>
    </div>

    <!-- Currency -->
    <div class="sm:col-span-3 field-wrapper">
        <x-ui.label for="currency{{ $idSuffix }}">
            Valută <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-2">
            <x-ui.select name="currency" id="currency{{ $idSuffix }}" required>
                <option value="RON" {{ old('currency', $revenue->currency ?? 'RON') == 'RON' ? 'selected' : '' }}>RON</option>
                <option value="EUR" {{ old('currency', $revenue->currency ?? '') == 'EUR' ? 'selected' : '' }}>EUR</option>
            </x-ui.select>
        </div>
    </div>

    <!-- Date -->
    <div class="sm:col-span-3 field-wrapper">
        <x-ui.label for="occurred_at{{ $idSuffix }}">
            Dată <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="date"
                name="occurred_at"
                id="occurred_at{{ $idSuffix }}"
                required
                value="{{ old('occurred_at', $revenue ? $revenue->occurred_at?->format('Y-m-d') : now()->format('Y-m-d')) }}"
            />
        </div>
    </div>

    <!-- Client -->
    <div class="sm:col-span-3 field-wrapper">
        <x-ui.label for="client_id{{ $idSuffix }}">Client</x-ui.label>
        <div class="mt-2">
            <x-ui.select name="client_id" id="client_id{{ $idSuffix }}">
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
        <x-ui.label for="note{{ $idSuffix }}">Notă</x-ui.label>
        <div class="mt-2">
            <x-ui.textarea name="note" id="note{{ $idSuffix }}" rows="3">{{ old('note', $revenue->note ?? '') }}</x-ui.textarea>
        </div>
    </div>
</div>
