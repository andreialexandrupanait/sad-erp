<?php

namespace App\Services\Financial;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Financial Query Builder Service
 *
 * Provides reusable query building methods for financial records (revenues and expenses).
 * Handles common filtering patterns, aggregations, and data transformations.
 */
class QueryBuilderService
{
    /**
     * Apply common financial filters to a query
     *
     * @param Builder $query The base query
     * @param array $filters Array of filter values
     * @return Builder
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['year'] ?? null, fn($q, $year) => $q->forYear($year))
            ->when($filters['month'] ?? null, fn($q, $month) => $q->where('month', $month))
            ->when($filters['currency'] ?? null, fn($q, $currency) => $q->where('currency', $currency))
            ->when($filters['client_id'] ?? null, fn($q, $clientId) => $q->where('client_id', $clientId))
            ->when($filters['category_option_id'] ?? null, fn($q, $categoryId) => $q->where('category_option_id', $categoryId))
            ->when($filters['search'] ?? null, function($q, $search) use ($filters) {
                return $this->applySearch($q, $search, $filters['searchFields'] ?? []);
            });
    }

    /**
     * Apply search filter across specified fields
     *
     * @param Builder $query
     * @param string $search
     * @param array $searchFields Fields to search in
     * @return Builder
     */
    protected function applySearch(Builder $query, string $search, array $searchFields): Builder
    {
        return $query->where(function($q) use ($search, $searchFields) {
            // Default searchable fields
            $q->where('document_name', 'like', "%{$search}%")
              ->orWhere('note', 'like', "%{$search}%");

            // Add custom searchable relationships
            foreach ($searchFields as $field => $relation) {
                if (is_array($relation)) {
                    $q->orWhereHas($field, fn($subQuery) =>
                        $subQuery->where($relation['column'], 'like', "%{$search}%")
                    );
                }
            }
        });
    }

    /**
     * Calculate currency-grouped totals for filtered results
     *
     * @param Builder $query Base query with filters already applied
     * @return \Illuminate\Support\Collection
     */
    public function calculateFilteredTotals(Builder $query): \Illuminate\Support\Collection
    {
        return $query
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(fn($item) => [$item->currency => $item->total]);
    }

    /**
     * Calculate yearly totals by currency
     *
     * @param string $modelClass The model class name
     * @param int $year The year to calculate for
     * @param array $additionalFilters Optional additional filters (e.g., client_id, category_id)
     * @return \Illuminate\Support\Collection
     */
    public function calculateYearlyTotals(string $modelClass, int $year, array $additionalFilters = []): \Illuminate\Support\Collection
    {
        $query = $modelClass::forYear($year);

        foreach ($additionalFilters as $field => $value) {
            if ($value !== null) {
                $query->where($field, $value);
            }
        }

        return $query
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(fn($item) => [$item->currency => $item->total]);
    }

    /**
     * Get months with transactions, including counts and totals
     * Note: Total only sums RON amounts to avoid mixing currencies
     *
     * @param Builder $query Base query with year and optional filters
     * @return \Illuminate\Support\Collection
     */
    public function getMonthsWithTransactions(Builder $query): \Illuminate\Support\Collection
    {
        return $query
            ->select(
                'month',
                DB::raw('COUNT(*) as count'),
                DB::raw("SUM(CASE WHEN currency = 'RON' THEN amount ELSE 0 END) as total")
            )
            ->groupBy('month')
            ->get()
            ->mapWithKeys(fn($item) => [$item->month => [
                'count' => $item->count,
                'total' => $item->total
            ]]);
    }

    /**
     * Get category breakdown for expenses
     *
     * @param Builder $query Base query with filters applied
     * @param int $limit Number of top categories to return
     * @return \Illuminate\Support\Collection
     */
    public function getCategoryBreakdown(Builder $query, int $limit = 5): \Illuminate\Support\Collection
    {
        return $query
            ->whereNotNull('category_option_id')
            ->select('category_option_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_option_id')
            ->with('category')
            ->get()
            ->sortByDesc('total')
            ->take($limit);
    }

    /**
     * Get available years range (from 2019 to current year)
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableYears(): \Illuminate\Support\Collection
    {
        $currentYear = now()->year;
        return collect(range(2019, $currentYear))->reverse()->values();
    }

    /**
     * Build paginated query with filters and sorting
     *
     * @param string $modelClass The model class name
     * @param array $filters Filter parameters
     * @param string $sortBy Sort column
     * @param string $sortDir Sort direction (asc|desc)
     * @param int $perPage Items per page
     * @param array $with Relationships to eager load
     * @param array $withCount Relationships to count
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function buildPaginatedQuery(
        string $modelClass,
        array $filters,
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        int $perPage = 50,
        array $with = [],
        array $withCount = []
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {
        $query = $modelClass::query();

        if (!empty($with)) {
            $query->with($with);
        }

        if (!empty($withCount)) {
            $query->withCount($withCount);
        }

        $this->applyFilters($query, $filters);

        return $query
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Count records matching filters
     *
     * @param string $modelClass The model class name
     * @param array $filters Filter parameters
     * @return int
     */
    public function countFiltered(string $modelClass, array $filters): int
    {
        $query = $modelClass::query();
        $this->applyFilters($query, $filters);
        return $query->count();
    }
}
