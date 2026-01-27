<nav x-data="{ open: false }" class="bg-white border-b border-slate-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        @if(isset($globalAppSettings['app_logo']) && $globalAppSettings['app_logo'])
                            <img src="{{ asset('storage/' . $globalAppSettings['app_logo']) }}" alt="{{ $globalAppSettings['app_name'] ?? 'Logo' }}" class="h-9 w-auto">
                        @else
                            <x-application-logo class="block h-9 w-auto fill-current text-slate-800" />
                        @endif
                        <span class="text-lg font-semibold text-slate-800">{{ $globalAppSettings['app_name'] ?? __('ERP System') }}</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                        {{ __('Clients') }}
                    </x-nav-link>
                    <x-nav-link :href="route('financial.dashboard')" :active="request()->routeIs('financial.*')">
                        {{ __('Financial') }}
                    </x-nav-link>
                    <x-nav-link :href="route('offers.index')" :active="request()->routeIs('offers.*')">
                        {{ __('Offers') }}
                    </x-nav-link>
                    <x-nav-link :href="route('contracts.index')" :active="request()->routeIs('contracts.*')">
                        {{ __('Contracts') }}
                    </x-nav-link>
                    <x-nav-link :href="route('subscriptions.index')" :active="request()->routeIs('subscriptions.*')">
                        {{ __('Subscriptions') }}
                    </x-nav-link>
                    <x-nav-link :href="route('domains.index')" :active="request()->routeIs('domains.*')">
                        {{ __('Domains') }}
                    </x-nav-link>
                    <x-nav-link :href="route('credentials.index')" :active="request()->routeIs('credentials.*')">
                        {{ __('Credentials') }}
                    </x-nav-link>
                    <x-nav-link :href="route('internal-accounts.index')" :active="request()->routeIs('internal-accounts.*')">
                        {{ __('Accounts') }}
                    </x-nav-link>
                    <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">
                        {{ __('Settings') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-slate-500 bg-white hover:text-slate-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none focus:bg-slate-100 focus:text-slate-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                {{ __('Clients') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('financial.dashboard')" :active="request()->routeIs('financial.*')">
                {{ __('Financial') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('offers.index')" :active="request()->routeIs('offers.*')">
                {{ __('Offers') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('contracts.index')" :active="request()->routeIs('contracts.*')">
                {{ __('Contracts') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('subscriptions.index')" :active="request()->routeIs('subscriptions.*')">
                {{ __('Subscriptions') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('domains.index')" :active="request()->routeIs('domains.*')">
                {{ __('Domains') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('credentials.index')" :active="request()->routeIs('credentials.*')">
                {{ __('Credentials') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('internal-accounts.index')" :active="request()->routeIs('internal-accounts.*')">
                {{ __('Accounts') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">
                {{ __('Settings') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-slate-200">
            <div class="px-4">
                <div class="font-medium text-base text-slate-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
