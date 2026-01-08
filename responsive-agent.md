# Responsive Mobile Design Agent

## Overview

This agent guides the process of making the SimpleAD ERP application fully responsive and mobile-friendly. The app is built with Laravel 12, Tailwind CSS 3, and Alpine.js.

---

## Application Analysis Summary

### Tech Stack
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Alpine.js 3, Livewire 3
- **Styling:** Tailwind CSS 3 with @tailwindcss/forms
- **Build:** Vite 7
- **Editors:** Quill.js, TinyMCE, Trix

### Current State
- **311 Blade templates** across 18+ modules
- **151 reusable components** in `resources/views/components/`
- **Partial responsive implementation** exists (sidebar toggle, some breakpoints)
- **Mobile-first approach** already in place but inconsistent

---

## Priority Modules (Order of Implementation)

### Phase 1: Core Layout & Navigation (HIGH PRIORITY)
1. `layouts/app.blade.php` - Main app layout
2. `layouts/navigation.blade.php` - Sidebar navigation
3. `layouts/guest.blade.php` - Guest/auth layout
4. `components/sidebar/` - Sidebar components

### Phase 2: Dashboard & Widgets (HIGH PRIORITY)
1. `dashboard.blade.php` - Main dashboard
2. `components/dashboard/` - Dashboard widgets
3. `components/widgets/` - Activity, metrics, financial widgets

### Phase 3: Data Tables & Lists (MEDIUM-HIGH PRIORITY)
1. `clients/index.blade.php` - Client listing
2. `contracts/index.blade.php` - Contracts listing
3. `offers/index.blade.php` - Offers listing
4. `financial/` - Financial views (revenues, expenses)
5. `subscriptions/index.blade.php` - Subscriptions listing
6. `credentials/` - Credentials views (flat, grouped, by-site)

### Phase 4: Forms & CRUD Views (MEDIUM PRIORITY)
1. `*/create.blade.php` - All create forms
2. `*/edit.blade.php` - All edit forms
3. `*/show.blade.php` - All detail views

### Phase 5: Complex Components (MEDIUM PRIORITY)
1. `components/builder/` - Offer/contract builders
2. `components/offer/` - Offer templates
3. `components/bank-import/` - Bank import functionality

### Phase 6: UI Components (LOW-MEDIUM PRIORITY)
1. `components/ui/` - Base UI components
2. Modals, dropdowns, selects
3. Rich text editors

### Phase 7: Settings & Admin (LOW PRIORITY)
1. `settings/` - All settings views
2. `profile/` - Profile views
3. `auth/` - Authentication views

---

## Responsive Design Guidelines

### Tailwind Breakpoints
```
sm: 640px   - Small devices (landscape phones)
md: 768px   - Medium devices (tablets)
lg: 1024px  - Large devices (desktops)
xl: 1280px  - Extra large devices
2xl: 1536px - 2X large devices
```

### Mobile-First Approach
Always write mobile styles first, then add breakpoint modifiers:
```html
<!-- Mobile first: stack vertically, then row on md+ -->
<div class="flex flex-col md:flex-row gap-4">
```

### Common Patterns to Apply

#### 1. Responsive Grid Layouts
```html
<!-- From -->
<div class="grid grid-cols-3 gap-4">

<!-- To -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
```

#### 2. Responsive Tables (Card Pattern)
```html
<!-- Desktop: Table, Mobile: Cards -->
<div class="hidden md:block">
  <table>...</table>
</div>
<div class="md:hidden space-y-4">
  <!-- Mobile card view -->
</div>
```

#### 3. Responsive Typography
```html
<h1 class="text-xl sm:text-2xl md:text-3xl font-bold">
```

#### 4. Responsive Padding/Margins
```html
<div class="p-4 md:p-6 lg:p-8">
```

#### 5. Responsive Buttons
```html
<!-- Full width on mobile, auto on desktop -->
<button class="w-full sm:w-auto">
```

#### 6. Hidden/Visible Elements
```html
<!-- Hide on mobile, show on desktop -->
<span class="hidden md:inline">Full Text Here</span>
<span class="md:hidden">Short</span>
```

#### 7. Responsive Flexbox
```html
<!-- Stack on mobile, row on desktop -->
<div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4">
```

---

## Component-Specific Guidelines

### Navigation/Sidebar
- [ ] Hamburger menu visible on mobile (< md)
- [ ] Sidebar slides in from left on mobile
- [ ] Overlay backdrop when sidebar open
- [ ] Touch swipe to close
- [ ] Close on navigation item click (mobile)

### Data Tables
- [ ] Horizontal scroll wrapper on mobile
- [ ] OR card-based layout for mobile
- [ ] Sticky first column option
- [ ] Collapsed columns on mobile (show essential only)
- [ ] Touch-friendly row actions

### Forms
- [ ] Full-width inputs on mobile
- [ ] Stacked label/input on mobile
- [ ] Larger touch targets (min 44px)
- [ ] Visible submit buttons (not hidden by keyboard)

### Modals
- [ ] Full-screen on mobile
- [ ] Slide-up animation on mobile
- [ ] Easy close button access

### Cards/Widgets
- [ ] Single column on mobile
- [ ] Reduced padding on mobile
- [ ] Collapsible sections

