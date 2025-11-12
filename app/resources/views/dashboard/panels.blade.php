{{-- Client Create Slide Panel --}}
<x-slide-panel name="client-create" :show="false" maxWidth="2xl">
    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900">Adaugă Client Nou</h2>
        <button type="button" @click="$dispatch('close-slide-panel', 'client-create')" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <form method="POST" action="{{ route('clients.store') }}" id="client-create-form-dashboard" class="flex-1 overflow-y-auto">
        @csrf
        <div class="px-8 py-6">
            <x-client-form-fields :statuses="$clientStatuses" />
        </div>
    </form>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','client-create')">Cancel</x-ui.button>
        <x-ui.button type="submit" variant="default" form="client-create-form-dashboard">Creează Client</x-ui.button>
    </div>
</x-slide-panel>

{{-- Domain Create Slide Panel --}}
<x-slide-panel name="domain-create" :show="false" maxWidth="2xl">
    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900">Adaugă Domeniu Nou</h2>
        <button type="button" @click="$dispatch('close-slide-panel', 'domain-create')" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <form method="POST" action="{{ route('domains.store') }}" id="domain-create-form-dashboard" class="flex-1 overflow-y-auto">
        @csrf
        <div class="px-8 py-6">
            <x-domain-form-fields :clients="$clients" :registrars="$registrars" :statuses="$domainStatuses" />
        </div>
    </form>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','domain-create')">Cancel</x-ui.button>
        <x-ui.button type="submit" variant="default" form="domain-create-form-dashboard">Creează Domeniu</x-ui.button>
    </div>
</x-slide-panel>

{{-- Subscription Create Slide Panel --}}
<x-slide-panel name="subscription-create" :show="false" maxWidth="2xl">
    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900">Abonament Nou</h2>
        <button type="button" @click="$dispatch('close-slide-panel', 'subscription-create')" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <form method="POST" action="{{ route('subscriptions.store') }}" id="subscription-create-form-dashboard" class="flex-1 overflow-y-auto" x-data="{ billingCycle: 'monthly' }">
        @csrf
        <div class="px-8 py-6">
            <x-subscription-form-fields :billingCycles="$billingCycles" :statuses="$statuses" />
        </div>
    </form>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','subscription-create')">Cancel</x-ui.button>
        <x-ui.button type="submit" variant="default" form="subscription-create-form-dashboard">Creează Abonament</x-ui.button>
    </div>
</x-slide-panel>

{{-- Credential Create Slide Panel --}}
<x-slide-panel name="credential-create" :show="false" maxWidth="2xl">
    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900">Acces Nou</h2>
        <button type="button" @click="$dispatch('close-slide-panel', 'credential-create')" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <form method="POST" action="{{ route('credentials.store') }}" id="credential-create-form-dashboard" class="flex-1 overflow-y-auto" x-data="{ showPass: false }">
        @csrf
        <div class="px-8 py-6">
            <x-credential-form-fields :clients="$clients" :platforms="$platforms" />
        </div>
    </form>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','credential-create')">Cancel</x-ui.button>
        <x-ui.button type="submit" variant="default" form="credential-create-form-dashboard">Salvează</x-ui.button>
    </div>
</x-slide-panel>

{{-- Revenue Create Slide Panel --}}
<x-slide-panel name="revenue-create" :show="false" maxWidth="2xl">
    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900">Adaugă venit nou</h2>
        <button type="button" @click="$dispatch('close-slide-panel', 'revenue-create')" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <form method="POST" action="{{ route('financial.revenues.store') }}" id="revenue-create-form-dashboard" class="flex-1 overflow-y-auto">
        @csrf
        <div class="px-8 py-6">
            <x-revenue-form-fields :clients="$clients" :currencies="$currencies" />
        </div>
    </form>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel', 'revenue-create')">Anulează</x-ui.button>
        <x-ui.button type="submit" form="revenue-create-form-dashboard" variant="default">Salvează venit</x-ui.button>
    </div>
</x-slide-panel>

{{-- Expense Create Slide Panel --}}
<x-slide-panel name="expense-create" :show="false" maxWidth="2xl">
    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900">Adaugă cheltuială nouă</h2>
        <button type="button" @click="$dispatch('close-slide-panel', 'expense-create')" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <form method="POST" action="{{ route('financial.expenses.store') }}" id="expense-create-form-dashboard" class="flex-1 overflow-y-auto">
        @csrf
        <div class="px-8 py-6">
            <x-expense-form-fields :categories="$expenseCategories" :currencies="$currencies" />
        </div>
    </form>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','expense-create')">Anulează</x-ui.button>
        <x-ui.button type="submit" form="expense-create-form-dashboard" variant="default">Salvează cheltuială</x-ui.button>
    </div>
</x-slide-panel>

{{-- Edit panels for expiring domains and overdue subscriptions --}}
@foreach($expiringDomains as $domain)
<x-slide-panel name="domain-edit-{{$domain->id}}" :show="false" maxWidth="2xl">
    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900">Editează Domeniu</h2>
        <button type="button" @click="$dispatch('close-slide-panel','domain-edit-{{$domain->id}}')" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <form method="POST" action="{{ route('domains.update', $domain) }}" id="domain-edit-form-{{$domain->id}}" class="flex-1 overflow-y-auto">
        @csrf
        @method('PUT')
        <div class="px-8 py-6">
            <x-domain-form-fields :domain="$domain" :clients="$clients" :registrars="$registrars" :statuses="$domainStatuses" />
        </div>
    </form>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','domain-edit-{{$domain->id}}')">Cancel</x-ui.button>
        <x-ui.button type="submit" variant="default" form="domain-edit-form-{{$domain->id}}">Actualizează</x-ui.button>
    </div>
</x-slide-panel>
@endforeach

@foreach($overdueSubscriptions as $subscription)
<x-slide-panel name="subscription-edit-{{$subscription->id}}" :show="false" maxWidth="2xl">
    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900">Editează Abonament</h2>
        <button type="button" @click="$dispatch('close-slide-panel','subscription-edit-{{$subscription->id}}')" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <form method="POST" action="{{ route('subscriptions.update', $subscription) }}" id="subscription-edit-form-{{$subscription->id}}" class="flex-1 overflow-y-auto" x-data="{ billingCycle: '{{$subscription->billing_cycle}}' }">
        @csrf
        @method('PUT')
        <div class="px-8 py-6">
            <x-subscription-form-fields :subscription="$subscription" :billingCycles="$billingCycles" :statuses="$statuses" />
        </div>
    </form>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','subscription-edit-{{$subscription->id}}')">Cancel</x-ui.button>
        <x-ui.button type="submit" variant="default" form="subscription-edit-form-{{$subscription->id}}">Actualizează</x-ui.button>
    </div>
</x-slide-panel>
@endforeach
