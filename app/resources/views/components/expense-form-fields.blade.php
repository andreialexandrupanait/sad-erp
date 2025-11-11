@props(['expense' => null, 'categories' => [], 'idSuffix' => ''])

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
                value="{{ old('document_name', $expense->document_name ?? '') }}"
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
                value="{{ old('amount', $expense->amount ?? '') }}"
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
                <option value="RON" {{ old('currency', $expense->currency ?? 'RON') == 'RON' ? 'selected' : '' }}>RON</option>
                <option value="EUR" {{ old('currency', $expense->currency ?? '') == 'EUR' ? 'selected' : '' }}>EUR</option>
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
                value="{{ old('occurred_at', $expense ? $expense->occurred_at?->format('Y-m-d') : now()->format('Y-m-d')) }}"
            />
        </div>
    </div>

    <!-- Category -->
    <div class="sm:col-span-3 field-wrapper">
        <x-ui.label for="category_option_id{{ $idSuffix }}">Categorie</x-ui.label>
        <div class="mt-2">
            <x-ui.select name="category_option_id" id="category_option_id{{ $idSuffix }}">
                <option value="">Selectează categorie (opțional)</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_option_id', $expense->category_option_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->option_label }}
                    </option>
                @endforeach
            </x-ui.select>
        </div>
    </div>

    <!-- Notes -->
    <div class="sm:col-span-6 field-wrapper">
        <x-ui.label for="note{{ $idSuffix }}">Notă</x-ui.label>
        <div class="mt-2">
            <x-ui.textarea name="note" id="note{{ $idSuffix }}" rows="3">{{ old('note', $expense->note ?? '') }}</x-ui.textarea>
        </div>
    </div>
</div>
