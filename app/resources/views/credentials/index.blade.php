<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ __('Credentials') }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">Manage client access credentials and login information</p>
            </div>
            <x-ui.button variant="default" onclick="window.location.href='{{ route('credentials.create') }}'">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add New Credential
            </x-ui.button>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8 space-y-6">
        <!-- Success Messages -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- Search and Filter Form -->
        <x-ui.card>
            <x-ui.card-content>
                <form method="GET" action="{{ route('credentials.index') }}">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Search -->
                        <div class="flex-1">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <x-ui.input
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Search by platform, username, client..."
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Client Filter -->
                        <div class="w-full sm:w-52">
                            <x-ui.select name="client_id">
                                <option value="">All Clients</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->display_name }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <!-- Platform Filter -->
                        <div class="w-full sm:w-48">
                            <x-ui.select name="platform">
                                <option value="">All Platforms</option>
                                @foreach ($platforms as $key => $value)
                                    <option value="{{ $key }}" {{ request('platform') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-2">
                            <x-ui.button type="submit" variant="default">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                Search
                            </x-ui.button>
                            @if(request()->has('search') || request()->has('client_id') || request()->has('platform'))
                                <x-ui.button variant="outline" onclick="window.location.href='{{ route('credentials.index') }}'">
                                    Clear
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Credentials Table -->
        <x-ui.card>
            @if($credentials->isEmpty())
                <div class="px-6 py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">No credentials</h3>
                    <p class="mt-1 text-sm text-slate-500">Get started by creating your first credential.</p>
                    <div class="mt-6">
                        <x-ui.button variant="default" onclick="window.location.href='{{ route('credentials.create') }}'">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Credential
                        </x-ui.button>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr class="border-b transition-colors hover:bg-slate-50/50">
                                <x-ui.table-head>Client</x-ui.table-head>
                                <x-ui.table-head>Platform</x-ui.table-head>
                                <x-ui.table-head>Username</x-ui.table-head>
                                <x-ui.table-head>Password</x-ui.table-head>
                                <x-ui.table-head>URL</x-ui.table-head>
                                <x-ui.table-head>Last Accessed</x-ui.table-head>
                                <x-ui.table-head class="text-right">Actions</x-ui.table-head>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            @foreach ($credentials as $credential)
                                <x-ui.table-row>
                                    <x-ui.table-cell>
                                        <div class="font-medium text-slate-900">
                                            {{ $credential->client->display_name }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <x-ui.badge variant="secondary">
                                            {{ $credential->platform }}
                                        </x-ui.badge>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm text-slate-700">
                                            {{ $credential->username ?? '-' }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm font-mono text-slate-500">
                                            {{ $credential->masked_password }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        @if ($credential->url)
                                            <a href="{{ $credential->url }}" target="_blank" class="text-sm text-slate-600 hover:text-slate-900 underline truncate block max-w-xs">
                                                {{ $credential->url }}
                                            </a>
                                        @else
                                            <span class="text-sm text-slate-500">-</span>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm text-slate-700">
                                            {{ $credential->last_accessed_at ? $credential->last_accessed_at->diffForHumans() : 'Never' }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-ui.button
                                                variant="secondary"
                                                size="sm"
                                                onclick="window.location.href='{{ route('credentials.show', $credential) }}'"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View
                                            </x-ui.button>
                                            <x-ui.button
                                                variant="outline"
                                                size="sm"
                                                onclick="window.location.href='{{ route('credentials.edit', $credential) }}'"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </x-ui.button>
                                            <form action="{{ route('credentials.destroy', $credential) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this credential?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.button type="submit" variant="destructive" size="sm">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Delete
                                                </x-ui.button>
                                            </form>
                                        </div>
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($credentials->hasPages())
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                        {{ $credentials->links() }}
                    </div>
                @endif
            @endif
        </x-ui.card>
    </div>
</x-app-layout>
