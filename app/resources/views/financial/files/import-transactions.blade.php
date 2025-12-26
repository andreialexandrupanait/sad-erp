<x-app-layout>
    <x-slot name="pageTitle">{{ __('Import Tranzactii din Extras') }}</x-slot>

    <div class="p-6 space-y-6">
        <!-- Back Button & File Info -->
        <div>
            <a href="{{ route('financial.files.category', ['year' => $file->an, 'month' => $file->luna, 'category' => 'extrase']) }}"
               class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('Inapoi la fisiere') }}
            </a>

            <x-ui.card>
                <x-ui.card-content>
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-slate-900">{{ __('Import Tranzactii') }}</h1>
                            <p class="text-sm text-slate-600 mt-1">{{ $file->file_name }}</p>
                        </div>
                        <x-ui.badge variant="secondary" class="bg-blue-100 text-blue-800 border-blue-200">
                            {{ $metadata['currency'] ?? 'RON' }}
                        </x-ui.badge>
                    </div>

                    @if(!empty($metadata))
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            @if($metadata['account_name'])
                                <div>
                                    <span class="text-slate-500">{{ __('Cont') }}:</span>
                                    <span class="font-medium text-slate-900 ml-1">{{ $metadata['account_name'] }}</span>
                                </div>
                            @endif
                            @if($metadata['iban'])
                                <div>
                                    <span class="text-slate-500">IBAN:</span>
                                    <span class="font-medium text-slate-900 ml-1">{{ $metadata['iban'] }}</span>
                                </div>
                            @endif
                            @if($metadata['period_start'] && $metadata['period_end'])
                                <div>
                                    <span class="text-slate-500">{{ __('Perioada') }}:</span>
                                    <span class="font-medium text-slate-900 ml-1">
                                        {{ \Carbon\Carbon::parse($metadata['period_start'])->format('d.m.Y') }} -
                                        {{ \Carbon\Carbon::parse($metadata['period_end'])->format('d.m.Y') }}
                                    </span>
                                </div>
                            @endif
                            @if($metadata['opening_balance'] !== null)
                                <div>
                                    <span class="text-slate-500">{{ __('Sold initial') }}:</span>
                                    <span class="font-medium text-slate-900 ml-1">{{ number_format($metadata['opening_balance'], 2) }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </x-ui.card-content>
            </x-ui.card>
        </div>

        @if(count($transactions) === 0)
            <x-ui.card>
                <x-ui.empty-state
                    icon="document"
                    :title="__('Nu s-au gasit tranzactii')"
                    :description="__('Fisierul PDF nu contine tranzactii ce pot fi importate sau formatul nu este recunoscut.')"
                />
            </x-ui.card>
        @else
            <form action="{{ route('financial.files.process-import-transactions', $file) }}" method="POST" x-data="importTransactions()">
                @csrf
                <input type="hidden" name="currency" value="{{ $metadata['currency'] ?? 'RON' }}">

                <!-- Summary & Actions -->
                <x-ui.card class="mb-6">
                    <x-ui.card-content>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="text-sm text-slate-600">
                                    <span class="font-semibold text-slate-900" x-text="selectedCount"></span> {{ __('selectate din') }} {{ count($transactions) }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <x-ui.button type="button" variant="ghost" size="sm" @click="selectAllNew()">
                                        {{ __('Selecteaza toate noi') }}
                                    </x-ui.button>
                                    <x-ui.button type="button" variant="ghost" size="sm" @click="deselectAll()">
                                        {{ __('Deselecteaza toate') }}
                                    </x-ui.button>
                                </div>
                            </div>
                            <x-ui.button type="submit" variant="default"
                                         x-bind:disabled="selectedCount === 0"
                                         x-bind:class="{ 'opacity-50 cursor-not-allowed': selectedCount === 0 }">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                {{ __('Importa Selectate') }}
                            </x-ui.button>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                <!-- Transactions Table -->
                <x-ui.card>
                    <div class="overflow-x-auto">
                        <table class="w-full caption-bottom text-sm">
                            <thead class="bg-slate-100">
                                <tr class="border-b border-slate-200">
                                    <th class="px-3 py-3 text-left align-middle font-medium text-slate-600 w-10">
                                        <input type="checkbox"
                                               @change="toggleAll($event.target.checked)"
                                               :checked="allSelected"
                                               class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950 focus:ring-offset-2">
                                    </th>
                                    <th class="px-3 py-3 text-left align-middle font-medium text-slate-600 w-32">{{ __('Data') }}</th>
                                    <th class="px-3 py-3 text-left align-middle font-medium text-slate-600">{{ __('Descriere') }}</th>
                                    <th class="px-3 py-3 text-right align-middle font-medium text-slate-600 w-28">{{ __('Suma') }}</th>
                                    <th class="px-3 py-3 text-center align-middle font-medium text-slate-600 w-20">{{ __('Tip') }}</th>
                                    <th class="px-3 py-3 text-left align-middle font-medium text-slate-600 w-56">{{ __('Categorie') }}</th>
                                    <th class="px-3 py-3 text-center align-middle font-medium text-slate-600 w-24">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="[&_tr:last-child]:border-0">
                                @foreach($transactions as $index => $tx)
                                    <tr class="border-b transition-colors hover:bg-slate-50/50" x-bind:class="{ 'bg-blue-50': transactions[{{ $index }}].selected }">
                                        <!-- Checkbox -->
                                        <td class="px-3 py-2 align-middle">
                                            <input type="checkbox"
                                                   x-model="transactions[{{ $index }}].selected"
                                                   name="transactions[{{ $index }}][selected]"
                                                   value="1"
                                                   class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-950 focus:ring-offset-2">
                                        </td>

                                        <!-- Date -->
                                        <td class="px-3 py-2 align-middle whitespace-nowrap">
                                            <input type="date"
                                                   name="transactions[{{ $index }}][date]"
                                                   value="{{ $tx['date'] }}"
                                                   class="w-full text-sm border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 py-1.5 px-2">
                                        </td>

                                        <!-- Description -->
                                        <td class="px-3 py-2 align-middle">
                                            <input type="text"
                                                   name="transactions[{{ $index }}][description]"
                                                   value="{{ $tx['description'] }}"
                                                   class="w-full text-sm border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 py-1.5 px-2">
                                        </td>

                                        <!-- Amount -->
                                        <td class="px-3 py-2 align-middle whitespace-nowrap text-right">
                                            <input type="number"
                                                   step="0.01"
                                                   name="transactions[{{ $index }}][amount]"
                                                   value="{{ $tx['amount'] }}"
                                                   class="w-full text-sm text-right border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 py-1.5 px-2 {{ $tx['type'] === 'debit' ? 'text-red-600' : 'text-green-600' }}">
                                        </td>

                                        <!-- Type -->
                                        <td class="px-3 py-2 align-middle text-center">
                                            <input type="hidden" name="transactions[{{ $index }}][type]" value="{{ $tx['type'] }}">
                                            @if($tx['type'] === 'debit')
                                                <x-ui.badge variant="outline" class="bg-red-50 text-red-700 border-red-200">
                                                    {{ __('Plata') }}
                                                </x-ui.badge>
                                            @else
                                                <x-ui.badge variant="outline" class="bg-green-50 text-green-700 border-green-200">
                                                    {{ __('Incasare') }}
                                                </x-ui.badge>
                                            @endif
                                        </td>

                                        <!-- Category (only for debits/expenses) -->
                                        <td class="px-3 py-2 align-middle">
                                            @if($tx['type'] === 'debit')
                                                @php
                                                    // For duplicates, use existing category; otherwise use suggested
                                                    $selectedCategoryId = ($tx['is_duplicate'] ?? false)
                                                        ? ($tx['existing_category_id'] ?? null)
                                                        : ($tx['suggested_category'] ?? null);
                                                @endphp
                                                <select name="transactions[{{ $index }}][category_id]"
                                                        class="w-full text-sm border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 py-1.5 px-2">
                                                    <option value="">{{ __('Fara categorie') }}</option>
                                                    @foreach($categories as $category)
                                                        {{-- Parent category (optgroup style but selectable) --}}
                                                        <option value="{{ $category->id }}"
                                                            {{ $selectedCategoryId == $category->id ? 'selected' : '' }}
                                                            class="font-semibold">
                                                            {{ $category->label }}
                                                        </option>
                                                        {{-- Child categories with indent --}}
                                                        @foreach($category->children as $child)
                                                            <option value="{{ $child->id }}"
                                                                {{ $selectedCategoryId == $child->id ? 'selected' : '' }}>
                                                                &nbsp;&nbsp;&nbsp;└ {{ $child->label }}
                                                            </option>
                                                        @endforeach
                                                    @endforeach
                                                </select>
                                            @else
                                                <span class="text-slate-400 text-sm">-</span>
                                                <input type="hidden" name="transactions[{{ $index }}][category_id]" value="">
                                            @endif
                                        </td>

                                        <!-- Status -->
                                        <td class="px-3 py-2 align-middle text-center">
                                            @if($tx['is_duplicate'] ?? false)
                                                <x-ui.badge variant="outline" class="bg-amber-50 text-amber-700 border-amber-200"
                                                            title="{{ __('Salvat ca') }}: {{ $tx['existing_description'] ?? '' }}">
                                                    {{ __('Duplicat') }}
                                                </x-ui.badge>
                                            @else
                                                <x-ui.badge variant="outline" class="bg-blue-50 text-blue-700 border-blue-200">
                                                    {{ __('Nou') }}
                                                </x-ui.badge>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>

                <!-- Bottom Submit Button -->
                <div class="mt-6 flex justify-end">
                    <x-ui.button type="submit" variant="default"
                                 x-bind:disabled="selectedCount === 0"
                                 x-bind:class="{ 'opacity-50 cursor-not-allowed': selectedCount === 0 }">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        {{ __('Importa Selectate') }} (<span x-text="selectedCount"></span>)
                    </x-ui.button>
                </div>
            </form>

            <script>
                function importTransactions() {
                    return {
                        transactions: @json(collect($transactions)->map(fn($tx, $i) => ['selected' => !($tx['is_duplicate'] ?? false)])->values()),

                        get selectedCount() {
                            return this.transactions.filter(t => t.selected).length;
                        },

                        get allSelected() {
                            return this.transactions.every(t => t.selected);
                        },

                        toggleAll(checked) {
                            this.transactions.forEach(t => t.selected = checked);
                        },

                        selectAllNew() {
                            @php
                                $newIndices = collect($transactions)->map(fn($tx, $i) => !($tx['is_duplicate'] ?? false) ? $i : null)->filter()->values()->toJson();
                            @endphp
                            const newIndices = {!! $newIndices !!};
                            this.transactions.forEach((t, i) => {
                                t.selected = newIndices.includes(i);
                            });
                        },

                        deselectAll() {
                            this.transactions.forEach(t => t.selected = false);
                        }
                    };
                }
            </script>
        @endif
    </div>
</x-app-layout>
