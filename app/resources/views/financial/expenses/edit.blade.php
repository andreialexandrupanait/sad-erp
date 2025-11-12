<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ __('Edit Expense') }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">Update expense information</p>
            </div>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8">
        <div class="max-w-4xl mx-auto">
            <x-expense-form
                :expense="$expense"
                :categories="$categories"
                :currencies="$currencies"
                :action="route('financial.expenses.update', $expense)"
                method="PUT"
            />
        </div>
    </div>
</x-app-layout>