### Rich Text Editors
- [ ] Simplified toolbar on mobile
- [ ] Touch-friendly controls
- [ ] Adequate height for mobile editing

---

## Files to Modify (Detailed List)

### Layouts (3 files)
```
resources/views/layouts/
├── app.blade.php
├── navigation.blade.php
└── guest.blade.php
```

### Dashboard (1 file + widgets)
```
resources/views/dashboard.blade.php
resources/views/components/dashboard/
resources/views/components/widgets/
```

### Clients Module (4 files)
```
resources/views/clients/
├── index.blade.php
├── show.blade.php
├── create.blade.php
└── edit.blade.php
```

### Contracts Module (4 files)
```
resources/views/contracts/
├── index.blade.php
├── show.blade.php
├── create.blade.php
└── edit.blade.php
```

### Offers Module (4 files)
```
resources/views/offers/
├── index.blade.php
├── show.blade.php
├── create.blade.php
└── edit.blade.php
```

### Financial Module (Multiple files)
```
resources/views/financial/
├── dashboard.blade.php
├── revenues/
├── expenses/
└── files/
```

### Core UI Components (Priority)
```
resources/views/components/ui/
├── button.blade.php
├── card.blade.php
├── input.blade.php
├── select.blade.php
├── modal.blade.php
├── dropdown.blade.php
├── table.blade.php
└── ...
```

---

## Testing Checklist

### Viewport Testing
- [ ] 320px (iPhone SE)
- [ ] 375px (iPhone 12/13)
- [ ] 390px (iPhone 14 Pro)
- [ ] 414px (iPhone Plus sizes)
- [ ] 768px (iPad)
- [ ] 1024px (iPad Pro / Desktop)
- [ ] 1280px+ (Large desktop)

### Touch Interactions
- [ ] All buttons/links min 44x44px touch target
- [ ] No hover-only interactions
- [ ] Swipe gestures work correctly
- [ ] Scroll behavior smooth

### Content
- [ ] Text readable without zooming
- [ ] Images scale properly
- [ ] Tables scrollable or transformed
- [ ] Forms usable with mobile keyboard

### Performance
- [ ] Fast load on 3G
- [ ] No layout shift
- [ ] Images optimized

---

## Implementation Commands

### Start with a specific module:
```bash
# Example: Make clients module responsive
claude "Make the clients module responsive following responsive-agent.md guidelines"
```

### Full responsive sweep:
```bash
# Phase by phase
claude "Implement Phase 1 from responsive-agent.md - Core Layout & Navigation"
claude "Implement Phase 2 from responsive-agent.md - Dashboard & Widgets"
# ... continue with each phase
```

---

## Progress Tracking

### Phase 1: Core Layout & Navigation
- [ ] `layouts/app.blade.php`
- [ ] `layouts/navigation.blade.php`
- [ ] `layouts/guest.blade.php`
- [ ] Sidebar components

### Phase 2: Dashboard & Widgets
- [ ] `dashboard.blade.php`
- [ ] Dashboard widgets
- [ ] Activity widget
- [ ] Metrics widget
- [ ] Financial widget

### Phase 3: Data Tables & Lists
- [ ] Clients index
- [ ] Contracts index
- [ ] Offers index
- [ ] Financial views
- [ ] Subscriptions index
- [ ] Credentials views

### Phase 4: Forms & CRUD Views
- [ ] Create forms
- [ ] Edit forms
- [ ] Detail/show views

### Phase 5: Complex Components
- [ ] Offer builder
- [ ] Contract builder
- [ ] Bank import

### Phase 6: UI Components
- [ ] Button component
- [ ] Card component
- [ ] Input component
- [ ] Select component
- [ ] Modal component
- [ ] Dropdown component
- [ ] Table component
- [ ] Alert component

### Phase 7: Settings & Admin
- [ ] Settings views
- [ ] Profile views
- [ ] Auth views

---

## CSS Utilities to Add (if needed)

Add to `resources/css/app.css`:

```css
@layer utilities {
  /* Safe area for notched devices */
  .safe-bottom {
    padding-bottom: env(safe-area-inset-bottom);
  }

  .safe-top {
    padding-top: env(safe-area-inset-top);
  }

  /* Touch-friendly sizing */
  .touch-target {
    min-height: 44px;
    min-width: 44px;
  }

  /* Prevent text selection on interactive elements */
  .no-select {
    -webkit-user-select: none;
    user-select: none;
  }

  /* Smooth momentum scrolling */
  .scroll-smooth {
    -webkit-overflow-scrolling: touch;
  }
}

/* Mobile-specific overrides */
@media (max-width: 767px) {
  /* Larger form elements on mobile */
  input, select, textarea {
    font-size: 16px; /* Prevents iOS zoom */
  }
}
```

---

## Notes

- Always test on real devices when possible
- Use Chrome DevTools device emulation for quick testing
- Consider landscape orientation on phones
- Test with slow network throttling
- Ensure accessibility is maintained (focus states, screen readers)
- Keep bundle size in mind when adding responsive features

---

## Agent Execution

To start the responsive adaptation process, run:

```bash
claude "Start implementing responsive design changes following responsive-agent.md, beginning with Phase 1"
```

The agent will:
1. Read the relevant files
2. Apply responsive patterns
3. Test the changes conceptually
4. Move to the next component
5. Track progress in this file
