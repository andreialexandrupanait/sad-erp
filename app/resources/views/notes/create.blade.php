<x-app-layout>
    <x-slot name="pageTitle">{{ __('Create Note') }}</x-slot>

    <div class="p-4 md:p-6 space-y-6">
        <x-ui.card>
            <x-ui.card-content>
                <form method="POST" action="{{ route('notes.store') }}" class="space-y-6">
                    @csrf

                    <!-- Client Selection -->
                    <div>
                        <x-ui.label for="client_id" required>{{ __('Client') }}</x-ui.label>
                        <x-ui.client-select
                            name="client_id"
                            :clients="$clients"
                            :selected="old('client_id', $selectedClient?->id)"
                            :placeholder="__('Select a client...')"
                            :allowEmpty="false"
                            required
                        />
                        @error('client_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Note Content -->
                    <div>
                        <x-ui.label for="content" required>{{ __('Note Content') }}</x-ui.label>
                        <x-ui.simple-editor
                            name="content"
                            id="content"
                            :value="old('content', '')"
                            :placeholder="__('Write your message here...')"
                            minHeight="400"
                            :clients="$clients"
                            clientFieldId="client_id"
                        />
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-slate-500">
                            {{ __('Tags will be automatically extracted based on keywords like: contract, factura, grafica, mentenanta, hosting, etc.') }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 sm:gap-x-4 border-t border-slate-200 pt-4">
                        <x-ui.button type="button" variant="outline" class="w-full sm:w-auto" onclick="window.history.back()">
                            {{ __('Cancel') }}
                        </x-ui.button>
                        <x-ui.button type="submit" variant="default" class="w-full sm:w-auto">
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
