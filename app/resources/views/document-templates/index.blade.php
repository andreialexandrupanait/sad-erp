<x-app-layout>
    <x-slot name="pageTitle">{{ __('Document Templates') }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
                        <a href="{{ route('settings.index') }}" class="hover:text-slate-700">{{ __('Settings') }}</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span>{{ __('Document Templates') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">{{ __('Document Templates') }}</h1>
                            <p class="text-slate-500 mt-1">{{ __('Manage templates for offers, contracts, and annexes') }}</p>
                        </div>
                        <a href="{{ route('settings.document-templates.create') }}"
                           class="inline-flex items-center h-10 px-4 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('New Template') }}
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                @endif

                {{-- All Templates in One Table --}}
                <div class="bg-white rounded-[10px] border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 text-left text-xs font-medium text-slate-500 uppercase tracking-wider bg-slate-50">
                                    <th class="px-6 py-3">{{ __('Name') }}</th>
                                    <th class="px-6 py-3 w-28">{{ __('Type') }}</th>
                                    <th class="px-6 py-3 w-28 text-center">{{ __('Status') }}</th>
                                    <th class="px-6 py-3 w-36">{{ __('Updated') }}</th>
                                    <th class="px-6 py-3 w-32"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-slate-900">{{ $template->name }}</span>
                                            @if($template->is_default)
                                                <span class="px-1.5 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded">{{ __('Default') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        @php
                                            $typeLabels = ['offer' => __('Offer'), 'contract' => __('Contract'), 'annex' => __('Annex')];
                                            $typeColors = ['offer' => 'bg-blue-100 text-blue-700', 'contract' => 'bg-emerald-100 text-emerald-700', 'annex' => 'bg-purple-100 text-purple-700'];
                                        @endphp
                                        <span class="px-2 py-0.5 text-xs font-medium rounded {{ $typeColors[$template->type] ?? 'bg-slate-100 text-slate-600' }}">
                                            {{ $typeLabels[$template->type] ?? $template->type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        @if($template->is_active)
                                            <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">{{ __('Active') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-500 rounded-full">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="text-sm text-slate-500">{{ $template->updated_at->diffForHumans() }}</span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            {{-- Edit - goes to builder for offers, edit page for others --}}
                                            @if($template->type === 'offer')
                                                <a href="{{ route('settings.document-templates.builder', $template) }}"
                                                   class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded"
                                                   title="{{ __('Edit') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                            @else
                                                <a href="{{ route('settings.document-templates.edit', $template) }}"
                                                   class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded"
                                                   title="{{ __('Edit') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                            {{-- Duplicate --}}
                                            <form action="{{ route('settings.document-templates.duplicate', $template) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded"
                                                        title="{{ __('Duplicate') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                            {{-- Delete --}}
                                            <form action="{{ route('settings.document-templates.destroy', $template) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded"
                                                        title="{{ __('Delete') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No templates') }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ __('Create your first template to get started.') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
