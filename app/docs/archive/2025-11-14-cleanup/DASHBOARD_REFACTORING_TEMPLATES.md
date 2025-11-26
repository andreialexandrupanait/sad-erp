# Dashboard Slide Panel Refactoring Templates

Replace the inline forms in `dashboard.blade.php` with these component-based templates.

## 1. Client Create Slide Panel

Replace the existing `<!-- Client Create Slide Panel -->` section with:

```blade
<!-- Client Create Slide Panel -->
<x-slide-panel name="client-create" :show="false" maxWidth="2xl">
    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900">Adaugă Client Nou</h2>
        <button type="button" @click="$dispatch('close-slide-panel', 'client-create')" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto px-8 py-6">
        <x-ajax-form-wrapper
            formId="client-create-form-dashboard"
            :action="route('clients.store')"
            method="POST"
            slidePanel="client-create"
            successMessage="Client created successfully!"
        >
            @php $clientStatuses = \App\Models\ClientSetting::active()->ordered()->get(); @endphp
            <x-client-form-fields :statuses="$clientStatuses" idSuffix="_dash" />
        </x-ajax-form-wrapper>
    </div>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','client-create')">Cancel</x-ui.button>
        <x-ui.button type="submit" variant="default" form="client-create-form-dashboard">Creează Client</x-ui.button>
    </div>
</x-slide-panel>
```

## 2. Domain Create Slide Panel

Replace the existing `<!-- Domain Create Slide Panel -->` section with:

```blade
<!-- Domain Create Slide Panel -->
<x-slide-panel name="domain-create" :show="false" maxWidth="2xl">
    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900">Adaugă Domeniu Nou</h2>
        <button type="button" @click="$dispatch('close-slide-panel', 'domain-create')" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto px-8 py-6">
        <x-ajax-form-wrapper
            formId="domain-create-form-dashboard"
            :action="route('domains.store')"
            method="POST"
            slidePanel="domain-create"
            successMessage="Domain created successfully!"
        >
            <x-domain-form-fields :clients="$clients" idSuffix="_dash" />
        </x-ajax-form-wrapper>
    </div>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','domain-create')">Cancel</x-ui.button>
        <x-ui.button type="submit" variant="default" form="domain-create-form-dashboard">Creează Domeniu</x-ui.button>
    </div>
</x-slide-panel>
```

## 3. Subscription Create Slide Panel

Replace the existing `<!-- Subscription Create Slide Panel -->` section with:

```blade
<!-- Subscription Create Slide Panel -->
<x-slide-panel name="subscription-create" :show="false" maxWidth="2xl">
    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900">Abonament Nou</h2>
        <button type="button" @click="$dispatch('close-slide-panel', 'subscription-create')" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto px-8 py-6">
        <x-ajax-form-wrapper
            formId="subscription-create-form-dashboard"
            :action="route('subscriptions.store')"
            method="POST"
            slidePanel="subscription-create"
            successMessage="Subscription created successfully!"
        >
            <div x-data="{ billingCycle: 'monthly' }">
                <x-subscription-form-fields idSuffix="_dash" />
            </div>
        </x-ajax-form-wrapper>
    </div>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','subscription-create')">Cancel</x-ui.button>
        <x-ui.button type="submit" variant="default" form="subscription-create-form-dashboard">Creează Abonament</x-ui.button>
    </div>
</x-slide-panel>
```

## 4. Credential Create Slide Panel

Replace the existing `<!-- Credential Create Slide Panel -->` section with:

```blade
<!-- Credential Create Slide Panel -->
<x-slide-panel name="credential-create" :show="false" maxWidth="2xl">
    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900">Acces Nou</h2>
        <button type="button" @click="$dispatch('close-slide-panel', 'credential-create')" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto px-8 py-6">
        <x-ajax-form-wrapper
            formId="credential-create-form-dashboard"
            :action="route('credentials.store')"
            method="POST"
            slidePanel="credential-create"
            successMessage="Credential created successfully!"
        >
            <div x-data="{ showPass: false }">
                <x-credential-form-fields :clients="$clients" idSuffix="_dash" />
            </div>
        </x-ajax-form-wrapper>
    </div>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','credential-create')">Cancel</x-ui.button>
        <x-ui.button type="submit" variant="default" form="credential-create-form-dashboard">Salvează</x-ui.button>
    </div>
</x-slide-panel>
```

## Important Notes

### Alpine.js State Management

For components that need Alpine.js reactive state (like password toggle or billing cycle):

1. **Credential Form** (password toggle):
   ```blade
   <div x-data="{ showPass: false }">
       <x-credential-form-fields :clients="$clients" idSuffix="_dash" />
   </div>
   ```

2. **Subscription Form** (billing cycle):
   ```blade
   <div x-data="{ billingCycle: 'monthly' }">
       <x-subscription-form-fields idSuffix="_dash" />
   </div>
   ```

### Benefits After Refactoring

1. **Zero Duplication**: Same form fields used in dedicated pages AND dashboard panels
2. **Automatic Updates**: Change a field once, it updates everywhere
3. **Consistent Validation**: Same error handling across all contexts
4. **Easier Maintenance**: Less code to manage
5. **Type Safety**: Same props across all usages

### Testing Checklist

After replacing each panel:
- [ ] Open dashboard
- [ ] Click the quick action button
- [ ] Verify the slide panel opens
- [ ] Verify all fields render correctly
- [ ] Test form submission
- [ ] Verify validation errors display properly
- [ ] Verify success toast and page reload
- [ ] Check browser console for errors

## Edit Panels for Expiring Domains

The dashboard also has dynamic edit panels for expiring domains. These can use the same pattern:

```blade
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
    <div class="flex-1 overflow-y-auto px-8 py-6">
        <x-ajax-form-wrapper
            formId="domain-edit-form-{{$domain->id}}"
            :action="route('domains.update', $domain)"
            method="PUT"
            slidePanel="domain-edit-{{$domain->id}}"
            successMessage="Domain updated successfully!"
        >
            <x-domain-form-fields :domain="$domain" :clients="$clients" idSuffix="_edit_{{$domain->id}}" />
        </x-ajax-form-wrapper>
    </div>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','domain-edit-{{$domain->id}}')">Cancel</x-ui.button>
        <x-ui.button type="submit" variant="default" form="domain-edit-form-{{$domain->id}}">Actualizează</x-ui.button>
    </div>
</x-slide-panel>
@endforeach
```

Apply the same pattern for overdue subscriptions edit panels.

## Final Step

After all replacements, clear the view cache:

```bash
rm -rf storage/framework/views/*.php
```
