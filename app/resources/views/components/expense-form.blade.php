@props(['expense' => null, 'categories' => [], 'currencies' => [], 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6" enctype="multipart/form-data"
    x-data="{
        ...fileUploader(@js($expense?->files ?? [])),
        currency: '{{ old('currency', $expense->currency ?? 'RON') }}',
        amountEur: '{{ old('amount_eur', $expense->amount_eur ?? '') }}',
        amountRon: '{{ old('amount', $expense->amount ?? '') }}',
        exchangeRate: '{{ old('exchange_rate', $expense->exchange_rate ?? '') }}',
        occurredAt: '{{ old('occurred_at', $expense ? $expense->occurred_at?->format('Y-m-d') : (request('month') && request('year') ? request('year') . '-' . str_pad(request('month'), 2, '0', STR_PAD_LEFT) . '-01' : now()->format('Y-m-d'))) }}',
        loadingRate: false,
        rateError: null,

        async fetchRate() {
            if (this.currency !== 'EUR' || !this.occurredAt) return;

            this.loadingRate = true;
            this.rateError = null;

            try {
                const response = await fetch(`/api/exchange-rate?from=EUR&to=RON&date=${this.occurredAt}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.rate) {
                        this.exchangeRate = data.rate.toFixed(4);
                        this.calculateRon();
                    }
                } else {
                    this.rateError = 'Nu s-a putut obține cursul BNR';
                }
            } catch (e) {
                this.rateError = 'Eroare la obținerea cursului';
            } finally {
                this.loadingRate = false;
            }
        },

        calculateRon() {
            if (this.amountEur && this.exchangeRate) {
                this.amountRon = (parseFloat(this.amountEur) * parseFloat(this.exchangeRate)).toFixed(2);
            }
        },

        onCurrencyChange() {
            if (this.currency === 'EUR') {
                this.fetchRate();
            } else {
                this.amountEur = '';
                this.exchangeRate = '';
            }
        },

        init() {
            if (this.currency === 'EUR' && !this.exchangeRate) {
                this.fetchRate();
            }
        }
    }">
    @csrf
    <x-unsaved-form-warning />
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-ui.card-content>
            <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <!-- Document Name (Required) -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="document_name">
                        {{ __('Nume document') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="document_name"
                            id="document_name"
                            required
                            placeholder="{{ __('Enter expense description') }}"
                            value="{{ old('document_name', $expense->document_name ?? '') }}"
                        />
                    </div>
                </div>

                <!-- Currency -->
                <div class="sm:col-span-2 field-wrapper">
                    <x-ui.label for="currency">
                        {{ __('Valută') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <select name="currency" id="currency" required x-model="currency" @change="onCurrencyChange()"
                            class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            @foreach($currencies as $curr)
                                <option value="{{ $curr->value }}">{{ $curr->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- EUR Amount (shown only when currency is EUR) -->
                <template x-if="currency === 'EUR'">
                    <div class="sm:col-span-2 field-wrapper">
                        <x-ui.label for="amount_eur">
                            {{ __('Sumă EUR') }} <span class="text-red-500">*</span>
                        </x-ui.label>
                        <div class="mt-2">
                            <input type="number" step="0.01" name="amount_eur" id="amount_eur"
                                x-model="amountEur" @input="calculateRon()"
                                placeholder="0.00" required
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6" />
                        </div>
                    </div>
                </template>

                <!-- Exchange Rate (shown only when currency is EUR) -->
                <template x-if="currency === 'EUR'">
                    <div class="sm:col-span-2 field-wrapper">
                        <x-ui.label for="exchange_rate">
                            {{ __('Curs BNR') }}
                            <span x-show="loadingRate" class="text-slate-400 text-xs">(se încarcă...)</span>
                        </x-ui.label>
                        <div class="mt-2">
                            <input type="number" step="0.0001" name="exchange_rate" id="exchange_rate"
                                x-model="exchangeRate" @input="calculateRon()"
                                placeholder="0.0000"
                                class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6" />
                            <p x-show="rateError" x-text="rateError" class="mt-1 text-xs text-red-500"></p>
                        </div>
                    </div>
                </template>

                <!-- Amount RON (primary amount - always shown) -->
                <div class="field-wrapper" :class="currency === 'EUR' ? 'sm:col-span-2' : 'sm:col-span-4'">
                    <x-ui.label for="amount">
                        <span x-text="currency === 'EUR' ? 'Sumă RON (calculată)' : 'Sumă'"></span>
                        <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <input type="number" step="0.01" name="amount" id="amount"
                            x-model="amountRon"
                            placeholder="0.00" required
                            class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6" />
                    </div>
                    <p x-show="currency === 'EUR' && amountEur && exchangeRate" class="mt-1 text-xs text-slate-500">
                        <span x-text="amountEur"></span> EUR × <span x-text="exchangeRate"></span> = <span x-text="amountRon"></span> RON
                    </p>
                </div>

                <!-- Date -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="occurred_at">
                        {{ __('Dată') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <input type="date" name="occurred_at" id="occurred_at" required
                            x-model="occurredAt" @change="currency === 'EUR' && fetchRate()"
                            class="block w-full rounded-md border-0 py-1.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6" />
                    </div>
                </div>

                <!-- Category -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="category_option_id">{{ __('Categorie') }}</x-ui.label>
                    <div class="mt-2">
                        <x-category-combobox
                            :categories="$categories"
                            :selected="old('category_option_id', $expense->category_option_id ?? null)"
                            name="category_option_id"
                            placeholder="{{ __('Selectează categorie (opțional)') }}"
                            :allow-create="true"
                            :allow-empty="true"
                            width="100%"
                        />
                    </div>
                </div>

                <!-- File Upload -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label>{{ __('Files') }}</x-ui.label>
                    <div class="mt-2 space-y-4">
                        <!-- Existing Files (Edit Mode) -->
                        <template x-if="existingFiles.length > 0">
                            <div class="space-y-2">
                                <p class="text-sm text-slate-600">{{ __('Attached files') }}:</p>
                                <template x-for="file in existingFiles" :key="file.id">
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xl flex-shrink-0" x-text="file.icon || '📎'"></span>
                                            <div>
                                                <p class="text-sm font-medium text-slate-900" x-text="file.file_name"></p>
                                                <p class="text-xs text-slate-500" x-text="formatFileSize(file.file_size)"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a :href="`/financial/files/${file.id}/download`" class="text-slate-600 hover:text-slate-900" title="{{ __('Download') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                            </a>
                                            <button type="button" @click="removeExistingFile(file.id)" class="text-red-600 hover:text-red-800" title="{{ __('Delete') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Hidden inputs for files marked for deletion - must be outside x-if block -->
                        <template x-for="fileId in filesToDelete">
                            <input type="hidden" name="delete_files[]" :value="fileId">
                        </template>

                        <!-- File Upload Area -->
                        <div class="border-2 border-dashed border-slate-300 rounded-lg p-6 text-center hover:border-slate-400 transition-colors"
                             @dragover.prevent="$el.classList.add('border-blue-500', 'bg-blue-50')"
                             @dragleave.prevent="$el.classList.remove('border-blue-500', 'bg-blue-50')"
                             @drop.prevent="handleDrop($event); $el.classList.remove('border-blue-500', 'bg-blue-50')">
                            <input type="file" name="files[]" id="file-upload" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.zip,.rar" class="hidden" @change="handleFileSelect">
                            <label for="file-upload" class="cursor-pointer">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <div class="mt-2">
                                    <span class="text-sm font-medium text-slate-900">{{ __('Click to upload') }}</span>
                                    <span class="text-sm text-slate-500">{{ __('or drag and drop') }}</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">PDF, DOC, XLS, JPG, PNG, ZIP {{ __('up to 10MB') }}</p>
                            </label>
                        </div>

                        <!-- New Files Preview -->
                        <template x-if="newFiles.length > 0">
                            <div class="space-y-2">
                                <p class="text-sm text-slate-600">{{ __('New files to upload') }}:</p>
                                <template x-for="(file, index) in newFiles" :key="index">
                                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xl flex-shrink-0" x-text="getFileIcon(file.name)"></span>
                                            <div>
                                                <p class="text-sm font-medium text-slate-900" x-text="file.name"></p>
                                                <p class="text-xs text-slate-500" x-text="formatFileSize(file.size)"></p>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeNewFile(index)" class="text-red-600 hover:text-red-800">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Notes -->
                <div class="sm:col-span-6 field-wrapper">
                    <x-ui.label for="note">{{ __('Notă') }}</x-ui.label>
                    <div class="mt-2">
                        <x-ui.textarea name="note" id="note" rows="3" placeholder="{{ __('Additional notes about this expense (optional)') }}">{{ old('note', $expense->note ?? '') }}</x-ui.textarea>
                    </div>
                </div>
            </div>
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-6 border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('financial.expenses.index') }}'">
                {{ __('Cancel') }}
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $expense ? __('Update Expense') : __('Create Expense') }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
