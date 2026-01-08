<header class="bg-white dark:bg-slate-800 border-b border-gray-100 dark:border-slate-700 sticky top-0 z-40 shadow-sm transition-colors duration-200">
    <div class="flex items-center justify-between px-6 py-4">
        <!-- Left Side - Page Title -->
        <div class="flex items-center gap-4">
            <button @click="open = !open" class="md:hidden p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                <svg class="w-6 h-6 text-gray-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    @yield('page-title', 'Dashboard')
                </h1>
                @hasSection('page-description')
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">@yield('page-description')</p>
                @endif
            </div>
        </div>

        <!-- Right Side - Search & User Actions -->
        <div class="flex items-center gap-3">
            <!-- Search Bar -->
            <div class="hidden md:flex items-center relative">
                <svg class="absolute left-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="search"
                    placeholder="Search..."
                    class="pl-10 pr-4 py-2 w-64 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                >
            </div>

            <!-- Notifications Bell -->
            <button class="relative p-2 rounded-xl hover:bg-gray-50 transition-colors">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- User Avatar Menu -->
            <div x-data="{ userMenuOpen: false }" class="relative">
                <button
                    @click="userMenuOpen = !userMenuOpen"
                    class="flex items-center gap-2 p-1.5 pr-3 rounded-xl hover:bg-gray-50 transition-colors"
                >
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-medium text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="hidden lg:block text-left">
                        <div class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role) }}</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div
                    x-show="userMenuOpen"
                    @click.away="userMenuOpen = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50"
                    style="display: none;"
                >
                    <div class="px-4 py-3 border-b border-gray-100">
                        <div class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ auth()->user()->email }}</div>
                    </div>

                    <div class="py-2">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile
                        </a>
                        <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Settings
                        </a>
                    </div>

                    <div class="border-t border-gray-100 pt-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
