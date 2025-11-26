# $wire is not defined - Error Fix

## 🔴 Error Messages
```
Uncaught ReferenceError: $wire is not defined (cdn.min.js:5)
Alpine Expression Error: $wire is not defined (cdn.min.js:1)
Failed to load resource: task-view.css:1 (403)
```

## 🔍 Root Cause

The error occurred because **Alpine.js was initializing BEFORE Livewire**, causing `$wire` to be undefined when Alpine tried to evaluate expressions in the template.

### Script Loading Order (BEFORE - ❌ WRONG):
```html
<head>
    <!-- Alpine loads with defer in HEAD -->
    <script defer src="alpinejs"></script>
</head>
<body>
    <!-- Content -->

    <!-- Livewire loads at BOTTOM of body -->
    @livewireScripts
</body>
```

**Problem**: Even though Alpine has `defer`, it executes as soon as DOM is ready, which is BEFORE Livewire scripts at the bottom of `<body>`.

## ✅ Solution

**Correct Script Loading Order**:
```html
<head>
    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body>
    <!-- Content -->

    <!-- 1. Livewire Scripts FIRST -->
    @livewireScripts

    <!-- 2. Alpine Collapse Plugin -->
    <script defer src="alpinejs-collapse"></script>

    <!-- 3. Alpine.js LAST -->
    <script defer src="alpinejs"></script>
</body>
```

## 📝 Changes Made

### 1. Moved Livewire Scripts Before Alpine.js
**File**: [layouts/app.blade.php](resources/views/layouts/app.blade.php#L396-L403)

**Before**:
```blade
<!-- Scripts in HEAD -->
<script defer src="alpinejs"></script>

<!-- At bottom of BODY -->
@livewireScripts
```

**After**:
```blade
<!-- At bottom of BODY -->
@livewireScripts                    <!-- 1st: Livewire loads -->
<script defer src="alpinejs-collapse"></script>  <!-- 2nd: Alpine plugin -->
<script defer src="alpinejs"></script>           <!-- 3rd: Alpine.js -->
```

### 2. Added $wire Safety Check
**File**: [task-row.blade.php](resources/views/livewire/tasks/task-row.blade.php#L71-L75)

Added defensive check to prevent errors if `$wire` is somehow still undefined:

```blade
@blur="
    if (typeof $wire !== 'undefined') {
        $wire.call('updateField', 'name', $event.target.value);
    } else {
        console.error('Livewire ($wire) not available');
    }
"
```

### 3. Fixed CSS File Permissions
**File**: `/var/www/erp/app/public/css/task-view.css`

**Before**: `-rw------- (600)` - Only owner can read/write
**After**: `-rw-r--r-- (644)` - Owner can read/write, others can read

```bash
chmod 644 /var/www/erp/app/public/css/task-view.css
```

## 🧪 How to Verify Fix

### 1. Check Browser Console
- **Before**: `Uncaught ReferenceError: $wire is not defined`
- **After**: No errors, only: `"Blur - saving: [value]"` when editing

### 2. Check Network Tab
- **Before**: `task-view.css` shows 403 Forbidden
- **After**: `task-view.css` loads successfully (200 OK)

### 3. Test Functionality
1. Click on a task name to edit
2. Type a new value
3. Press Enter or click away
4. **Expected**: No console errors, value saves correctly

## 📚 Why Script Order Matters

### Livewire + Alpine.js Integration

Livewire provides the `$wire` magic variable that Alpine.js uses to communicate with Livewire components. For this to work:

1. **Livewire MUST load first** to:
   - Register all Livewire components
   - Create the `window.Livewire` object
   - Inject `$wire` into the DOM elements

2. **Alpine.js loads second** to:
   - Find elements with `x-data`, `@click`, etc.
   - Evaluate expressions (which may use `$wire`)
   - Initialize reactive components

### The `defer` Attribute

`defer` means:
- Script downloads in parallel with HTML parsing
- Script executes AFTER HTML parsing completes
- Scripts execute in the ORDER they appear in HTML

**BUT**: Scripts in `<body>` without `defer` execute immediately when encountered, BEFORE deferred scripts in `<head>`.

**That's why** moving Livewire scripts to the bottom of `<body>` (without defer) ensures they load BEFORE Alpine.js (with defer).

## 🎯 Best Practices for Livewire + Alpine

### ✅ Correct Pattern
```html
<body>
    {{ $slot }}

    @livewireScripts              <!-- No defer - loads immediately -->
    <script defer src="alpine">   <!-- Defer - loads after Livewire -->
</body>
```

### ❌ Wrong Patterns

**Pattern 1: Alpine in HEAD**
```html
<head>
    <script defer src="alpine">  <!-- ❌ Loads too early -->
</head>
<body>
    @livewireScripts
</body>
```

**Pattern 2: Both with defer**
```html
<body>
    <script defer src="livewire">  <!-- ❌ Race condition -->
    <script defer src="alpine">    <!-- ❌ May load before Livewire -->
</body>
```

## 🔗 References

- [Livewire + Alpine.js Integration](https://livewire.laravel.com/docs/alpine)
- [Script defer Attribute](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/script#defer)
- [Livewire Magic Properties](https://livewire.laravel.com/docs/properties#accessing-properties-from-javascript)

## ✨ Summary

**The `$wire is not defined` error is now fixed by**:
1. ✅ Loading Livewire scripts BEFORE Alpine.js
2. ✅ Adding defensive `typeof $wire !== 'undefined'` checks
3. ✅ Fixing CSS file permissions (644)

**Test it**: Edit a task name, press Enter, check for NO console errors!
