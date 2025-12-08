<x-app-layout>
    <x-slot name="pageTitle">Active Sessions</x-slot>

    <div class="p-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-3xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __("Browser Sessions") }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("Manage and logout your active sessions on other browsers and devices.") }}
                            </p>
                        </header>

                        @if(session('success'))
                            <div class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
                                <p class="text-sm text-green-800">{{ session('success') }}</p>
                            </div>
                        @endif

                        <div class="mt-6 space-y-4">
                            @foreach($sessions as $session)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border {{ $session->is_current ? 'border-blue-300' : 'border-gray-200' }}">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-shrink-0">
                                            @if($session->is_desktop)
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                            @elseif($session->is_mobile)
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">
                                                {{ $session->browser }} on {{ $session->platform }}
                                                @if($session->is_current)
                                                    <span class="ml-2 px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">{{ __("This device") }}</span>
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ $session->ip_address }} &middot; {{ $session->last_active }}
                                            </p>
                                        </div>
                                    </div>

                                    @if(!$session->is_current)
                                        <form method="POST" action="{{ route('profile.sessions.destroy', $session->id) }}" x-data>
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="password" x-ref="sessionPassword" />
                                            <button type="button" @click="
                                                const pwd = prompt('Enter your password to logout this session:');
                                                if (pwd) {
                                                    $refs.sessionPassword.value = pwd;
                                                    $el.closest('form').submit();
                                                }
                                            " class="text-sm text-red-600 hover:text-red-800">
                                                {{ __("Logout") }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if(count($sessions) > 1)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <form method="POST" action="{{ route('profile.sessions.destroy-others') }}" x-data>
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="password" x-ref="logoutAllPassword" />
                                    <x-ui.button type="button" variant="destructive" @click="
                                        const pwd = prompt('Enter your password to logout all other sessions:');
                                        if (pwd) {
                                            $refs.logoutAllPassword.value = pwd;
                                            $el.closest('form').submit();
                                        }
                                    ">
                                        {{ __("Logout All Other Sessions") }}
                                    </x-ui.button>
                                </form>
                            </div>
                        @endif
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 hover:text-gray-900">
                        &larr; {{ __("Back to Profile") }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
