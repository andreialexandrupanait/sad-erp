<x-app-layout>
    <x-slot name="pageTitle">{{ __('Edit Custom Field') }}</x-slot>

    <div class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Edit Custom Field') }}</h2>
                    <p class="text-sm text-slate-600 mt-1">{{ __('Update the custom field configuration.') }}</p>
                </div>

                <!-- Form -->
                <form action="{{ route('task-custom-fields.update', $taskCustomField) }}" method="POST" class="p-6" x-data="customFieldForm()">
                    @csrf
                    @method('PUT')

                    <!-- Field Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ __('Field Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $taskCustomField->name) }}"
                            required
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('name') border-red-500 @enderror"
                            placeholder="{{ __('e.g., Budget, Department, Priority Level') }}"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Field Type -->
                    <div class="mb-6">
                        <label for="type" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ __('Field Type') }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="type"
                            id="type"
                            x-model="fieldType"
                            required
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('type') border-red-500 @enderror"
                        >
                            <option value="">{{ __('Select a field type') }}</option>
                            <option value="text" {{ old('type', $taskCustomField->type) === 'text' ? 'selected' : '' }}>{{ __('Text') }}</option>
                            <option value="number" {{ old('type', $taskCustomField->type) === 'number' ? 'selected' : '' }}>{{ __('Number') }}</option>
                            <option value="date" {{ old('type', $taskCustomField->type) === 'date' ? 'selected' : '' }}>{{ __('Date') }}</option>
                            <option value="dropdown" {{ old('type', $taskCustomField->type) === 'dropdown' ? 'selected' : '' }}>{{ __('Dropdown') }}</option>
                            <option value="checkbox" {{ old('type', $taskCustomField->type) === 'checkbox' ? 'selected' : '' }}>{{ __('Checkbox') }}</option>
                            <option value="email" {{ old('type', $taskCustomField->type) === 'email' ? 'selected' : '' }}>{{ __('Email') }}</option>
                            <option value="url" {{ old('type', $taskCustomField->type) === 'url' ? 'selected' : '' }}>{{ __('URL') }}</option>
                            <option value="phone" {{ old('type', $taskCustomField->type) === 'phone' ? 'selected' : '' }}>{{ __('Phone') }}</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dropdown Options (shown only for dropdown type) -->
                    <div class="mb-6" x-show="fieldType === 'dropdown'" x-cloak>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            {{ __('Dropdown Options') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2" x-data="{ options: {{ json_encode(old('options', $taskCustomField->options ?? [''])) }} }">
                            <template x-for="(option, index) in options" :key="index">
                                <div class="flex items-center gap-2">
                                    <input
                                        type="text"
                                        :name="'options[' + index + ']'"
                                        x-model="options[index]"
                                        class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                        :placeholder="'{{ __('Option') }} ' + (index + 1)"
                                    >
                                    <button
                                        type="button"
                                        @click="options.splice(index, 1)"
                                        x-show="options.length > 1"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <button
                                type="button"
                                @click="options.push('')"
                                class="inline-flex items-center px-3 py-1.5 text-sm text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('Add Option') }}
                            </button>
                        </div>
                        @error('options')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ __('Description') }}
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="3"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('description') border-red-500 @enderror"
                            placeholder="{{ __('Optional description to help users understand this field') }}"
                        >{{ old('description', $taskCustomField->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Is Required -->
                    <div class="mb-6">
                        <label class="flex items-start">
                            <input
                                type="checkbox"
                                name="is_required"
                                value="1"
                                {{ old('is_required', $taskCustomField->is_required) ? 'checked' : '' }}
                                class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded"
                            >
                            <span class="ml-3">
                                <span class="text-sm font-medium text-slate-700">{{ __('Required Field') }}</span>
                                <span class="block text-sm text-slate-500">{{ __('Users must fill this field when creating or editing tasks') }}</span>
                            </span>
                        </label>
                    </div>

                    <!-- Is Active -->
                    <div class="mb-6">
                        <label class="flex items-start">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $taskCustomField->is_active) ? 'checked' : '' }}
                                class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded"
                            >
                            <span class="ml-3">
                                <span class="text-sm font-medium text-slate-700">{{ __('Active') }}</span>
                                <span class="block text-sm text-slate-500">{{ __('Inactive fields will not be shown in task forms') }}</span>
                            </span>
                        </label>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200">
                        <a href="{{ route('task-custom-fields.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                            {{ __('Update Custom Field') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function customFieldForm() {
        return {
            fieldType: '{{ old('type', $taskCustomField->type) }}'
        };
    }
    </script>
    @endpush
</x-app-layout>
