<x-app-layout>
    <x-slot name="pageTitle">{{ __('Tasks') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('tasks.create') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('New Task') }}
        </x-ui.button>
    </x-slot>

    <!-- Main Content -->
    <div class="min-h-screen bg-white">
        <!-- Success Message -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- V2 Task List View (Cache-based with lazy loading) -->
        <x-tasks.v2.list-view
            :statuses="$statuses"
            :organizationId="$organizationId"
            :taskStatuses="$taskStatuses"
            :taskPriorities="$taskPriorities"
            :services="$services"
            :lists="$lists"
            :users="$users"
            :filters="$filters"
        />
    </div>
</x-app-layout>
