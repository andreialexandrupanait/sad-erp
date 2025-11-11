@props(['expense' => null, 'categories' => [], 'action', 'method' => 'POST'])

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
                        Document Name <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="document_name"
                            id="document_name"
                            required
                            value="{{ old('document_name', $expense->document_name ?? '') }}"
                        />
                    </div>
                    @error('document_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="amount">
                        Amount <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="number"
                            step="0.01"
                            name="amount"
                            id="amount"
                            required
                            value="{{ old('amount', $expense->amount ?? '') }}"
                        />
                    </div>
                    @error('amount')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Currency -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="currency">
                        Currency <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="currency" id="currency" required>
                            <option value="RON" {{ old('currency', $expense->currency ?? 'RON') == 'RON' ? 'selected' : '' }}>RON</option>
                            <option value="EUR" {{ old('currency', $expense->currency ?? '') == 'EUR' ? 'selected' : '' }}>EUR</option>
                            <option value="USD" {{ old('currency', $expense->currency ?? '') == 'USD' ? 'selected' : '' }}>USD</option>
                        </x-ui.select>
                    </div>
                    @error('currency')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="occurred_at">
                        Date <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="date"
                            name="occurred_at"
                            id="occurred_at"
                            required
                            value="{{ old('occurred_at', $expense ? $expense->occurred_at?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                        />
                    </div>
                    @error('occurred_at')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="category_option_id">Category</x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="category_option_id" id="category_option_id">
                            <option value="">Select Category (Optional)</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_option_id', $expense->category_option_id ?? '') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    @error('category_option_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="note">Notes</x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea name="note" id="note" rows="3">{{ old('note', $expense->note ?? '') }}</x-ui.textarea>
                    </div>
                    @error('note')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-6 border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('financial.expenses.index') }}'">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $expense ? 'Update Expense' : 'Create Expense' }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
