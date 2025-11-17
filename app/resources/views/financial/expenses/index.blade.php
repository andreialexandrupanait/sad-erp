<x-app-layout>
    <x-slot name="pageTitle">Cheltuieli</x-slot>

    <x-slot name="headerActions">
        <div class="flex items-center gap-3">
            <form method="GET" id="filterForm" class="flex items-center gap-3">
                <input type="hidden" name="month" value="{{ $month }}">

                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-700">{{ __('An') }}:</label>
                    <x-ui.select name="year" onchange="this.form.submit()">
                        @foreach($availableYears as $availableYear)
                            <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-700">{{ __('Valută') }}:</label>
                    <x-ui.select name="currency" onchange="this.form.submit()">
                        <option value="">{{ __('Toate') }}</option>
                        @foreach($currencies as $curr)
                            <option value="{{ $curr->value }}" {{ $currency == $curr->value ? 'selected' : '' }}>{{ $curr->label }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </form>

            <x-ui.button variant="default" onclick="window.location.href='{{ route('financial.expenses.create') }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Adaugă cheltuială
            </x-ui.button>
        </div>
    </x-slot>

    <div class="p-6" x-data>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <!-- Widgets Container -->
        <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Widget 1: Filtered Total -->
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-medium text-slate-500 uppercase">
                            @if($month)
                                @php
                                    $romanianMonths = ['Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'];
                                @endphp
                                {{ __('Total pentru') }} {{ $romanianMonths[$month - 1] }}
                            @else
                                {{ __('Total an') }} {{ $year }}
                            @endif
                        </p>
                        <div class="mt-2">
                            @if($filteredTotals->count() > 0)
                                <p class="text-lg font-bold text-red-700">
                                    @foreach($filteredTotals as $curr => $total)
                                        {{ number_format($total, 2) }} {{ $curr }}@if(!$loop->last) / @endif
                                    @endforeach
                                </p>
                            @else
                                <p class="text-lg font-bold text-slate-400">0.00</p>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ $currency ?: __('Toate valutele') }}
                        </p>
                    </div>
                    <div class="p-3 bg-red-50 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Widget 2: Yearly Total (RON Only) -->
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-medium text-slate-500 uppercase">{{ __('Total pe an') }} {{ $year }}</p>
                        <div class="mt-2">
                            <p class="text-lg font-bold text-blue-700">{{ number_format($yearTotalsRonOnly, 2) }} RON</p>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ __('Doar RON') }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Month Selector -->
        <div class="mb-6 bg-white rounded-lg shadow p-4">
            <form method="GET" id="monthForm">
                <!-- Keep year and currency hidden to maintain filter state -->
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="currency" value="{{ $currency }}">

                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-slate-700">{{ __('Selectează luna') }}</h3>
                    @if($month)
                        <button type="button" onclick="document.getElementById('month-input').value = ''; document.getElementById('monthForm').submit();" class="text-xs text-slate-500 hover:text-slate-700">
                            {{ __('Toate lunile') }}
                        </button>
                    @endif
                </div>
                <input type="hidden" name="month" id="month-input" value="{{ $month }}">
                <div class="grid grid-cols-6 gap-2 sm:grid-cols-12">
                    @foreach(['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Noi', 'Dec'] as $index => $monthName)
                        @php
                            $monthNum = $index + 1;
                            $hasTransactions = isset($monthsWithTransactions[$monthNum]);
                            $isSelected = $month == $monthNum;
                            $transactionCount = $hasTransactions ? $monthsWithTransactions[$monthNum]['count'] : 0;
                        @endphp
                        <div class="relative group">
                            <button
                                type="button"
                                onclick="document.getElementById('month-input').value = '{{ $monthNum }}'; document.getElementById('monthForm').submit();"
                                class="w-full relative flex flex-col items-center justify-center p-3 rounded-lg border-2 transition-all
                                    {{ $isSelected ? 'border-red-500 bg-red-50 text-red-700 font-semibold' : ($hasTransactions ? 'border-slate-200 bg-slate-50 hover:border-red-300 hover:bg-red-50' : 'border-slate-100 bg-white text-slate-400') }}"
                                title="{{ $hasTransactions ? $transactionCount . ' ' . __('transactions') : __('No items') }}"
                            >
                                <span class="text-xs font-medium">{{ $monthName }}</span>
                                @if($hasTransactions)
                                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $isSelected ? 'bg-red-400' : 'bg-blue-400' }} opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 {{ $isSelected ? 'bg-red-500' : 'bg-blue-500' }}"></span>
                                    </span>
                                    <span class="mt-1 text-[10px] font-semibold {{ $isSelected ? 'text-red-600' : 'text-slate-600' }}">
                                        {{ $transactionCount }}
                                    </span>
                                @endif
                            </button>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>

        <!-- Category Breakdown Widget -->
        @if($categoryBreakdown->isNotEmpty())
            <div class="mb-6 bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Top Expense Categories') }}</h3>
                    <span class="text-xs text-slate-500">{{ $month ? __('Month') : __('Year') }} {{ $year }}</span>
                </div>
                <div class="space-y-3">
                    @foreach($categoryBreakdown as $item)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <span class="px-2 py-1 rounded text-xs {{ $item->category->badge_class ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $item->category->label ?? __('Uncategorized') }}
                                </span>
                                <span class="text-xs text-slate-500">{{ $item->count }} {{ __('transactions') }}</span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-red-600">{{ number_format($item->total, 2) }}</p>
                            </div>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            @php
                                $maxTotal = $categoryBreakdown->first()->total;
                                $percentage = $maxTotal > 0 ? ($item->total / $maxTotal) * 100 : 0;
                            @endphp
                            <div class="bg-red-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Table -->
        <x-ui.card>
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <p class="text-sm text-slate-600">
                    {{ __('Afișare') }} <span class="font-semibold text-slate-900">{{ $recordCount }}</span> {{ __('înregistrări') }}
                    @if($month)
                        @php
                            $romanianMonths = ['Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'];
                        @endphp
                        <span class="text-slate-500">{{ __('pentru') }} {{ $romanianMonths[$month - 1] }} {{ $year }}</span>
                    @else
                        <span class="text-slate-500">{{ __('pentru anul') }} {{ $year }}</span>
                    @endif
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-slate-50/50">
                            <x-ui.sortable-header column="occurred_at" label="{{ __('Date') }}" />
                            <x-ui.sortable-header column="document_name" label="{{ __('Document') }}" />
                            <x-ui.sortable-header column="category_option_id" label="{{ __('Category') }}" />
                            <x-ui.sortable-header column="amount" label="{{ __('Amount') }}" class="text-right" />
                            <x-ui.table-head class="text-center">{{ __('Files') }}</x-ui.table-head>
                            <x-ui.table-head class="text-right">{{ __('Actions') }}</x-ui.table-head>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        @forelse($expenses as $expense)
                            <x-ui.table-row>
                                <x-ui.table-cell>
                                    <div class="text-sm text-slate-900">{{ $expense->occurred_at->format('d M Y') }}</div>
                                </x-ui.table-cell>
                                <x-ui.table-cell>
                                    <div class="text-sm font-medium text-slate-900">{{ $expense->document_name }}</div>
                                </x-ui.table-cell>
                                <x-ui.table-cell>
                                    @if($expense->category)
                                        <span class="px-2 py-1 rounded text-xs {{ $expense->category->badge_class }}">
                                            {{ $expense->category->label }}
                                        </span>
                                    @else
                                        <span class="text-sm text-slate-400">—</span>
                                    @endif
                                </x-ui.table-cell>
                                <x-ui.table-cell class="text-right">
                                    <div class="text-sm font-bold text-red-600">{{ number_format($expense->amount, 2) }} {{ $expense->currency }}</div>
                                </x-ui.table-cell>

                                <!-- Files Column -->
                                <x-ui.table-cell class="text-center">
                                    @if($expense->files_count > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $expense->files_count }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </x-ui.table-cell>

                                <!-- Actions Column -->
                                <x-ui.table-cell class="text-right">
                                    <x-table-actions
                                        :viewUrl="route('financial.expenses.show', $expense)"
                                        :editUrl="route('financial.expenses.edit', $expense)"
                                        :deleteAction="route('financial.expenses.destroy', $expense)"
                                        :deleteConfirm="__('Are you sure you want to delete this expense?')">
                                        {{-- Download files action --}}
                                        @if($expense->files_count > 0)
                                            @if($expense->files_count == 1)
                                                <a href="{{ route('financial.files.download', $expense->files->first()) }}" class="text-green-600 hover:text-green-900 transition-colors" title="{{ __('Download') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </a>
                                            @else
                                                <a href="{{ route('financial.expenses.show', $expense) }}#files" class="text-green-600 hover:text-green-900 transition-colors" title="{{ __('View files') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        @endif
                                    </x-table-actions>
                                </x-ui.table-cell>
                            </x-ui.table-row>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-sm">{{ __('No expenses') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($expenses->hasPages())
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                    {{ $expenses->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>

    <!-- Toast Notifications -->
    <x-toast />
</x-app-layout>
