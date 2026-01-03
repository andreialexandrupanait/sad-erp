<x-app-layout>
    <x-slot name="pageTitle">{{ __('Edit Note') }}</x-slot>

    <div class="p-6 space-y-6">
        <x-ui.card>
            <x-ui.card-content>
                <form method="POST" action="{{ route('client-notes.update', $note) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Client (read-only display) -->
                    <div>
                        <x-ui.label>{{ __('Client') }}</x-ui.label>
                        <div class="flex items-center gap-3 mt-1">
                            @if($note->client)
                                <span class="inline-flex items-center px-3 py-2 rounded-md bg-slate-100 text-sm font-medium text-slate-800">
                                    {{ $note->client->name }}
                                </span>
                                <a href="{{ route('clients.show', $note->client) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                    {{ __('View Client') }} &rarr;
                                </a>
                            @else
                                <span class="inline-flex items-center px-3 py-2 rounded-md bg-slate-100 text-sm font-medium text-slate-500 italic">
                                    {{ __('No client assigned') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Note Content -->
                    <div>
                        <x-ui.label for="content" required>{{ __('Note Content') }}</x-ui.label>
                        <x-ui.simple-editor
                            name="content"
                            id="content"
                            :value="old('content', $note->content)"
                            :placeholder="__('Write your message here...')"
                            minHeight="250px"
                        />
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-slate-500">
                            {{ __('Tags will be automatically re-extracted when you save.') }}
                        </p>
                    </div>

                    <!-- Current Tags (display only) -->
                    @if(!empty($note->tags))
                        <div>
                            <x-ui.label>{{ __('Current Tags') }}</x-ui.label>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($note->tags as $tag)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ ucfirst($tag) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Metadata -->
                    <div class="bg-slate-50 rounded-lg p-4 text-sm text-slate-600">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <span class="font-medium">{{ __('Created:') }}</span>
                                {{ $note->created_at->format('d M Y, H:i') }}
                            </div>
                            <div>
                                <span class="font-medium">{{ __('By:') }}</span>
                                {{ $note->user->name ?? 'Unknown' }}
                            </div>
                            @if($note->updated_at->ne($note->created_at))
                                <div>
                                    <span class="font-medium">{{ __('Updated:') }}</span>
                                    {{ $note->updated_at->format('d M Y, H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between border-t border-slate-200 pt-4">
                        <form method="POST" action="{{ route('client-notes.destroy', $note) }}"
                              onsubmit="return confirm('{{ __('Are you sure you want to delete this note?') }}')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="destructive">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                {{ __('Delete') }}
                            </x-ui.button>
                        </form>

                        <div class="flex items-center gap-x-4">
                            <x-ui.button type="button" variant="outline" onclick="window.history.back()">
                                {{ __('Cancel') }}
                            </x-ui.button>
                            <x-ui.button type="submit" variant="default">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('Update Note') }}
                            </x-ui.button>
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-app-layout>
