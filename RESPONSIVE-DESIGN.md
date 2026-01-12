# Responsive Design - Mobile Implementation

## Overview

This document tracks the mobile responsiveness implementation for the ERP application. The goal is to make all pages work well on mobile devices **without changing the desktop layout**.

### Approach
- Use Tailwind CSS responsive prefixes (`sm:`, `md:`, `lg:`)
- Mobile-first: base styles for mobile, prefixed styles for desktop
- Pattern: `hidden md:block` for desktop-only elements, `md:hidden` for mobile-only elements

---

## Completed Work

### Shared Components
- [x] `layouts/app.blade.php` - Main layout, sidebar, header
- [x] `layouts/guest.blade.php` - Auth layout with responsive padding
- [x] `components/dashboard/quick-actions.blade.php` - Mobile dropdown
- [x] `components/ui/card.blade.php` - Card padding
- [x] `components/ui/card-header.blade.php` - Header padding, text color fix
- [x] `components/ui/card-content.blade.php` - Content padding
- [x] `components/ui/table.blade.php` - Table cell padding
- [x] `components/ui/table-cell.blade.php` - Cell padding

### Dashboard
- [x] `dashboard.blade.php` - Responsive stats grid, widget sizes

### Clients Module
- [x] `clients/index.blade.php` - Mobile cards, hidden columns, dropdown positioning
- [x] `clients/create.blade.php` - Already responsive
- [x] `clients/edit.blade.php` - Already responsive
- [x] `clients/show.blade.php` - Responsive padding and tabs
- [x] `clients/import.blade.php` - Already responsive

### Credentials Module
- [x] `credentials/index.blade.php` - Responsive padding
- [x] `credentials/partials/credentials-list.blade.php` - Mobile cards, header dropdown
- [x] `credentials/create.blade.php` - Already responsive
- [x] `credentials/edit.blade.php` - Already responsive
- [x] `credentials/show.blade.php` - Responsive padding

### Domains Module
- [x] `domains/index.blade.php` - Mobile cards, responsive stats grid
- [x] `domains/create.blade.php` - Already responsive
- [x] `domains/edit.blade.php` - Already responsive
- [x] `domains/show.blade.php` - Responsive padding

### Subscriptions Module
- [x] `subscriptions/index.blade.php` - Mobile cards, widgets, full-width filter
- [x] `subscriptions/create.blade.php` - Form button stacking
- [x] `subscriptions/edit.blade.php` - Form button stacking
- [x] `subscriptions/show.blade.php` - Responsive padding

### Internal Accounts Module
- [x] `internal-accounts/index.blade.php` - Mobile cards, widgets, full-width filter
- [x] `internal-accounts/create.blade.php` - Form button stacking
- [x] `internal-accounts/edit.blade.php` - Form button stacking
- [x] `internal-accounts/show.blade.php` - Responsive padding

### Notes Module
- [x] `notes/index.blade.php` - Full-width buttons, slide-over padding

### Document Templates
- [x] `document-templates/index.blade.php` - Mobile cards, hidden table
- [x] `document-templates/create.blade.php` - Scrollable breadcrumbs
- [x] `document-templates/edit.blade.php` - Scrollable breadcrumbs

### Profile Module
- [x] `profile/edit.blade.php` - Card header text color fix
- [x] `profile/sessions.blade.php` - Session rows stack on mobile
- [x] `profile/activities.blade.php` - Mobile cards for activity log
- [x] `profile/two-factor.blade.php` - Already responsive
- [x] `profile/two-factor-enable.blade.php` - Button stacking
- [x] `profile/two-factor-recovery-codes.blade.php` - Button stacking

### Auth Pages
- [x] `auth/login.blade.php` - Already responsive
- [x] `auth/register.blade.php` - Footer stacking, full-width button
- [x] `auth/forgot-password.blade.php` - Already responsive
- [x] `auth/reset-password.blade.php` - Already responsive
- [x] `auth/verify-email.blade.php` - Buttons stack on mobile
- [x] `auth/confirm-password.blade.php` - Full-width confirm button
- [x] `auth/two-factor-challenge.blade.php` - Full-width verify button

### Settings Module
- [x] `settings/partials/sidebar.blade.php` - Mobile dropdown, desktop sidebar
- [x] `settings/index.blade.php` - Flex layout for mobile
- [x] `settings/application.blade.php` - Form button stacking
- [x] `settings/nomenclature.blade.php` - Flex layout
- [x] `settings/users/index.blade.php` - Mobile cards, stats grid
- [x] `settings/users/create.blade.php` - Button stacking
- [x] `settings/users/edit.blade.php` - Button stacking
- [x] `settings/users/show.blade.php` - Flex layout
- [x] `settings/services/index.blade.php` - Flex layout
- [x] `settings/services/create.blade.php` - Flex layout
- [x] `settings/services/edit.blade.php` - Flex layout
- [x] `settings/integrations/*` - All pages with flex layout
- [x] `settings/business/*` - All pages with flex layout
- [x] `settings/backup/index.blade.php` - Flex layout
- [x] `settings/smartbill/*` - All pages with flex layout
- [x] `settings/notifications.blade.php` - Flex layout
- [x] `settings/yearly-objectives.blade.php` - Flex layout
- [x] `settings/contract-templates/*` - Flex layout

