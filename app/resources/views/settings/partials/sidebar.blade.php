<aside class="w-64 bg-white border-r border-slate-200 flex-shrink-0">
    <div class="sticky top-0 overflow-y-auto max-h-screen">
        <div class="p-6">
            <h1 class="text-xl font-bold text-slate-900">{{ __('Settings') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('Manage your application') }}</p>
        </div>

        <nav class="px-3 pb-6 space-y-6">
            <!-- Application Settings -->
            <div>
                <div class="px-3 mb-2">
                    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Application') }}</h2>
                </div>
                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.index') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ __('Application Settings') }}
                </a>
            </div>

            <!-- Business Information (NEW) -->
            <div>
                <div class="px-3 mb-2">
                    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Business') }}</h2>
                </div>
                <a href="{{ route('settings.business-info') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.business-info') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    {{ __('Business Information') }}
                </a>
                <a href="{{ route('settings.invoice-settings') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.invoice-settings') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('Invoice Settings') }}
                </a>
            </div>

            <!-- Notifications (NEW) -->
            <div>
                <div class="px-3 mb-2">
                    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Notifications') }}</h2>
                </div>
                <a href="{{ route('settings.notifications') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.notifications') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    {{ __('Notification Settings') }}
                </a>
            </div>

            <!-- Integrations -->
            <div>
                <div class="px-3 mb-2">
                    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Integrations') }}</h2>
                </div>
                <a href="{{ route('settings.smartbill.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.smartbill.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('Smartbill Import') }}
                </a>
            </div>

            <!-- Nomenclatoare -->
            <div>
                <div class="px-3 mb-2">
                    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Nomenclature') }}</h2>
                </div>

                <a href="{{ route('settings.client-statuses') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.client-statuses') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>{{ __('Client Statuses') }}</span>
                </a>

                <a href="{{ route('settings.domain-statuses') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.domain-statuses') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>{{ __('Domain Statuses') }}</span>
                </a>

                <a href="{{ route('settings.subscription-statuses') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.subscription-statuses') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>{{ __('Subscription Statuses') }}</span>
                </a>

                <a href="{{ route('settings.access-platforms') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.access-platforms') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>{{ __('Platform Categories') }}</span>
                </a>

                <a href="{{ route('settings.expense-categories') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.expense-categories') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>{{ __('Expense Categories') }}</span>
                </a>

                <a href="{{ route('settings.payment-methods') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.payment-methods') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>{{ __('Payment Methods') }}</span>
                </a>

                <a href="{{ route('settings.billing-cycles') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.billing-cycles') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>{{ __('Billing Cycles') }}</span>
                </a>

                <a href="{{ route('settings.domain-registrars') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.domain-registrars') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>{{ __('Domain Registrars') }}</span>
                </a>

                <a href="{{ route('settings.currencies') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.currencies') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>{{ __('Currencies') }}</span>
                </a>
            </div>
        </nav>
    </div>
</aside>
