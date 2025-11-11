<x-app-layout>
    <x-slot name="header">
        <div class="px-6 lg:px-8 py-8">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                {{ __('Edit Client') }}
            </h2>
            <p class="mt-2 text-sm text-slate-600">{{ $client->name }}</p>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8">
        <x-client-form
            :client="$client"
            :statuses="$clientStatuses"
            :action="route('clients.update', $client)"
            method="PUT"
        />
    </div>
</x-app-layout>
