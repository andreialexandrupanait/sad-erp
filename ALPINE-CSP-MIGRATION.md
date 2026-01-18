# Alpine.js CSP Migration Plan

## Current State

### CSP Configuration
- **Location**: `app/Http/Middleware/SecurityHeaders.php`
- **Current script-src**: `'self' 'unsafe-inline' 'unsafe-eval' + CDN domains`
- **CSP Enforcement**: Now enabled by default (`config/app.php`)

### Why 'unsafe-eval' is Required
Alpine.js (bundled with Livewire v3) uses `new Function()` to evaluate expressions like:
- `x-data="{ open: false }"`
- `@click="open = !open"`
- `x-show="isVisible"`
- `x-bind:class="{ 'active': selected }"`

These are JavaScript expressions that Alpine evaluates at runtime using `eval()`-like mechanisms.

---

## Migration Scope

### Analysis Results
- **Total x-data occurrences**: 126 across 89 files
- **Files affected**: Blade templates in `resources/views/`

### Common Patterns Found

| Pattern | Count | Complexity |
|---------|-------|------------|
| `{ open: false }` | 9 | Low |
| `{ show: true }` | 8 | Low |
| Function calls (e.g., `quickAddCredential()`) | 10+ | Already compatible |
| Complex inline objects | 20+ | High |
| Inline with methods | 30+ | High |

---

## Migration Options

### Option 1: Document & Defer (Recommended for Now)
**Risk**: None
**Effort**: Minimal

Keep current CSP with 'unsafe-eval'. Document as technical debt.

**Pros**:
- No risk of breaking the application
- CSP still provides protection against other attacks
- 'unsafe-eval' is safer than no CSP at all

**Cons**:
- Doesn't achieve strictest CSP
- Some security scanners will flag it

**Action Items**:
- [x] Enable CSP enforcement (done)
- [ ] Add comment in SecurityHeaders.php explaining Alpine dependency
- [ ] Create ticket for future migration

---

### Option 2: Partial Migration
**Risk**: Medium
**Effort**: 2-3 days

Convert the most common patterns to Alpine.data() registrations.

**Approach**:
1. Create reusable Alpine data components for common patterns
2. Register them in `resources/js/app.js`
3. Update templates to use registered components
4. Keep 'unsafe-eval' for remaining complex cases

**Components to Create**:
```javascript
// resources/js/alpine-components.js

// Simple toggle state
Alpine.data('toggle', (initial = false) => ({
    open: initial,
    toggle() { this.open = !this.open }
}));

// Show/hide with animation
Alpine.data('disclosure', (initial = true) => ({
    show: initial,
    toggle() { this.show = !this.show }
}));

// Mobile menu
Alpine.data('mobileMenu', () => ({
    mobileMenuOpen: false,
    toggle() { this.mobileMenuOpen = !this.mobileMenuOpen }
}));
```

**Template Changes**:
```blade
{{-- Before --}}
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
</div>

{{-- After --}}
<div x-data="toggle">
    <button @click="toggle">Toggle</button>
</div>
```

---

### Option 3: Full Migration to Alpine CSP Build
**Risk**: High
**Effort**: 1-2 weeks

Complete migration to `@alpinejs/csp` build.

**Requirements**:
1. Install `@alpinejs/csp` package (done: already installed)
2. Configure Livewire to not inject bundled Alpine
3. Convert ALL 126 x-data occurrences to Alpine.data()
4. Convert ALL inline event handlers to method calls
5. Extensive regression testing

**Major Challenges**:

1. **Livewire Integration**
   - Livewire v3 bundles Alpine internally
   - Must disable Livewire's Alpine and load CSP version first
   - Risk of Livewire internals breaking

2. **Inline Expressions**
   - Every `@click="something = value"` must become `@click="methodName"`
   - Every `x-show="condition"` with complex logic needs a method

3. **Dynamic Data**
   - Components with Blade-injected data need refactoring:
   ```blade
   {{-- This won't work with CSP Alpine --}}
   x-data="{ items: @js($items) }"

   {{-- Must become --}}
   x-data="myComponent" x-init="items = {{ Js::from($items) }}"
   ```

**Files Requiring Most Work**:
- `layouts/app.blade.php` - Large inline components
- `credentials/index.blade.php` - 5 occurrences
- `dashboard.blade.php` - 5 occurrences
- `contracts/show.blade.php` - 5 occurrences

---

## Livewire Configuration for CSP Alpine

If proceeding with migration, update `config/livewire.php`:

```php
// Disable Livewire's bundled Alpine
'inject_assets' => false,
```

Then in layout, manually control script loading order:

```blade
{{-- Load Alpine CSP BEFORE Livewire --}}
@vite(['resources/js/app.js'])

{{-- Then Livewire (will detect existing Alpine) --}}
@livewireScripts
```

Update `resources/js/app.js`:

```javascript
// Import CSP-safe Alpine
import Alpine from '@alpinejs/csp';
import collapse from '@alpinejs/collapse';

// Register all data components BEFORE starting Alpine
import './alpine-components.js';

// Register plugins
Alpine.plugin(collapse);

// Make Alpine available globally for Livewire
window.Alpine = Alpine;

// Start Alpine
Alpine.start();
```

---

## Recommendation

**For immediate security improvement**: Stay with Option 1.

The current CSP configuration with 'unsafe-eval' still provides significant security benefits:
- Blocks inline scripts from injected content (XSS via innerHTML)
- Enforces script sources to known domains
- Prevents other CSP-blocked attack vectors

**For future improvement**: Plan for Option 2 (Partial Migration) when time permits.

Create reusable Alpine.data() components for new features, gradually reducing reliance on inline expressions.

---

## References

- [Alpine.js CSP Build](https://alpinejs.dev/advanced/csp)
- [Livewire v3 Documentation](https://livewire.laravel.com/docs)
- [CSP 'unsafe-eval' Implications](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src)

---

## Status

- [x] CSP enforcement enabled by default
- [x] 'unsafe-eval' documented as Alpine.js requirement
- [x] @alpinejs/csp package installed (available when ready)
- [ ] Partial migration (future)
- [ ] Full migration (future)

*Last updated: 2026-01-18*
