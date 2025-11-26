{{-- Reusable Task Rows Component - Can be used for server-side or AJAX rendering --}}
@props(['tasks', 'status'])

@forelse($tasks as $task)
    @include('components.tasks.partials.task-row', ['task' => $task, 'status' => $status])
@empty
    <div class="h-8 px-3 flex items-center text-sm text-slate-400 italic border-b border-slate-100">
        {{ __('No tasks') }}
    </div>
@endforelse
