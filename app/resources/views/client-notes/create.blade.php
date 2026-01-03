<x-app-layout>
    <x-slot name="pageTitle">{{ __('Create Note') }}</x-slot>

    <div class="p-6 space-y-6">
        <x-ui.card>
            <x-ui.card-content>
                <form method="POST" action="{{ route('client-notes.store') }}" class="space-y-6">
                    @csrf

                    <!-- Client Selection -->
                    <div>
                        <x-ui.label for="client_id" required>{{ __('Client') }}</x-ui.label>
                        <x-ui.searchable-select
                            name="client_id"
                            id="client_id"
                            :options="$clients"
                            :selected="old('client_id', $selectedClient?->id)"
                            :placeholder="__('Select a client...')"
                            required
                        />
                        @error('client_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Note Content -->
                    <div>
                        <x-ui.label for="content" required>{{ __('Note Content') }}</x-ui.label>
                        <x-ui.textarea
                            id="content"
                            name="content"
                            rows="12"
                            required
                            placeholder="{{ __('Write your message here...') }}"
                            class="font-mono"
                        >{{ old('content') }}</x-ui.textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-slate-500">
                            {{ __('Tags will be automatically extracted based on keywords like: contract, factura, grafica, mentenanta, hosting, etc.') }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-x-4 border-t border-slate-200 pt-4">
                        <x-ui.button type="button" variant="outline" onclick="window.history.back()">
                            {{ __('Cancel') }}
                        </x-ui.button>
                        <x-ui.button type="submit" variant="default">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('Save Note') }}
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-app-layout>
