@props(['credential' => null, 'clients' => [], 'platforms' => [], 'credentialTypes' => [], 'sites' => [], 'action', 'method' => 'POST', 'clientStatuses' => []])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-ui.card-content>
            <x-credential-form-fields
                :credential="$credential"
                :clients="$clients"
                :platforms="$platforms"
                :sites="$sites"
                :clientStatuses="$clientStatuses"
                :compact="false"
            />
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-4 border-t border-slate-200 px-4 py-4 sm:px-6 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('credentials.index') }}'">
                {{ __('Cancel') }}
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $credential ? __('Save') : __('Create') }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
