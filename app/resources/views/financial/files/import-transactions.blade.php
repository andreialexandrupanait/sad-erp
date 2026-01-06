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
                                    <span class="font-medium text-slate-900 ml-1">{{ number_format($metadata['opening_balance'], 2) }} {{ $metadata['currency'] ?? 'RON' }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </x-ui.card-content>
            </x-ui.card>
        </div>

        @if(session('error'))
            <x-ui.alert variant="destructive">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>{{ session('error') }}</div>
            </x-ui.alert>
        @endif

        @if($errors->any())
            <x-ui.alert variant="destructive">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </x-ui.alert>
        @endif

        @if(count($transactions) === 0)
            <x-ui.card>
                <x-ui.empty-state
                    icon="document"
                    :title="__('Nu s-au gasit tranzactii')"
                    :description="__('Fisierul PDF nu contine tranzactii ce pot fi importate sau formatul nu este recunoscut.')"
                />
            </x-ui.card>
        @else
            <form action="{{ route('financial.files.process-import-transactions', $file) }}" method="POST" enctype="multipart/form-data" x-data="importTransactions()">
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
                                    <th class="px-3 py-3 text-right align-middle font-medium text-slate-600 w-36">{{ __('Suma') }}</th>
                                    <th class="px-3 py-3 text-center align-middle font-medium text-slate-600 w-20">{{ __('Tip') }}</th>
                                    <th class="px-3 py-3 text-left align-middle font-medium text-slate-600 w-64">{{ __('Categorie') }}</th>
                                    <th class="px-3 py-3 text-center align-middle font-medium text-slate-600 w-24">{{ __('Status') }}</th>
                                    <th class="px-3 py-3 text-center align-middle font-medium text-slate-600 w-32">{{ __('Fisiere') }}</th>
                                </tr>
                            </thead>
                            <tbody class="[&_tr:last-child]:border-0">
                                @foreach($transactions as $index => $tx)
                                    <tr class="border-b transition-colors hover:bg-slate-50/50"
                                        x-bind:class="{
                                            'bg-blue-50': transactions[{{ $index }}].selected && !{{ json_encode($tx['is_duplicate'] ?? false) }},
                                            'bg-slate-50': {{ json_encode($tx['is_duplicate'] ?? false) }}
                                        }">
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
                                            <div class="relative">
                                                <input type="number"
                                                       step="0.01"
                                                       name="transactions[{{ $index }}][amount]"
                                                       value="{{ $tx['amount'] }}"
                                                       class="w-full text-sm text-right border-slate-200 rounded-md focus:ring-slate-950 focus:border-slate-950 py-1.5 pr-11 pl-2 {{ $tx['type'] === 'debit' ? 'text-red-600' : 'text-green-600' }}">
                                                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 pointer-events-none font-medium">{{ $metadata['currency'] ?? 'RON' }}</span>
                                            </div>
                                            @if(!empty($tx['ron_equivalent']) && ($metadata['currency'] ?? 'RON') !== 'RON')
                                                <div class="text-xs text-slate-500 mt-1 text-right">
                                                    ≈ {{ number_format($tx['ron_equivalent'], 2) }} RON
                                                </div>
                                            @endif
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
                                                <x-category-combobox
                                                    :categories="$categories"
                                                    :selected="$selectedCategoryId"
                                                    name="transactions[{{ $index }}][category_id]"
                                                    :placeholder="__('Fara categorie')"
                                                    :allowEmpty="true"
                                                    :allowCreate="true"
                                                    width="100%"
                                                />
                                            @else
                                                <span class="text-slate-400 text-sm">-</span>
                                                <input type="hidden" name="transactions[{{ $index }}][category_id]" value="">
                                            @endif
                                        </td>

                                        <!-- Status -->
                                        <td class="px-3 py-2 align-middle text-center">
                                            @if($tx['is_duplicate'] ?? false)
                                                <x-ui.badge variant="outline" class="bg-orange-500 text-white border-orange-600 font-semibold"
                                                            title="{{ __('Salvat ca') }}: {{ $tx['existing_description'] ?? '' }}">
                                                    {{ __('Preluat') }}
                                                </x-ui.badge>
                                            @else
                                                <x-ui.badge variant="outline" class="bg-blue-50 text-blue-700 border-blue-200">
                                                    {{ __('Nou') }}
                                                </x-ui.badge>
                                            @endif
                                        </td>

                                        <!-- Files -->
                                        <td class="px-3 py-2 align-middle text-center">
                                            <div class="relative" x-data="{ open: false }">
                                                @php
                                                    $existingFileCount = count($tx['existing_files'] ?? []);
                                                @endphp
                                                <button type="button"
                                                        @click="open = !open"
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-md border transition-colors"
                                                        :class="transactions[{{ $index }}].files.length > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : '{{ $existingFileCount > 0 ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}'">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                    </svg>
                                                    <span x-text="transactions[{{ $index }}].files.length + {{ $existingFileCount }}"></span>
                                                </button>

                                                <!-- Dropdown -->
                                                <div x-show="open"
                                                     x-cloak
                                                     @click.outside="open = false"
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="transform opacity-0 scale-95"
                                                     x-transition:enter-end="transform opacity-100 scale-100"
                                                     x-transition:leave="transition ease-in duration-75"
                                                     x-transition:leave-start="transform opacity-100 scale-100"
                                                     x-transition:leave-end="transform opacity-0 scale-95"
                                                     class="absolute right-0 z-50 mt-1 w-64 bg-white rounded-lg shadow-lg border border-slate-200 p-3">

                                                    @if(($tx['is_duplicate'] ?? false) && !empty($tx['existing_files']))
                                                    <!-- Existing files (for duplicates) -->
                                                    <div class="mb-3">
                                                        <p class="text-xs font-medium text-amber-700 mb-2">{{ __('Fisiere existente') }}:</p>
                                                        <div class="space-y-1.5">
                                                            @foreach($tx['existing_files'] as $existingFile)
                                                                <a href="{{ route('financial.files.download', $existingFile['id']) }}"
                                                                   target="_blank"
                                                                   class="flex items-center gap-2 p-2 bg-amber-50 border border-amber-200 rounded text-xs hover:bg-amber-100 transition-colors">
                                                                    <span class="text-base">{{ Str::endsWith(strtolower($existingFile['name']), '.pdf') ? '📄' : '🖼️' }}</span>
                                                                    <span class="truncate text-amber-800">{{ $existingFile['name'] }}</span>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <!-- New file list -->
                                                    <template x-if="transactions[{{ $index }}].files.length > 0">
                                                        <div class="space-y-2 mb-3">
                                                            <p class="text-xs font-medium text-slate-600">{{ __('Fisiere noi') }}:</p>
                                                            <template x-for="(file, fileIdx) in transactions[{{ $index }}].files" :key="fileIdx">
                                                                <div class="flex items-center justify-between gap-2 p-2 bg-slate-50 rounded text-xs">
                                                                    <div class="flex items-center gap-2 min-w-0">
                                                                        <span class="text-base" x-text="getFileIcon(file.name)"></span>
                                                                        <span class="truncate" x-text="file.name"></span>
                                                                    </div>
                                                                    <button type="button"
                                                                            @click="removeFile({{ $index }}, fileIdx)"
                                                                            class="flex-shrink-0 text-red-500 hover:text-red-700">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>

                                                    <!-- Empty state -->
                                                    <template x-if="transactions[{{ $index }}].files.length === 0 && !{{ json_encode(!empty($tx['existing_files'])) }}">
                                                        <p class="text-xs text-slate-500 text-center mb-3">{{ __('Niciun fisier atasat') }}</p>
                                                    </template>

                                                    <!-- Add file button -->
                                                    <label class="flex items-center justify-center gap-2 w-full px-3 py-2 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-md cursor-pointer transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        {{ __('Adauga fisier') }}
                                                        <input type="file"
                                                               class="hidden"
                                                               multiple
                                                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                                               @change="addFiles({{ $index }}, $event)">
                                                    </label>
                                                </div>
                                            </div>
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
                        transactions: @json(collect($transactions)->map(fn($tx, $i) => ['selected' => !($tx['is_duplicate'] ?? false), 'files' => []])->values()),
                        fileInputs: {},

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
                        },

                        addFiles(txIndex, event) {
                            const files = Array.from(event.target.files);
                            const maxSize = 10 * 1024 * 1024; // 10MB

                            files.forEach(file => {
                                if (file.size > maxSize) {
                                    alert('Fisierul "' + file.name + '" depaseste limita de 10MB');
                                    return;
                                }
                                this.transactions[txIndex].files.push(file);
                            });

                            // Reset input so same file can be selected again
                            event.target.value = '';

                            // Update DataTransfer for form submission
                            this.updateFileInputs(txIndex);
                        },

                        removeFile(txIndex, fileIndex) {
                            this.transactions[txIndex].files.splice(fileIndex, 1);
                            this.updateFileInputs(txIndex);
                        },

                        updateFileInputs(txIndex) {
                            // Create or update the actual file input for form submission
                            const containerId = 'file-inputs-' + txIndex;
                            let container = document.getElementById(containerId);

                            if (!container) {
                                container = document.createElement('div');
                                container.id = containerId;
                                container.style.display = 'none';
                                this.$el.appendChild(container);
                            }

                            // Clear existing inputs
                            container.innerHTML = '';

                            // Create new file inputs using DataTransfer
                            const files = this.transactions[txIndex].files;
                            if (files.length > 0) {
                                const dt = new DataTransfer();
                                files.forEach(file => dt.items.add(file));

                                const input = document.createElement('input');
                                input.type = 'file';
                                input.name = 'transaction_files[' + txIndex + '][]';
                                input.multiple = true;
                                input.files = dt.files;
                                container.appendChild(input);
                            }
                        },

                        getFileIcon(filename) {
                            const ext = filename.split('.').pop().toLowerCase();
                            const icons = {
                                'pdf': '📄',
                                'doc': '📝',
                                'docx': '📝',
                                'xls': '📊',
                                'xlsx': '📊',
                                'jpg': '🖼️',
                                'jpeg': '🖼️',
                                'png': '🖼️'
                            };
                            return icons[ext] || '📎';
                        }
                    };
                }
            </script>
        @endif
    </div>
</x-app-layout>
