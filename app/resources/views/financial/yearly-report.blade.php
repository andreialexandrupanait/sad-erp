<x-app-layout>
    <x-slot name="pageTitle">Raport {{ $year }}</x-slot>

    <x-slot name="headerActions">
        <a href="{{ route('financial.export', $year) }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition-colors">
            Exportă CSV
        </a>
    </x-slot>

    <div class="p-6">

        <!-- Monthly Summary Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <h2 class="text-lg font-semibold p-4 bg-slate-50">Rezumat lunar</h2>
            <table class="min-w-full">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Lună</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Venituri RON</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Venituri EUR</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Cheltuieli RON</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Cheltuieli EUR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @for($month = 1; $month <= 12; $month++)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium">
                                {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-green-600">
                                {{ number_format($monthlySummary[$month]['revenues_ron'], 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-green-600">
                                {{ number_format($monthlySummary[$month]['revenues_eur'], 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-red-600">
                                {{ number_format($monthlySummary[$month]['expenses_ron'], 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-red-600">
                                {{ number_format($monthlySummary[$month]['expenses_eur'], 2) }}
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <!-- Detailed Transactions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenues -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <h3 class="text-lg font-semibold p-4 bg-green-50 text-green-900">Toate veniturile</h3>
                <div class="p-4 space-y-2">
                    @foreach($revenues as $revenue)
                        <div class="flex justify-between items-center p-2 hover:bg-slate-50 rounded">
                            <div>
                                <p class="text-sm font-medium">{{ $revenue->document_name }}</p>
                                <p class="text-xs text-slate-500">{{ $revenue->occurred_at->format('d M Y') }}</p>
                            </div>
                            <p class="text-sm font-bold text-green-600">{{ number_format($revenue->amount, 2) }} {{ $revenue->currency }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Expenses -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <h3 class="text-lg font-semibold p-4 bg-red-50 text-red-900">Toate cheltuielile</h3>
                <div class="p-4 space-y-2">
                    @foreach($expenses as $expense)
                        <div class="flex justify-between items-center p-2 hover:bg-slate-50 rounded">
                            <div>
                                <p class="text-sm font-medium">{{ $expense->document_name }}</p>
                                <p class="text-xs text-slate-500">{{ $expense->occurred_at->format('d M Y') }}</p>
                            </div>
                            <p class="text-sm font-bold text-red-600">{{ number_format($expense->amount, 2) }} {{ $expense->currency }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
