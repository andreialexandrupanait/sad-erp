<x-app-layout>
    <x-slot name="pageTitle">{{ __('Edit Service') }}</x-slot>

    <div class="p-6 max-w-4xl mx-auto">
        <x-ui.card>
            <x-ui.card-header
                :title="__('Edit Task Service')"
                :description="__('Update service details')"
            />

            <x-ui.card-content>
                <form action="{{ route('task-services.update', $taskService) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <!-- Service Name -->
                        <div>
                            <x-ui.label for="name" required>{{ __('Service Name') }}</x-ui.label>
                            <x-ui.input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name', $taskService->name) }}"
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
                                value="{{ old('default_hourly_rate', $taskService->default_hourly_rate) }}"
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
                            >{{ old('description', $taskService->description) }}</x-ui.textarea>
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
                                {{ old('is_active', $taskService->is_active) ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-slate-900">
                                {{ __('Active') }}
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Inactive services will not appear in task creation forms') }}</p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-between pt-6 mt-6 border-t border-slate-200">
                        <!-- Delete Button -->
                        <form action="{{ route('task-services.destroy', $taskService) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this service?') }}')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="danger" type="submit">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                {{ __('Delete Service') }}
                            </x-ui.button>
                        </form>

                        <!-- Save/Cancel Buttons -->
                        <div class="flex items-center gap-3">
                            <x-ui.button variant="outline" type="button" onclick="window.location.href='{{ route('task-services.index') }}'">
                                {{ __('Cancel') }}
                            </x-ui.button>
                            <x-ui.button variant="default" type="submit">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('Update Service') }}
                            </x-ui.button>
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-app-layout>
