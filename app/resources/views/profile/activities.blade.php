<x-app-layout>
    <x-slot name="pageTitle">{{ __('Activity Log') }}</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="outline" onclick="window.location.href='{{ route('profile.edit') }}'">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('Back to Profile') }}
        </x-ui.button>
    </x-slot>

    <div class="p-6">
        <div class="max-w-4xl mx-auto">
            <x-ui.card>
                <x-ui.card-header title="{{ __('Activity Log') }}" description="{{ __('Complete history of your account activity.') }}" />
                <x-ui.card-content class="pt-0">
                    @if($activities->count() > 0)
                        <div class="overflow-x-auto">
                            <x-ui.table>
                                <x-ui.table-head>
                                    <x-ui.table-row>
                                        <x-ui.table-cell header>{{ __('Action') }}</x-ui.table-cell>
                                        <x-ui.table-cell header>{{ __('Description') }}</x-ui.table-cell>
                                        <x-ui.table-cell header>{{ __('IP Address') }}</x-ui.table-cell>
                                        <x-ui.table-cell header>{{ __('Date') }}</x-ui.table-cell>
                                    </x-ui.table-row>
                                </x-ui.table-head>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    @foreach($activities as $activity)
                                        <x-ui.table-row class="hover:bg-slate-50">
                                            <x-ui.table-cell class="whitespace-nowrap">
                                                <span class="inline-flex items-center gap-2 text-sm {{ $activity->action_icon }}">
                                                    @switch($activity->action)
                                                        @case('login')
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                                            </svg>
                                                            @break
                                                        @case('logout')
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                            </svg>
                                                            @break
                                                        @default
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                    @endswitch
                                                    {{ $activity->action_label }}
                                                </span>
                                            </x-ui.table-cell>
                                            <x-ui.table-cell class="text-slate-500">
                                                {{ $activity->description ?? '-' }}
                                            </x-ui.table-cell>
                                            <x-ui.table-cell class="whitespace-nowrap font-mono text-slate-500">
                                                {{ $activity->ip_address ?? '-' }}
                                            </x-ui.table-cell>
                                            <x-ui.table-cell class="whitespace-nowrap text-slate-500">
                                                <span title="{{ $activity->created_at->format('d/m/Y H:i:s') }}">
                                                    {{ $activity->created_at->diffForHumans() }}
                                                </span>
                                            </x-ui.table-cell>
                                        </x-ui.table-row>
                                    @endforeach
                                </tbody>
                            </x-ui.table>
                        </div>

                        <div class="mt-4">
                            {{ $activities->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ __('No activity recorded') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Your account activity will appear here.') }}</p>
                        </div>
                    @endif
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
