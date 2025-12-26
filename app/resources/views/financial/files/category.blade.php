{{--
    Category View - Shows files table with bulk actions
    URL: /financial/files/{year}/{month}/{category}
--}}

<x-financial-files-layout
    :year="$year"
    :month="$month"
    :category="$category"
    :available-years="$availableYears"
    :all-years-summary="$allYearsSummary"
>
    @php
        $monthName = \Carbon\Carbon::create()->setMonth($month)->locale('ro')->isoFormat('MMMM');
        $categoryLabels = ['incasare' => 'Incasari', 'plata' => 'Plati', 'extrase' => 'Extrase', 'general' => 'General'];
        $categoryLabel = $categoryLabels[$category] ?? 'General';
        $fileIds = $files->pluck('id')->toArray();
    @endphp

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-slate-200 px-6 py-3">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('financial.files.year', ['year' => $year]) }}"
               class="text-slate-600 hover:text-slate-900 hover:underline">{{ $year }}</a>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('financial.files.month', ['year' => $year, 'month' => $month]) }}"
               class="text-slate-600 hover:text-slate-900 capitalize hover:underline">{{ $monthName }}</a>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-slate-900 font-medium">{{ $categoryLabel }}</span>
        </div>
    </div>

    <!-- File List Header with Bulk Actions -->
    <div class="bg-white border-b border-slate-200 px-6 py-3" x-data="bulkFileActions(@js($fileIds))">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h2 class="text-lg font-semibold text-slate-900">
                    Fisiere ({{ $files->total() }})
                </h2>

                <!-- Bulk Actions - shown when files are selected -->
                <template x-if="selectedIds.length > 0">
                    <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
                        <span class="text-sm text-slate-600">
                            <span class="font-medium" x-text="selectedIds.length"></span> selectate
                        </span>
                        <button @click="bulkDelete()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Sterge
                        </button>
                        <button @click="clearSelection()" class="text-sm text-slate-500 hover:text-slate-700">
                            Anuleaza
                        </button>
                    </div>
                </template>
            </div>

            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition-colors">
                + Incarca fisier
            </button>
        </div>

        <!-- File Table -->
        <div class="mt-4">
            @if($files->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-slate-500 mb-2">Nu exista fisiere in aceasta categorie</p>
                    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                            class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                        Incarca primul fisier
                    </button>
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="w-12 px-6 py-4">
                                    <input type="checkbox"
                                           @change="toggleAll($event.target.checked)"
                                           :checked="allSelected"
                                           :indeterminate="someSelected && !allSelected"
                                           class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nume fisier</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tip</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Dimensiune</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Incarcat la</th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actiuni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @foreach($files as $file)
                                <tr class="hover:bg-slate-50 transition-colors" :class="{ 'bg-primary-50': selectedIds.includes({{ $file->id }}) }">
                                    <!-- Checkbox -->
                                    <td class="w-12 px-6 py-4">
                                        <input type="checkbox"
                                               @change="toggleFile({{ $file->id }})"
                                               :checked="selectedIds.includes({{ $file->id }})"
                                               class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                    </td>

                                    <!-- File Name -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-2xl mr-3">{{ $file->icon }}</span>
                                            <div>
                                                <div class="text-sm font-medium text-slate-900">{{ pathinfo($file->file_name, PATHINFO_FILENAME) }}</div>
                                                @if($file->entity)
                                                    <div class="text-xs text-slate-500">
                                                        @if($file->entity instanceof \App\Models\FinancialRevenue)
                                                            Venit: {{ $file->entity->document_name }}
                                                        @elseif($file->entity instanceof \App\Models\FinancialExpense)
                                                            Cheltuiala: {{ $file->entity->document_name }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Type Badge -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($file->tip == 'incasare')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Incasare</span>
                                        @elseif($file->tip == 'plata')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Plata</span>
                                        @elseif($file->tip == 'extrase')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Extrase</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-800">General</span>
                                        @endif
                                    </td>

                                    <!-- Size -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        {{ $file->formatted_size }}
                                    </td>

                                    <!-- Date -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        {{ $file->created_at->format('d.m.Y H:i') }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-1">
                                        @if($file->tip === 'extrase' && in_array($file->file_type, ['application/pdf', 'application/x-pdf']))
                                            {{-- Import transactions from bank statement PDF --}}
                                            <a href="{{ route('financial.files.import-transactions', $file) }}"
                                               class="inline-flex items-center justify-center w-8 h-8 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                               title="Importa tranzactii din extras">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                                </svg>
                                            </a>
                                        @endif

                                        <a href="{{ route('financial.files.show', $file) }}" target="_blank"
                                           class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                           title="Vizualizare">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>

                                        <a href="{{ route('financial.files.download', $file) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                           title="Descarcare">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>

                                        <button onclick="copyToClipboard('{{ $file->file_name }}')"
                                                class="inline-flex items-center justify-center w-8 h-8 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"
                                                title="Copiaza nume">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>

                                        <form action="{{ route('financial.files.destroy', $file) }}" method="POST" class="inline" onsubmit="return confirm('Esti sigur ca vrei sa stergi acest fisier?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Sterge">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $files->links() }}
                </div>
            @endif
        </div>
    </div><!-- Close bulkFileActions x-data -->
</x-financial-files-layout>