### Import/Export
- [x] `import-export/index.blade.php` - Already responsive (grid)
- [x] `import-export/import.blade.php` - Template card stacking, button stacking
- [x] Added to Settings sidebar

### Contracts Module
- [x] `contracts/index.blade.php` - Mobile cards, responsive stats, summary box
- [x] `contracts/create.blade.php` - Form button stacking, responsive padding
- [x] `contracts/show.blade.php` - Mobile dropdown menu, responsive header actions
- [x] `contracts/add-annex.blade.php` - Button stacking, responsive card header
- [x] `contracts/annex-edit.blade.php` - Responsive grid, button stacking, responsive header
- [x] `contracts/annex-show.blade.php` - Mobile header buttons, responsive padding
- [x] `contracts/pdf.blade.php` - Print only, skip
- [x] `contracts/annex-pdf.blade.php` - Print only, skip

---

## Remaining Work

### Contracts Module (Partial)
- [ ] `contracts/edit.blade.php` - Needs permission fix for responsive header/info bar

### Offers Module
- [ ] `offers/index.blade.php` - Add mobile cards, hide table columns
- [ ] `offers/create.blade.php` - Form responsiveness
- [ ] `offers/edit.blade.php` - Form responsiveness
- [ ] `offers/show.blade.php` - Responsive details layout
- [ ] `offers/builder.blade.php` - Complex, may need special handling
- [ ] `offers/simple-builder.blade.php` - Complex, may need special handling
- [ ] `offers/public.blade.php` - Public view responsiveness
- [ ] `offers/pdf.blade.php` - Print only, skip

### Financial Module
- [ ] `financial/dashboard.blade.php` - Stats grid, charts responsiveness
- [ ] `financial/cashflow.blade.php` - Table/chart responsiveness
- [ ] `financial/yearly-report.blade.php` - Table responsiveness
- [ ] `financial/revenues/index.blade.php` - Mobile cards, filters
- [ ] `financial/revenues/create.blade.php` - Form responsiveness
- [ ] `financial/revenues/edit.blade.php` - Form responsiveness
- [ ] `financial/revenues/show.blade.php` - Details responsiveness
- [ ] `financial/revenues/import.blade.php` - Form responsiveness
- [ ] `financial/expenses/index.blade.php` - Mobile cards, filters
- [ ] `financial/expenses/create.blade.php` - Form responsiveness
- [ ] `financial/expenses/edit.blade.php` - Form responsiveness
- [ ] `financial/expenses/show.blade.php` - Details responsiveness
- [ ] `financial/expenses/import.blade.php` - Form responsiveness
- [ ] `financial/files/index.blade.php` - Grid responsiveness
- [ ] `financial/files/year.blade.php` - Numeric alignment (done), grid
- [ ] `financial/files/month.blade.php` - Grid responsiveness
- [ ] `financial/files/category.blade.php` - Grid responsiveness
- [ ] `financial/files/import-transactions.blade.php` - Form responsiveness

---

## Common Patterns Used

### Mobile Cards Pattern
Convert tables to card layout on mobile:
```blade
{{-- Mobile Cards --}}
<div class="md:hidden divide-y divide-slate-200">
    @foreach($items as $item)
        <div class="p-4">
            <!-- Card content -->
        </div>
    @endforeach
</div>

{{-- Desktop Table --}}
<div class="hidden md:block overflow-x-auto">
    <table>...</table>
</div>
```

### Button Stacking Pattern
Stack buttons on mobile, inline on desktop:
```blade
<div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
    <x-ui.button variant="outline" class="w-full sm:w-auto">Cancel</x-ui.button>
    <x-ui.button class="w-full sm:w-auto">Save</x-ui.button>
</div>
```

### Responsive Grid Pattern
```blade
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Grid items -->
</div>
```

### Responsive Padding Pattern
```blade
<div class="p-4 md:p-6">
    <!-- Content -->
</div>
```

### Settings Sidebar Pattern
Mobile dropdown + Desktop sidebar:
```blade
{{-- Mobile: Dropdown --}}
<div class="lg:hidden">
    <select onchange="window.location.href=this.value">...</select>
</div>

{{-- Desktop: Sidebar --}}
<aside class="hidden lg:block w-64">...</aside>
```

### Flex Layout for Settings Pages
```blade
<div class="flex flex-col lg:flex-row min-h-screen">
    @include('settings.partials.sidebar')
    <div class="flex-1">...</div>
</div>
```

---

## Cache Clear Command
Run after making changes:
```bash
docker exec erp_app php -r "opcache_reset();" && \
docker exec erp_app rm -rf /var/www/html/storage/framework/views/* && \
docker restart erp_app
```

---

## Notes

1. **PDF pages** (contracts/pdf.blade.php, offers/pdf.blade.php, etc.) are print-only and don't need responsive styling.

2. **Builder pages** (offers/builder.blade.php, offers/simple-builder.blade.php) are complex drag-and-drop interfaces that may need special handling or may not be suitable for mobile use.

3. **Financial charts** may need special consideration for mobile - consider hiding complex charts on mobile or using simplified versions.

4. **Always test on actual mobile devices** or using browser dev tools mobile emulation.
