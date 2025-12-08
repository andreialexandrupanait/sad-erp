{{--
    Year Overview - Shows 12 month cards
    URL: /financial/files/{year}
--}}

<x-financial-files-layout
    :year="$year"
    :month="null"
    :category="null"
    :available-years="$availableYears"
    :all-years-summary="$allYearsSummary"
>
    @php
        $yearData = $allYearsSummary[$year] ?? [];
        $yearTotal = collect($yearData)->sum('total');
        $monthNames = [
            1 => 'Ianuarie', 2 => 'Februarie', 3 => 'Martie', 4 => 'Aprilie',
            5 => 'Mai', 6 => 'Iunie', 7 => 'Iulie', 8 => 'August',
            9 => 'Septembrie', 10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie'
        ];
    @endphp

    <div class="p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-slate-900">Fisiere {{ $year }}</h2>

            @if($yearTotal > 0)
                <a href="{{ route('financial.files.download-yearly-zip', ['year' => $year]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descarca tot (ZIP)
                </a>
            @endif
        </div>

        <!-- Months Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @for($m = 12; $m >= 1; $m--)
                @php
                    $monthData = $yearData[$m] ?? ['incasare' => 0, 'plata' => 0, 'extrase' => 0, 'general' => 0, 'total' => 0];
                    $hasFiles = ($monthData['total'] ?? 0) > 0;
                @endphp

                <div class="relative flex flex-col bg-white rounded-lg shadow-sm border border-slate-200 hover:shadow-md hover:border-primary-300 transition-all {{ !$hasFiles ? 'opacity-60' : '' }}">
                    <!-- Month Card Content -->
                    <a href="{{ route('financial.files.month', ['year' => $year, 'month' => $m]) }}"
                       class="block p-5 flex-1">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-slate-900">{{ $monthNames[$m] }}</h3>
                            <span class="inline-flex items-center justify-center min-w-[2rem] h-7 px-2 rounded-full bg-primary-100 text-primary-700 text-sm font-bold tabular-nums">
                                {{ $monthData['total'] ?? 0 }}
                            </span>
                        </div>

                        <div class="space-y-2">
                            <!-- Incasari -->
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-600">Incasari:</span>
                                <span class="font-medium {{ ($monthData['incasare'] ?? 0) > 0 ? 'text-green-700' : 'text-slate-400' }} tabular-nums">
                                    {{ $monthData['incasare'] ?? 0 }}
                                </span>
                            </div>

                            <!-- Plati -->
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-600">Plati:</span>
                                <span class="font-medium {{ ($monthData['plata'] ?? 0) > 0 ? 'text-red-700' : 'text-slate-400' }} tabular-nums">
                                    {{ $monthData['plata'] ?? 0 }}
                                </span>
                            </div>

                            <!-- Extrase -->
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-600">Extrase:</span>
                                <span class="font-medium {{ ($monthData['extrase'] ?? 0) > 0 ? 'text-blue-700' : 'text-slate-400' }} tabular-nums">
                                    {{ $monthData['extrase'] ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </a>

                    <!-- Download Button -->
                    @if($hasFiles)
                        <div class="border-t border-slate-100 px-5 py-2.5 bg-slate-50/50 rounded-b-lg">
                            <a href="{{ route('financial.files.download-monthly-zip', ['year' => $year, 'month' => $m]) }}"
                               onclick="event.stopPropagation()"
                               class="inline-flex items-center gap-2 text-xs font-medium text-slate-600 hover:text-slate-900 transition-colors group">
                                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>Descarca arhiva pentru contabil</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endfor
        </div>
    </div>
</x-financial-files-layout>
