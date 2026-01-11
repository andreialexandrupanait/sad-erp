<x-app-layout>
    <x-slot name="pageTitle">{{ __('Add Domain') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="outline" onclick="window.location.href='{{ route('domains.index') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('Back') }}
        </x-ui.button>
    </x-slot>

    <div class="p-4 md:p-6">
        <div class="max-w-4xl mx-auto">
            <x-domain-form
                :clients="$clients"
                :registrars="$registrars"
                :statuses="$statuses"
                :action="route('domains.store')"
                method="POST"
            />
        </div>
    </div>
</x-app-layout>
