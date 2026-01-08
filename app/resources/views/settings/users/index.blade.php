<x-app-layout>
    <x-slot name="pageTitle">{{ __("Users & Permissions") }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg border border-slate-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">{{ __("Total Users") }}</p>
                            <p class="text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg border border-slate-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">{{ __("Active") }}</p>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg border border-slate-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">{{ __("Inactive") }}</p>
                            <p class="text-2xl font-bold text-slate-600">{{ $stats['inactive'] }}</p>
                        </div>
                        <div class="p-3 bg-slate-100 rounded-full">
                            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg border border-slate-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">{{ __("Archived") }}</p>
                            <p class="text-2xl font-bold text-red-600">{{ $stats['archived'] }}</p>
                        </div>
                        <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-lg font-medium text-slate-900">
                                {{ __("Team Members") }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ __("Manage users and their access permissions.") }}
                            </p>
                        </div>
                        <a href="{{ route('settings.users.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            {{ __("Add User") }}
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-50  rounded-lg border border-green-200 ">
                            <p class="text-sm text-green-800 ">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-50  rounded-lg border border-red-200 ">
                            <p class="text-sm text-red-800 ">{{ session('error') }}</p>
                        </div>
                    @endif

                    <!-- Filters -->
                    <form method="GET" class="mb-6 flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <x-ui.input type="text" name="search" placeholder="{{ __('Search by name or email...') }}" value="{{ request('search') }}" />
                        </div>
                        <div>
                            <x-ui.select name="role">
                                <option value="">{{ __("All Roles") }}</option>
                                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>{{ __("Admin") }}</option>
                                <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>{{ __("Manager") }}</option>
                                <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>{{ __("User") }}</option>
                            </x-ui.select>
                        </div>
                        <div>
                            <x-ui.select name="status">
                                <option value="">{{ __("Toate statusurile") }}</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __("Activ") }}</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __("Inactiv") }}</option>
                                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>{{ __("Arhivat") }}</option>
                            </x-ui.select>
                        </div>
                        <x-ui.button type="submit" variant="outline">
                            {{ __("Filtrează") }}
                        </x-ui.button>
                        @if(request()->hasAny(['search', 'role', 'status']))
                            <a href="{{ route('settings.users.index') }}" class="px-4 py-2 text-slate-500 hover:text-slate-700">
                                {{ __("Resetează") }}
                            </a>
                        @endif
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __("User") }}</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __("Role") }}</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __("Status") }}</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __("Last Login") }}</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __("Actions") }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @forelse($users as $user)
                                    <tr class="{{ $user->trashed() ? 'opacity-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-slate-600">
                                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-slate-900">
                                                        {{ $user->name }}
                                                        @if($user->id === auth()->id())
                                                            <span class="ml-1 text-xs text-gray-500">({{ __("you") }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-sm text-slate-500">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $roleBadges = [
                                                    'superadmin' => 'bg-purple-100 text-purple-800',
                                                    'admin' => 'bg-blue-100 text-blue-800',
                                                    'manager' => 'bg-amber-100 text-amber-800',
                                                    'user' => 'bg-slate-100 text-slate-800',
                                                ];
                                                $roleLabels = [
                                                    'superadmin' => __("Super Admin"),
                                                    'admin' => __("Admin"),
                                                    'manager' => __("Manager"),
                                                    'user' => __("User"),
                                                ];
                                            @endphp
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $roleBadges[$user->role] ?? $roleBadges['user'] }}">
                                                {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($user->trashed())
                                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">{{ __("Arhivat") }}</span>
                                            @elseif($user->status === 'active')
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">{{ __("Activ") }}</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium bg-slate-100 text-slate-800 rounded-full">{{ __("Inactiv") }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            @if($user->last_login_at)
                                                {{ $user->last_login_at->diffForHumans() }}
                                            @else
                                                <span class="text-slate-400">{{ __("Niciodată") }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if($user->trashed())
                                                <form method="POST" action="{{ route('settings.users.restore', $user->id) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-800">
                                                        {{ __("Restaurează") }}
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('settings.users.show', $user) }}" class="text-blue-600 hover:text-blue-800 mr-3">
                                                    {{ __("Permisiuni") }}
                                                </a>
                                                <a href="{{ route('settings.users.edit', $user) }}" class="text-slate-600 hover:text-slate-800 mr-3">
                                                    {{ __("Editează") }}
                                                </a>
                                                @if($user->id !== auth()->id() && !$user->isSuperAdmin())
                                                    <form method="POST" action="{{ route('settings.users.destroy', $user) }}" class="inline" onsubmit="return confirm('{{ __("Ești sigur că vrei să arhivezi acest utilizator?") }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                                            {{ __("Arhivează") }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            <p class="mt-2">{{ __("No users found.") }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                        <div class="mt-4">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
