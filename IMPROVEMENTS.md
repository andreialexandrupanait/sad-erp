# SAD-ERP Improvement Plan

> Generated: 2026-01-18
> Application Status: Production
> Focus: Functionality & Scalability (No new features)

---

## Table of Contents

- [Critical Issues](#-critical-issues)
- [High Priority - Performance](#-high-priority---performance--scalability)
- [Medium Priority - Code Quality](#-medium-priority---code-quality--maintainability)
- [Lower Priority - Testing](#-lower-priority---testing--error-handling)
- [Infrastructure & Database](#-infrastructure--database)
- [Implementation Checklist](#-implementation-checklist)

---

## 🔴 Critical Issues

### 1. Broken Query Scopes (Bug)

**Files:**
- `app/Models/FinancialRevenue.php:119-120`
- `app/Models/FinancialExpense.php:119-120`

**Issue:** `scopeByCurrency()` has invalid syntax with 3 parameters instead of 2.

```php
// BROKEN
->where('currency', 'exchange_rate', $currency)

// FIXED
->where('currency', $currency)
```

**Risk:** Query failures in production when filtering by currency.

---

### 2. Mass Assignment Vulnerability in User Model

**File:** `app/Models/User.php:35-36`

**Issue:** Sensitive 2FA fields are in `$fillable`:
```php
protected $fillable = [
    // ...
    'two_factor_secret',           // ⚠️ REMOVE
    'two_factor_recovery_codes',   // ⚠️ REMOVE
    // ...
];
```

**Risk:** Attacker could reset user's 2FA via mass assignment.

**Fix:** Remove from `$fillable`, use explicit setters instead.

---

### 3. Missing Authorization on OfferController

**File:** `app/Http/Controllers/OfferController.php`

**Issue:** No `authorizeResource()` call. Methods without authorization:
- `edit()` - line 125
- `show()` - line 115
- `approve()` - line 337
- `convertToContract()` - line 367
- `regenerateContract()` - line 470
- `send()` - line 243
- `downloadPdf()` - line 307
- `saveAsTemplate()` - line 759

**Fix:** Add to constructor:
```php
public function __construct(OfferService $offerService)
{
    $this->offerService = $offerService;
    $this->authorizeResource(Offer::class, 'offer');
}
```

---

### 4. Missing Authorization on API Endpoints

**File:** `app/Http/Controllers/Api/WidgetController.php:36-65`

**Issue:** `topClients()` and other methods lack authorization checks.

**Risk:** Users might access data from other organizations.

**Fix:** Add policy checks:
```php
public function topClients(Request $request): JsonResponse
{
    $this->authorize('viewAny', Client::class);
    // ...
}
```

---

## 🟠 High Priority - Performance & Scalability

### 5. Synchronous Email Sending Blocking Requests

**File:** `app/Services/Offer/OfferService.php`

**Locations:** Lines 294, 452, 457, 475, 481, 1009, 1041

**Issue:** `Mail::send()` blocks request for 5-30 seconds per email.

**Fix:** Change to queued mail:
```php
// BEFORE
Mail::send('emails.offer-sent', $data, function ($mail) { ... });

// AFTER
Mail::to($recipient)->queue(new OfferSentMail($offer));
```

---

### 6. Synchronous PDF Generation

**File:** `app/Services/Offer/OfferService.php:243, 411-412`

**Issue:** `dispatchSync()` blocks request during PDF generation.

```php
// BEFORE
GenerateDocumentPdfJob::dispatchSync($offer, Document::TYPE_OFFER_SENT);

// AFTER (for non-critical PDFs)
GenerateDocumentPdfJob::dispatch($offer, Document::TYPE_OFFER_SENT);
```

---

### 7. Multiple ApplicationSetting Database Queries

**File:** `app/Services/Offer/OfferService.php:336-345`

**Issue:** 8 separate DB queries for SMTP settings per email.

**Fix:** Batch fetch or cache:
```php
// Option 1: Batch fetch
$settings = ApplicationSetting::whereIn('key', [
    'smtp_enabled', 'smtp_host', 'smtp_port',
    'smtp_username', 'smtp_password', 'smtp_encryption',
    'smtp_from_email', 'smtp_from_name'
])->pluck('value', 'key');

// Option 2: Use existing cache config (erp.php has 24h TTL for settings)
$settings = cache()->remember('smtp_settings', 86400, fn() => ...);
```

---

### 8. Missing Database Indexes

**Create migration:** `add_performance_indexes`

```php
Schema::table('offers', function (Blueprint $table) {
    $table->index('created_by_user_id');
    $table->index('template_id');
    $table->index(['organization_id', 'status']);
    $table->index(['organization_id', 'created_at']);
});

Schema::table('contracts', function (Blueprint $table) {
    $table->index('template_id');
    $table->index('status');
    $table->index(['organization_id', 'status']);
});

Schema::table('clients', function (Blueprint $table) {
    $table->index('created_at');
});

Schema::table('financial_revenues', function (Blueprint $table) {
    $table->index(['organization_id', 'year', 'month']);
});

Schema::table('financial_expenses', function (Blueprint $table) {
    $table->index(['organization_id', 'year', 'month']);
});
```

---

### 9. N+1 Query in Aggregators

**Files:**
- `app/Services/Financial/RevenueAggregator.php:125-133`
- `app/Services/Financial/ExpenseAggregator.php:99-107`

**Issue:** `.with('client')` after `.groupBy()` doesn't work properly.

**Fix:** Load clients separately:
```php
// Get aggregated data
$results = FinancialRevenue::forYear($year)
    ->select('client_id', DB::raw('SUM(amount) as total'))
    ->groupBy('client_id')
    ->orderByDesc('total')
    ->limit($limit)
    ->get();

// Load clients separately
$clientIds = $results->pluck('client_id');
$clients = Client::whereIn('id', $clientIds)->get()->keyBy('id');

// Map clients to results
$results->each(fn($r) => $r->setRelation('client', $clients[$r->client_id] ?? null));
```

---

### 10. Large Controllers Need Splitting

| Controller | Lines | Split Into |
|------------|-------|------------|
| `OfferController.php` | 1,346 | `OfferController`, `OfferPublicController`, `OfferBuilderController` |
| `ContractController.php` | 1,308 | `ContractController`, `ContractAnnexController`, `ContractVersionController` |
| `ClientController.php` | 1,346 | `ClientController`, `ClientNotesController` |

**Suggested structure:**
```
Controllers/
├── Offer/
│   ├── OfferController.php      # CRUD operations
│   ├── OfferPublicController.php # Public share, accept, reject
│   └── OfferBuilderController.php # Simple builder
├── Contract/
│   ├── ContractController.php    # CRUD operations
│   ├── ContractAnnexController.php
│   └── ContractVersionController.php
```

---

### 11. Large Services Need Splitting

| Service | Lines | Extract To |
|---------|-------|------------|
| `OfferService.php` | 1,199 | `OfferPdfService`, `OfferNotificationService` |
| `ContractService.php` | 1,005 | `ContractPdfService`, `AnnexService` |
| `VariableRegistry.php` | 967 | `VariableResolver`, `VariableValidator`, `ServiceListRenderer` |
| `RevenueImportService.php` | 931 | Already delegating, consider `ImportStatisticsTracker` |

---

## 🟡 Medium Priority - Code Quality & Maintainability

### 12. Duplicate SMTP Configuration Code

**File:** `app/Services/Offer/OfferService.php`

**Issue:** `configureSmtpFromDatabase()` called 6+ times.

**Fix:** Create `SmtpConfigurationService`:
```php
class SmtpConfigurationService
{
    public function configure(): void
    {
        $settings = $this->getSettings();
        if (!$settings['enabled']) return;

        config([
            'mail.mailers.smtp.host' => $settings['host'],
            'mail.mailers.smtp.port' => $settings['port'],
            // ...
        ]);
    }

    private function getSettings(): array
    {
        return cache()->remember('smtp_settings', 86400, fn() => [
            'enabled' => ApplicationSetting::get('smtp_enabled', false),
            // ... batch all settings
        ]);
    }
}
```

---

### 13. Duplicate Validation Code

**Files:**
- `OfferController.php:598-618`
- `ContractController.php:539-569`

**Issue:** Identical `updateTempClient` validation in both controllers.

**Fix:** Create `app/Http/Requests/UpdateTempClientRequest.php`:
```php
class UpdateTempClientRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'temp_client_name' => 'required|string|max:255',
            'temp_client_email' => 'nullable|email|max:255',
            'temp_client_phone' => 'nullable|string|max:50',
            'temp_client_company' => 'nullable|string|max:255',
            'temp_client_address' => 'nullable|string|max:500',
            'temp_client_tax_id' => 'nullable|string|max:50',
            'temp_client_registration_number' => 'nullable|string|max:100',
            'temp_client_bank_account' => 'nullable|string|max:100',
        ];
    }
}
```

---

### 14. Missing Form Request Classes

| Method | Current | Create |
|--------|---------|--------|
| `OfferController::updateTempClient()` | Inline validation | `UpdateTempClientRequest` |
| `OfferController::simpleStore()` | Partial duplication | Use `StoreOfferRequest` |
| `OfferController::approve()` | No validation | `ApproveOfferRequest` |

---

### 15. Inconsistent Response Formats

**Current inconsistency:**
```php
// Format 1
return response()->json(['success' => true, 'message' => '...', 'offer' => $offer], 201);

// Format 2
return response()->json(['error' => $e->getMessage()], 500);

// Format 3
return response()->json(['success' => false, 'message' => '...'], 500);
```

**Fix:** Create consistent format:
```php
// app/Http/Responses/ApiResponse.php
class ApiResponse
{
    public static function success($data = null, string $message = null, int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function error(string $message, int $status = 500, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
```

---

### 16. Missing Interfaces for Services

**Services needing interfaces:**
- `ContractService` → `ContractServiceInterface`
- `RevenueAggregator` → `RevenueAggregatorInterface`
- `ExpenseAggregator` → `ExpenseAggregatorInterface`
- `SmartbillService` → `SmartbillServiceInterface`
- `VariableRegistry` → `VariableRegistryInterface`

**Benefits:**
- Easier mocking in tests
- Swappable implementations
- Clear public API definition

---

### 17. Direct auth() Calls in Services

**Files:**
- `RevenueAggregator.php:25-26`
- `ExpenseAggregator.php:25`
- `NomenclatureService.php:27`
- `FinancialDashboardService.php:41`

**Issue:** Direct `auth()->user()->organization_id` breaks background jobs.

**Fix:** Accept as parameter:
```php
// BEFORE
private function cacheKey(string $key): string
{
    $orgId = auth()->user()->organization_id ?? 'default';
    return "org.{$orgId}.{$key}";
}

// AFTER
public function __construct(private ?int $organizationId = null)
{
    $this->organizationId = $organizationId ?? auth()->user()?->organization_id;
}

private function cacheKey(string $key): string
{
    return "org.{$this->organizationId}.{$key}";
}
```

---

### 18. Service Locator Pattern

**File:** `app/Services/Contract/ContractService.php:53`

**Issue:**
```php
$this->documentFileService ??= app(DocumentFileService::class);
```

**Fix:** Use constructor injection:
```php
public function __construct(
    private DocumentFileService $documentFileService
) {}
```

---

### 19. Direct Service Instantiation

**File:** `app/Services/SmartbillImporter.php:55`

**Issue:**
```php
$this->smartbillService = new SmartbillService($username, $token, $cif);
```

**Fix:** Inject via constructor or factory:
```php
public function __construct(
    private SmartbillServiceFactory $smartbillFactory
) {}

public function import(): void
{
    $service = $this->smartbillFactory->create($username, $token, $cif);
}
```

---

### 20. HTML Generation in Services

**Files:**
- `app/Services/VariableRegistry.php:477-535`
- `app/Services/Contract/ContractVariableRegistry.php:306-347`

**Issue:** Inline HTML with inline styles in business logic.

**Fix:** Extract to renderer:
```php
// app/Services/Renderers/ServiceListRenderer.php
class ServiceListRenderer
{
    public function render(Collection $items, string $currency): string
    {
        return view('components.service-list', [
            'items' => $items,
            'currency' => $currency,
        ])->render();
    }
}
```

---

### 21. Missing Soft Deletes

**Tables needing soft deletes:**
- `offer_items` - Part of historical invoice records
- `contract_items` - Part of contract records

**Migration:**
```php
Schema::table('offer_items', function (Blueprint $table) {
    $table->softDeletes();
});

Schema::table('contract_items', function (Blueprint $table) {
    $table->softDeletes();
});
```

---

### 22. Naming Inconsistencies

**Foreign key naming:**
| Current | Should Be |
|---------|-----------|
| `user_id` (ambiguous) | `created_by_user_id` or `assigned_to_user_id` |
| Both `user()` and `creator()` relationships | Standardize to one |

**Recommendation:** Audit and standardize:
- `created_by_user_id` → creator relationship
- `assigned_to_user_id` → assignee relationship
- `user_id` → owner relationship (when context is clear)

---

## 🟢 Lower Priority - Testing & Error Handling

### 23. Critical Test Coverage Gaps

| Module | Current | Target | Priority |
|--------|---------|--------|----------|
| Banking (TransactionMatchingService) | 0% | 80% | HIGH |
| Currency Conversion | 0% | 90% | HIGH |
| Financial Dashboard | 0% | 70% | HIGH |
| Authorization Policies | 25% | 100% | HIGH |
| Subscription Renewals | Partial | 80% | HIGH |
| Console Commands | 0% | 60% | MEDIUM |
| Observers | 0% | 50% | LOW |

**Test files to create:**
```
tests/Unit/Services/Banking/
├── TransactionMatchingServiceTest.php
├── ConfidenceCalculatorTest.php
├── MatchCandidateFinderTest.php
└── StringSimilarityMatcherTest.php

tests/Unit/Services/Currency/
└── CurrencyConversionServiceTest.php

tests/Unit/Services/Financial/
├── FinancialDashboardServiceTest.php
├── RevenueAggregatorTest.php
└── ExpenseAggregatorTest.php

tests/Unit/Policies/
├── ContractPolicyTest.php
├── ClientPolicyTest.php
├── BankTransactionPolicyTest.php
└── SubscriptionPolicyTest.php
```

---

### 24. Missing Error Handling

**File:** `app/Http/Controllers/OfferController.php`

**Issue:** No try-catch around service calls:
```php
// BEFORE
$offer = $this->offerService->create($validated, $items);
return response()->json([...]);

// AFTER
try {
    $offer = $this->offerService->create($validated, $items);
    return ApiResponse::success($offer, __('Offer created successfully.'), 201);
} catch (ValidationException $e) {
    return ApiResponse::error($e->getMessage(), 422, $e->errors());
} catch (ModelNotFoundException $e) {
    return ApiResponse::error(__('Related resource not found.'), 404);
} catch (\Exception $e) {
    Log::error('Failed to create offer', ['error' => $e->getMessage()]);
    return ApiResponse::error(__('An error occurred.'), 500);
}
```

---

### 25. Generic Exception Catching

**Issue:** All controllers catch `\Exception $e` everywhere.

**Fix:** Catch specific exceptions:
```php
try {
    // ...
} catch (ValidationException $e) {
    return response()->json(['errors' => $e->errors()], 422);
} catch (AuthorizationException $e) {
    return response()->json(['error' => 'Unauthorized'], 403);
} catch (ModelNotFoundException $e) {
    return response()->json(['error' => 'Not found'], 404);
} catch (\Exception $e) {
    Log::error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
    return response()->json(['error' => 'Server error'], 500);
}
```

---

### 26. Missing Audit Logging

**File:** `app/Http/Controllers/ImportExportController.php:176-214`

**Issue:** No audit logging for successful imports.

**Fix:**
```php
Log::channel('audit')->info('Clients imported successfully', [
    'user_id' => auth()->id(),
    'count' => $result['imported'],
    'errors' => count($result['errors']),
    'ip' => request()->ip(),
]);
```

---

### 27. Silent Decryption Failures

**File:** `app/Http/Controllers/CredentialController.php:515-521`

**Issue:**
```php
try {
    $smtpPassword = decrypt($smtpPassword);
} catch (\Exception $e) {
    // Silently ignored
}
```

**Fix:**
```php
try {
    $smtpPassword = decrypt($smtpPassword);
} catch (DecryptException $e) {
    Log::warning('SMTP password decryption failed', [
        'user_id' => auth()->id(),
        'ip' => request()->ip(),
    ]);
    // Use raw value or handle gracefully
}
```

---

### 28. Missing Rate Limiting

**Unprotected endpoints:**
- `/widgets/*` endpoints
- `/api/clients/{client}/notes`

**Fix in `routes/web.php`:**
```php
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/widgets/top-clients', [WidgetController::class, 'topClients']);
    // ...
});
```

---

## 🔵 Infrastructure & Database

### 29. Cache Key Generation Duplication

**Files with duplicate logic:**
- `RevenueAggregator.php`
- `ExpenseAggregator.php`
- `NomenclatureService.php`
- `FinancialDashboardService.php`

**Fix:** Create trait or service:
```php
// app/Services/Concerns/HasOrganizationCache.php
trait HasOrganizationCache
{
    protected function cacheKey(string $key): string
    {
        $orgId = $this->getOrganizationId();
        return "org.{$orgId}.{$key}";
    }

    protected function getOrganizationId(): int|string
    {
        return $this->organizationId
            ?? auth()->user()?->organization_id
            ?? 'default';
    }
}
```

---

### 30. Missing Composite Indexes

```php
// Additional indexes for common query patterns
Schema::table('offers', function (Blueprint $table) {
    $table->index(['client_id', 'status']);
    $table->index(['organization_id', 'status', 'created_at']);
});

Schema::table('financial_revenues', function (Blueprint $table) {
    $table->index(['client_id', 'occurred_at']);
    $table->index(['organization_id', 'currency', 'year']);
});
```

---

### 31. Large Blade Templates

| File | Lines | Action |
|------|-------|--------|
| `offers/builder.blade.php` | 1,563 | Split into components |
| `layouts/app.blade.php` | 1,402 | Extract navigation, sidebar, scripts |
| `contracts/show.blade.php` | 1,031 | Extract tabs into partials |
| `contracts/index.blade.php` | 1,009 | Extract table, filters, modals |

**Suggested component structure:**
```
components/
├── offers/
│   ├── builder-header.blade.php
│   ├── builder-items.blade.php
│   ├── builder-totals.blade.php
│   └── builder-actions.blade.php
├── contracts/
│   ├── show-header.blade.php
│   ├── show-details.blade.php
│   ├── show-annexes.blade.php
│   └── show-history.blade.php
```

---

### 32. VariableRegistry Not Cached

**File:** `app/Services/VariableRegistry.php:55`

**Issue:** `getDefinitions()` returns large static array, called repeatedly.

**Fix:**
```php
public static function getDefinitions(): array
{
    return cache()->rememberForever('variable_registry_definitions', fn() => [
        'client' => [...],
        'contract' => [...],
        // ...
    ]);
}

// Clear cache when definitions change
public static function clearCache(): void
{
    cache()->forget('variable_registry_definitions');
}
```

---

## ✅ Implementation Checklist

### Phase 1: Critical Fixes (Do First)
- [ ] Fix `scopeByCurrency()` in FinancialRevenue and FinancialExpense
- [ ] Remove 2FA fields from User `$fillable`
- [ ] Add `authorizeResource()` to OfferController
- [ ] Add authorization to WidgetController API endpoints

### Phase 2: Performance Quick Wins
- [ ] Change `Mail::send()` to `Mail::queue()` in OfferService
- [ ] Create migration for missing database indexes
- [ ] Batch ApplicationSetting queries for SMTP
- [ ] Fix N+1 in RevenueAggregator and ExpenseAggregator

### Phase 3: Code Quality
- [ ] Create `UpdateTempClientRequest` form request
- [ ] Create `ApiResponse` helper for consistent responses
- [ ] Extract `SmtpConfigurationService`
- [ ] Add rate limiting to unprotected endpoints

### Phase 4: Refactoring
- [ ] Split OfferController into 3 controllers
- [ ] Split ContractController into 3 controllers
- [ ] Split OfferService into focused services
- [ ] Add interfaces for major services

### Phase 5: Testing
- [ ] Add banking service tests
- [ ] Add currency conversion tests
- [ ] Add missing policy tests
- [ ] Add financial dashboard tests

### Phase 6: Infrastructure
- [ ] Add soft deletes to offer_items and contract_items
- [ ] Split large Blade templates into components
- [ ] Cache VariableRegistry definitions
- [ ] Standardize cache key generation

---

## Notes

- All changes should be tested in staging before production deployment
- Database migrations should be run during low-traffic periods
- Consider feature flags for major refactoring
- Keep backward compatibility during transition periods
