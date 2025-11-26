@props(['lists' => [], 'taskStatuses' => [], 'taskPriorities' => [], 'users' => [], 'services' => []])

<!-- Advanced Filters Panel -->
<div x-data="{ showFilters: false }" class="mb-6">
    <!-- Filter Toggle Button -->
    <div class="flex items-center justify-between mb-4">
        <button
            @click="showFilters = !showFilters"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 transition-colors text-sm font-medium text-slate-700"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <span x-text="showFilters ? '{{ __('Hide Filters') }}' : '{{ __('Show Filters') }}'"></span>
            @if(request()->hasAny(['search', 'list_id', 'status_id', 'priority_id', 'assigned_to', 'service_id']))
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ __('Active') }}
                </span>
            @endif
        </button>

        @if(request()->hasAny(['search', 'list_id', 'status_id', 'priority_id', 'assigned_to', 'service_id']))
            <a
                href="{{ route('tasks.index') }}"
                class="text-sm text-slate-600 hover:text-slate-900 underline"
            >
                {{ __('Clear all filters') }}
            </a>
        @endif
    </div>

    <!-- Filters Form -->
    <div
        x-show="showFilters"
        x-transition
        x-cloak
        class="bg-white rounded-lg border border-slate-200 p-6"
    >
        <form method="GET" action="{{ route('tasks.index') }}" class="space-y-4">
            <!-- Keep existing params -->
            <input type="hidden" name="view" value="{{ request('view', 'table') }}">
            <input type="hidden" name="sort" value="{{ request('sort', 'position') }}">
            <input type="hidden" name="dir" value="{{ request('dir', 'asc') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label for="filter-search" class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('Search') }}
                    </label>
                    <input
                        type="text"
                        id="filter-search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ __('Search tasks...') }}"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <!-- List -->
                <div>
                    <label for="filter-list" class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('List') }}
                    </label>
                    <select
                        id="filter-list"
                        name="list_id"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">{{ __('All lists') }}</option>
                        @foreach($lists as $list)
                            <option value="{{ $list->id }}" {{ request('list_id') == $list->id ? 'selected' : '' }}>
                                {{ $list->name }}
                                @if($list->client)
                                    - {{ $list->client->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="filter-status" class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('Status') }}
                    </label>
                    <select
                        id="filter-status"
                        name="status_id"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach($taskStatuses as $status)
                            <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                {{ $status->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Priority -->
                <div>
                    <label for="filter-priority" class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('Priority') }}
                    </label>
                    <select
                        id="filter-priority"
                        name="priority_id"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">{{ __('All priorities') }}</option>
                        @foreach($taskPriorities as $priority)
                            <option value="{{ $priority->id }}" {{ request('priority_id') == $priority->id ? 'selected' : '' }}>
                                {{ $priority->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Assigned To -->
                <div>
                    <label for="filter-assigned" class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('Assigned To') }}
                    </label>
                    <select
                        id="filter-assigned"
                        name="assigned_to"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">{{ __('Anyone') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Service -->
                <div>
                    <label for="filter-service" class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('Service') }}
                    </label>
                    <select
                        id="filter-service"
                        name="service_id"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">{{ __('All services') }}</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a
                    href="{{ route('tasks.index') }}"
                    class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition-colors"
                >
                    {{ __('Reset') }}
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                >
                    {{ __('Apply Filters') }}
                </button>
            </div>
        </form>
    </div>
</div>
