<x-app-layout>
    <x-slot name="pageTitle">{{ __('Edit Task') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="outline" onclick="window.location.href='{{ route('tasks.index') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('Back') }}
        </x-ui.button>
    </x-slot>

    <div class="p-6">
        <div class="max-w-4xl mx-auto">
            <x-task-form
                :task="$task"
                :lists="$lists"
                :services="$services"
                :users="$users"
                :action="route('tasks.update', $task)"
                method="PATCH"
            />
        </div>
    </div>
</x-app-layout>
