<?php

namespace App\Services\Client;

use App\Models\Client;
use App\Services\NomenclatureService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Client Query Service
 *
 * Handles all client listing, filtering, sorting, and data transformation logic.
 * Extracted from ClientController to improve separation of concerns.
 */
class ClientQueryService
{
    /**
     * Column mapping from frontend names to database columns.
     */
    protected array $columnMap = [
        'name' => 'name',
        'revenue' => 'total_incomes',
        'status' => 'status_id',
        'created' => 'created_at',
        'email' => 'email',
        'total_incomes' => 'total_incomes',
        'status_id' => 'status_id',
        'created_at' => 'created_at',
        'activity' => 'activity',
        'last_invoice_at' => 'last_invoice_at',
    ];

    /**
     * Columns that default to descending sort.
     */
    protected array $defaultDescColumns = ['revenue', 'total_incomes', 'created', 'created_at', 'activity', 'last_invoice_at'];

    public function __construct(
        protected NomenclatureService $nomenclatureService
    ) {}

    /**
     * Get paginated clients with filters, sorting, and eager loading.
     */
    public function getPaginatedClients(Request $request): LengthAwarePaginator
    {
        $query = Client::with(['status'])
            ->withCount(['revenues as invoices_count']);

        $this->applyStatusFilter($query, $request);
        $this->applySearchFilter($query, $request);
        $this->applySorting($query, $request);

        return $query->paginate($this->getPerPage($request));
    }

    /**
     * Apply status filter to query.
     */
    protected function applyStatusFilter($query, Request $request): void
    {
        if (!$request->filled('status')) {
            return;
        }

        $statusSlugs = array_filter(explode(',', $request->status));
        if (empty($statusSlugs)) {
            return;
        }

        $statusIds = $this->getStatusIdsBySlugs($statusSlugs);
        if (!empty($statusIds)) {
            $query->whereIn('status_id', $statusIds);
        }
    }

    /**
     * Apply search filter to query.
     */
    protected function applySearchFilter($query, Request $request): void
    {
        if ($request->filled('q')) {
            $query->search($request->q);
        }
    }

    /**
     * Apply sorting to query.
     */
    protected function applySorting($query, Request $request): void
    {
        $sort = $request->get('sort', 'activity:desc');
        [$column, $direction] = $this->parseSort($sort);

        if ($column === 'activity') {
            // Combined sort: invoice count (most active) + last invoice date (most recent)
            // Use withCount instead of raw subquery for better performance
            $query->withCount('revenues')
                  ->orderByDesc('revenues_count')
                  ->orderByDesc('last_invoice_at');
        } else {
            $query->orderBy($column, $direction);
        }
    }

    /**
     * Get per-page limit from request with validation.
     */
    public function getPerPage(Request $request): int
    {
        $perPage = $request->get('limit', config('erp.pagination.default', 25));
        $allowedPerPage = config('erp.pagination.allowed', [10, 25, 50, 100]);
        $maxPerPage = config('erp.pagination.max', 100);

        $perPage = (int) $perPage;

        if (!in_array($perPage, $allowedPerPage) || $perPage > $maxPerPage) {
            return config('erp.pagination.default', 25);
        }

        return $perPage;
    }

    /**
     * Parse sort parameter (format: "column" or "column:direction").
     */
    public function parseSort(string $sort): array
    {
        $parts = explode(':', $sort);
        $column = $parts[0];
        $direction = $parts[1] ?? null;

        // If no direction specified, use default based on column
        if ($direction === null) {
            $direction = in_array($column, $this->defaultDescColumns) ? 'desc' : 'asc';
        }

        return [
            $this->columnMap[$column] ?? 'name',
            in_array($direction, ['asc', 'desc']) ? $direction : 'asc',
        ];
    }

