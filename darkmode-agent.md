# Dark Mode Agent - SAD ERP

This document provides comprehensive instructions for implementing dark mode in the SAD ERP application.

## Project Overview

- **Framework:** Laravel 12 + Vite
- **Frontend Stack:** Blade Templates, Alpine.js 3.x, Livewire 3, Tailwind CSS 3.1.0
- **Current State:** Light mode only, but theme preference UI already exists

## Existing Infrastructure (Ready to Use)

### User Settings
- `User::getSetting('theme', 'light')` - Get user theme preference
- `User::setSetting('theme', 'dark')` - Save user theme preference
- User `settings` JSON column already exists

### Theme UI Already Present
- `resources/views/profile/partials/user-preferences.blade.php` (lines 57-67)
- `resources/views/settings/application.blade.php` (lines 189-209)
- Options: `light`, `dark`, `auto`

### Some Dark Classes Exist
- `resources/views/layouts/navigation.blade.php` has some `dark:` classes
- Can be used as reference pattern

---

## Implementation Steps

### Step 1: Enable Tailwind Dark Mode

**File:** `tailwind.config.js`

```javascript
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    darkMode: 'class', // ADD THIS LINE
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [forms],
};
```

### Step 2: Add Theme Switcher JavaScript

**File:** `resources/js/theme-switcher.js` (create new file)

```javascript
// Theme Switcher for SAD ERP
window.themeSwitcher = function() {
    return {
        theme: localStorage.getItem('theme') || 'light',

        init() {
            this.applyTheme();

            // Listen for system preference changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.theme === 'auto') {
                    this.applyTheme();
                }
            });
        },

        setTheme(newTheme) {
            this.theme = newTheme;
            localStorage.setItem('theme', newTheme);
            this.applyTheme();

            // Sync to server if user is authenticated
            if (typeof window.syncThemeToServer === 'function') {
                window.syncThemeToServer(newTheme);
            }
        },

        applyTheme() {
            const html = document.documentElement;

            if (this.theme === 'dark' ||
                (this.theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
            }
        },

        get isDark() {
            return document.documentElement.classList.contains('dark');
        }
    };
};

// Apply theme immediately to prevent flash
(function() {
    const theme = localStorage.getItem('theme') || 'light';
    if (theme === 'dark' ||
        (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    }
})();
```

**File:** `resources/js/app.js` - Add import:

```javascript
import './theme-switcher';
```

### Step 3: Update Main Layout

**File:** `resources/views/layouts/app.blade.php`

Add to `<html>` tag:
```html
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="themeSwitcher()"
      x-init="init()"
      :class="{ 'dark': isDark }">
```

Update `<body>` tag:
```html
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-900 dark:text-slate-100">
```

### Step 4: Add Inline Script for Flash Prevention

Add to `<head>` section of `resources/views/layouts/app.blade.php`:

```html
<script>
    // Prevent flash of wrong theme
    (function() {
        const theme = localStorage.getItem('theme') || 'light';
        if (theme === 'dark' ||
            (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
```

---

## Component Updates Required

### High Priority Components (Core Layout)

#### 1. Main Layout (`resources/views/layouts/app.blade.php`)

| Element | Current Classes | Add Dark Classes |
|---------|----------------|------------------|
| Body | `bg-slate-50` | `dark:bg-slate-900 dark:text-slate-100` |
| Header | `bg-white border-b border-slate-200` | `dark:bg-slate-800 dark:border-slate-700` |
| Main content | `bg-slate-50` | `dark:bg-slate-900` |
| Breadcrumbs | `text-slate-500` | `dark:text-slate-400` |
| Page title | `text-slate-900` | `dark:text-white` |

#### 2. Sidebar (`resources/views/components/sidebar.blade.php`)

The sidebar is already dark (`bg-slate-900`). May need minor adjustments:
- Ensure text colors work in both modes
- Border colors: `dark:border-slate-700`

#### 3. Navigation (`resources/views/layouts/navigation.blade.php`)

Already has some `dark:` classes - verify and complete coverage.

### Medium Priority (UI Components)

#### Card Component (`resources/views/components/ui/card.blade.php`)

```blade
<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700']) }}>
    @if(isset($header))
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            {{ $header }}
        </div>
    @endif
    <div class="p-4">
        {{ $slot }}
    </div>
</div>
```

#### Input Component (`resources/views/components/ui/input.blade.php`)

Add dark mode classes:
```
dark:bg-slate-700 dark:border-slate-600 dark:text-white dark:placeholder-slate-400
```

#### Button Components

Primary buttons:
```
bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600
```

Secondary buttons:
```
bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200
```

### Low Priority (Feature Pages)

Update all page templates in:
- `resources/views/clients/`
- `resources/views/products/`
- `resources/views/invoices/`
- `resources/views/reports/`
- `resources/views/settings/`
- etc.

---

## CSS Updates

**File:** `resources/css/app.css`

### Update Custom Component Classes

```css
/* Card components */
.card {
    @apply bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700;
}

.card-header {
    @apply px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50;
}

.card-body {
    @apply p-4;
}

/* Summary cards */
.summary-card {
    @apply bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700;
}

/* Filter bar */
.filter-bar {
    @apply bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700;
}

/* Combobox */
.category-combobox {
    @apply bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600;
}

.combobox-dropdown {
    @apply bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 shadow-lg;
}

.combobox-option {
    @apply hover:bg-slate-100 dark:hover:bg-slate-600;
}

/* Scrollbar (webkit) */
::-webkit-scrollbar-track {
    @apply bg-slate-100 dark:bg-slate-800;
}

::-webkit-scrollbar-thumb {
    @apply bg-slate-300 dark:bg-slate-600;
}

::-webkit-scrollbar-thumb:hover {
    @apply bg-slate-400 dark:bg-slate-500;
}

/* Amount styling */
.amount-debit {
    @apply text-red-600 dark:text-red-400;
}

.amount-credit {
    @apply text-emerald-600 dark:text-emerald-400;
}
```

