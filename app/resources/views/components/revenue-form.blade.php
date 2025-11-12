@props(['revenue' => null, 'clients' => [], 'currencies' => [], 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-ui.card-content>
            <x-revenue-form-fields
                :revenue="$revenue"
                :clients="$clients"
                :currencies="$currencies"
            />
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-6 border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('financial.revenues.index') }}'">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $revenue ? 'Update Revenue' : 'Create Revenue' }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
