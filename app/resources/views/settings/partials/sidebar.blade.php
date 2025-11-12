<aside class="w-64 bg-white border-r border-slate-200 flex-shrink-0">
    <div class="sticky top-0 overflow-y-auto max-h-screen">
        <div class="p-6">
            <h1 class="text-xl font-bold text-slate-900">Settings</h1>
            <p class="text-sm text-slate-500 mt-1">Manage your application</p>
        </div>

        <nav class="px-3 pb-6">
            <!-- Application Settings -->
            <div class="mb-6">
                <div class="px-3 mb-2">
                    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Application</h2>
                </div>
                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.index') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Application Settings
                </a>
            </div>

            <!-- Nomenclatoare -->
            <div>
                <div class="px-3 mb-2">
                    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nomenclatoare</h2>
                </div>

                <a href="{{ route('settings.client-statuses') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.client-statuses') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>Status clienti</span>
                </a>

                <a href="{{ route('settings.domain-statuses') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.domain-statuses') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>Status domenii</span>
                </a>

                <a href="{{ route('settings.subscription-statuses') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.subscription-statuses') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>Status abonamente</span>
                </a>

                <a href="{{ route('settings.access-platforms') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.access-platforms') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>Categorii platforme</span>
                </a>

                <a href="{{ route('settings.expense-categories') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.expense-categories') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>Categorii cheltuieli</span>
                </a>

                <a href="{{ route('settings.payment-methods') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.payment-methods') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>Metode de plata</span>
                </a>

                <a href="{{ route('settings.billing-cycles') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.billing-cycles') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>Cicluri de facturare</span>
                </a>

                <a href="{{ route('settings.domain-registrars') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group {{ request()->routeIs('settings.domain-registrars') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>Registratori de domenii</span>
                </a>
            </div>
        </nav>
    </div>
</aside>
