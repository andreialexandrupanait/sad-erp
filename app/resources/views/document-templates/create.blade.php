<x-app-layout>
    <x-slot name="pageTitle">{{ __('New Document Template') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-4 md:p-6">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
                        <a href="{{ route('settings.index') }}" class="hover:text-slate-700">{{ __('Settings') }}</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <a href="{{ route('settings.document-templates.index') }}" class="hover:text-slate-700">{{ __('Document Templates') }}</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span>{{ __('New') }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ __('New Template') }}</h1>
                </div>

                <form action="{{ route('settings.document-templates.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- Main Form --}}
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                                <div class="px-6 py-4 bg-slate-100 border-b border-slate-200">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Template Details') }}</h3>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">{{ __('Name') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" id="name" required
                                                   value="{{ old('name') }}"
                                                   placeholder="{{ __('e.g., Standard Offer Template') }}"
                                                   class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                            @error('name')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="type" class="block text-sm font-medium text-slate-700 mb-1">{{ __('Type') }} <span class="text-red-500">*</span></label>
                                            <select name="type" id="type" required
                                                    class="w-full h-10 border border-slate-300 rounded-lg px-3 focus:border-slate-500 focus:ring-1 focus:ring-slate-500">
                                                @foreach($types as $typeKey => $typeName)
                                                    <option value="{{ $typeKey }}" {{ $selectedType === $typeKey ? 'selected' : '' }}>{{ $typeName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="content" class="block text-sm font-medium text-slate-700 mb-1">{{ __('Content') }} <span class="text-red-500">*</span></label>
                                        <textarea name="content" id="content" required rows="20"
                                                  placeholder="{{ __('Enter template HTML content...') }}"
                                                  class="w-full border border-slate-300 rounded-lg px-3 py-2 font-mono text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500">{{ old('content') }}</textarea>
                                        @error('content')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="flex items-center gap-6 pt-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                                                   class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                            <span class="text-sm text-slate-700">{{ __('Set as default for this type') }}</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                                   class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                            <span class="text-sm text-slate-700">{{ __('Active') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Sidebar --}}
                        <div class="space-y-6">
                            {{-- Actions --}}
                            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                                <div class="px-6 py-4 bg-slate-100 border-b border-slate-200">
                                    <h3 class="font-semibold text-gray-900">{{ __('Actions') }}</h3>
                                </div>
                                <div class="p-4 space-y-3">
                                    <button type="submit" class="w-full h-10 px-4 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800">
                                        {{ __('Create Template') }}
                                    </button>
                                    <a href="{{ route('settings.document-templates.index') }}"
                                       class="flex items-center justify-center w-full h-10 px-4 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-100">
                                        {{ __('Cancel') }}
                                    </a>
                                </div>
                            </div>

                            {{-- Available Variables --}}
                            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                                <div class="px-6 py-4 bg-slate-100 border-b border-slate-200">
                                    <h3 class="font-semibold text-gray-900">{{ __('Available Variables') }}</h3>
                                </div>
                                <div class="p-4">
                                    <p class="text-sm text-slate-500 mb-3">{{ __('Use these variables in your template.') }}</p>
                                    <div class="space-y-1.5 text-sm max-h-80 overflow-y-auto">
                                        @foreach($variables as $varKey => $varLabel)
                                            <div class="flex items-center justify-between py-1">
                                                <code class="text-xs bg-slate-100 px-2 py-0.5 rounded font-mono text-slate-700">@verbatim{{@endverbatim {{ $varKey }} @verbatim}}@endverbatim</code>
                                                <span class="text-slate-500 text-xs">{{ $varLabel }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
