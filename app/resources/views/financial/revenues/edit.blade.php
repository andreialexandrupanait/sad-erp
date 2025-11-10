<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold text-slate-900 mb-6">Editează venit</h1>

        <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
            <form method="POST" action="{{ route('financial.venituri.update', $revenue) }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nume document *</label>
                        <input type="text" name="document_name" value="{{ old('document_name', $revenue->document_name) }}" required
                               class="w-full rounded-lg border-slate-300">
                        @error('document_name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Sumă *</label>
                            <input type="number" step="0.01" name="amount" value="{{ old('amount', $revenue->amount) }}" required
                                   class="w-full rounded-lg border-slate-300">
                            @error('amount')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Valută *</label>
                            <select name="currency" required class="w-full rounded-lg border-slate-300">
                                <option value="RON" {{ old('currency', $revenue->currency) == 'RON' ? 'selected' : '' }}>RON</option>
                                <option value="EUR" {{ old('currency', $revenue->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Dată *</label>
                        <input type="date" name="occurred_at" value="{{ old('occurred_at', $revenue->occurred_at->format('Y-m-d')) }}" required
                               class="w-full rounded-lg border-slate-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Client</label>
                        <select name="client_id" class="w-full rounded-lg border-slate-300">
                            <option value="">Selectează client (opțional)</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $revenue->client_id) == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Notă</label>
                        <textarea name="note" rows="3" class="w-full rounded-lg border-slate-300">{{ old('note', $revenue->note) }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        Actualizează venit
                    </button>
                    <a href="{{ route('financial.venituri.index') }}" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">
                        Anulează
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
