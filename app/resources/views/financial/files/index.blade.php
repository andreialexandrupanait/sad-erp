<x-app-layout>
    <x-slot name="pageTitle">Fișiere</x-slot>

    <!-- Define Alpine components BEFORE they are used -->
    <script>
        // File upload manager - must be defined before Alpine initializes
        window.fileUploadManager = function() {
            return {
                newFiles: [],

                handleFileSelect(event) {
                    const files = Array.from(event.target.files);
                    this.addFiles(files);
                },

                handleDrop(event) {
                    const files = Array.from(event.dataTransfer.files);
                    this.addFiles(files);
                },

                addFiles(files) {
                    const maxSize = 10 * 1024 * 1024;
                    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png',
                                        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/zip', 'application/x-rar-compressed', 'application/x-zip-compressed'];

                    files.forEach(file => {
                        if (file.size > maxSize) {
                            alert(`${file.name} este prea mare. Dimensiunea maximă este 10MB.`);
                            return;
                        }
                        if (!allowedTypes.includes(file.type) && !file.name.match(/\.(pdf|jpe?g|png|docx?|xlsx?|zip|rar)$/i)) {
                            alert(`${file.name} are un tip de fișier neacceptat.`);
                            return;
                        }
                        if (!this.newFiles.find(f => f.name === file.name && f.size === file.size)) {
                            this.newFiles.push(file);
                        }
                    });
                    this.syncFileInput();
                },

                removeNewFile(index) {
                    this.newFiles.splice(index, 1);
                    this.syncFileInput();
                },

                syncFileInput() {
                    const input = document.getElementById('file-upload-financial');
                    if (input) {
                        const dataTransfer = new DataTransfer();
                        this.newFiles.forEach(file => dataTransfer.items.add(file));
                        input.files = dataTransfer.files;
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
        };
    </script>

    <div class="h-full flex flex-col bg-slate-50" x-data="fileTree()">
        <!-- Mobile Navigation -->
        <div class="lg:hidden bg-white border-b border-slate-200" x-data="{ mobileMenuOpen: false }">
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="w-full px-4 py-3 flex items-center justify-between text-left">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    <span class="font-medium text-slate-900">
                        {{ $year }}
                        @if($month)
                            / {{ ucfirst(\Carbon\Carbon::create()->setMonth($month)->locale('ro')->isoFormat('MMMM')) }}
                        @endif
                        @if($tip)
                            / {{ $tip === 'incasare' ? 'Încasări' : ($tip === 'plata' ? 'Plăți' : ucfirst($tip)) }}
                        @endif
                    </span>
                </div>
                <svg :class="{'rotate-180': mobileMenuOpen}" class="w-5 h-5 text-slate-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="mobileMenuOpen" class="border-t border-slate-200 max-h-96 overflow-y-auto">
                <div class="p-4 space-y-2">
                    @foreach($availableYears as $y)
                        <!-- Year Folder -->
                        <div class="mb-2">
                            <div class="flex items-center gap-2 w-full text-left px-2 py-1.5 rounded-lg hover:bg-slate-50 {{ $year == $y && !$month ? 'bg-primary-50' : '' }}">
                                <button @click="toggle('year-{{ $y }}')" type="button" class="p-0.5 hover:bg-slate-100 rounded">
                                    <svg :class="{'rotate-90': isExpanded('year-{{ $y }}')}" class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                                <a href="{{ route('financial.files.index', ['year' => $y]) }}"
                                   @click.prevent="navigate($event.target.closest('a').href)"
                                   class="flex items-center gap-2 flex-1">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-slate-900">{{ $y }}</span>
                                </a>
                            </div>

                            <!-- Months -->
                            <div x-show="isExpanded('year-{{ $y }}')" class="ml-6 mt-1 space-y-1">
                                @for($m = 12; $m >= 1; $m--)
                                    @php
                                        $monthName = \Carbon\Carbon::create()->setMonth($m)->locale('ro')->isoFormat('MMMM');
                                        $monthTotal = $allYearsSummary[$y][$m]['total'] ?? 0;
                                    @endphp

                                    @if($monthTotal > 0)
                                        <div class="relative">
                                            <div class="flex items-center justify-between w-full px-2 py-1.5 rounded-lg hover:bg-slate-50 {{ $year == $y && $month == $m && !$tip ? 'bg-primary-50' : '' }}">
                                                <div class="flex items-center gap-2 flex-1">
                                                    <button @click="toggle('month-{{ $y }}-{{ $m }}')" type="button" class="p-0.5 hover:bg-slate-100 rounded">
                                                        <svg :class="{'rotate-90': isExpanded('month-{{ $y }}-{{ $m }}')}" class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                    </button>
                                                    <a href="{{ route('financial.files.index', ['year' => $y, 'month' => $m]) }}"
                                                       @click.prevent="navigate($event.target.closest('a').href)"
                                                       class="flex items-center gap-2 flex-1">
                                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                        </svg>
                                                        <span class="text-sm font-medium text-slate-700">{{ ucfirst($monthName) }}</span>
                                                    </a>
                                                </div>
                                                <span class="text-sm text-slate-500">{{ $monthTotal }}</span>
                                            </div>

                                            <!-- Categories -->
                                            <div x-show="isExpanded('month-{{ $y }}-{{ $m }}')" class="ml-[30px] mt-1 space-y-1">
                                                @if(($allYearsSummary[$y][$m]['incasare'] ?? 0) > 0)
                                                    <a href="{{ route('financial.files.index', ['year' => $y, 'month' => $m, 'tip' => 'incasare']) }}"
                                                       @click.prevent="navigate($event.target.closest('a').href)"
                                                       class="flex items-center justify-between pl-0 pr-2 py-1 rounded-lg hover:bg-green-50 {{ $year == $y && $month == $m && $tip == 'incasare' ? 'bg-green-100 font-medium' : '' }}">
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            <span class="text-sm text-green-700">Încasări</span>
                                                        </div>
                                                        <span class="text-sm text-green-600 font-medium">{{ $allYearsSummary[$y][$m]['incasare'] }}</span>
                                                    </a>
                                                @endif

                                                @if(($allYearsSummary[$y][$m]['plata'] ?? 0) > 0)
                                                    <a href="{{ route('financial.files.index', ['year' => $y, 'month' => $m, 'tip' => 'plata']) }}"
                                                       @click.prevent="navigate($event.target.closest('a').href)"
                                                       class="flex items-center justify-between pl-0 pr-2 py-1 rounded-lg hover:bg-red-50 {{ $year == $y && $month == $m && $tip == 'plata' ? 'bg-red-100 font-medium' : '' }}">
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            <span class="text-sm text-red-700">Plăți</span>
                                                        </div>
                                                        <span class="text-sm text-red-600 font-medium">{{ $allYearsSummary[$y][$m]['plata'] }}</span>
                                                    </a>
                                                @endif

                                                @if(($allYearsSummary[$y][$m]['extrase'] ?? 0) > 0)
                                                    <a href="{{ route('financial.files.index', ['year' => $y, 'month' => $m, 'tip' => 'extrase']) }}"
                                                       @click.prevent="navigate($event.target.closest('a').href)"
                                                       class="flex items-center justify-between pl-0 pr-2 py-1 rounded-lg hover:bg-blue-50 {{ $year == $y && $month == $m && $tip == 'extrase' ? 'bg-blue-100 font-medium' : '' }}">
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            <span class="text-sm text-blue-700">Extrase</span>
                                                        </div>
                                                        <span class="text-sm text-blue-600 font-medium">{{ $allYearsSummary[$y][$m]['extrase'] }}</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Left Sidebar - File Tree (Desktop) -->
            <div class="w-64 bg-white border-r border-slate-200 overflow-y-auto lg:block hidden">
                <div class="p-4 space-y-2">
                    @foreach($availableYears as $y)
                        <!-- Year Folder -->
                        <div class="mb-2">
                            <div class="flex items-center gap-2 w-full text-left px-2 py-1.5 rounded-lg hover:bg-slate-50 {{ $year == $y && !$month ? 'bg-primary-50' : '' }}">
                                <button @click="toggle('year-{{ $y }}')" type="button" class="p-0.5 hover:bg-slate-100 rounded">
                                    <svg :class="{'rotate-90': isExpanded('year-{{ $y }}')}" class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                                <a href="{{ route('financial.files.index', ['year' => $y]) }}"
                                   @click.prevent="navigate($event.target.closest('a').href)"
                                   class="flex items-center gap-2 flex-1">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-slate-900">{{ $y }}</span>
                                </a>
                            </div>

                            <!-- Months -->
                            <div x-show="isExpanded('year-{{ $y }}')" class="ml-6 mt-1 space-y-1">
                                @for($m = 12; $m >= 1; $m--)
                                    @php
                                        $monthName = \Carbon\Carbon::create()->setMonth($m)->locale('ro')->isoFormat('MMMM');
                                        $monthTotal = $allYearsSummary[$y][$m]['total'] ?? 0;
                                    @endphp

                                    @if($monthTotal > 0)
                                        <div class="relative">
                                            <div class="flex items-center justify-between w-full px-2 py-1.5 rounded-lg hover:bg-slate-50 {{ $year == $y && $month == $m && !$tip ? 'bg-primary-50' : '' }}">
                                                <div class="flex items-center gap-2 flex-1">
                                                    <button @click="toggle('month-{{ $y }}-{{ $m }}')" type="button" class="p-0.5 hover:bg-slate-100 rounded">
                                                        <svg :class="{'rotate-90': isExpanded('month-{{ $y }}-{{ $m }}')}" class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                    </button>
                                                    <a href="{{ route('financial.files.index', ['year' => $y, 'month' => $m]) }}"
                                                       @click.prevent="navigate($event.target.closest('a').href)"
                                                       class="flex items-center gap-2 flex-1">
                                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                        </svg>
                                                        <span class="text-sm font-medium text-slate-700">{{ ucfirst($monthName) }}</span>
                                                    </a>
                                                </div>
                                                <span class="text-sm text-slate-500">{{ $monthTotal }}</span>
                                            </div>

                                            <!-- Categories -->
                                            <div x-show="isExpanded('month-{{ $y }}-{{ $m }}')" class="ml-[30px] mt-1 space-y-1">
                                                @if(($allYearsSummary[$y][$m]['incasare'] ?? 0) > 0)
                                                    <a href="{{ route('financial.files.index', ['year' => $y, 'month' => $m, 'tip' => 'incasare']) }}"
                                                       @click.prevent="navigate($event.target.closest('a').href)"
                                                       class="flex items-center justify-between pl-0 pr-2 py-1 rounded-lg hover:bg-green-50 {{ $year == $y && $month == $m && $tip == 'incasare' ? 'bg-green-100 font-medium' : '' }}">
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            <span class="text-sm text-green-700">Încasări</span>
                                                        </div>
                                                        <span class="text-sm text-green-600 font-medium">{{ $allYearsSummary[$y][$m]['incasare'] }}</span>
                                                    </a>
                                                @endif

                                                @if(($allYearsSummary[$y][$m]['plata'] ?? 0) > 0)
                                                    <a href="{{ route('financial.files.index', ['year' => $y, 'month' => $m, 'tip' => 'plata']) }}"
                                                       @click.prevent="navigate($event.target.closest('a').href)"
                                                       class="flex items-center justify-between pl-0 pr-2 py-1 rounded-lg hover:bg-red-50 {{ $year == $y && $month == $m && $tip == 'plata' ? 'bg-red-100 font-medium' : '' }}">
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            <span class="text-sm text-red-700">Plăți</span>
                                                        </div>
                                                        <span class="text-sm text-red-600 font-medium">{{ $allYearsSummary[$y][$m]['plata'] }}</span>
                                                    </a>
                                                @endif

                                                @if(($allYearsSummary[$y][$m]['extrase'] ?? 0) > 0)
                                                    <a href="{{ route('financial.files.index', ['year' => $y, 'month' => $m, 'tip' => 'extrase']) }}"
                                                       @click.prevent="navigate($event.target.closest('a').href)"
                                                       class="flex items-center justify-between pl-0 pr-2 py-1 rounded-lg hover:bg-blue-50 {{ $year == $y && $month == $m && $tip == 'extrase' ? 'bg-blue-100 font-medium' : '' }}">
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            <span class="text-sm text-blue-700">Extrase</span>
                                                        </div>
                                                        <span class="text-sm text-blue-600 font-medium">{{ $allYearsSummary[$y][$m]['extrase'] }}</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right Content - File List -->
            <div class="flex-1 overflow-y-auto" id="file-content-area">
                <!-- Loading overlay -->
                <div x-show="loading" class="absolute inset-0 bg-white/75 flex items-center justify-center z-10">
                    <div class="flex flex-col items-center gap-3">
                        <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-slate-600">Se încarcă...</span>
                    </div>
                </div>

                @if(!$month)
                    <!-- Year Level: Show Month Statistics -->
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-slate-900">Fișiere {{ $year }}</h2>
                            @php
                                $yearTotal = collect($allYearsSummary[$year] ?? [])->sum('total');
                            @endphp
                            @if($yearTotal > 0)
                                <a href="{{ route('financial.files.download-yearly-zip', ['year' => $year]) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 text-sm font-medium transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Descarcă tot (ZIP)
                                </a>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @for($m = 12; $m >= 1; $m--)
                                @php
                                    $monthName = \Carbon\Carbon::create()->setMonth($m)->locale('ro')->isoFormat('MMMM');
                                    $monthData = $allYearsSummary[$year][$m] ?? ['incasare' => 0, 'plata' => 0, 'extrase' => 0, 'total' => 0];
                                    $hasFiles = $monthData['total'] > 0;
                                @endphp
                                <div class="relative flex flex-col bg-white rounded-lg shadow-sm border border-slate-200 hover:shadow-md hover:border-primary-300 transition-all {{ !$hasFiles ? 'opacity-60' : '' }}">
                                    <a href="{{ route('financial.files.index', ['year' => $year, 'month' => $m]) }}" class="block p-5 flex-1">
                                        <div class="flex items-center gap-3 mb-3">
                                            <h3 class="text-lg font-semibold text-slate-900 capitalize">{{ $monthName }}</h3>
                                            <span class="ml-auto inline-flex items-center justify-center h-8 rounded-full bg-primary-100 text-primary-700 text-sm font-bold pl-3 tabular-nums">
                                                {{ $monthData['total'] }}
                                            </span>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="text-slate-600">Încasări:</span>
                                                <span class="font-medium text-green-700 tabular-nums text-right min-w-[2ch]">{{ $monthData['incasare'] }}</span>
                                            </div>
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="text-slate-600">Plăți:</span>
                                                <span class="font-medium text-red-700 tabular-nums text-right min-w-[2ch]">{{ $monthData['plata'] }}</span>
                                            </div>
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="text-slate-600">Extrase:</span>
                                                <span class="font-medium text-blue-700 tabular-nums text-right min-w-[2ch]">{{ $monthData['extrase'] }}</span>
                                            </div>
                                        </div>
                                    </a>
                                    @if($hasFiles)
                                        <div class="border-t border-slate-100 px-5 py-2.5 bg-slate-50/50 rounded-b-lg">
                                            <a href="{{ route('financial.files.download-monthly-zip', ['year' => $year, 'month' => $m]) }}"
                                               onclick="event.stopPropagation()"
                                               class="inline-flex items-center gap-2 text-xs font-medium text-slate-600 hover:text-slate-900 transition-colors group">
                                                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                <span>Descarcă arhiva pentru contabil</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    </div>

                @elseif($month && !$tip)
                    <!-- Month Level: Show Category Statistics -->
                    <!-- Breadcrumb -->
                    <div class="bg-white border-b border-slate-200 px-6 py-3">
                        <div class="flex items-center gap-2 text-sm">
                            <a href="{{ route('financial.files.index', ['year' => $year]) }}" class="text-slate-600 hover:text-slate-900">{{ $year }}</a>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-slate-900 font-medium capitalize">{{ \Carbon\Carbon::create()->setMonth($month)->locale('ro')->isoFormat('MMMM') }}</span>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-slate-900 capitalize">{{ \Carbon\Carbon::create()->setMonth($month)->locale('ro')->isoFormat('MMMM') }} {{ $year }}</h2>
                            @if(($allYearsSummary[$year][$month]['total'] ?? 0) > 0)
                                <a href="{{ route('financial.files.download-monthly-zip', ['year' => $year, 'month' => $month]) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 text-sm font-medium transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Descarcă tot (ZIP)
                                </a>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            @php
                                $monthData = $allYearsSummary[$year][$month] ?? ['incasare' => 0, 'plata' => 0, 'extrase' => 0, 'total' => 0];
                                $categories = [
                                    'incasare' => ['label' => 'Încasări', 'color' => 'green', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                    'plata' => ['label' => 'Plăți', 'color' => 'red', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                                    'extrase' => ['label' => 'Extrase', 'color' => 'blue', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                                    'general' => ['label' => 'General', 'color' => 'slate', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
                                ];
                            @endphp

                            <!-- Încasări Category -->
                            <a href="{{ route('financial.files.index', ['year' => $year, 'month' => $month, 'tip' => 'incasare']) }}"
                               class="block bg-white rounded-lg shadow-sm border-2 border-green-200 p-6 hover:shadow-md hover:border-green-400 transition-all {{ ($monthData['incasare'] ?? 0) == 0 ? 'opacity-60' : '' }}">
                                <div class="flex items-center gap-4 mb-2">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-slate-900">Încasări</h3>
                                        <p class="text-2xl font-bold text-green-600 mt-1">{{ $monthData['incasare'] ?? 0 }}</p>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ ($monthData['incasare'] ?? 0) > 0 ? 'Folder cu Încasări' : 'Folder gol' }}
                                </div>
                            </a>

                            <!-- Plăți Category -->
                            <a href="{{ route('financial.files.index', ['year' => $year, 'month' => $month, 'tip' => 'plata']) }}"
                               class="block bg-white rounded-lg shadow-sm border-2 border-red-200 p-6 hover:shadow-md hover:border-red-400 transition-all {{ ($monthData['plata'] ?? 0) == 0 ? 'opacity-60' : '' }}">
                                <div class="flex items-center gap-4 mb-2">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-slate-900">Plăți</h3>
                                        <p class="text-2xl font-bold text-red-600 mt-1">{{ $monthData['plata'] ?? 0 }}</p>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ ($monthData['plata'] ?? 0) > 0 ? 'Folder cu Plăți' : 'Folder gol' }}
                                </div>
                            </a>

                            <!-- Extrase Category -->
                            <a href="{{ route('financial.files.index', ['year' => $year, 'month' => $month, 'tip' => 'extrase']) }}"
                               class="block bg-white rounded-lg shadow-sm border-2 border-blue-200 p-6 hover:shadow-md hover:border-blue-400 transition-all {{ ($monthData['extrase'] ?? 0) == 0 ? 'opacity-60' : '' }}">
                                <div class="flex items-center gap-4 mb-2">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-slate-900">Extrase</h3>
                                        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $monthData['extrase'] ?? 0 }}</p>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ ($monthData['extrase'] ?? 0) > 0 ? 'Folder cu Extrase' : 'Folder gol' }}
                                </div>
                            </a>

                            <!-- General Category -->
                            <a href="{{ route('financial.files.index', ['year' => $year, 'month' => $month, 'tip' => 'general']) }}"
                               class="block bg-white rounded-lg shadow-sm border-2 border-slate-200 p-6 hover:shadow-md hover:border-slate-400 transition-all {{ ($monthData['general'] ?? 0) == 0 ? 'opacity-60' : '' }}">
                                <div class="flex items-center gap-4 mb-2">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-slate-900">General</h3>
                                        <p class="text-2xl font-bold text-slate-600 mt-1">{{ $monthData['general'] ?? 0 }}</p>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ ($monthData['general'] ?? 0) > 0 ? 'Folder cu General' : 'Folder gol' }}
                                </div>
                            </a>
                        </div>
                    </div>

                @else
                    <!-- Category Level: Show Files Table -->
                    <!-- Breadcrumb -->
                    <div class="bg-white border-b border-slate-200 px-6 py-3">
                        <div class="flex items-center gap-2 text-sm">
                            <a href="{{ route('financial.files.index', ['year' => $year]) }}" class="text-slate-600 hover:text-slate-900">{{ $year }}</a>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <a href="{{ route('financial.files.index', ['year' => $year, 'month' => $month]) }}" class="text-slate-600 hover:text-slate-900 capitalize">{{ \Carbon\Carbon::create()->setMonth($month)->locale('ro')->isoFormat('MMMM') }}</a>
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
                                                            <div class="text-sm font-medium text-slate-900">{{ pathinfo($file->file_name, PATHINFO_FILENAME) }}</div>
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
                @endif
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         x-data="window.fileUploadManager()">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-slate-200 sticky top-0 bg-white z-10">
                <h3 class="text-lg font-semibold text-slate-900">Încarcă fișiere noi</h3>
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
                    <label class="block text-sm font-medium text-slate-700 mb-2">Selectează fișiere</label>

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
                                <span class="text-sm font-medium text-slate-900">Click pentru a încărca</span>
                                <span class="text-sm text-slate-500"> sau trage și plasează aici</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">PDF, DOC, XLS, JPG, PNG, ZIP până la 10MB per fișier</p>
                        </label>
                    </div>

                    <!-- Files Preview -->
                    <template x-if="newFiles.length > 0">
                        <div class="mt-4 space-y-2">
                            <p class="text-sm font-medium text-slate-700">Fișiere selectate (<span x-text="newFiles.length"></span>):</p>
                            <template x-for="(file, index) in newFiles" :key="index">
                                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-slate-900 truncate" x-text="file.name"></p>
                                            <p class="text-xs text-slate-500" x-text="formatFileSize(file.size)"></p>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeNewFile(index)" class="text-red-600 hover:text-red-800 flex-shrink-0 ml-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
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

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')"
                            class="px-4 py-2 text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                        Anulează
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                            :disabled="newFiles.length === 0"
                            :class="{ 'opacity-50 cursor-not-allowed': newFiles.length === 0 }">
                        <span x-text="newFiles.length === 0 ? 'Selectează fișiere' : 'Încarcă ' + newFiles.length + (newFiles.length === 1 ? ' fișier' : ' fișiere')"></span>
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
        // Helper: Get current path from server context
        function getCurrentPath() {
            const year = {{ $year }};
            const month = {{ $month ?? 'null' }};
            const path = [`year-${year}`];
            if (month) {
                path.push(`month-${year}-${month}`);
            }
            return path;
        }

        // Helper: Initialize expanded state (merge saved + current)
        function initExpandedState() {
            const saved = JSON.parse(localStorage.getItem('treeExpanded') || '[]');
            const current = getCurrentPath();
            // Use Set to avoid duplicates, then convert to array
            return [...new Set([...saved, ...current])];
        }

        // Main Alpine.js component
        function fileTree() {
            return {
                // State: Array of expanded node IDs
                expanded: initExpandedState(),

                // Navigation state
                loading: false,
                currentYear: {{ $year }},
                currentMonth: {{ $month ?? 'null' }},
                currentTip: {{ $tip ? "'{$tip}'" : 'null' }},

                // Query: Check if node is expanded
                isExpanded(nodeId) {
                    return this.expanded.includes(nodeId);
                },

                // Command: Toggle node expansion
                toggle(nodeId) {
                    if (this.isExpanded(nodeId)) {
                        this.expanded = this.expanded.filter(id => id !== nodeId);
                    } else {
                        this.expanded = [...this.expanded, nodeId];
                    }
                    this.persist();
                },

                // Command: Expand node
                expand(nodeId) {
                    if (!this.isExpanded(nodeId)) {
                        this.expanded = [...this.expanded, nodeId];
                        this.persist();
                    }
                },

                // Command: Collapse node
                collapse(nodeId) {
                    this.expanded = this.expanded.filter(id => id !== nodeId);
                    this.persist();
                },

                // Persistence: Save to localStorage
                persist() {
                    localStorage.setItem('treeExpanded', JSON.stringify(this.expanded));
                },

                // SPA Navigation: Navigate without page reload
                async navigate(url, pushState = true) {
                    // Prevent navigation if already loading
                    if (this.loading) return;

                    this.loading = true;

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const data = await response.json();

                        // Update current context
                        this.currentYear = data.year;
                        this.currentMonth = data.month;
                        this.currentTip = data.tip;

                        // Update content area
                        this.updateContent(data);

                        // Update browser history
                        if (pushState) {
                            history.pushState({ url, data }, '', url);
                        }

                    } catch (error) {
                        console.error('Navigation error:', error);
                        // Fallback to traditional navigation on error
                        window.location.href = url;
                    } finally {
                        this.loading = false;
                    }
                },

                // Update the content area with new data
                updateContent(data) {
                    const contentArea = document.getElementById('file-content-area');
                    if (!contentArea) return;

                    // Build new HTML based on navigation level
                    let html = '';

                    if (!data.month) {
                        // Year level - show months
                        html = this.renderYearView(data);
                    } else if (data.month && !data.tip) {
                        // Month level - show categories
                        html = this.renderMonthView(data);
                    } else {
                        // Category level - show files
                        html = this.renderFilesView(data);
                    }

                    contentArea.innerHTML = html;
                },

                // Render Year View - Grid of months
                renderYearView(data) {
                    const yearTotal = Object.values(data.allYearsSummary[data.year] || {}).reduce((sum, month) => sum + (month.total || 0), 0);
                    const downloadBtn = yearTotal > 0 ? `
                        <a href="/financial/files/download-yearly-zip/${data.year}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Descarcă tot (ZIP)
                        </a>` : '';

                    let html = `
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-2xl font-bold text-slate-900">Fișiere ${data.year}</h2>
                                ${downloadBtn}
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">`;

                    const monthNames = ['', 'ianuarie', 'februarie', 'martie', 'aprilie', 'mai', 'iunie', 'iulie', 'august', 'septembrie', 'octombrie', 'noiembrie', 'decembrie'];

                    for (let m = 12; m >= 1; m--) {
                        const monthData = data.allYearsSummary[data.year]?.[m] || {incasare: 0, plata: 0, extrase: 0, total: 0};
                        const hasFiles = monthData.total > 0;
                        const monthName = monthNames[m];

                        html += `
                            <div class="relative flex flex-col bg-white rounded-lg shadow-sm border border-slate-200 hover:shadow-md hover:border-primary-300 transition-all ${!hasFiles ? 'opacity-60' : ''}">
                                <a href="/financial/files?year=${data.year}&month=${m}"
                                   @click.prevent="navigate($event.target.closest('a').href)"
                                   class="block p-5 flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <h3 class="text-lg font-semibold text-slate-900 capitalize">${monthName}</h3>
                                        <span class="ml-auto inline-flex items-center justify-center h-8 rounded-full bg-primary-100 text-primary-700 text-sm font-bold pl-3 tabular-nums">${monthData.total}</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-slate-600">Încasări:</span>
                                            <span class="font-medium text-green-700 tabular-nums text-right min-w-[2ch]">${monthData.incasare}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-slate-600">Plăți:</span>
                                            <span class="font-medium text-red-700 tabular-nums text-right min-w-[2ch]">${monthData.plata}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-slate-600">Extrase:</span>
                                            <span class="font-medium text-blue-700 tabular-nums text-right min-w-[2ch]">${monthData.extrase}</span>
                                        </div>
                                    </div>
                                </a>`;

                        if (hasFiles) {
                            html += `
                                <div class="border-t border-slate-100 px-5 py-2.5 bg-slate-50/50 rounded-b-lg">
                                    <a href="/financial/files/download-monthly-zip/${data.year}/${m}"
                                       onclick="event.stopPropagation()"
                                       class="inline-flex items-center gap-2 text-xs font-medium text-slate-600 hover:text-slate-900 transition-colors group">
                                        <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <span>Descarcă arhiva pentru contabil</span>
                                    </a>
                                </div>`;
                        }

                        html += `</div>`;
                    }

                    html += `</div></div>`;
                    return html;
                },

                // Render Month View - Category cards
                renderMonthView(data) {
                    const monthNames = ['', 'ianuarie', 'februarie', 'martie', 'aprilie', 'mai', 'iunie', 'iulie', 'august', 'septembrie', 'octombrie', 'noiembrie', 'decembrie'];
                    const monthName = monthNames[data.month];
                    const monthData = data.allYearsSummary[data.year]?.[data.month] || {incasare: 0, plata: 0, extrase: 0, general: 0, total: 0};

                    const downloadBtn = monthData.total > 0 ? `
                        <a href="/financial/files/download-monthly-zip/${data.year}/${data.month}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Descarcă tot (ZIP)
                        </a>` : '';

                    return `
                        <div class="bg-white border-b border-slate-200 px-6 py-3">
                            <div class="flex items-center gap-2 text-sm">
                                <a href="/financial/files?year=${data.year}"
                                   @click.prevent="navigate($event.target.closest('a').href)"
                                   class="text-slate-600 hover:text-slate-900">${data.year}</a>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span class="text-slate-900 font-medium capitalize">${monthName}</span>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-2xl font-bold text-slate-900 capitalize">${monthName} ${data.year}</h2>
                                ${downloadBtn}
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                ${this.renderCategoryCard('incasare', 'Încasări', 'green', monthData.incasare, data.year, data.month)}
                                ${this.renderCategoryCard('plata', 'Plăți', 'red', monthData.plata, data.year, data.month)}
                                ${this.renderCategoryCard('extrase', 'Extrase', 'blue', monthData.extrase, data.year, data.month)}
                                ${this.renderCategoryCard('general', 'General', 'slate', monthData.general, data.year, data.month)}
                            </div>
                        </div>`;
                },

                // Helper: Render category card
                renderCategoryCard(tip, label, color, count, year, month) {
                    const icons = {
                        incasare: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        plata: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                        extrase: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                        general: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'
                    };

                    return `
                        <a href="/financial/files?year=${year}&month=${month}&tip=${tip}"
                           @click.prevent="navigate($event.target.closest('a').href)"
                           class="block bg-white rounded-lg shadow-sm border-2 border-${color}-200 p-6 hover:shadow-md hover:border-${color}-400 transition-all ${count == 0 ? 'opacity-60' : ''}">
                            <div class="flex items-center gap-4 mb-2">
                                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-${color}-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-${color}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icons[tip]}"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-slate-900">${label}</h3>
                                    <p class="text-2xl font-bold text-${color}-600 mt-1">${count}</p>
                                </div>
                            </div>
                            <div class="text-xs text-slate-500">
                                ${count > 0 ? `Folder cu ${label}` : 'Folder gol'}
                            </div>
                        </a>`;
                },

                // Render Files View - Table of files
                renderFilesView(data) {
                    const monthNames = ['', 'ianuarie', 'februarie', 'martie', 'aprilie', 'mai', 'iunie', 'iulie', 'august', 'septembrie', 'octombrie', 'noiembrie', 'decembrie'];
                    const monthName = monthNames[data.month];
                    const tipLabels = {incasare: 'Încasări', plata: 'Plăți', extrase: 'Extrase', general: 'General'};
                    const tipLabel = tipLabels[data.tip] || 'General';

                    let html = `
                        <div class="bg-white border-b border-slate-200 px-6 py-3">
                            <div class="flex items-center gap-2 text-sm">
                                <a href="/financial/files?year=${data.year}"
                                   @click.prevent="navigate($event.target.closest('a').href)"
                                   class="text-slate-600 hover:text-slate-900">${data.year}</a>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                <a href="/financial/files?year=${data.year}&month=${data.month}"
                                   @click.prevent="navigate($event.target.closest('a').href)"
                                   class="text-slate-600 hover:text-slate-900 capitalize">${monthName}</a>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span class="text-slate-900 font-medium capitalize">${tipLabel}</span>
                            </div>
                        </div>

                        <div class="bg-white border-b border-slate-200 px-6 py-3">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-slate-900">Fișiere (${data.pagination.total})</h2>
                                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                                        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition-colors">
                                    + Încarcă fișier
                                </button>
                            </div>
                        </div>

                        <div class="p-6">`;

                    if (data.files.length === 0) {
                        html += `
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-slate-500 mb-2">Nu există fișiere în această categorie</p>
                                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                                        class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                                    Încarcă primul fișier
                                </button>
                            </div>`;
                    } else {
                        html += this.renderFilesTable(data.files);
                        html += data.pagination.links;
                    }

                    html += `</div>`;
                    return html;
                },

                // Helper: Render files table
                renderFilesTable(files) {
                    let html = `
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
                                <tbody class="bg-white divide-y divide-slate-200">`;

                    files.forEach(file => {
                        const tipBadges = {
                            incasare: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Încasare</span>',
                            plata: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Plată</span>',
                            extrase: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Extrase</span>',
                            general: '<span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-800">General</span>'
                        };

                        html += `
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">${file.file_name.replace(/\.[^/.]+$/, '')}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ${tipBadges[file.tip] || tipBadges.general}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    ${this.formatFileSize(file.file_size)}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    ${new Date(file.created_at).toLocaleString('ro-RO')}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                    <a href="/financial/files/${file.id}" target="_blank"
                                       class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                       title="Vizualizare">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="/financial/files/${file.id}/download"
                                       class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                       title="Descărcare">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>`;
                    });

                    html += `
                                </tbody>
                            </table>
                        </div>`;

                    return html;
                },

                // Helper: Format file size
                formatFileSize(bytes) {
                    if (!bytes) return '-';
                    if (bytes < 1024) return bytes + ' B';
                    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                    return (bytes / 1048576).toFixed(1) + ' MB';
                }
            }
        }

        // Handle browser back/forward buttons
        window.addEventListener('popstate', function(event) {
            if (event.state && event.state.url) {
                // Get the Alpine component instance
                const treeComponent = Alpine.$data(document.querySelector('[x-data="fileTree()"]'));
                if (treeComponent) {
                    treeComponent.navigate(event.state.url, false);
                }
            }
        });

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
