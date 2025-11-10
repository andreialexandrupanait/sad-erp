<x-app-layout>
    <div class="h-screen flex flex-col bg-slate-50">
        <!-- Header -->
        <div class="bg-white border-b border-slate-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Fișiere</h1>
                    <p class="text-sm text-slate-600 mt-1">Gestionează fișierele financiare cu structură dinamică</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Year Filter -->
                    <form method="GET" class="flex items-center gap-2">
                        <label class="text-sm font-medium text-slate-700">An:</label>
                        <select name="year" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm">
                            @forelse($availableYears as $availableYear)
                                <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>
                                    {{ $availableYear }}
                                </option>
                            @empty
                                <option value="{{ now()->year }}">{{ now()->year }}</option>
                            @endforelse
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Left Sidebar - File Tree -->
            <div class="w-64 bg-white border-r border-slate-200 overflow-y-auto">
                <div class="p-4">
                    <!-- Year Folder -->
                    <div x-data="{ open: true }" class="mb-2">
                        <button @click="open = !open" class="flex items-center gap-2 w-full text-left px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                            <svg :class="{'rotate-90': open}" class="w-4 h-4 text-slate-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <span class="text-sm font-medium text-slate-900">{{ $year }}</span>
                        </button>

                        <!-- Months -->
                        <div x-show="open" x-collapse class="ml-6 mt-1 space-y-1">
                            @for($m = 1; $m <= 12; $m++)
                                @php
                                    $monthName = \Carbon\Carbon::create()->month($m)->locale('ro')->isoFormat('MMMM');
                                    $monthTotal = $summary[$m]['total'] ?? 0;
                                @endphp

                                @if($monthTotal > 0)
                                    <div x-data="{ open: {{ $month == $m ? 'true' : 'false' }} }" class="relative">
                                        <button @click="open = !open" class="flex items-center justify-between w-full px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-colors {{ $month == $m ? 'bg-primary-50' : '' }}">
                                            <div class="flex items-center gap-2">
                                                <svg :class="{'rotate-90': open}" class="w-3 h-3 text-slate-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                </svg>
                                                <span class="text-xs font-medium text-slate-700">{{ ucfirst($monthName) }}</span>
                                            </div>
                                            <span class="text-xs text-slate-500">{{ $monthTotal }}</span>
                                        </button>

                                        <!-- Categories -->
                                        <div x-show="open" x-collapse class="ml-5 mt-1 space-y-1">
                                            @if(($summary[$m]['incasare'] ?? 0) > 0)
                                                <a href="{{ route('financial.files.index', ['year' => $year, 'month' => $m, 'tip' => 'incasare']) }}"
                                                   class="flex items-center justify-between px-2 py-1 rounded-lg hover:bg-green-50 transition-colors {{ $month == $m && $tip == 'incasare' ? 'bg-green-50' : '' }}">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        <span class="text-xs text-green-700">Încasări</span>
                                                    </div>
                                                    <span class="text-xs text-green-600 font-medium">{{ $summary[$m]['incasare'] }}</span>
                                                </a>
                                            @endif

                                            @if(($summary[$m]['plata'] ?? 0) > 0)
                                                <a href="{{ route('financial.files.index', ['year' => $year, 'month' => $m, 'tip' => 'plata']) }}"
                                                   class="flex items-center justify-between px-2 py-1 rounded-lg hover:bg-red-50 transition-colors {{ $month == $m && $tip == 'plata' ? 'bg-red-50' : '' }}">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        <span class="text-xs text-red-700">Plăți</span>
                                                    </div>
                                                    <span class="text-xs text-red-600 font-medium">{{ $summary[$m]['plata'] }}</span>
                                                </a>
                                            @endif

                                            @if(($summary[$m]['extrase'] ?? 0) > 0)
                                                <a href="{{ route('financial.files.index', ['year' => $year, 'month' => $m, 'tip' => 'extrase']) }}"
                                                   class="flex items-center justify-between px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors {{ $month == $m && $tip == 'extrase' ? 'bg-blue-50' : '' }}">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        <span class="text-xs text-blue-700">Extrase</span>
                                                    </div>
                                                    <span class="text-xs text-blue-600 font-medium">{{ $summary[$m]['extrase'] }}</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content - File List -->
            <div class="flex-1 overflow-y-auto">
                @if($month)
                    <!-- Breadcrumb -->
                    <div class="bg-white border-b border-slate-200 px-6 py-3">
                        <div class="flex items-center gap-2 text-sm">
                            <a href="{{ route('financial.files.index', ['year' => $year]) }}" class="text-slate-600 hover:text-slate-900">{{ $year }}</a>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-slate-900 font-medium">{{ \Carbon\Carbon::create()->month($month)->locale('ro')->isoFormat('MMMM') }}</span>
                            @if($tip)
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span class="text-slate-900 font-medium capitalize">{{ $tip === 'incasare' ? 'Încasări' : ($tip === 'plata' ? 'Plăți' : ucfirst($tip)) }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- File List Header -->
                    <div class="bg-white border-b border-slate-200 px-6 py-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-slate-900">
                                Fișiere ({{ $files->total() }})
                            </h2>
                            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition-colors">
                                + Încarcă fișier
                            </button>
                        </div>
                    </div>

                    <!-- File Table -->
                    <div class="p-6">
                        @if($files->isEmpty())
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-slate-500 mb-2">Nu există fișiere în această categorie</p>
                                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                                        class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                                    Încarcă primul fișier
                                </button>
                            </div>
                        @else
                            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nume fișier</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tip</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Dimensiune</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Încărcat la</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Acțiuni</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-200">
                                        @foreach($files as $file)
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <span class="text-2xl mr-3">{{ $file->icon }}</span>
                                                        <div>
                                                            <div class="text-sm font-medium text-slate-900">{{ $file->file_name }}</div>
                                                            @if($file->entity)
                                                                <div class="text-xs text-slate-500">
                                                                    @if($file->entity instanceof \App\Models\FinancialRevenue)
                                                                        Venit: {{ $file->entity->document_name }}
                                                                    @elseif($file->entity instanceof \App\Models\FinancialExpense)
                                                                        Cheltuială: {{ $file->entity->document_name }}
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($file->tip == 'incasare')
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Încasare</span>
                                                    @elseif($file->tip == 'plata')
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Plată</span>
                                                    @elseif($file->tip == 'extrase')
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Extrase</span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-800">General</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                    {{ $file->formatted_size }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                    {{ $file->created_at->format('d.m.Y H:i') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
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
                                                       title="Descărcare">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                        </svg>
                                                    </a>
                                                    <button onclick="copyToClipboard('{{ $file->file_name }}')"
                                                            class="inline-flex items-center justify-center w-8 h-8 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"
                                                            title="Copiază nume">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                    </button>
                                                    <form action="{{ route('financial.files.destroy', $file) }}" method="POST" class="inline" onsubmit="return confirm('Ești sigur că vrei să ștergi acest fișier?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                                title="Șterge">
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
                @else
                    <!-- No month selected -->
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <svg class="w-20 h-20 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <p class="text-slate-600 text-lg mb-2">Selectează o lună din arbore pentru a vedea fișierele</p>
                            <p class="text-slate-500 text-sm">Folosește meniul din stânga pentru a naviga prin fișiere</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Încarcă fișier nou</h3>
                <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="{{ route('financial.files.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="an" value="{{ $year }}">
                <input type="hidden" name="luna" value="{{ $month }}">
                @if($tip)
                    <input type="hidden" name="tip" value="{{ $tip }}">
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Selectează fișier</label>
                    <input type="file" name="file" required
                           class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    <p class="mt-2 text-xs text-slate-500">PDF, JPG, PNG, DOC, DOCX, XLS, XLSX, ZIP, RAR (max 10MB)</p>
                </div>

                @if(!$tip)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Tip fișier</label>
                        <select name="tip" class="w-full rounded-lg border-slate-300">
                            <option value="general">General</option>
                            <option value="incasare">Încasare</option>
                            <option value="plata">Plată</option>
                            <option value="extrase">Extrase bancare</option>
                        </select>
                    </div>
                @endif

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')"
                            class="px-4 py-2 text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                        Anulează
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Încarcă fișier
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="fixed bottom-4 right-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Nume fișier copiat în clipboard!');
            });
        }

        // Auto-hide success message after 3 seconds
        setTimeout(() => {
            const successMsg = document.querySelector('.fixed.bottom-4');
            if (successMsg) {
                successMsg.style.display = 'none';
            }
        }, 3000);
    </script>
</x-app-layout>
