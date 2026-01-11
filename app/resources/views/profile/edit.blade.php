<x-app-layout>
    <x-slot name="pageTitle">{{ __('Profile') }}</x-slot>

    <div class="p-4 md:p-6">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Quick Links -->
            <x-ui.card>
                <x-ui.card-header title="{{ __('Quick Actions') }}" />
                <x-ui.card-content class="pt-0">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('profile.two-factor') }}" class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition group">
                            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-blue-100 rounded-lg group-hover:bg-blue-200 transition">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ __("Two-Factor Auth") }}</p>
                                <p class="text-xs text-slate-500">
                                    @if(auth()->user()->hasTwoFactorEnabled())
                                        <span class="text-green-600">{{ __("Enabled") }}</span>
                                    @else
                                        <span class="text-amber-600">{{ __("Not enabled") }}</span>
                                    @endif
                                </p>
                            </div>
                        </a>

                        <a href="{{ route('profile.sessions') }}" class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition group">
                            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-green-100 rounded-lg group-hover:bg-green-200 transition">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ __("Sessions") }}</p>
                                <p class="text-xs text-slate-500">{{ __("Manage devices") }}</p>
                            </div>
                        </a>

                        <a href="{{ route('profile.activities') }}" class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition group">
                            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-purple-100 rounded-lg group-hover:bg-purple-200 transition">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ __("Activity Log") }}</p>
                                <p class="text-xs text-slate-500">{{ __("View history") }}</p>
                            </div>
                        </a>

                        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition group">
                            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-slate-200 rounded-lg group-hover:bg-slate-300 transition">
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ __("App Settings") }}</p>
                                <p class="text-xs text-slate-500">{{ __("Organization") }}</p>
                            </div>
                        </a>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Account Information -->
            <x-ui.card>
                <x-ui.card-header title="{{ __('Account Information') }}" description="{{ __('Your account details and status.') }}" />
                <x-ui.card-content class="pt-0">
                    @include('profile.partials.account-information')
                </x-ui.card-content>
            </x-ui.card>

            <!-- Profile Information -->
            <x-ui.card>
                <x-ui.card-header title="{{ __('Profile Information') }}" description="{{ __('Update your account profile information and email address.') }}" />
                <x-ui.card-content class="pt-0">
                    @include('profile.partials.update-profile-information-form')
                </x-ui.card-content>
            </x-ui.card>

            <!-- User Preferences -->
            <x-ui.card>
                <x-ui.card-header title="{{ __('Preferences') }}" description="{{ __('Customize your experience with personal preferences.') }}" />
                <x-ui.card-content class="pt-0">
                    @include('profile.partials.user-preferences')
                </x-ui.card-content>
            </x-ui.card>

            <!-- Notification Preferences -->
            <x-ui.card>
                <x-ui.card-header title="{{ __('Notification Preferences') }}" description="{{ __('Choose which notifications you want to receive.') }}" />
                <x-ui.card-content class="pt-0">
                    @include('profile.partials.notification-preferences')
                </x-ui.card-content>
            </x-ui.card>

            <!-- Update Password -->
            <x-ui.card>
                <x-ui.card-header title="{{ __('Update Password') }}" description="{{ __('Ensure your account is using a long, random password to stay secure.') }}" />
                <x-ui.card-content class="pt-0">
                    @include('profile.partials.update-password-form')
                </x-ui.card-content>
            </x-ui.card>

            <!-- Recent Activity -->
            <x-ui.card>
                <x-ui.card-header>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-semibold leading-none tracking-tight">{{ __('Recent Activity') }}</h3>
                            <p class="text-sm text-slate-500">{{ __('Your recent account activity and security events.') }}</p>
                        </div>
                        @if($recentActivities->count() > 0)
                            <a href="{{ route('profile.activities') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                {{ __('View all') }} &rarr;
                            </a>
                        @endif
                    </div>
                </x-ui.card-header>
                <x-ui.card-content class="pt-0">
                    @include('profile.partials.activity-log')
                </x-ui.card-content>
            </x-ui.card>

            <!-- Delete Account -->
            <x-ui.card class="border-red-200">
                <x-ui.card-header title="{{ __('Danger Zone') }}" description="{{ __('Irreversible and destructive actions.') }}" class="bg-red-50 border-b-red-200" />
                <x-ui.card-content class="pt-0">
                    @include('profile.partials.delete-user-form')
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
