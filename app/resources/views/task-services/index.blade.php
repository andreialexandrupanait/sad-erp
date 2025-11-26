<x-app-layout>
    <x-slot name="pageTitle">{{ __('Task Services') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" onclick="window.location.href='{{ route('task-services.create') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('New Service') }}
        </x-ui.button>
    </x-slot>

    <div class="p-6 space-y-6" x-data>
        <!-- Success Message -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- Search and Filter Bar -->
        <x-ui.card>
            <x-ui.card-content>
                <form method="GET" action="{{ route('task-services.index') }}">
                    <div class="flex flex-col sm:flex-row gap-3 items-center">
                        <!-- Search -->
                        <div class="flex-1 w-full">
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
                                    placeholder="{{ __('Search services') }}"
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Active Status Filter -->
                        <div class="w-full sm:w-48">
                            <x-ui.select name="is_active">
                                <option value="">{{ __('All Services') }}</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </x-ui.select>
                        </div>

                        <!-- Search Button -->
                        <x-ui.button variant="secondary" type="submit">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            {{ __('Search') }}
                        </x-ui.button>

                        <!-- Clear Filters -->
                        @if(request()->hasAny(['search', 'is_active']))
                            <x-ui.button variant="outline" type="button" onclick="window.location.href='{{ route('task-services.index') }}'">
                                {{ __('Clear') }}
                            </x-ui.button>
                        @endif
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Services Table -->
        <x-ui.card>
            <x-ui.card-content class="p-0">
                @if($services->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        {{ __('Service Name') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        {{ __('Default Rate') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        {{ __('Description') }}
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($services as $service)
                                    <tr class="hover:bg-slate-50 cursor-pointer" onclick="window.location.href='{{ route('task-services.edit', $service) }}'">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-slate-900">{{ $service->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900">{{ number_format($service->default_hourly_rate, 2) }} RON/{{ __('hour') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($service->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ __('Active') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                                    {{ __('Inactive') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-slate-500 line-clamp-2">{{ $service->description ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" onclick="event.stopPropagation()">
                                            <a href="{{ route('task-services.edit', $service) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                {{ __('Edit') }}
                                            </a>
                                            <form action="{{ route('task-services.destroy', $service) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this service?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-slate-200">
                        {{ $services->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">{{ __('No services found') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Get started by creating a new service.') }}</p>
                        <div class="mt-6">
                            <x-ui.button variant="default" onclick="window.location.href='{{ route('task-services.create') }}'">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('New Service') }}
                            </x-ui.button>
                        </div>
                    </div>
                @endif
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-app-layout>
