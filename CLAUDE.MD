1. First think through the problem, read the codebase for relevant files.
2. Before you make any major changes, check in with me and I will verify the plan.
3. Please every step of the way just give me a high level explanation of what changes you made
4. Make every task and code change you do as simple as possible. We want to avoid making any massive or complex changes. Every change should impact as little code as possible. Everything is about simplicity.
5. Maintain a documentation file that describes how the architecture of the app works inside and out.
6. Never speculate about code you have not opened. If the user references a specific file, you MUST read the file before answering. Make sure to investigate and read relevant files BEFORE answering questions about the codebase. Never make any claims about code before investigating unless you are certain of the correct answer - give grounded and hallucination-free answers.

---

## Financial Module: Dual Currency (EUR/RON) Strategy

### Data Structure

For financial records (revenues/expenses), we use a dual-value storage approach:

| Field | Purpose | Example |
|-------|---------|---------|
| `amount` | **RON value** - Used in ALL statistics and calculations | 5,593.61 |
| `amount_eur` | Original EUR value (only for EUR records) | 1,100.00 |
| `currency` | Indicates original currency ('EUR' or 'RON') | 'EUR' |
| `exchange_rate` | BNR rate used for conversion | 5.0851 |

**Key Principle:** The `amount` field ALWAYS contains RON. For EUR invoices, it stores the converted RON value. This ensures all statistics use a single currency (RON) without complex conversions.

### Display Format

- **EUR records:** "1,100.00 EUR / 5,593.61 RON" (black EUR, green RON)
- **RON records:** "500.00 RON" (green)

In views, use conditional display:
```blade
@if($record->currency === 'EUR' && $record->amount_eur)
    <span class="text-slate-900">{{ number_format($record->amount_eur, 2) }} EUR</span> /
    <span class="text-green-600">{{ number_format($record->amount, 2) }} RON</span>
@else
    <span class="text-green-600">{{ number_format($record->amount, 2) }} {{ $record->currency }}</span>
@endif
```

### Widget Display Format

Widgets show totals as: "217 179 Lei (din care 14,842.50 EUR)"
- Lei value in green (revenues) or red (expenses)
- EUR value in black, smaller text

### Aggregator Logic (app/Services/Financial/)

**RevenueAggregator.php / ExpenseAggregator.php:**

```php
// getYearlyTotals() returns:
return collect([
    "RON" => $totalRon,  // sum of ALL amount fields (includes converted EUR)
    "EUR" => $totalEur,  // sum of amount_eur for EUR records only
]);

// getMonthlyData() - sums ALL amounts (no currency filter)
->select("month", DB::raw("SUM(amount) as total"))
```

### QueryBuilderService Logic

**calculateFilteredTotals() / calculateYearlyTotals():**

```php
// Use COALESCE to handle NULL amount_eur gracefully
DB::raw("SUM(CASE WHEN currency = 'EUR' THEN COALESCE(amount_eur, 0) ELSE amount END) as total")
```

This ensures:
- EUR records with `amount_eur` → use `amount_eur`
- EUR records without `amount_eur` → use 0 (not RON amount)
- RON records → use `amount`

### Data Integrity Rules

1. **EUR records MUST have `amount_eur` populated** with the original EUR value
2. **If `amount_eur` is NULL for a EUR record**, either:
   - Populate it from source documents, OR
   - Change `currency` to 'RON' (since only RON value is known)
3. **Never store EUR values in the `amount` field** - it must always be RON

### Import Process (SmartBill)

When importing EUR invoices from SmartBill:
1. Store original EUR value in `amount_eur`
2. Fetch BNR exchange rate for invoice date
3. Calculate RON value: `amount = amount_eur * exchange_rate`
4. Store `currency = 'EUR'` and `exchange_rate`

### Files Reference

| File | Purpose |
|------|---------|
| `app/Services/Financial/RevenueAggregator.php` | Revenue totals & monthly data |
| `app/Services/Financial/ExpenseAggregator.php` | Expense totals & monthly data |
| `app/Services/Financial/QueryBuilderService.php` | Filtered/yearly totals with currency logic |
| `app/Services/Financial/FinancialDashboardService.php` | Dashboard data orchestration |
| `app/Services/Financial/ChartDataBuilder.php` | Chart & monthly breakdown |
| `resources/views/financial/revenues/index.blade.php` | Revenue list display |
| `resources/views/financial/expenses/index.blade.php` | Expense list display |
| `resources/views/financial/dashboard.blade.php` | Financial dashboard |

### Verification Queries

```php
// Check for EUR records missing amount_eur (data quality issue)
FinancialRevenue::where('currency', 'EUR')->whereNull('amount_eur')->count();
FinancialExpense::where('currency', 'EUR')->whereNull('amount_eur')->count();

// Verify totals
FinancialRevenue::forYear($year)->sum('amount'); // Total RON
FinancialRevenue::forYear($year)->where('currency', 'EUR')->sum('amount_eur'); // Total EUR
```