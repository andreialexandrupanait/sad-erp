<aside class="w-64 bg-white border-r border-slate-200 flex-shrink-0">
    <div class="sticky top-0 overflow-y-auto max-h-screen">
        <div class="p-6">
            <h1 class="text-xl font-bold text-slate-900">{{ __('Settings') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('Manage your application') }}</p>
        </div>

        <nav class="px-3 pb-6 space-y-1">
            <!-- Application -->
            <a href="{{ route('settings.application') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.application') || request()->routeIs('settings.index') ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'text-slate-600 hover:bg-slate-50' }}">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('settings.application') || request()->routeIs('settings.index') ? 'bg-blue-100' : 'bg-slate-100' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('settings.application') || request()->routeIs('settings.index') ? 'text-blue-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <span class="block">{{ __('Application') }}</span>
                    <span class="text-xs {{ request()->routeIs('settings.application') || request()->routeIs('settings.index') ? 'text-blue-500' : 'text-slate-400' }}">{{ __('Name, logo, language') }}</span>
                </div>
            </a>

            <!-- Business -->
            <a href="{{ route('settings.business') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.business*') || request()->routeIs('settings.services*') || request()->routeIs('settings.invoice-settings') ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'text-slate-600 hover:bg-slate-50' }}">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('settings.business*') || request()->routeIs('settings.services*') || request()->routeIs('settings.invoice-settings') ? 'bg-emerald-100' : 'bg-slate-100' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('settings.business*') || request()->routeIs('settings.services*') || request()->routeIs('settings.invoice-settings') ? 'text-emerald-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <span class="block">{{ __('Business') }}</span>
                    <span class="text-xs {{ request()->routeIs('settings.business*') || request()->routeIs('settings.services*') || request()->routeIs('settings.invoice-settings') ? 'text-emerald-500' : 'text-slate-400' }}">{{ __('Company, invoicing, services') }}</span>
                </div>
            </a>

            <!-- Integrations -->
            <a href="{{ route('settings.integrations') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.integrations*') || request()->routeIs('settings.smartbill.*') || request()->routeIs('settings.clickup.*') ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'text-slate-600 hover:bg-slate-50' }}">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('settings.integrations*') || request()->routeIs('settings.smartbill.*') || request()->routeIs('settings.clickup.*') ? 'bg-purple-100' : 'bg-slate-100' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('settings.integrations*') || request()->routeIs('settings.smartbill.*') || request()->routeIs('settings.clickup.*') ? 'text-purple-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                    </svg>
                </div>
                <div>
                    <span class="block">{{ __('Integrations') }}</span>
                    <span class="text-xs {{ request()->routeIs('settings.integrations*') || request()->routeIs('settings.smartbill.*') || request()->routeIs('settings.clickup.*') ? 'text-purple-500' : 'text-slate-400' }}">{{ __('SmartBill, ClickUp') }}</span>
                </div>
            </a>

            <!-- Nomenclature -->
            <a href="{{ route('settings.nomenclature') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.nomenclature') || request()->routeIs('settings.client-statuses') || request()->routeIs('settings.domain-statuses') || request()->routeIs('settings.subscription-statuses') || request()->routeIs('settings.access-platforms') || request()->routeIs('settings.expense-categories') || request()->routeIs('settings.payment-methods') || request()->routeIs('settings.billing-cycles') || request()->routeIs('settings.domain-registrars') || request()->routeIs('settings.currencies') ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'text-slate-600 hover:bg-slate-50' }}">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('settings.nomenclature') || request()->routeIs('settings.client-statuses') || request()->routeIs('settings.domain-statuses') || request()->routeIs('settings.subscription-statuses') || request()->routeIs('settings.access-platforms') || request()->routeIs('settings.expense-categories') || request()->routeIs('settings.payment-methods') || request()->routeIs('settings.billing-cycles') || request()->routeIs('settings.domain-registrars') || request()->routeIs('settings.currencies') ? 'bg-amber-100' : 'bg-slate-100' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('settings.nomenclature') || request()->routeIs('settings.client-statuses') || request()->routeIs('settings.domain-statuses') || request()->routeIs('settings.subscription-statuses') || request()->routeIs('settings.access-platforms') || request()->routeIs('settings.expense-categories') || request()->routeIs('settings.payment-methods') || request()->routeIs('settings.billing-cycles') || request()->routeIs('settings.domain-registrars') || request()->routeIs('settings.currencies') ? 'text-amber-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
                <div>
                    <span class="block">{{ __('Nomenclature') }}</span>
                    <span class="text-xs {{ request()->routeIs('settings.nomenclature') || request()->routeIs('settings.client-statuses') || request()->routeIs('settings.domain-statuses') || request()->routeIs('settings.subscription-statuses') || request()->routeIs('settings.access-platforms') || request()->routeIs('settings.expense-categories') || request()->routeIs('settings.payment-methods') || request()->routeIs('settings.billing-cycles') || request()->routeIs('settings.domain-registrars') || request()->routeIs('settings.currencies') ? 'text-amber-500' : 'text-slate-400' }}">{{ __('Statuses, categories') }}</span>
                </div>
            </a>

            <!-- Notifications -->
            <a href="{{ route('settings.notifications') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.notifications*') ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'text-slate-600 hover:bg-slate-50' }}">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('settings.notifications*') ? 'bg-rose-100' : 'bg-slate-100' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('settings.notifications*') ? 'text-rose-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div>
                    <span class="block">{{ __('Notifications') }}</span>
                    <span class="text-xs {{ request()->routeIs('settings.notifications*') ? 'text-rose-500' : 'text-slate-400' }}">{{ __('Alerts, reminders') }}</span>
                </div>
            </a>

            <!-- Yearly Objectives -->
            <a href="{{ route('settings.yearly-objectives') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.yearly-objectives') ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'text-slate-600 hover:bg-slate-50' }}">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('settings.yearly-objectives') ? 'bg-teal-100' : 'bg-slate-100' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('settings.yearly-objectives') ? 'text-teal-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div>
                    <span class="block">{{ __('Yearly Objectives') }}</span>
                    <span class="text-xs {{ request()->routeIs('settings.yearly-objectives') ? 'text-teal-500' : 'text-slate-400' }}">{{ __('Budget thresholds') }}</span>
                </div>
            </a>
        </nav>
    </div>
</aside>
