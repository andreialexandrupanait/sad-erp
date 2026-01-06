<x-app-layout>
    <x-slot name="pageTitle">{{ __('Edit Template') }} - {{ $template->name }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
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
                        <span>{{ __('Edit') }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $template->name }}</h1>
                </div>

                @if(session('info'))
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">{{ session('info') }}</p>
                    </div>
                @endif

                <form action="{{ route('settings.document-templates.update', $template) }}" method="POST">
                    @csrf
                    @method('PUT')

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
                                                   value="{{ old('name', $template->name) }}"
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
                                                    <option value="{{ $typeKey }}" {{ $template->type === $typeKey ? 'selected' : '' }}>{{ $typeName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    @if($template->type === 'offer')
                                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                            <div class="flex items-start gap-3">
                                                <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <div>
                                                    <p class="text-sm text-blue-800 font-medium">{{ __('Visual Builder Available') }}</p>
                                                    <p class="text-sm text-blue-600 mt-1">{{ __('For offer templates, we recommend using the Visual Builder for easier editing.') }}</p>
                                                    <a href="{{ route('settings.document-templates.builder', $template) }}"
                                                       class="inline-flex items-center mt-2 text-sm font-medium text-blue-700 hover:text-blue-800">
                                                        {{ __('Open Visual Builder') }}
                                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div>
                                        <label for="content" class="block text-sm font-medium text-slate-700 mb-1">{{ __('Content') }} <span class="text-red-500">*</span></label>
                                        <textarea name="content" id="content" required rows="20"
                                                  class="w-full border border-slate-300 rounded-lg px-3 py-2 font-mono text-sm focus:border-slate-500 focus:ring-1 focus:ring-slate-500">{{ old('content', $template->content) }}</textarea>
                                        @error('content')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        <p class="mt-1 text-xs text-slate-500">{{ __('For offer templates, this contains JSON data. Use the Visual Builder for easier editing.') }}</p>
                                    </div>

                                    <div class="flex items-center gap-6 pt-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="is_default" value="1" {{ old('is_default', $template->is_default) ? 'checked' : '' }}
                                                   class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                            <span class="text-sm text-slate-700">{{ __('Set as default for this type') }}</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}
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
                                        {{ __('Update Template') }}
                                    </button>
                                    <a href="{{ route('settings.document-templates.preview', $template) }}"
                                       class="flex items-center justify-center w-full h-10 px-4 bg-white text-slate-700 text-sm font-medium border border-slate-300 rounded-lg hover:bg-slate-50">
                                        {{ __('Preview') }}
                                    </a>
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