---

## Color Palette Reference

### Light Mode → Dark Mode Mappings

| Light Mode | Dark Mode | Usage |
|------------|-----------|-------|
| `bg-white` | `dark:bg-slate-800` | Cards, modals, dropdowns |
| `bg-slate-50` | `dark:bg-slate-900` | Page backgrounds |
| `bg-slate-100` | `dark:bg-slate-800/50` | Subtle backgrounds |
| `border-slate-200` | `dark:border-slate-700` | Borders |
| `border-slate-300` | `dark:border-slate-600` | Form borders |
| `text-slate-900` | `dark:text-white` | Headings |
| `text-slate-700` | `dark:text-slate-200` | Body text |
| `text-slate-600` | `dark:text-slate-300` | Secondary text |
| `text-slate-500` | `dark:text-slate-400` | Muted text |
| `placeholder-slate-400` | `dark:placeholder-slate-500` | Placeholders |

### Interactive States

| Light Mode | Dark Mode | Usage |
|------------|-----------|-------|
| `hover:bg-slate-50` | `dark:hover:bg-slate-700` | Row hovers |
| `hover:bg-slate-100` | `dark:hover:bg-slate-700` | Button hovers |
| `focus:ring-blue-500` | Same | Focus rings (keep same) |
| `focus:border-blue-500` | Same | Focus borders (keep same) |

### Status Colors

| Status | Light Mode | Dark Mode |
|--------|------------|-----------|
| Success | `text-emerald-600 bg-emerald-50` | `dark:text-emerald-400 dark:bg-emerald-900/20` |
| Error | `text-red-600 bg-red-50` | `dark:text-red-400 dark:bg-red-900/20` |
| Warning | `text-amber-600 bg-amber-50` | `dark:text-amber-400 dark:bg-amber-900/20` |
| Info | `text-blue-600 bg-blue-50` | `dark:text-blue-400 dark:bg-blue-900/20` |

---

## Testing Checklist

### Visual Testing
- [ ] Toggle between light/dark/auto modes
- [ ] Test system preference detection (auto mode)
- [ ] Verify no flash of wrong theme on page load
- [ ] Check all pages for readable contrast
- [ ] Test forms (inputs, selects, checkboxes, radios)
- [ ] Test modals and dropdowns
- [ ] Test tables and data grids
- [ ] Test charts and graphs
- [ ] Test notification toasts
- [ ] Test loading states

### Functional Testing
- [ ] Theme persists on page refresh
- [ ] Theme syncs across browser tabs
- [ ] Theme saves to user settings
- [ ] Auto mode responds to system changes

### Accessibility Testing
- [ ] Minimum contrast ratio 4.5:1 for text
- [ ] Focus indicators visible in both modes
- [ ] No color-only information conveyance

---

## Files to Update (Complete List)

### Configuration Files
- [ ] `tailwind.config.js` - Add `darkMode: 'class'`

### JavaScript Files
- [ ] `resources/js/theme-switcher.js` - Create new
- [ ] `resources/js/app.js` - Import theme-switcher

### Layout Files
- [ ] `resources/views/layouts/app.blade.php`
- [ ] `resources/views/layouts/navigation.blade.php`
- [ ] `resources/views/layouts/guest.blade.php` (if exists)

### Component Files (in `resources/views/components/`)
- [ ] `sidebar.blade.php`
- [ ] `ui/card.blade.php`
- [ ] `ui/input.blade.php`
- [ ] `ui/button.blade.php`
- [ ] `ui/select.blade.php`
- [ ] `ui/textarea.blade.php`
- [ ] `ui/checkbox.blade.php`
- [ ] `ui/modal.blade.php`
- [ ] `ui/dropdown.blade.php`
- [ ] `ui/table.blade.php`
- [ ] `ui/badge.blade.php`
- [ ] `ui/alert.blade.php`
- [ ] All other UI components

### CSS Files
- [ ] `resources/css/app.css` - Update custom classes

### Page Templates (311 blade files)
- Prioritize most-used pages first
- Update iteratively, grouping similar pages

---

## Implementation Order

1. **Phase 1: Foundation** (Critical)
   - Enable Tailwind dark mode
   - Add theme switcher JS
   - Update main layout and body
   - Add flash prevention script

2. **Phase 2: Core Components** (High Priority)
   - Update sidebar
   - Update navigation
   - Update card component
   - Update form inputs

3. **Phase 3: UI Library** (Medium Priority)
   - Update all components in `components/ui/`
   - Update CSS custom classes

4. **Phase 4: Page Templates** (Lower Priority)
   - Update page templates in batches
   - Start with dashboard
   - Then clients, products, invoices
   - Then reports, settings

5. **Phase 5: Polish** (Final)
   - Test all pages
   - Fix edge cases
   - Ensure consistent contrast
   - Performance optimization

---

## Notes

- The sidebar is already dark-themed, so it may need inverse logic or stay dark in both modes
- Primary color (`--primary-color`) is dynamic - ensure it works in dark mode
- Charts/graphs may need separate dark theme configuration
- PDF exports should remain light mode
- Print styles should remain light mode

## Build Command

After making changes, rebuild assets:
```bash
npm run build
# or for development:
npm run dev
```
