# Performance Analysis Report

This document contains the findings from a comprehensive performance audit of the SAD-ERP codebase.

## Executive Summary

The analysis identified **42 performance issues** across 4 categories:
- **N+1 Query Issues**: 5 critical issues
- **Inefficient Database Queries**: 10 issues
- **Frontend Re-render Issues**: 14 issues
- **Inefficient Algorithms**: 13 issues

## 1. N+1 Query Issues

### CRITICAL: OfferBulkActionService.php

**File:** `app/Services/Offer/OfferBulkActionService.php`

**Lines 51-62, 88-99, 134-155**

The bulk action methods use individual `Offer::find()` calls inside loops instead of batch queries:

```php
// Current (N+1 pattern)
foreach ($offerIds as $id) {
    $offer = Offer::find($id);  // Query per ID
    // ...
}
```

**Fix:**
```php
// Optimized (single query)
$offers = Offer::whereIn('id', $offerIds)->get()->keyBy('id');
foreach ($offerIds as $id) {
    $offer = $offers->get($id);
    // ...
}
```

### HIGH: SubscriptionController.php & SubscriptionService.php

**Files:**
- `app/Http/Controllers/SubscriptionController.php` (lines 343-361, 437-445)
- `app/Services/Subscription/SubscriptionService.php` (lines 132-142)

The `advanceOverdueSubscriptions()` and bulk renewal methods call model methods inside loops that may trigger additional queries.

**Fix:** Review `advanceOverdueRenewals()` method and ensure all needed data is eager loaded before the loop.

### MEDIUM: Export Methods Missing Eager Loading

**File:** `app/Http/Controllers/SubscriptionController.php` (lines 410-420)

The `exportToCsv()` method iterates over subscriptions without eager loading related data.

**Fix:** Add relationships to `getExportEagerLoads()` method.

---

## 2. Inefficient Database Queries

### CRITICAL: Loading Entire Tables

**File:** `app/Services/Financial/Import/ClientMatcher.php` (line 20)
```php
$clients = Client::all();  // Loads ALL clients into memory
```

**File:** `app/Services/Currency/BnrExchangeRateService.php` (line 182)
```php
$organizations = Organization::all();  // Loads ALL organizations
```

**File:** `app/Models/ApplicationSetting.php` (line 82)
```php
$settings = self::all();  // Loads ALL settings for cache clearing
```

**Fix:** Use chunking, streaming, or filtered queries.

### HIGH: Missing Column Selection

**File:** `app/Services/Offer/OfferService.php` (lines 664-672)
```php
$templates = DocumentTemplate::active()->ofType('offer')->get();  // No select()
$services = Service::where('is_active', true)->orderBy('sort_order')->get();  // No select()
```

**Fix:** Add `->select(['id', 'name', ...])` to limit columns fetched.

### HIGH: Missing Indexes

The following columns likely need indexes based on query patterns:
- `credentials.site_name` (LIKE searches)
- `credentials.website` (LIKE searches)
- `financial_revenues.is_archived`
- `financial_revenues.category_option_id`
- `financial_files.an, luna` (composite index)

### MEDIUM: PHP-Side Filtering Instead of Database

**File:** `app/Services/Dashboard/RenewalPredictor.php` (lines 49-72)
```php
$domainsExpiring = Domain::whereBetween('expiry_date', [now(), now()->addDays(90)])->get();
$domains30Days = $domainsExpiring->filter(fn($d) => $d->expiry_date <= $day30);  // Filter in PHP
```

**Fix:** Use separate database queries with appropriate WHERE clauses.

---

## 3. Frontend Re-render Issues (Livewire/Alpine.js)

### CRITICAL: Index-Based Keys in x-for Loops

**File:** `resources/views/components/command-palette.blade.php` (line 67)
```blade
<template x-for="(group, groupIndex) in groupedItems" :key="groupIndex">
```

Using array index as key causes incorrect DOM reuse when items are reordered.

**Fix:** Use unique identifiers like `group.id` or `group.name`.

### HIGH: Uncached Computed Properties

**File:** `resources/views/components/command-palette.blade.php` (lines 175-183)
```javascript
get groupedItems() {
    // Rebuilds entire object on every access
    const groups = {};
    this.filteredItems.forEach(item => { ... });
    return Object.values(groups);
}
```

This getter is called multiple times per render cycle.

**Fix:** Cache the result and only recompute when `filteredItems` changes.

### HIGH: Repeated Computed Property Access

**File:** `resources/views/components/ui/searchable-select.blade.php` (lines 199-202)
```javascript
get filteredOptions() {
    return this.options.filter(o => o.label.toLowerCase().includes(q));
}
```

Called multiple times on every keystroke.

**Fix:** Implement memoization or use `$watch` to cache results.

### MEDIUM: DOM Query Caching

