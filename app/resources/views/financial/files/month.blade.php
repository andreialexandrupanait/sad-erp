{{--
    Month Overview - Shows 4 category cards (widgets) linking to category pages
    URL: /financial/files/{year}/{month}
--}}

<x-financial-files-layout
    :year="$year"
    :month="$month"
    :category="null"
    :available-years="$availableYears"
    :all-years-summary="$allYearsSummary"
>
    @php
        $monthName = \Carbon\Carbon::create()->setMonth($month)->locale('ro')->isoFormat('MMMM');
        $monthData = $allYearsSummary[$year][$month] ?? ['incasare' => 0, 'plata' => 0, 'extrase' => 0, 'general' => 0, 'total' => 0];
        $monthTotal = $monthData['total'] ?? 0;
    @endphp

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-slate-200 px-6 py-3">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('financial.files.year', ['year' => $year]) }}"
               class="text-slate-600 hover:text-slate-900 hover:underline">{{ $year }}</a>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-slate-900 font-medium capitalize">{{ $monthName }}</span>
        </div>
    </div>

    <div class="p-4 md:p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-slate-900 capitalize">{{ $monthName }} {{ $year }}</h2>

            <div class="flex items-center gap-3">
                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Incarca fisier
                </button>

                @if($monthTotal > 0)
                    <a href="{{ route('financial.files.download-monthly-zip', ['year' => $year, 'month' => $month]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Descarca tot (ZIP)
                    </a>
                @endif
            </div>
        </div>

        <!-- Categories Grid - 4 Widget Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Incasari -->
            <a href="{{ route('financial.files.category', ['year' => $year, 'month' => $month, 'category' => 'incasare']) }}"
               class="block bg-white rounded-lg shadow-sm border-2 border-green-200 p-6 hover:shadow-md hover:border-green-400 transition-all {{ ($monthData['incasare'] ?? 0) == 0 ? 'opacity-60' : '' }}">
                <div class="flex items-center gap-4 mb-2">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-slate-900">Incasari</h3>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ $monthData['incasare'] ?? 0 }}</p>
                    </div>
                </div>
                <div class="text-xs text-slate-500">
                    {{ ($monthData['incasare'] ?? 0) > 0 ? 'Fisiere de incasari' : 'Folder gol' }}
                </div>
            </a>

            <!-- Plati -->
            <a href="{{ route('financial.files.category', ['year' => $year, 'month' => $month, 'category' => 'plata']) }}"
               class="block bg-white rounded-lg shadow-sm border-2 border-red-200 p-6 hover:shadow-md hover:border-red-400 transition-all {{ ($monthData['plata'] ?? 0) == 0 ? 'opacity-60' : '' }}">
                <div class="flex items-center gap-4 mb-2">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-slate-900">Plati</h3>
                        <p class="text-2xl font-bold text-red-600 mt-1">{{ $monthData['plata'] ?? 0 }}</p>
                    </div>
                </div>
                <div class="text-xs text-slate-500">
                    {{ ($monthData['plata'] ?? 0) > 0 ? 'Fisiere de plati' : 'Folder gol' }}
                </div>
            </a>

            <!-- Extrase -->
            <a href="{{ route('financial.files.category', ['year' => $year, 'month' => $month, 'category' => 'extrase']) }}"
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
                    {{ ($monthData['extrase'] ?? 0) > 0 ? 'Extrase bancare' : 'Folder gol' }}
                </div>
            </a>

            <!-- General -->
            <a href="{{ route('financial.files.category', ['year' => $year, 'month' => $month, 'category' => 'general']) }}"
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
                    {{ ($monthData['general'] ?? 0) > 0 ? 'Alte documente' : 'Folder gol' }}
                </div>
            </a>
        </div>
    </div>
</x-financial-files-layout>
