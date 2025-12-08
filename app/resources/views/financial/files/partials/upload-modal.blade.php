{{--
    Upload Modal Partial - File upload form

    Required variables:
    - $year: Current year
    - $month: Current month (nullable)
    - $category: Current category (nullable)
--}}

<div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
     x-data="window.fileUploadManager()">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-slate-200 sticky top-0 bg-white z-10">
            <h3 class="text-lg font-semibold text-slate-900">Incarca fisiere noi</h3>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Upload Form -->
        <form action="{{ route('financial.files.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="an" id="upload-year" value="{{ $year }}">
            <input type="hidden" name="luna" id="upload-month" value="{{ $month ?? '' }}">
            <input type="hidden" name="tip" id="upload-tip" value="{{ $category ?? '' }}">

            <!-- File Selection -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Selecteaza fisiere</label>

                <!-- Drag & Drop Zone -->
                <div class="border-2 border-dashed border-slate-300 rounded-lg p-6 text-center hover:border-slate-400 transition-colors"
                     @dragover.prevent="$el.classList.add('border-blue-500', 'bg-blue-50')"
                     @dragleave.prevent="$el.classList.remove('border-blue-500', 'bg-blue-50')"
                     @drop.prevent="handleDrop($event); $el.classList.remove('border-blue-500', 'bg-blue-50')">
                    <input type="file" name="files[]" id="file-upload-financial" multiple
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.zip,.rar"
                           class="hidden" @change="handleFileSelect">
                    <label for="file-upload-financial" class="cursor-pointer">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <div class="mt-2">
                            <span class="text-sm font-medium text-slate-900">Click pentru a incarca</span>
                            <span class="text-sm text-slate-500"> sau trage si plaseaza aici</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">PDF, DOC, XLS, JPG, PNG, ZIP pana la 10MB per fisier</p>
                    </label>
                </div>
            </div>

            <!-- Selected Files Preview -->
            <template x-if="newFiles.length > 0">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-slate-700">Fisiere selectate:</p>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        <template x-for="(file, index) in newFiles" :key="index">
                            <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm text-slate-700 truncate" x-text="file.name"></span>
                                    <span class="text-xs text-slate-500" x-text="formatFileSize(file.size)"></span>
                                </div>
                                <button type="button" @click="removeNewFile(index)" class="text-red-500 hover:text-red-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Category Selection (only show when not already in a category) -->
            @if(!$category)
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tip document</label>
                    <select name="tip" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">-- Selecteaza tipul --</option>
                        <option value="incasare">Incasare</option>
                        <option value="plata">Plata</option>
                        <option value="extrase">Extrase bancare</option>
                        <option value="general">General</option>
                    </select>
                </div>
            @endif

            <!-- Month Selection (only show when not already in a month) -->
            @if(!$month)
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Luna</label>
                    <select name="luna" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">-- Selecteaza luna --</option>
                        @for($m = 1; $m <= 12; $m++)
                            @php
                                $monthName = \Carbon\Carbon::create()->setMonth($m)->locale('ro')->isoFormat('MMMM');
                            @endphp
                            <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ ucfirst($monthName) }}</option>
                        @endfor
                    </select>
                </div>
            @endif

            <!-- Submit -->
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button"
                        onclick="document.getElementById('uploadModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                    Anuleaza
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition-colors"
                        :disabled="newFiles.length === 0">
                    Incarca fisiere
                </button>
            </div>
        </form>
    </div>
</div>
