<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ __('Add Internal Account') }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">Create a new internal account or application credential</p>
            </div>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8">
        <div class="max-w-4xl mx-auto">
            <x-internal-account-form
                :action="route('internal-accounts.store')"
                method="POST"
            />
        </div>
    </div>
</x-app-layout>
