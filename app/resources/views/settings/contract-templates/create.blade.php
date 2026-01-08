<x-app-layout>
    <x-slot name="pageTitle">{{ __('Create Contract Template') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="outline" onclick="window.location.href='{{ route('settings.document-templates.index') }}'">
            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('Back') }}
        </x-ui.button>
    </x-slot>

    <div class="p-6">
        @if ($errors->any())
            <x-ui.alert variant="destructive" class="mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form action="{{ route('settings.contract-templates.store') }}" method="POST">
            @csrf

            <x-ui.card class="mb-6">
                <x-slot name="header">
                    <h3 class="text-lg font-medium text-slate-900">{{ __('Template Details') }}</h3>
                </x-slot>

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">{{ __('Template Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-slate-700 mb-1">{{ __('Category') }} <span class="text-red-500">*</span></label>
                        <select name="category" id="category" required
                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="text-sm text-slate-700">{{ __('Set as default template') }}</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="text-sm text-slate-700">{{ __('Active') }}</span>
                        </label>
                    </div>
                </div>
            </x-ui.card>

            <div class="flex justify-end gap-3">
                <x-ui.button type="button" variant="outline" onclick="window.location.href='{{ route('settings.document-templates.index') }}'">
                    {{ __('Cancel') }}
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    {{ __('Create & Edit Content') }}
                </x-ui.button>
            </div>
        </form>
    </div>
</x-app-layout>
