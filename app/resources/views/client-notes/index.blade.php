<x-app-layout>
    <x-slot name="pageTitle">{{ __('Client Notes') }}</x-slot>

    @php
        $clientStatuses = \App\Models\SettingOption::clientStatuses()->get();
    @endphp

    <div class="p-6 space-y-6" x-data="notesPage" @client-created.window="handleClientCreated($event.detail)">
        <!-- Success Messages -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- New Note Form -->
        <x-ui.card>
            <x-ui.card-content>
                <form method="POST" action="{{ route('client-notes.store') }}" class="space-y-4">
                    @csrf

                    <!-- Client Selection (Optional) - Full Width -->
                    <div>
                        <x-ui.label for="client_id">{{ __('Client') }} <span class="text-slate-400 font-normal">({{ __('optional') }})</span></x-ui.label>
                        <x-ui.client-select
                            name="client_id"
                            :clients="$clients"
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
                            minHeight="250"
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
                    <div class="divide-y divide-slate-200" id="notes-list">
                        @foreach($notes as $note)
                            <div class="py-3 first:pt-0 last:pb-0 note-item" id="note-{{ $note->id }}"
                                 x-data="{
                                     expanded: false,
                                     showClientPicker: false,
                                     clientName: '{{ addslashes($note->client?->name ?? __('No client')) }}',
                                     clientId: {{ $note->client_id ?? 'null' }},
                                     saving: false,
                                     deleting: false,
                                     search: '',
                                     async updateClient(newClientId) {
                                         this.saving = true;
                                         try {
                                             const res = await fetch('{{ route('client-notes.update-client', $note) }}', {
                                                 method: 'PATCH',
                                                 headers: {
                                                     'Content-Type': 'application/json',
                                                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                     'Accept': 'application/json'
                                                 },
                                                 body: JSON.stringify({ client_id: newClientId || null })
                                             });
                                             const data = await res.json();
                                             if (data.success) {
                                                 this.clientName = data.client_name;
                                                 this.clientId = data.client_id;
                                             }
                                         } catch (e) { console.error(e); }
                                         this.saving = false;
                                         this.showClientPicker = false;
                                     },
                                     async deleteNote() {
                                         if (!confirm('{{ __('Are you sure you want to delete this note?') }}')) return;
                                         this.deleting = true;
                                         try {
                                             const res = await fetch('{{ route('client-notes.destroy', $note) }}', {
                                                 method: 'DELETE',
                                                 headers: {
                                                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                     'Accept': 'application/json'
                                                 }
                                             });
                                             const data = await res.json();
                                             if (data.success) {
                                                 this.$el.remove();
                                             }
                                         } catch (e) { console.error(e); this.deleting = false; }
                                     }
                                 }">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <!-- Client & Date -->
                                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                            <!-- Inline Client Picker -->
                                            <div class="relative">
                                                <button @click="showClientPicker = !showClientPicker"
                                                        class="text-sm font-semibold hover:text-blue-600 flex items-center gap-1"
                                                        :class="clientId ? 'text-slate-900' : 'text-slate-500 italic'">
                                                    <span x-text="clientName"></span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                                <!-- Dropdown -->
                                                <div x-show="showClientPicker" x-cloak
                                                     @click.outside="showClientPicker = false; search = ''"
                                                     class="absolute z-50 mt-1 w-72 bg-white border border-slate-200 rounded-lg shadow-lg">
                                                    <!-- Search Input -->
                                                    <div class="p-2 border-b border-slate-100">
                                                        <input type="text"
                                                               x-model="search"
                                                               @click.stop
                                                               placeholder="{{ __('Search clients...') }}"
                                                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    </div>
                                                    <!-- Client List -->
                                                    <div class="max-h-48 overflow-y-auto p-2">
                                                        <!-- Add New Client -->
                                                        <button type="button"
                                                           @click="showClientPicker = false; $dispatch('open-client-slideover', { noteId: {{ $note->id }} })"
                                                           class="w-full flex items-center gap-2 px-3 py-2 text-sm text-emerald-700 font-medium hover:bg-emerald-50 rounded border-b border-slate-100 mb-1">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                            {{ __('Add new client') }}
                                                        </button>
                                                        <button @click="updateClient(null); search = ''"
                                                                x-show="!search"
                                                                class="w-full text-left px-3 py-2 text-sm text-slate-500 italic hover:bg-slate-100 rounded"
                                                                :class="{ 'bg-slate-100': !clientId }">
                                                            {{ __('No client') }}
                                                        </button>
                                                        @foreach($clients as $client)
                                                            <button @click="updateClient({{ $client->id }}); search = ''"
                                                                    x-show="!search || '{{ strtolower(addslashes($client->name)) }}'.includes(search.toLowerCase())"
                                                                    class="w-full text-left px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 rounded"
                                                                    :class="{ 'bg-blue-50 text-blue-700': clientId == {{ $client->id }} }">
                                                                {{ $client->name }}
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <span x-show="saving" class="ml-2 text-xs text-blue-600">{{ __('Saving...') }}</span>
                                            </div>
                                            <span class="text-xs text-slate-500">
                                                {{ $note->created_at->format('d M Y, H:i') }}
                                            </span>
                                            <span class="text-xs text-slate-400">
                                                {{ __('by') }} {{ $note->user->name ?? 'Unknown' }}
                                            </span>
                                        </div>

                                        <!-- Content Preview -->
                                        @php
                                            $plainText = strip_tags($note->content);
                                            $isLong = strlen($plainText) > 200;
                                        @endphp
                                        <div>
                                            <div x-show="!expanded" class="text-sm text-slate-700">
                                                {{ Str::limit($plainText, 200) }}
                                                @if($isLong)
                                                    <button @click="expanded = true" class="text-blue-600 hover:text-blue-800 font-medium ml-1">{{ __('Read more') }}</button>
                                                @endif
                                            </div>
                                            @if($isLong)
                                            <div x-show="expanded" x-cloak class="text-sm text-slate-700 prose prose-sm max-w-none">
                                                {!! $note->content !!}
                                                <button @click="expanded = false" class="text-blue-600 hover:text-blue-800 font-medium ml-1">{{ __('Show less') }}</button>
                                            </div>
                                            @endif
                                        </div>

                                        <!-- Tags -->
                                        @if(!empty($note->tags))
                                            <div class="mt-2 flex flex-wrap gap-1">
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
                                    <div class="flex items-center gap-3 ml-3">
                                        <a href="{{ route('client-notes.edit', $note) }}"
                                           class="inline-flex items-center text-blue-600 hover:text-blue-900 transition-colors"
                                           title="{{ __('Edit') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <button type="button" @click="deleteNote()" x-bind:disabled="deleting"
                                                class="inline-flex items-center text-red-600 hover:text-red-900 transition-colors"
                                                title="{{ __('Delete') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
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

        <x-toast />

        <!-- Slide-Over for Creating New Client -->
        <div
            x-show="slideOverOpen"
            x-cloak
            class="fixed inset-0 z-[10000] overflow-hidden"
            role="dialog"
            aria-modal="true"
            @open-client-slideover.window="openSlideOver($event.detail.noteId)"
        >
            <!-- Backdrop -->
            <div
                x-show="slideOverOpen"
                x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="closeSlideOver()"
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
            ></div>

            <!-- Panel -->
            <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div
                    x-show="slideOverOpen"
                    x-transition:enter="transform transition ease-in-out duration-300"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-300"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                    class="w-screen max-w-lg"
                >
                    <div class="flex h-full flex-col bg-white shadow-xl">
                        <!-- Header -->
                        <div class="bg-slate-50 px-4 py-6 sm:px-6 border-b border-slate-200 flex-shrink-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Add New Client') }}</h2>
                                    <p class="mt-1 text-sm text-slate-500">{{ __('Create a new client and automatically select it.') }}</p>
                                </div>
                                <button type="button" @click="closeSlideOver()" class="rounded-md text-slate-400 hover:text-slate-500">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Form -->
                        <div id="new-client-form-container" class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
                            <div x-show="formErrors.length > 0" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <template x-for="error in formErrors" :key="error">
                                    <p class="text-sm text-red-700" x-text="error"></p>
                                </template>
                            </div>
                            <x-client-form-fields :client="null" :statuses="$clientStatuses" prefix="new_client_" :compact="true" />
                        </div>

                        <!-- Footer -->
                        <div class="flex-shrink-0 border-t border-slate-200 px-4 py-4 sm:px-6 bg-slate-50">
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="closeSlideOver()" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50">
                                    {{ __('Cancel') }}
                                </button>
                                <button type="button" @click="createClient()" :disabled="saving" class="px-4 py-2 text-sm font-medium text-white bg-slate-900 rounded-md hover:bg-slate-800 disabled:opacity-50">
                                    <span x-show="!saving">{{ __('Create Client') }}</span>
                                    <span x-show="saving">{{ __('Creating...') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notesPage', () => ({
            slideOverOpen: false,
            activeNoteId: null,
            saving: false,
            formErrors: [],

            openSlideOver(noteId) {
                this.activeNoteId = noteId;
                this.formErrors = [];
                this.slideOverOpen = true;
            },

            closeSlideOver() {
                this.slideOverOpen = false;
                this.activeNoteId = null;
            },

            async createClient() {
                this.saving = true;
                this.formErrors = [];

                const form = document.getElementById('new-client-form-container');
                const formData = new FormData();

                // Collect form fields
                form.querySelectorAll('input, select, textarea').forEach(el => {
                    if (el.name) {
                        const fieldName = el.name.replace('new_client_', '');
                        // Handle checkboxes properly
                        if (el.type === 'checkbox') {
                            formData.append(fieldName, el.checked ? '1' : '0');
                        } else {
                            formData.append(fieldName, el.value);
                        }
                    }
                });

                try {
                    const res = await fetch('{{ route('clients.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    const data = await res.json();

                    if (res.ok && data.client) {
                        // Dispatch event with new client data
                        window.dispatchEvent(new CustomEvent('client-created', {
                            detail: { client: data.client, noteId: this.activeNoteId }
                        }));
                        this.closeSlideOver();
                    } else if (data.errors) {
                        this.formErrors = Object.values(data.errors).flat();
                    }
                } catch (e) {
                    console.error(e);
                    this.formErrors = ['{{ __('An error occurred. Please try again.') }}'];
                }

                this.saving = false;
            },

            handleClientCreated(detail) {
                // Find the note row and update its client
                const noteEl = document.getElementById('note-' + detail.noteId);
                if (noteEl && window.Alpine) {
                    const noteData = Alpine.$data(noteEl);
                    if (noteData) {
                        noteData.clientName = detail.client.name;
                        noteData.clientId = detail.client.id;
                        // Also update via API
                        noteData.updateClient(detail.client.id);
                    }
                }
            }
        }));
    });
    </script>
</x-app-layout>
