<x-app-layout>
    <x-slot name="pageTitle">{{ __('Client Notes') }}</x-slot>

    <div class="p-6 space-y-6">
        <!-- Success Messages -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- Filters -->
        <x-ui.card>
            <x-ui.card-content>
                <form method="GET" action="{{ route('client-notes.index') }}">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1">
                            <x-ui.label for="q">{{ __('Search') }}</x-ui.label>
                            <x-ui.input
                                type="text"
                                name="q"
                                id="q"
                                value="{{ $filters['q'] ?? '' }}"
                                placeholder="{{ __('Search in notes...') }}"
                            />
                        </div>

                        <!-- Client Filter -->
                        <div class="w-full lg:w-64">
                            <x-ui.label for="filter_client_id">{{ __('Filter by Client') }}</x-ui.label>
                            <x-ui.searchable-select
                                name="client_id"
                                id="filter_client_id"
                                :options="$clients"
                                :selected="$filters['client_id'] ?? null"
                                :placeholder="__('All Clients')"
                                :emptyLabel="__('All Clients')"
                            />
                        </div>

                        <!-- Tag Filter -->
                        <div class="w-full lg:w-48">
                            <x-ui.label for="tag">{{ __('Tag') }}</x-ui.label>
                            <x-ui.select name="tag" id="tag">
                                <option value="">{{ __('All Tags') }}</option>
                                @foreach($availableTags as $tag)
                                    <option value="{{ $tag }}" {{ ($filters['tag'] ?? '') == $tag ? 'selected' : '' }}>
                                        {{ ucfirst($tag) }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <!-- Date Range -->
                        <div class="w-full lg:w-40">
                            <x-ui.label for="start_date">{{ __('From') }}</x-ui.label>
                            <x-ui.input
                                type="date"
                                name="start_date"
                                id="start_date"
                                value="{{ $filters['start_date'] ?? '' }}"
                            />
                        </div>

                        <div class="w-full lg:w-40">
                            <x-ui.label for="end_date">{{ __('To') }}</x-ui.label>
                            <x-ui.input
                                type="date"
                                name="end_date"
                                id="end_date"
                                value="{{ $filters['end_date'] ?? '' }}"
                            />
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-end gap-2">
                            <x-ui.button type="submit" variant="default">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                {{ __('Filter') }}
                            </x-ui.button>
                            @if(!empty(array_filter($filters ?? [])))
                                <x-ui.button type="button" variant="outline" onclick="window.location.href='{{ route('client-notes.index') }}'">
                                    {{ __('Clear') }}
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <!-- New Note Form -->
        <x-ui.card>
            <x-ui.card-content>
                <form method="POST" action="{{ route('client-notes.store') }}" class="space-y-4">
                    @csrf

                    <!-- Client Selection (Optional) - Full Width -->
                    <div>
                        <x-ui.label for="client_id">{{ __('Client') }} <span class="text-slate-400 font-normal">({{ __('optional') }})</span></x-ui.label>
                        <x-ui.searchable-select
                            name="client_id"
                            id="note_client_id"
                            :options="$clients"
                            :selected="old('client_id')"
                            :placeholder="__('Select client (optional)')"
                            :emptyLabel="__('No client')"
                        />
                        @error('client_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Note Content - Full Width with Rich Editor -->
                    <div>
                        <x-ui.label for="content">{{ __('Message') }}</x-ui.label>
                        <x-ui.simple-editor
                            name="content"
                            id="content"
                            :value="old('content', '')"
                            :placeholder="__('Write your note here...')"
                            minHeight="70px"
                            :clients="$clients"
                            clientFieldId="note_client_id"
                        />
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-slate-500">
                            {{ __('Tags are auto-detected from keywords like: contract, factura, grafica, mentenanta, etc.') }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <x-ui.button type="submit" variant="default">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Add Note') }}
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Notes List -->
        <x-ui.card>
            <x-ui.card-content>
                @if($notes->isEmpty())
                    <x-ui.empty-state
                        icon="document"
                        :title="__('No notes yet')"
                        :description="__('Create your first note using the form above.')"
                    />
                @else
                    <div class="space-y-4">
                        @foreach($notes as $note)
                            <div class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <!-- Client & Date -->
                                        <div class="flex items-center gap-3 mb-2">
                                            @if($note->client)
                                                <a href="{{ route('clients.show', $note->client) }}" class="text-sm font-semibold text-slate-900 hover:text-blue-600">
                                                    {{ $note->client->name }}
                                                </a>
                                            @else
                                                <span class="text-sm font-medium text-slate-500 italic">{{ __('No client') }}</span>
                                            @endif
                                            <span class="text-xs text-slate-500">
                                                {{ $note->created_at->format('d M Y, H:i') }}
                                            </span>
                                            <span class="text-xs text-slate-400">
                                                {{ __('by') }} {{ $note->user->name ?? 'Unknown' }}
                                            </span>
                                        </div>

                                        <!-- Content -->
                                        <div class="text-sm text-slate-700 prose prose-sm max-w-none">{!! $note->content !!}</div>

                                        <!-- Tags -->
                                        @if(!empty($note->tags))
                                            <div class="mt-3 flex flex-wrap gap-1">
                                                @foreach($note->tags as $tag)
                                                    <a href="{{ route('client-notes.index', ['tag' => $tag]) }}"
                                                       class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                                        {{ ucfirst($tag) }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-1 ml-4">
                                        <x-ui.button variant="ghost" size="sm" onclick="window.location.href='{{ route('client-notes.edit', $note) }}'" title="{{ __('Edit') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </x-ui.button>
                                        <form method="POST" action="{{ route('client-notes.destroy', $note) }}" class="inline"
                                              onsubmit="return confirm('{{ __('Are you sure you want to delete this note?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="ghost" size="sm" title="{{ __('Delete') }}">
                                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </x-ui.button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $notes->links() }}
                    </div>
                @endif
            </x-ui.card-content>
        </x-ui.card>
    </div>

    <x-toast />
</x-app-layout>
