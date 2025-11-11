@props(['revenueTrend', 'expenseTrend'])

<x-ui.card>
    <x-ui.card-header>
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900">Tendință lunară</h3>
            <div class="flex items-center gap-4 text-xs">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-slate-600">Venituri</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <span class="text-slate-600">Cheltuieli</span>
                </div>
            </div>
        </div>
    </x-ui.card-header>
    <x-ui.card-content>
        <div class="h-64 flex items-end justify-between gap-2">
            @foreach($revenueTrend as $index => $month)
                <div class="flex-1 flex flex-col items-center gap-2">
                    <div class="w-full flex flex-col gap-1 items-center">
                        @php
                            $maxAmount = max(array_column($revenueTrend, 'amount'));
                            $maxExpense = max(array_column($expenseTrend, 'amount'));
                            $overallMax = max($maxAmount, $maxExpense, 1);
                            $revenueHeight = ($month['amount'] / $overallMax) * 100;
                            $expenseHeight = ($expenseTrend[$index]['amount'] / $overallMax) * 100;
                        @endphp
                        <div class="w-full bg-green-500 rounded-t transition-all hover:bg-green-600"
                             style="height: {{ $revenueHeight }}%"
                             title="Venituri: {{ $month['formatted'] }}">
                        </div>
                        <div class="w-full bg-red-500 rounded-t transition-all hover:bg-red-600"
                             style="height: {{ $expenseHeight }}%"
                             title="Cheltuieli: {{ $expenseTrend[$index]['formatted'] }}">
                        </div>
                    </div>
                    <span class="text-xs text-slate-600 font-medium">{{ $month['month'] }}</span>
                </div>
            @endforeach
        </div>
    </x-ui.card-content>
</x-ui.card>
