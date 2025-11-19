@props(['revenue' => null, 'clients' => [], 'currencies' => [], 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6" enctype="multipart/form-data" x-data="fileUploader(@js($revenue?->files ?? []))">
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
                        {{ __('Nume document') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="text"
                            name="document_name"
                            id="document_name"
                            required
                            placeholder="{{ __('Enter revenue description') }}"
                            value="{{ old('document_name', $revenue->document_name ?? '') }}"
                        />
                    </div>
                </div>

                <!-- Amount -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="amount">
                        {{ __('Sumă') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="number"
                            step="0.01"
                            name="amount"
                            id="amount"
                            required
                            placeholder="{{ __('0.00') }}"
                            value="{{ old('amount', $revenue->amount ?? '') }}"
                        />
                    </div>
                </div>

                <!-- Currency -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="currency">
                        {{ __('Valută') }} <span class="text-red-500">*</span>
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
                        {{ __('Dată') }} <span class="text-red-500">*</span>
                    </x-ui.label>
                    <div class="mt-2">
                        <x-ui.input
                            type="date"
                            name="occurred_at"
                            id="occurred_at"
                            required
                            placeholder="{{ __('YYYY-MM-DD') }}"
                            value="{{ old('occurred_at', $revenue ? $revenue->occurred_at?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                        />
                    </div>
                </div>

                <!-- Client -->
                <div class="sm:col-span-3 field-wrapper">
                    <x-ui.label for="client_id">{{ __('Client') }}</x-ui.label>
                    <div class="mt-2">
                        <x-ui.select name="client_id" id="client_id">
                            <option value="">{{ __('Selectează client (opțional)') }}</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $revenue->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                    {{ $client->display_name }}
                                </option>
                            @endforeach
                        </x-ui.select>
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
                                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
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
                                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
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
                        <x-ui.textarea name="note" id="note" rows="3" placeholder="{{ __('Additional notes about this revenue (optional)') }}">{{ old('note', $revenue->note ?? '') }}</x-ui.textarea>
                    </div>
                </div>
            </div>
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-6 border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('financial.revenues.index') }}'">
                {{ __('Cancel') }}
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $revenue ? __('Update Revenue') : __('Create Revenue') }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>

<script>
function fileUploader(existingFilesData) {
    return {
        existingFiles: existingFilesData || [],
        newFiles: [],
        filesToDelete: [],

        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            this.addFiles(files);
            // Don't reset the input - keep files for form submission
        },

        handleDrop(event) {
            const files = Array.from(event.dataTransfer.files);
            this.addFiles(files);
            // Need to programmatically add files to the file input
            const input = document.getElementById('file-upload');
            const dataTransfer = new DataTransfer();

            // Add dropped files to DataTransfer
            files.forEach(file => dataTransfer.items.add(file));

            // Set the files to the input
            input.files = dataTransfer.files;
        },

        addFiles(files) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png',
                                'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/zip', 'application/x-rar-compressed'];

            files.forEach(file => {
                if (file.size > maxSize) {
                    alert(`${file.name} is too large. Maximum size is 10MB.`);
                    return;
                }
                if (!allowedTypes.includes(file.type) && !file.name.match(/\.(pdf|jpe?g|png|docx?|xlsx?|zip|rar)$/i)) {
                    alert(`${file.name} has an unsupported file type.`);
                    return;
                }
                this.newFiles.push(file);
            });
        },

        removeNewFile(index) {
            this.newFiles.splice(index, 1);
            // Also update the actual file input
            const input = document.getElementById('file-upload');
            const dataTransfer = new DataTransfer();
            this.newFiles.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
        },

        removeExistingFile(fileId) {
            if (confirm('{{ __("Are you sure you want to delete this file?") }}')) {
                this.existingFiles = this.existingFiles.filter(f => f.id !== fileId);
                this.filesToDelete.push(fileId);
            }
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    };
}

// Initialize Tom Select for client dropdown
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('client_id')) {
        const tomSelect = new TomSelect('#client_id', {
            placeholder: '{{ __("Selectează client (opțional)") }}',
            allowEmptyOption: true,
            plugins: {
                'clear_button': {},
                'dropdown_input': {}
            },
            maxOptions: null,
            render: {
                no_results: function(data, escape) {
                    return '<div style="padding: 12px; text-align: center; color: #94a3b8;">{{ __("Nu s-au găsit rezultate") }}</div>';
                }
            },
            onInitialize: function() {
                // Hide the control input elegantly with CSS
                const controlInput = this.control_input;
                if (controlInput) {
                    controlInput.style.position = 'absolute';
                    controlInput.style.opacity = '0';
                    controlInput.style.width = '0';
                    controlInput.style.height = '0';
                    controlInput.style.padding = '0';
                    controlInput.style.border = 'none';
                    controlInput.setAttribute('tabindex', '-1');
                }
            }
        });
    }
});
</script>
