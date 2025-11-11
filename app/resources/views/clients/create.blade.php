<x-app-layout>
    <x-slot name="header">
        <div class="px-6 lg:px-8 py-8">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                {{ __('Create New Client') }}
            </h2>
            <p class="mt-2 text-sm text-slate-600">Add a new client to your database</p>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8">
        <x-client-form
            :statuses="$clientStatuses"
            :action="route('clients.store')"
            method="POST"
        />
    </div>
</x-app-layout>
