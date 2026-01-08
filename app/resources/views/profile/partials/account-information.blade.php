<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <!-- Organization -->
    <div class="p-4 bg-slate-50 rounded-lg">
        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Organization') }}</dt>
        <dd class="mt-1 text-sm font-semibold text-slate-900">
            {{ $user->organization->name ?? '-' }}
        </dd>
    </div>

    <!-- Role -->
    <div class="p-4 bg-slate-50 rounded-lg">
        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Role') }}</dt>
        <dd class="mt-1">
            @php
                $roleColors = [
                    'superadmin' => 'bg-purple-100 text-purple-800',
                    'admin' => 'bg-red-100 text-red-800',
                    'manager' => 'bg-blue-100 text-blue-800',
                    'user' => 'bg-green-100 text-green-800',
                    'viewer' => 'bg-slate-100 text-slate-800',
                ];
                $roleColor = $roleColors[$user->role] ?? 'bg-slate-100 text-slate-800';
            @endphp
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $roleColor }}">
                {{ $user->role_label }}
            </span>
        </dd>
    </div>

    <!-- Status -->
    <div class="p-4 bg-slate-50 rounded-lg">
        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Status') }}</dt>
        <dd class="mt-1">
            @if($user->status === 'active')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ __('Active') }}
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    {{ __('Inactive') }}
                </span>
            @endif
        </dd>
    </div>

    <!-- Member Since -->
    <div class="p-4 bg-slate-50 rounded-lg">
        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Member Since') }}</dt>
        <dd class="mt-1 text-sm font-semibold text-slate-900">
            {{ $user->created_at->format('d M Y') }}
        </dd>
    </div>

    <!-- Last Login -->
    <div class="p-4 bg-slate-50 rounded-lg">
        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Last Login') }}</dt>
        <dd class="mt-1 text-sm font-semibold text-slate-900">
            @if($user->last_login_at)
                {{ $user->last_login_at->diffForHumans() }}
            @else
                {{ __('Never') }}
            @endif
        </dd>
    </div>

    <!-- Email Verified -->
    <div class="p-4 bg-slate-50 rounded-lg">
        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Email Verified') }}</dt>
        <dd class="mt-1">
            @if($user->email_verified_at)
                <span class="inline-flex items-center gap-1 text-sm text-green-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ __('Verified') }}
                </span>
            @else
                <span class="inline-flex items-center gap-1 text-sm text-amber-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    {{ __('Unverified') }}
                </span>
            @endif
        </dd>
    </div>

    <!-- 2FA Status -->
    <div class="p-4 bg-slate-50 rounded-lg">
        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Two-Factor Auth') }}</dt>
        <dd class="mt-1">
            @if($user->hasTwoFactorEnabled())
                <span class="inline-flex items-center gap-1 text-sm text-green-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ __('Enabled') }}
                </span>
            @else
                <a href="{{ route('profile.two-factor') }}" class="inline-flex items-center gap-1 text-sm text-amber-600 hover:text-amber-700">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    {{ __('Not Enabled') }}
                </a>
            @endif
        </dd>
    </div>

    <!-- User ID -->
    <div class="p-4 bg-slate-50 rounded-lg">
        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('User ID') }}</dt>
        <dd class="mt-1 text-sm font-mono text-slate-900">
            #{{ $user->id }}
        </dd>
    </div>
</div>
