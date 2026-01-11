<x-app-layout>
    <x-slot name="pageTitle">{{ __('Edit Credential') }}</x-slot>

    <div class="p-4 md:p-6">
        <div class="max-w-4xl mx-auto">
            <x-credential-form
                :credential="$credential"
                :clients="$clients"
                :platforms="$platforms"
                :credentialTypes="$credentialTypes"
                :sites="$sites"
                :action="route('credentials.update', $credential)"
                method="PUT"
            />
        </div>
    </div>
</x-app-layout>
