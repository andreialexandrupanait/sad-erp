# Dependency Audit Report

**Project:** sad-erp (Laravel 12 + Vue/Alpine.js ERP Application)
**Date:** 2026-01-21
**Total Dependencies:** 172 PHP packages, 242 npm packages

---

## Executive Summary

| Category | Status |
|----------|--------|
| Security Vulnerabilities (npm) | **0 found** |
| Security Vulnerabilities (composer) | **0 found** |
| Outdated Packages (npm) | 23 packages |
| Outdated Packages (composer) | 4 direct packages |
| Potential Bloat | 3 areas identified |

---

## 1. Security Vulnerabilities

### NPM Packages
**Status: No vulnerabilities found**

### Composer Packages
**Status: No security vulnerability advisories found**

---

## 2. Outdated Packages

### NPM - Minor Updates (Recommended - Low Risk)

These updates should be safe to apply:

| Package | Current | Latest | Command |
|---------|---------|--------|---------|
| @alpinejs/collapse | 3.15.3 | 3.15.4 | `npm update @alpinejs/collapse` |
| @tailwindcss/forms | 0.5.10 | 0.5.11 | `npm update @tailwindcss/forms` |
| @tailwindcss/vite | 4.1.17 | 4.1.18 | `npm update @tailwindcss/vite` |
| autoprefixer | 10.4.22 | 10.4.23 | `npm update autoprefixer` |
| laravel-echo | 2.2.7 | 2.3.0 | `npm update laravel-echo` |
| laravel-vite-plugin | 2.0.1 | 2.1.0 | `npm update laravel-vite-plugin` |
| vite | 7.2.7 | 7.3.1 | `npm update vite` |

**Quick fix:** Run `npm update` to apply all minor updates.

### NPM - Major Updates (Evaluate Carefully)

| Package | Current | Latest | Breaking Changes |
|---------|---------|--------|------------------|
| @tiptap/* (15 packages) | 2.27.2 | 3.16.0 | Major rewrite - requires migration |
| tailwindcss | 3.4.18 | 4.1.18 | Significant config changes |

#### TipTap 3.x Migration Notes
- The TipTap rich text editor has a major version update
- **Recommendation:** Review [TipTap migration guide](https://tiptap.dev/docs/guides/upgrade-to-v3) before updating
- **Risk:** High - editor functionality changes
- **Usage in project:** Template editor, contract templates

#### Tailwind CSS 4.x Migration Notes
- Tailwind 4.0 introduces new configuration system
- **Recommendation:** Plan migration carefully, consider staying on 3.x until ecosystem stabilizes
- **Risk:** High - styling may break across application

### Composer - Minor Updates (Recommended - Low Risk)

| Package | Current | Latest | Command |
|---------|---------|--------|---------|
| larastan/larastan | 3.9.0 | 3.9.1 | `composer update larastan/larastan` |
| laravel/framework | 12.47.0 | 12.48.1 | `composer update laravel/framework` |

**Quick fix:** Run `composer update --with-dependencies` for these minor updates.

### Composer - Major Updates (Evaluate Carefully)

| Package | Current | Latest | Notes |
|---------|---------|--------|-------|
| livewire/livewire | 3.7.1 | 4.0.2 | Major release with breaking changes |
| phpunit/phpunit | 11.5.43 | 12.5.6 | Testing framework major update |

#### Livewire 4.x Notes
- **Recommendation:** Stay on 3.x for now unless specific features needed
- Livewire 4 requires careful migration planning
- **Risk:** High - affects all Livewire components

#### PHPUnit 12.x Notes
- **Recommendation:** Can update, but review test changes
- **Risk:** Medium - may require test syntax updates

---

## 3. Potential Bloat Analysis

### 3.1 Large Packages

| Package | Size | Purpose | Recommendation |
|---------|------|---------|----------------|
| aws/aws-sdk-php | 50MB | AWS services | See below |
| phpstan/phpstan | 26MB | Static analysis (dev) | Keep - valuable |
| laravel/pint | 15MB | Code formatting (dev) | Keep - valuable |
| fakerphp/faker | 11MB | Test data (dev) | Keep - valuable |
| dompdf/dompdf | 8.7MB | PDF generation | Keep - in use |

#### AWS SDK Optimization

The full AWS SDK is 50MB but you're only using S3 (`league/flysystem-aws-s3-v3`).

**Option 1:** Keep as-is (simplest, SDK is already optimized for autoloading)
**Option 2:** If size is critical, the flysystem adapter already handles S3 operations

**Recommendation:** Keep current setup - the SDK autoloads only needed classes.

### 3.2 PDF Library Redundancy

The project uses three PDF-related packages:

| Package | Purpose | Used In |
|---------|---------|---------|
| barryvdh/laravel-dompdf | Generate PDFs from HTML | `app/Models/Contract.php` |
| spatie/laravel-pdf | Generate PDFs (Chrome-based) | Jobs, Services, Controllers |
| smalot/pdfparser | Parse/read existing PDFs | Bank statement parsing |

**Analysis:**
- `barryvdh/laravel-dompdf` - Simple HTML-to-PDF, doesn't require Chrome
- `spatie/laravel-pdf` - Higher quality output, requires Chrome/Node
- `smalot/pdfparser` - Reads PDFs (different purpose)

**Recommendation:**
- If Chrome is available in production, consolidate to `spatie/laravel-pdf` only for generation
- If Chrome is NOT available, keep `barryvdh/laravel-dompdf` for simpler deployments
- `smalot/pdfparser` is required for parsing - keep it

### 3.3 Real-time Communication

The project includes both:
- `laravel/reverb` - Self-hosted WebSocket server
- `pusher/pusher-php-server` - Pusher service client

**Analysis:** This is intentional - Reverb uses Pusher-compatible protocol but runs self-hosted.

**Recommendation:** Keep both - this is the correct setup for Laravel Reverb.

### 3.4 Potentially Unused or Questionable Packages

| Package | Status | Recommendation |
|---------|--------|----------------|
| consoletvs/charts | Used | Consider modern alternative (Chart.js via Alpine) |
| spatie/browsershot | Not directly used | Dependency of spatie/laravel-pdf - keep |

---

## 4. Recommended Actions

### Immediate (Low Risk)
```bash
# Update all minor npm packages
cd app && npm update

# Update Laravel framework and Larastan
composer update laravel/framework larastan/larastan
```

### Short-term (Medium Risk)
1. Review and test after minor updates
2. Plan TipTap 3.x migration if rich text editor improvements needed
3. Run full test suite after any updates

### Long-term (High Risk - Plan Carefully)
1. **Tailwind CSS 4.x** - Wait for ecosystem maturity, plan dedicated migration sprint
2. **Livewire 4.x** - Wait for stable release, review breaking changes
3. **PDF consolidation** - Evaluate if Chrome is available in all environments

---

## 5. Dependency Health Score

| Metric | Score | Notes |
|--------|-------|-------|
| Security | **A+** | No vulnerabilities |
| Freshness | **B+** | Most packages current, some major updates pending |
| Bloat | **B** | Some optimization possible, but reasonable |
| Overall | **B+** | Healthy dependency setup |

---

## 6. Quick Commands

```bash
# Check for security issues
npm audit
composer audit

# See outdated packages
npm outdated
composer outdated --direct

# Apply safe minor updates
npm update
composer update --with-dependencies

# Regenerate lock files after updates
npm install
composer install
```

---

*Report generated by dependency audit analysis*
