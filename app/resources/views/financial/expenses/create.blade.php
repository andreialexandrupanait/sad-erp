<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ __('Add New Expense') }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">Register a new expense in the system</p>
            </div>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8">
        <div class="max-w-4xl mx-auto">
            <x-expense-form
                :categories="$categories"
                :currencies="$currencies"
                :action="route('financial.expenses.store')"
                method="POST"
            />
        </div>
    </div>
</x-app-layout>
