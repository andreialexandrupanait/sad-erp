<x-app-layout>
    <x-slot name="pageTitle">{{ __('Preview') }} - {{ $template->name }}</x-slot>

    <x-slot name="headerActions">
        <a href="{{ route('settings.document-templates.edit', $template) }}"
           class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">
            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            {{ __('Edit Template') }}
        </a>
    </x-slot>

    <div class="p-6">
        <x-ui.card>
            <x-ui.card-header>
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold">{{ $template->name }}</h2>
                        <p class="text-sm text-slate-500">{{ $template->type_label }} {{ __('Template') }}</p>
                    </div>
                    <span class="text-xs text-slate-400">{{ __('Preview with sample data') }}</span>
                </div>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="prose prose-sm max-w-none bg-white p-8 border rounded-lg">
                    {!! $content !!}
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-app-layout>
