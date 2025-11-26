<x-app-layout>
    <x-slot name="pageTitle">{{ __('Create Service') }}</x-slot>

    <div class="p-6 max-w-4xl mx-auto">
        <x-ui.card>
            <x-ui.card-header
                :title="__('New Task Service')"
                :description="__('Create a new billable service for tasks')"
            />

            <x-ui.card-content>
                <form action="{{ route('task-services.store') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <!-- Service Name -->
                        <div>
                            <x-ui.label for="name" required>{{ __('Service Name') }}</x-ui.label>
                            <x-ui.input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name') }}"
                                required
                                placeholder="{{ __('e.g., Web Development, Design, Consulting') }}"
                            />
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Default Hourly Rate -->
                        <div>
                            <x-ui.label for="default_hourly_rate" required>{{ __('Default Hourly Rate (RON)') }}</x-ui.label>
                            <x-ui.input
                                type="number"
                                name="default_hourly_rate"
                                id="default_hourly_rate"
                                value="{{ old('default_hourly_rate', 0) }}"
                                min="0"
                                step="0.01"
                                required
                                placeholder="{{ __('0.00') }}"
                            />
                            <p class="mt-1 text-sm text-slate-500">{{ __('This rate will be used as default for new tasks') }}</p>
                            @error('default_hourly_rate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <x-ui.label for="description">{{ __('Description') }}</x-ui.label>
                            <x-ui.textarea
                                name="description"
                                id="description"
                                rows="4"
                                placeholder="{{ __('Service description...') }}"
                            >{{ old('description') }}</x-ui.textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center">
                            <input
                                type="checkbox"
                                name="is_active"
                                id="is_active"
                                value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-slate-900">
                                {{ __('Active') }}
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Inactive services will not appear in task creation forms') }}</p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-slate-200">
                        <x-ui.button variant="outline" type="button" onclick="window.location.href='{{ route('task-services.index') }}'">
                            {{ __('Cancel') }}
                        </x-ui.button>
                        <x-ui.button variant="default" type="submit">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ __('Create Service') }}
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-app-layout>
