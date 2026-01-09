<x-app-layout>
    <x-slot name="pageTitle">{{ __('Document Templates') }}</x-slot>

    <x-slot name="headerActions">
        <div class="relative" x-data="{ open: false }">
            <x-ui.button variant="default" @click="open = !open" class="flex items-center gap-2">
                <svg class="-ml-1 mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('New Template') }}
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </x-ui.button>
            <div x-show="open" @click.away="open = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-slate-200 z-50 py-1">
                <a href="{{ route('settings.document-templates.create') }}"
                   class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    {{ __('Offer Template') }}
                </a>
                <a href="{{ route('settings.contract-templates.create') }}?category=general"
                   class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    {{ __('Contract Template') }}
                </a>
                <a href="{{ route('settings.contract-templates.create') }}?category=annex"
                   class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    {{ __('Annex Template') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6 space-y-6">
                {{-- Messages --}}
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="destructive">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('error') }}</div>
            </x-ui.alert>
        @endif

        {{-- Statistics Cards --}}
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            {{-- Total --}}
            <div class="rounded-lg border border-slate-200 bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-lg">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-300">{{ __('Total Templates') }}</p>
                            <p class="mt-1 text-2xl font-bold">{{ $stats['total'] ?? 0 }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/10">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Offers --}}
            <div class="rounded-lg border border-blue-200 bg-blue-50 shadow-sm">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600">{{ __('Offer Templates') }}</p>
                            <p class="mt-1 text-2xl font-bold text-blue-700">{{ $stats['offers'] ?? 0 }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contracts --}}
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 shadow-sm">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-emerald-600">{{ __('Contract Templates') }}</p>
                            <p class="mt-1 text-2xl font-bold text-emerald-700">{{ $stats['contracts'] ?? 0 }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Annexes --}}
            <div class="rounded-lg border border-purple-200 bg-purple-50 shadow-sm">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-purple-600">{{ __('Annex Templates') }}</p>
                            <p class="mt-1 text-2xl font-bold text-purple-700">{{ $stats['annexes'] ?? 0 }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100">
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search and Filters --}}
        <x-ui.card>
            <x-ui.card-content>
                <form method="GET" action="{{ route('settings.document-templates.index') }}">
                    <div class="flex flex-col sm:flex-row gap-4">
                        {{-- Search --}}
                        <div class="flex-1">
                            <x-ui.input
                                type="text"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="{{ __('Search templates...') }}"
                            />
                        </div>

                        {{-- Type Filter --}}
                        <div class="w-full sm:w-48">
                            <x-ui.select name="type">
                                <option value="">{{ __('All Types') }}</option>
                                <option value="offer" {{ request('type') == 'offer' ? 'selected' : '' }}>{{ __('Offers') }}</option>
                                <option value="contract" {{ request('type') == 'contract' ? 'selected' : '' }}>{{ __('Contracts') }}</option>
                                <option value="annex" {{ request('type') == 'annex' ? 'selected' : '' }}>{{ __('Annexes') }}</option>
                            </x-ui.select>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex gap-2">
                            <x-ui.button type="submit" variant="default">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                {{ __('Search') }}
                            </x-ui.button>
                            @if(request('q') || request('type'))
                                <x-ui.button type="button" variant="outline" onclick="window.location.href='{{ route('settings.document-templates.index') }}'">
                                    {{ __('Clear') }}
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Templates Table --}}
        <x-ui.card>
            @if(count($initialTemplates) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="bg-slate-100">
                            <tr class="border-b border-slate-200">
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Name') }}</th>
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Type') }}</th>
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Default') }}</th>
                                <th class="px-6 py-4 text-left align-middle font-medium text-slate-500">{{ __('Updated') }}</th>
                                <th class="px-6 py-4 text-right align-middle font-medium text-slate-500">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            @foreach($initialTemplates as $template)
                                @php
                                    $isContract = ($template->model_type ?? '') === 'contract_template';
                                    $type = $template->type ?? 'offer';
                                @endphp
                                <x-ui.table-row>
                                    <x-ui.table-cell>
                                        <div class="font-medium text-slate-900">{{ $template->name }}</div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        @if($type === 'offer')
                                            <x-ui.badge variant="info">{{ __('Offer') }}</x-ui.badge>
                                        @elseif($type === 'annex')
                                            <x-ui.badge variant="secondary">{{ __('Annex') }}</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="success">{{ __('Contract') }}</x-ui.badge>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        @if($template->is_active ?? true)
                                            <x-ui.badge variant="success">{{ __('Active') }}</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="secondary">{{ __('Inactive') }}</x-ui.badge>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        @if($template->is_default ?? false)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                </svg>
                                                {{ __('Default') }}
                                            </span>
                                        @else
                                            <form action="{{ $isContract ? route('settings.contract-templates.set-default', $template->id) : route('settings.document-templates.set-default', $template->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-2 py-1 text-xs text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-full border border-dashed border-slate-300 hover:border-amber-400 transition-colors"
                                                        title="{{ __('Set as Default') }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                    </svg>
                                                    {{ __('Set') }}
                                                </button>
                                            </form>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm text-slate-600">
                                            {{ $template->updated_at ? \Carbon\Carbon::parse($template->updated_at)->format('d.m.Y') : '-' }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="text-right">
                                        <x-table-actions
                                            :editUrl="$isContract ? route('settings.contract-templates.edit', $template->id) : route('settings.document-templates.builder', $template->id)"
                                            :deleteAction="$isContract ? route('settings.contract-templates.destroy', $template->id) : route('settings.document-templates.destroy', $template->id)"
                                            :deleteConfirm="__('Are you sure you want to delete this template?')"
                                        >
                                            {{-- Duplicate --}}
                                            <form action="{{ $isContract ? route('settings.contract-templates.duplicate', $template->id) : route('settings.document-templates.duplicate', $template->id) }}" method="POST" class="inline-flex">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center text-slate-600 hover:text-slate-900 transition-colors"
                                                        title="{{ __('Duplicate') }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </x-table-actions>
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                {{-- Empty State --}}
                <div class="px-6 py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No templates') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Get started by creating your first template.') }}</p>
                    <div class="mt-6 flex justify-center gap-3">
                        <a href="{{ route('settings.document-templates.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            {{ __('Offer') }}
                        </a>
                        <a href="{{ route('settings.contract-templates.create') }}?category=general" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ __('Contract') }}
                        </a>
                        <a href="{{ route('settings.contract-templates.create') }}?category=annex" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            {{ __('Annex') }}
                        </a>
                    </div>
                </div>
            @endif
            </x-ui.card>
            </div>
        </div>
    </div>
</x-app-layout>
