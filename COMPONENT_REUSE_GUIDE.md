# Component Reuse Guide

This guide explains the 100% component reuse pattern implemented across the ERP application.

## Architecture Overview

The application now uses a **three-layer component architecture** for maximum code reuse:

### Layer 1: Field Components (Reusable Fields)
Contains only the form fields without wrapping markup.
- **Purpose**: Can be used in both regular forms and AJAX slide panels
- **Example**: `client-form-fields.blade.php`
- **Props**: `idSuffix` parameter allows unique IDs for multiple instances on same page

### Layer 2: Full Form Components (Regular Forms)
Wraps field components with form tags, cards, and submit buttons.
- **Purpose**: Used in dedicated create/edit pages
- **Example**: `client-form.blade.php`
- **Structure**:
  ```blade
  <form method="POST" action="{{ $action }}">
      @csrf
      <x-ui.card>
          <x-ui.card-content>
              <x-{entity}-form-fields :entity="$entity" :related="$related" />
          </x-ui.card-content>
          <!-- Submit buttons -->
      </x-ui.card>
  </form>
  ```

### Layer 3: AJAX Form Wrapper
Wraps field components for AJAX submission in slide panels.
- **Component**: `ajax-form-wrapper.blade.php`
- **Features**:
  - Automatic AJAX submission
  - Error handling and validation display
  - Toast notifications
  - Slide panel integration

## Implementation Pattern

### Step 1: Create Field Component

Create `{entity}-form-fields.blade.php`:

```blade
@props(['entity' => null, 'relatedData' => [], 'idSuffix' => ''])

<div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
    <div class="sm:col-span-3 field-wrapper">
        <x-ui.label for="field_name{{ $idSuffix }}">
            Field Label <span class="text-red-500">*</span>
        </x-ui.label>
        <div class="mt-2">
            <x-ui.input
                type="text"
                name="field_name"
                id="field_name{{ $idSuffix }}"
                required
                value="{{ old('field_name', $entity->field_name ?? '') }}"
            />
        </div>
        @error('field_name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <!-- More fields... -->
</div>
```

**Key Points**:
- Add `{{ $idSuffix }}` to all `id` and `for` attributes
- Keep `name` attributes without suffix (for form submission)
- Wrap each field in `field-wrapper` class (for error handling)

### Step 2: Update Full Form Component

Update `{entity}-form.blade.php`:

```blade
@props(['entity' => null, 'relatedData' => [], 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <x-ui.card-content>
            <x-{entity}-form-fields :entity="$entity" :relatedData="$relatedData" />
        </x-ui.card-content>

        <div class="flex items-center justify-end gap-x-6 border-t border-slate-200 px-4 py-4 sm:px-8 bg-slate-50">
            <x-ui.button type="button" variant="ghost" onclick="window.location.href='{{ route('{entities}.index') }}'">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="default">
                {{ $entity ? 'Update' : 'Create' }}
            </x-ui.button>
        </div>
    </x-ui.card>
</form>
```

### Step 3: Use in Dedicated Pages

In `{entity}/create.blade.php` and `{entity}/edit.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center px-6 lg:px-8 py-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">
                    {{ __('Create Entity') }}
                </h2>
                <p class="mt-2 text-sm text-slate-600">Add a new entity</p>
            </div>
        </div>
    </x-slot>

    <div class="px-6 lg:px-8 py-8">
        <div class="max-w-4xl mx-auto">
            <x-{entity}-form
                :entity="$entity ?? null"
                :relatedData="$relatedData"
                :action="route('{entities}.store')"
                method="POST"
            />
        </div>
    </div>
</x-app-layout>
```

### Step 4: Use in Dashboard Slide Panels

In `dashboard.blade.php` slide panels:

```blade
<x-slide-panel name="entity-create" :show="false" maxWidth="2xl">
    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900">Create Entity</h2>
        <button type="button" @click="$dispatch('close-slide-panel', 'entity-create')" class="text-slate-400 hover:text-slate-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto px-8 py-6">
        <x-ajax-form-wrapper
            formId="entity-create-form-dashboard"
            :action="route('{entities}.store')"
            method="POST"
            slidePanel="entity-create"
            successMessage="Entity created successfully!"
            errorMessage="Please correct the errors."
        >
            <x-{entity}-form-fields :relatedData="$relatedData" idSuffix="_dash" />
        </x-ajax-form-wrapper>
    </div>
    <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
        <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','entity-create')">Cancel</x-ui.button>
        <x-ui.button type="submit" variant="default" form="entity-create-form-dashboard">Create</x-ui.button>
    </div>
</x-slide-panel>
```

## Benefits

1. **Zero Code Duplication**: Same field markup used everywhere
2. **Single Source of Truth**: Update fields once, changes reflect everywhere
3. **Consistent Validation**: Error handling works identically in all contexts
4. **Easy Maintenance**: Add/remove fields in one place
5. **Future-Proof**: Easy to add new features (like inline validation)

## Current Status

### ✅ Completed Form Components

| Entity | Fields Component | Full Form | Dedicated Pages | Dashboard Panel |
|--------|-----------------|-----------|-----------------|-----------------|
| Clients | ✅ | ✅ | ✅ | ✅ (Example) |
| Credentials | ❌ | ✅ | ✅ | 🔄 (Inline) |
| Domains | ❌ | ✅ | ✅ | 🔄 (Inline) |
| Subscriptions | ❌ | ✅ | ✅ | 🔄 (Inline) |
| Expenses | ❌ | ✅ | ✅ | N/A |
| Revenues | ❌ | ✅ | ✅ | N/A |
| Internal Accounts | ❌ | ✅ | ✅ | N/A |

✅ = Component reuse implemented
❌ = Fields component not yet created
🔄 = Still using inline form (needs refactoring)
N/A = No dashboard quick action

### Next Steps

To complete 100% component reuse:

1. Create remaining `-form-fields.blade.php` components:
   - `credential-form-fields.blade.php`
   - `domain-form-fields.blade.php`
   - `subscription-form-fields.blade.php`
   - `expense-form-fields.blade.php`
   - `revenue-form-fields.blade.php`
   - `internal-account-form-fields.blade.php`

2. Update full form components to use field components

3. Replace dashboard inline forms with AJAX wrapper + field components

4. Test all forms (both dedicated pages and slide panels)

5. Clear view cache: `rm -rf storage/framework/views/*.php`

## Testing Checklist

After refactoring, verify:

- [ ] Dedicated create pages work
- [ ] Dedicated edit pages work
- [ ] Dashboard quick actions work
- [ ] Form validation displays correctly
- [ ] AJAX submissions work
- [ ] Success toasts appear
- [ ] Page reloads after successful submission
- [ ] Alpine.js functionality intact (password toggle, etc.)
- [ ] All fields render correctly
- [ ] No console errors

## Notes

- Always use `idSuffix` parameter in field components for unique IDs
- Keep `name` attributes consistent (without suffix) for form submission
- Wrap fields in `.field-wrapper` for error message scoping
- Use `@error` directives for server-side validation errors
- AJAX wrapper handles client-side error display automatically

## Example: Complete Refactoring

See `client-form-fields.blade.php` and `client-form.blade.php` for a complete example of the pattern.
