<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    Edit Domain: {{ $domain->domain_name }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">Update domain information</p>
            </div>
            <div class="flex gap-2">
                <x-ui.button variant="outline" onclick="window.location.href='{{ route('domains.show', $domain) }}'">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back
                </x-ui.button>
            </div>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8">
        <x-domain-form
            :domain="$domain"
            :clients="$clients"
            :registrars="$registrars"
            :statuses="$statuses"
            :action="route('domains.update', $domain)"
            method="PUT"
        />
    </div>
</x-app-layout>
