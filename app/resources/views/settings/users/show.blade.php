<x-app-layout>
    <x-slot name="pageTitle">{{ __("Users & Permissions") }}</x-slot>

    <div class="flex min-h-screen bg-slate-50">
        @include('settings.partials.sidebar')

        <div class="flex-1 overflow-y-auto">
            <div class="p-4 md:p-6">
            <!-- User Info Card -->
            <div class="bg-white rounded-lg border border-slate-200 mb-6">
                <div class="p-4 md:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 h-16 w-16">
                                <div class="h-16 w-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="text-xl font-medium text-gray-600 dark:text-gray-300">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                                <p class="text-sm text-slate-500 dark:text-gray-400">{{ $user->email }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    @php
                                        $roleBadges = [
                                            'superadmin' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                            'admin' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            'manager' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
                                            'user' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
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
                                    @if($user->status === 'active')
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full">{{ __("Active") }}</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 rounded-full">{{ __("Inactive") }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('settings.users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                {{ __("Edit User") }}
                            </a>
                            <a href="{{ route('settings.users.index') }}" class="text-slate-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                {{ __("Back to List") }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Permission Matrix -->
            <div class="bg-white rounded-lg border border-slate-200">
                <div class="p-4 md:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-slate-900">
                                {{ __("Module Permissions") }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __("Configure what this user can access and do.") }}
                            </p>
                        </div>
                        @if(!$user->isSuperAdmin() && !$user->isOrgAdmin())
                            <form method="POST" action="{{ route('settings.users.permissions.reset', $user) }}" onsubmit="return confirm('{{ __("This will remove all custom permissions. Continue?") }}')">
                                @csrf
                                <button type="submit" class="text-sm text-slate-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    {{ __("Reset to Role Defaults") }}
                                </button>
                            </form>
                        @endif
                    </div>

                    @if($user->isSuperAdmin() || $user->isOrgAdmin())
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <div class="flex items-center gap-3">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <div>
                                    <p class="font-medium text-blue-800 dark:text-blue-200">{{ __("Full Access") }}</p>
                                    <p class="text-sm text-blue-600 dark:text-blue-300">
                                        {{ $user->isSuperAdmin() ? __("Super Admin has unrestricted access to all modules.") : __("Organization Admin has full access to all modules.") }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('settings.users.permissions.update', $user) }}" id="permissionsForm">
                            @csrf
                            @method('PUT')

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-slate-100 dark:bg-gray-900">
                                        <tr>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-slate-500 dark:text-gray-400 uppercase tracking-wider">{{ __("Module") }}</th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-slate-500 dark:text-gray-400 uppercase tracking-wider">{{ __("View") }}</th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-slate-500 dark:text-gray-400 uppercase tracking-wider">{{ __("Create") }}</th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-slate-500 dark:text-gray-400 uppercase tracking-wider">{{ __("Update") }}</th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-slate-500 dark:text-gray-400 uppercase tracking-wider">{{ __("Delete") }}</th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-slate-500 dark:text-gray-400 uppercase tracking-wider">{{ __("Export") }}</th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-slate-500 dark:text-gray-400 uppercase tracking-wider">{{ __("Source") }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($permissionMatrix as $slug => $data)
                                            <tr class="{{ $data['is_custom'] ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center gap-2">
                                                        @if($data['module']->icon)
                                                            <span class="text-gray-400">{!! $data['module']->icon !!}</span>
                                                        @endif
                                                        <div>
                                                            <div class="text-sm font-medium text-slate-900">{{ $data['module']->name }}</div>
                                                            @if($data['module']->description)
                                                                <div class="text-xs text-slate-500 dark:text-gray-400">{{ $data['module']->description }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                @foreach(['can_view', 'can_create', 'can_update', 'can_delete', 'can_export'] as $action)
                                                    <td class="px-6 py-4 text-center">
                                                        <input type="checkbox"
                                                            name="permissions[{{ $slug }}][{{ $action }}]"
                                                            value="1"
                                                            {{ $data['permissions'][$action] ? 'checked' : '' }}
                                                            class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500">
                                                    </td>
                                                @endforeach
                                                <td class="px-6 py-4 text-center">
                                                    @if($data['is_custom'])
                                                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">{{ __("Custom") }}</span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded-full">{{ __("Role") }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-6 flex items-center justify-between">
                                <div class="text-sm text-slate-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-3 h-3 bg-blue-100 dark:bg-blue-900/30 rounded"></span>
                                        {{ __("Highlighted rows have custom permissions") }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('settings.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                        {{ __("Cancel") }}
                                    </a>
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        {{ __("Save Permissions") }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <!-- User Activity Info -->
            <div class="mt-6 bg-white rounded-lg border border-slate-200">
                <div class="p-4 md:p-6">
                    <h3 class="text-lg font-medium text-slate-900 mb-4">{{ __("Account Information") }}</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-slate-500 dark:text-gray-400">{{ __("Created") }}</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $user->created_at->format('M j, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-500 dark:text-gray-400">{{ __("Last Login") }}</dt>
                            <dd class="mt-1 text-sm text-slate-900">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : __("Never") }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-500 dark:text-gray-400">{{ __("2FA Enabled") }}</dt>
                            <dd class="mt-1 text-sm text-slate-900">
                                @if($user->hasTwoFactorEnabled())
                                    <span class="text-green-600 dark:text-green-400">{{ __("Yes") }}</span>
                                @else
                                    <span class="text-slate-500">{{ __("No") }}</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