    /**
     * Get status IDs by slugs with caching.
     */
    public function getStatusIdsBySlugs(array $slugs): array
    {
        if (empty($slugs)) {
            return [];
        }

        $sortedSlugs = $slugs;
        sort($sortedSlugs);
        $slugKey = implode(',', $sortedSlugs);

        $orgId = auth()->user()?->organization_id ?? 0;
        $cacheKey = "client_status_ids:{$orgId}:{$slugKey}";
        $ttl = config('erp.cache.client_status_ttl', 3600);

        return Cache::remember($cacheKey, $ttl, function () use ($slugs) {
            return $this->nomenclatureService->getClientStatuses()
                ->filter(fn($status) => in_array($status->slug, $slugs))
                ->pluck('id')
                ->toArray();
        });
    }

    /**
     * Get status counts for filter pills.
     */
    public function getStatusCounts(): array
    {
        $counts = Client::selectRaw('status_id, COUNT(*) as count')
            ->groupBy('status_id')
            ->pluck('count', 'status_id')
            ->toArray();

        return [
            'total' => array_sum($counts),
            'by_status' => $counts,
        ];
    }

    /**
     * Parse URL filters for initial server-side rendering.
     */
    public function parseUrlFilters(Request $request): array
    {
        return [
            'status' => $request->filled('status') ? explode(',', $request->status) : [],
            'q' => $request->get('q', ''),
            'sort' => $request->get('sort', 'activity:desc'),
            'page' => (int) $request->get('page', 1),
        ];
    }

    /**
     * Transform client model to array for API response.
     */
    public function transformClient(Client $client): array
    {
        return [
            'id' => $client->id,
            'name' => $client->name,
            'slug' => $client->slug ?? null,
            'email' => $client->email,
            'phone' => $client->phone,
            'contact_person' => $client->contact_person,
            'company_name' => $client->company_name,
            'tax_id' => $client->tax_id,
            'total_incomes' => $client->total_incomes,
            'currency' => $client->currency ?? 'RON',
            'invoices_count' => $client->invoices_count ?? 0,
            'status_id' => $client->status_id,
            'status' => $this->transformStatus($client->status),
            'created_at' => $client->created_at?->toISOString(),
            'updated_at' => $client->updated_at?->toISOString(),
        ];
    }

    /**
     * Transform status model to array.
     */
    protected function transformStatus($status): ?array
    {
        if (!$status) {
            return null;
        }

        return [
            'id' => $status->id,
            'name' => $status->name,
            'slug' => $status->slug,
            'color_class' => $status->color_class,
            'color_background' => $status->color_background,
            'color_text' => $status->color_text,
        ];
    }

    /**
     * Transform paginator to response array with transformed clients.
     */
    public function transformPaginatedResponse(LengthAwarePaginator $paginator): array
    {
        return [
            'clients' => $paginator->getCollection()->map(
                fn($client) => $this->transformClient($client)
            ),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'status_counts' => $this->getStatusCounts(),
        ];
    }

    /**
     * Get initial data for server-side rendering (avoids loading flash).
     */
    public function getInitialData(Request $request, Collection $statuses): array
    {
        $query = Client::with(['status'])
            ->withCount(['revenues as invoices_count']);

        // Apply status filter using pre-loaded statuses
        if ($request->filled('status')) {
            $statusSlugs = array_filter(explode(',', $request->status));
            if (!empty($statusSlugs)) {
                $statusIds = $statuses
                    ->filter(fn($status) => in_array($status->slug, $statusSlugs))
                    ->pluck('id')
                    ->toArray();

                if (!empty($statusIds)) {
                    $query->whereIn('status_id', $statusIds);
                }
            }
        }

        $this->applySearchFilter($query, $request);
        $this->applySorting($query, $request);

        $clients = $query->paginate(config('erp.pagination.default', 25));

        return [
            'clients' => $clients->map(fn($client) => $this->transformClient($client))->toArray(),
            'pagination' => [
                'total' => $clients->total(),
                'per_page' => $clients->perPage(),
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
                'from' => $clients->firstItem(),
                'to' => $clients->lastItem(),
            ],
            'status_counts' => $this->getStatusCounts(),
        ];
    }
}
