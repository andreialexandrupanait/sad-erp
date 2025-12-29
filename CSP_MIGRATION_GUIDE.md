# Content Security Policy (CSP) Migration Guide

## Current Status

**Phase:** Report-Only Mode (Non-Breaking)
**Status:** Infrastructure in place, gradual migration in progress
**Target:** Full CSP enforcement without `unsafe-inline` or `unsafe-eval`

---

## What Is CSP?

Content Security Policy (CSP) is a security standard that helps prevent Cross-Site Scripting (XSS) attacks, clickjacking, and other code injection attacks by controlling which resources can be loaded and executed.

### CSP Levels

1. **No CSP** - No protection
2. **CSP with `unsafe-inline`/`unsafe-eval`** - Basic protection (current Week 1 state)
3. **Nonce-based CSP** - Strong protection (current Week 2 state)
4. **Strict CSP** - Maximum protection (future goal)

---

## Current Implementation

### SecurityHeaders Middleware

Location: `app/Http/Middleware/SecurityHeaders.php`

**Features:**
- ✅ Nonce generation for each request
- ✅ Report-Only mode (won't break existing functionality)
- ✅ Configurable enforcement via `config/app.php`
- ✅ Support for CDNs (Tailwind, jsDelivr, Quill, etc.)

**CSP Header:**
```
Content-Security-Policy-Report-Only:
    default-src 'self';
    script-src 'self' 'nonce-{random}' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net ...;
    style-src 'self' 'nonce-{random}' 'unsafe-inline' https://fonts.bunny.net ...;
    ...
```

### Helper Function

Location: `app/helpers.php`

```php
csp_nonce() // Returns nonce for current request
```

**Usage in Blade:**
```blade
<script nonce="{{ csp_nonce() }}">
    // Your inline JavaScript
</script>

<style nonce="{{ csp_nonce() }}">
    /* Your inline CSS */
</style>
```

---

## Migration Strategy

### Phase 1: Infrastructure Setup ✅ COMPLETE

- [x] Implement nonce generation in Security Headers middleware
- [x] Create `csp_nonce()` helper function
- [x] Enable Report-Only mode
- [x] Document migration process

### Phase 2: Gradual Migration (In Progress)

**Priority Order:**

1. **Critical Templates** (High Traffic)
   - `layouts/app.blade.php`
   - `layouts/guest.blade.php`
   - `dashboard.blade.php`
   - `clients/index.blade.php`

2. **Authentication & Security**
   - `profile/*` views
   - `auth/*` views

3. **Financial Module**
   - `financial/*` views

4. **Rest of Application**
   - Gradually migrate remaining 94 files

**How to Migrate a File:**

Before:
```blade
<script>
    function doSomething() {
        alert('Hello');
    }
</script>
```

After:
```blade
<script nonce="{{ csp_nonce() }}">
    function doSomething() {
        alert('Hello');
    }
</script>
```

Or better, extract to external file:
```blade
{{-- In blade file --}}
<script src="{{ asset('js/my-script.js') }}"></script>

{{-- In public/js/my-script.js --}}
function doSomething() {
    alert('Hello');
}
```

### Phase 3: Remove `unsafe-inline` (Future)

Once all inline scripts have nonces or are extracted:

1. Update `app/Http/Middleware/SecurityHeaders.php`
2. Remove `'unsafe-inline'` from script-src and style-src
3. Test thoroughly
4. Switch from Report-Only to enforcement

### Phase 4: Remove `unsafe-eval` (Future)

**Requires:**
- Audit all uses of `eval()`
- Replace with safer alternatives
- Test dynamic script loading

---

## Files Requiring Migration

### Inline Scripts (60 files)

**Layouts:**
- `layouts/app.blade.php`
- `layouts/guest.blade.php`
- `layouts/navigation.blade.php`

**High Priority:**
- `dashboard.blade.php`
- `clients/index.blade.php`
- `clients/show.blade.php`
- `financial/dashboard.blade.php`
- `offers/index.blade.php`
- `contracts/index.blade.php`

**Medium Priority:**
- Settings pages (14 files)
- Financial module (10 files)
- Credentials module (7 files)

**Low Priority:**
- Builder pages (rich editors)
- Chart vendor files (external libraries)
- Welcome page

### Inline Event Handlers (94 files)

**Pattern:** `onclick=`, `onchange=`, `onsubmit=`, etc.

**Migration Approach:**

Before:
```blade
<button onclick="deleteItem({{ $id }})">Delete</button>
```

After (Alpine.js):
```blade
<button @click="deleteItem({{ $id }})">Delete</button>
```

After (Plain JS):
```blade
<button data-action="delete" data-id="{{ $id }}">Delete</button>

<script nonce="{{ csp_nonce() }}">
document.querySelectorAll('[data-action="delete"]').forEach(btn => {
    btn.addEventListener('click', function() {
        deleteItem(this.dataset.id);
    });
});
</script>
```

---

## Configuration

### Enable CSP Enforcement

Edit `.env`:
```env
CSP_ENFORCE=false  # Report-Only mode (current)
CSP_ENFORCE=true   # Enforcement mode (future)
```

Add to `config/app.php`:
```php
'csp_enforce' => env('CSP_ENFORCE', false),
```

---

## Testing CSP

### 1. Check Browser Console

Open DevTools Console and look for CSP violations:

```
Refused to execute inline script because it violates the following
Content Security Policy directive: "script-src 'self' 'nonce-abc123'..."
```

### 2. Monitor CSP Reports (Future Enhancement)

Create reporting endpoint:

```php
// routes/web.php
Route::post('/csp-report', [CspReportController::class, 'report']);
```

Update CSP header:
```php
$csp[] = "report-uri /csp-report";
```

### 3. Manual Testing Checklist

- [ ] All pages load without errors
- [ ] Forms submit correctly
- [ ] JavaScript interactions work
- [ ] Charts render properly
- [ ] Modals and dropdowns function
- [ ] File uploads work
- [ ] Search functionality works

---

## Common Issues & Solutions

### Issue: Script Blocked

**Problem:**
```
Refused to execute inline script because it violates CSP
```

**Solution:**
Add `nonce="{{ csp_nonce() }}"` to the script tag:
```blade
<script nonce="{{ csp_nonce() }}">
    // Your code
</script>
```

### Issue: External Script Blocked

**Problem:**
```
Refused to load script from 'https://example.com/script.js'
```

**Solution:**
Add domain to `SecurityHeaders.php`:
```php
"script-src 'self' 'nonce-{$nonce}' https://example.com",
```

### Issue: Inline Style Blocked

**Problem:**
```
Refused to apply inline style
```

**Solution:**
Add nonce to style tag or extract to CSS file:
```blade
<style nonce="{{ csp_nonce() }}">
    /* Your styles */
</style>
```

### Issue: Event Handler Blocked

**Problem:**
```
Refused to execute inline event handler
```

**Solution:**
Use addEventListener or Alpine.js:
```blade
{{-- Before --}}
<button onclick="doSomething()">Click</button>

{{-- After (Alpine.js) --}}
<button @click="doSomething()">Click</button>

{{-- After (Plain JS) --}}
<button id="my-button">Click</button>
<script nonce="{{ csp_nonce() }}">
    document.getElementById('my-button').addEventListener('click', doSomething);
</script>
```

---

## Benefits of Full CSP

### Security Improvements

1. **XSS Prevention**: Blocks unauthorized script execution
2. **Data Exfiltration Protection**: Limits where data can be sent
3. **Clickjacking Defense**: Prevents iframe embedding
4. **Mixed Content Prevention**: Enforces HTTPS

### Compliance

- Required for PCI DSS Level 1
- Recommended by OWASP
- Security best practice for modern web apps

### User Trust

- Demonstrates commitment to security
- Reduces risk of data breaches
- Protects user privacy

---

## Migration Timeline

### Immediate (Week 2)
- ✅ CSP infrastructure in place
- ✅ Report-Only mode enabled
- ✅ Helper functions available
- ✅ Documentation complete

### Short Term (Month 2)
- [ ] Migrate critical templates (layouts, dashboard)
- [ ] Migrate authentication pages
- [ ] Test in staging environment

### Medium Term (Month 3)
- [ ] Migrate remaining templates
- [ ] Remove `unsafe-inline`
- [ ] Switch to enforcement mode

### Long Term (Month 4+)
- [ ] Remove `unsafe-eval`
- [ ] Implement CSP reporting
- [ ] Monitor and refine policy

---

## Resources

### Documentation
- [MDN: Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
- [Google: CSP Evaluator](https://csp-evaluator.withgoogle.com/)
- [CSP Is Dead, Long Live CSP!](https://research.google/pubs/pub45542/)

### Tools
- Browser DevTools Console (F12)
- [Report URI](https://report-uri.com/) - CSP monitoring
- [SecurityHeaders.com](https://securityheaders.com/) - Header scanner

---

## Notes

- **Current Mode**: Report-Only (won't break anything)
- **Migration is Optional**: Application works fine without it
- **Gradual Approach**: Migrate at your own pace
- **Test Thoroughly**: Always test after migrating templates

---

*Last Updated: December 28, 2025*
*Status: Phase 1 Complete (Infrastructure)*
*Next: Phase 2 (Gradual Migration)*