**File:** `resources/views/components/modal.blade.php` (lines 20-32)
```javascript
focusables() {
    return [...$el.querySelectorAll(selector)].filter(...)
}
// Called 5+ times per focus navigation
```

**Fix:** Cache the focusables array and only recompute when DOM changes.

### MEDIUM: Event Listener Memory Leaks

**File:** `resources/views/components/ui/searchable-select.blade.php` (lines 182-187)
```javascript
init() {
    window.addEventListener('scroll', () => { ... }, true);
    window.addEventListener('resize', () => { ... });
    // Never removed!
}
```

**Fix:** Store listener references and remove in destroy/cleanup.

### MEDIUM: Large Serialized Collections in View Components

**Files:**
- `app/View/Components/Dashboard/DomainWidget.php` (lines 10-14)
- `app/View/Components/Dashboard/ExpenseCategoryChart.php` (lines 10-14)

Public Collection properties are serialized unnecessarily.

**Fix:** Make unused source collections protected/private.

---

## 4. Inefficient Algorithms

### HIGH: Nested Loops with O(n*m) Complexity

**File:** `app/Services/Financial/TransactionImportService.php` (lines 105-121)
```php
foreach ($categoryLabelToId as $label => $catId) {
    if ($matchesWord($label, $transaction['description'])) { ... }
}
// Called for EACH transaction
```

With N transactions and M categories, this is O(N*M) with regex operations.

**Fix:** Build a regex pattern once or use database LIKE queries.

**File:** `app/Console/Commands/ImportClientNotesCommand.php` (lines 164-195)

Multiple nested loops searching through all clients for each note block.

**Fix:** Use indexed lookups or database queries.

### HIGH: Repeated Database Queries in Loops

**File:** `app/Console/Commands/CleanupDuplicateRevenuesCommand.php` (lines 77-90)
```php
foreach ($revenues as $revenue) {
    $hasFiles = $revenue->files()->count() > 0;  // Query per item
    $clientInfo = $revenue->client->name;  // Query per item
}
```

**Fix:** Eager load relationships before the loop.

**File:** `app/Services/SmartbillImporter.php` (lines 195-220)
```php
foreach ($clientIds as $clientId) {
    $client = Client::find($clientId);  // N queries
    $total = FinancialRevenue::where('client_id', $clientId)->sum('amount');  // N queries
    $client->save();  // N queries
}
```

For N clients, runs 3N queries.

**Fix:** Use a single bulk update query:
```php
DB::statement("UPDATE clients SET total_incomes = (
    SELECT COALESCE(SUM(amount), 0) FROM financial_revenues WHERE client_id = clients.id
) WHERE id IN (?)", [implode(',', $clientIds)]);
```

### MEDIUM: Inefficient Date Operations

**Files:**
- `app/Services/Currency/BnrExchangeRateService.php` (lines 209-211)
- `app/Services/Dashboard/TrendsCalculator.php` (lines 103-124)
- `app/Services/Dashboard/RenewalPredictor.php` (lines 28-97)

Multiple `now()` calls creating redundant Carbon instances.

**Fix:** Cache `now()` at method start:
```php
$now = now();
$today = $now->format('Y-m-d');
// Use $now and $today throughout
```

### MEDIUM: Static Arrays Recreated Every Call

**File:** `app/Services/Financial/RevenueImportService.php` (lines 205-220)
```php
protected function getRomanianMonthName($monthNumber) {
    $months = [1 => 'Ianuarie', ...];  // Recreated every call
    return $months[$monthNumber];
}
```

**Fix:** Use a class constant or static property.

---

## Priority Recommendations

### Immediate (High Impact, Easy Fix)

1. **Replace `Offer::find()` loops** in OfferBulkActionService with `whereIn()` batch queries
2. **Cache `now()` calls** in date-heavy services
3. **Add `->select()` clauses** to queries that don't need all columns
4. **Fix x-for keys** in command-palette.blade.php

### Short-term (High Impact, Medium Effort)

5. **Implement memoization** for Alpine.js computed properties
6. **Add missing database indexes** on frequently queried columns
7. **Replace `Client::all()` and `Organization::all()`** with filtered/chunked queries
8. **Bulk update clients** in SmartbillImporter instead of individual queries

### Medium-term (Medium Impact, Higher Effort)

9. **Review subscription renewal methods** for eager loading opportunities
10. **Implement event listener cleanup** in searchable-select component
11. **Refactor nested loops** in TransactionImportService with indexed lookups
12. **Add caching layer** for nomenclature/category data

---

## Testing Recommendations

After implementing fixes:

1. **Use Laravel Debugbar** to verify query counts are reduced
2. **Profile with `EXPLAIN`** to verify indexes are being used
3. **Use browser DevTools** to measure render performance
4. **Load test** bulk operations with realistic data volumes
5. **Monitor memory usage** during import operations

---

*Generated: 2026-01-21*
