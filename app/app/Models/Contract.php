<?php

namespace App\Models;

use App\Services\Contract\ContractVariableRegistry;
use App\Services\HtmlSanitizerService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'parent_contract_id',
        'client_id',
        'offer_id',
        'template_id',
        'contract_template_id',
        'contract_number',
        'title',
        'content',
        'blocks',
        'editor_settings',
        'status',
        'start_date',
        'end_date',
        'auto_renew',
        'total_value',
        'currency',
        'pdf_path',
        // Temp client fields for contracts without a linked client
        'temp_client_name',
        'temp_client_email',
        'temp_client_company',
        'is_finalized',
        'finalized_at',
        // Editing lock fields
        'locked_by',
        'locked_at',
        'lock_version',
        'current_version',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renew' => 'boolean',
        'total_value' => 'decimal:2',
        'blocks' => 'array',
        'editor_settings' => 'array',
        'is_finalized' => 'boolean',
        'finalized_at' => 'datetime',
        'locked_at' => 'datetime',
        'lock_version' => 'integer',
        'current_version' => 'integer',
    ];

    /**
     * Boot function - auto-scope and auto-generate fields
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contract) {
            if (auth()->check() && empty($contract->organization_id)) {
                $contract->organization_id = auth()->user()->organization_id;
            }

            // Generate contract number if not provided
            if (empty($contract->contract_number)) {
                $contract->contract_number = static::generateContractNumber($contract->organization_id);
            }
        });

        // Sanitize HTML content before saving to prevent XSS attacks
        static::saving(function ($contract) {
            $sanitizer = app(HtmlSanitizerService::class);

            // Sanitize content field that contains HTML
            if ($contract->isDirty('content')) {
                $contract->content = $sanitizer->sanitize($contract->content);
            }

            if ($contract->isDirty('title')) {
                $contract->title = $sanitizer->sanitize($contract->title);
            }

            // Sanitize blocks content if it contains HTML
            if ($contract->isDirty('blocks') && is_array($contract->blocks)) {
                $blocks = $contract->blocks;
                foreach ($blocks as &$block) {
                    if (isset($block['data']['content'])) {
                        $block['data']['content'] = $sanitizer->sanitize($block['data']['content']);
                    }
                    if (isset($block['data']['title'])) {
                        $block['data']['title'] = $sanitizer->sanitize($block['data']['title']);
                    }
                    if (isset($block['data']['text'])) {
                        $block['data']['text'] = $sanitizer->sanitize($block['data']['text']);
                    }
                }
                $contract->blocks = $blocks;
            }

            // Sanitize temp client fields (plain text, no HTML allowed)
            $tempClientFields = [
                'temp_client_name',
                'temp_client_email',
                'temp_client_company',
            ];

            foreach ($tempClientFields as $field) {
                if ($contract->isDirty($field) && !empty($contract->$field)) {
                    // Strip all HTML tags and encode special characters
                    $contract->$field = htmlspecialchars(strip_tags($contract->$field), ENT_QUOTES, 'UTF-8');
                }
            }
        });

        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('contracts.organization_id', auth()->user()->organization_id);
            }
        });
    }

    /**
     * Generate unique contract number with database locking to prevent race conditions.
     *
     * Uses SELECT ... FOR UPDATE to lock the row during number generation,
     * ensuring no two concurrent requests can generate the same number.
     */
    public static function generateContractNumber($organizationId = null)
    {
        $organizationId = $organizationId ?? (auth()->check() ? auth()->user()->organization_id : 1);
        $year = date('Y');
        $prefix = 'CTR';

        // Get organization prefix if available
        $org = Organization::find($organizationId);
        if ($org && $org->code) {
            $prefix = 'CTR-' . $org->code;
        }

        // Use database lock to prevent race condition
        // lockForUpdate() acquires an exclusive lock until the transaction commits
        // Get all contracts and find the max number in PHP (database-agnostic)
        $contracts = static::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->whereYear('created_at', $year)
            ->lockForUpdate()
            ->pluck('contract_number');

        $maxNumber = 0;
        foreach ($contracts as $contractNumber) {
            if (preg_match('/-(\d+)$/', $contractNumber, $matches)) {
                $number = intval($matches[1]);
                $maxNumber = max($maxNumber, $number);
            }
        }
        $nextNumber = $maxNumber + 1;

        return sprintf('%s-%d-%02d', $prefix, $year, $nextNumber);
    }

    /**
     * Parent contract relationship for renewals.
     */
    public function parentContract()
    {
        return $this->belongsTo(Contract::class, 'parent_contract_id');
    }

    /**
     * Child contracts (renewals) relationship.
     */
    public function renewals()
    {
        return $this->hasMany(Contract::class, 'parent_contract_id');
    }

    /**
     * Relationships
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * Legacy template relationship.
     *
     * @deprecated Use contractTemplate() instead. DocumentTemplate is being phased out
     *             in favor of ContractTemplate which provides better variable support.
     */
    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    public function contractTemplate()
    {
        return $this->belongsTo(ContractTemplate::class);
    }

    public function annexes()
    {
        return $this->hasMany(ContractAnnex::class)->orderBy('annex_number');
    }

    public function additionalOffers()
    {
        return $this->hasMany(Offer::class);
    }

    public function items()
    {
        return $this->hasMany(ContractItem::class)->orderBy('sort_order');
    }

    /**
     * Activity log relationship.
     */
    public function activities()
    {
        return $this->hasMany(ContractActivity::class)->orderBy('created_at', 'desc');
    }

    /**
     * Content versions relationship.
     */
    public function versions()
    {
        return $this->hasMany(ContractVersion::class)->orderBy('version_number', 'desc');
    }

    /**
     * User who has the editing lock.
     */
    public function lockedByUser()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Check if contract is currently locked for editing.
     */
    public function isLocked(): bool
    {
        if (!$this->locked_by || !$this->locked_at) {
            return false;
        }

        // Lock expires after 15 minutes of inactivity
        return $this->locked_at->diffInMinutes(now()) < 15;
    }

    /**
     * Check if current user can edit this contract.
     */
    public function canEdit(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        // If finalized, no one can edit
        if ($this->is_finalized) {
            return false;
        }

        // If not locked, anyone can edit
        if (!$this->isLocked()) {
            return true;
        }

        // Only the user who locked it can edit
        return $this->locked_by === $userId;
    }

    /**
     * Acquire editing lock for a user with database-level locking.
     *
     * Uses SELECT ... FOR UPDATE to prevent race conditions where multiple
     * users could acquire the lock simultaneously.
     *
     * @param int|null $userId The user ID to acquire lock for
     * @return bool True if lock acquired, false if already locked by another user
     */
    public function acquireLock(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        return \DB::transaction(function () use ($userId) {
            // Lock the row to prevent race conditions
            $contract = static::withoutGlobalScopes()
                ->where('id', $this->id)
                ->lockForUpdate()
                ->first();

            if (!$contract) {
                return false;
            }

            // Check if locked by another user (with fresh data)
            if ($contract->isLocked() && $contract->locked_by !== $userId) {
                return false;
            }

            // Acquire the lock
            $contract->update([
                'locked_by' => $userId,
                'locked_at' => now(),
            ]);

            // Refresh this instance with the new lock data
            $this->refresh();

            return true;
        });
    }

    /**
     * Save contract with optimistic locking version check.
     *
     * Prevents concurrent edits by checking if the lock_version matches
     * what was loaded. If another user saved in between, this will fail.
     *
     * @param array $data The data to update
     * @return bool True if saved successfully
     * @throws \App\Exceptions\ConcurrentModificationException If version mismatch detected
     */
    public function saveWithVersionCheck(array $data = []): bool
    {
        return \DB::transaction(function () use ($data) {
            // Get the current state with a lock
            $current = static::withoutGlobalScopes()
                ->where('id', $this->id)
                ->lockForUpdate()
                ->first();

            if (!$current) {
                throw new \RuntimeException('Contract not found');
            }

            // Check for concurrent modification
            $expectedVersion = $this->lock_version ?? 1;
            if ($current->lock_version !== $expectedVersion) {
                \Log::warning('Concurrent modification detected on contract', [
                    'contract_id' => $this->id,
                    'expected_version' => $expectedVersion,
                    'current_version' => $current->lock_version,
                    'user_id' => auth()->id(),
                ]);

                throw new \App\Exceptions\ConcurrentModificationException(
                    __('This contract was modified by another user. Please refresh and try again.')
                );
            }

            // Increment version and save
            $data['lock_version'] = ($current->lock_version ?? 1) + 1;

            $current->fill($data);
            $result = $current->save();

            // Refresh this instance
            $this->refresh();

            return $result;
        });
    }

    /**
     * Release editing lock.
     *
     * @param int|null $userId Only release if locked by this user (null = force release)
     * @return bool True if released
     */
    public function releaseLock(?int $userId = null): bool
    {
        // If user specified, only release if they hold the lock
        if ($userId !== null && $this->locked_by !== $userId) {
            return false;
        }

        $this->update([
            'locked_by' => null,
            'locked_at' => null,
        ]);

        return true;
    }

    /**
     * Refresh the lock (extend expiration).
     *
     * @param int|null $userId Only refresh if locked by this user
     * @return bool True if refreshed
     */
    public function refreshLock(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        if ($this->locked_by !== $userId) {
            return false;
        }

        $this->update(['locked_at' => now()]);

        return true;
    }

    /**
     * Get lock status information.
     */
    public function getLockStatus(): array
    {
        if (!$this->isLocked()) {
            return [
                'locked' => false,
                'can_edit' => !$this->is_finalized,
            ];
        }

        return [
            'locked' => true,
            'locked_by' => $this->locked_by,
            'locked_by_name' => $this->lockedByUser?->name ?? __('Unknown'),
            'locked_at' => $this->locked_at->toISOString(),
            'expires_at' => $this->locked_at->addMinutes(15)->toISOString(),
            'can_edit' => $this->locked_by === auth()->id(),
        ];
    }

    /**
     * Scopes
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            // FULLTEXT search on indexed columns (title, content)
            $q->whereRaw('MATCH(title, content) AGAINST(? IN BOOLEAN MODE)', [$search])
              // LIKE search for contract_number (exact match important)
              ->orWhere('contract_number', 'like', "%{$search}%")
              // Search in related client using FULLTEXT if available
              ->orWhereHas('client', function ($q) use ($search) {
                  $q->whereRaw('MATCH(name, company_name, email) AGAINST(? IN BOOLEAN MODE)', [$search]);
              });
        });
    }

    public function scopeExpiringBetween($query, $startDate, $endDate)
    {
        return $query->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [$startDate, $endDate]);
    }

    /**
     * Valid status values.
     */
    public const STATUSES = ['draft', 'active', 'completed', 'terminated', 'expired'];

    /**
     * Allowed status transitions.
     * Key = current status, Value = array of allowed target statuses.
     */
    public const STATUS_TRANSITIONS = [
        'draft' => ['active', 'terminated'],
        'active' => ['completed', 'terminated', 'expired'],
        'completed' => [], // Terminal state
        'terminated' => [], // Terminal state
        'expired' => ['active'], // Can be renewed/reactivated
    ];

    /**
     * Status helpers
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isTerminal()
    {
        return in_array($this->status, ['completed', 'terminated']);
    }

    public function isExpired()
    {
        return $this->status === 'expired' ||
               ($this->end_date && $this->end_date < now());
    }

    public function isIndefinite()
    {
        return is_null($this->end_date);
    }

    /**
     * Check if a status transition is allowed.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        if (!in_array($newStatus, self::STATUSES)) {
            return false;
        }

        $allowedTransitions = self::STATUS_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowedTransitions);
    }

    /**
     * Get allowed transitions from current status.
     */
    public function getAllowedTransitions(): array
    {
        return self::STATUS_TRANSITIONS[$this->status] ?? [];
    }

    /**
     * Transition to a new status with validation.
     *
     * @throws \InvalidArgumentException If transition is not allowed
     */
    public function transitionTo(string $newStatus): self
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException(
                __('Cannot transition contract from :from to :to', [
                    'from' => $this->status,
                    'to' => $newStatus,
                ])
            );
        }

        $this->status = $newStatus;
        $this->save();

        return $this;
    }

    /**
     * Activate a draft contract.
     *
     * @throws \InvalidArgumentException If transition is not allowed
     */
    public function activate(): self
    {
        return $this->transitionTo('active');
    }

    /**
     * Complete an active contract.
     *
     * @throws \InvalidArgumentException If transition is not allowed
     */
    public function complete(): self
    {
        return $this->transitionTo('completed');
    }

    /**
     * Mark contract as expired.
     *
     * @throws \InvalidArgumentException If transition is not allowed
     */
    public function markExpired(): self
    {
        return $this->transitionTo('expired');
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => __('Draft'),
            'active' => __('Active'),
            'completed' => __('Completed'),
            'terminated' => __('Terminated'),
            'expired' => __('Expired'),
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'draft' => 'gray',
            'active' => 'green',
            'completed' => 'blue',
            'terminated' => 'red',
            'expired' => 'yellow',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->end_date) {
            return null; // Indefinite
        }

        return now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false);
    }

    /**
     * Get expiry urgency
     */
    public function getExpiryUrgencyAttribute()
    {
        $days = $this->days_until_expiry;

        if ($days === null) return 'indefinite';
        if ($days < 0) return 'expired';
        if ($days <= 30) return 'urgent';
        if ($days <= 60) return 'warning';
        return 'normal';
    }

    /**
     * Get total contract value including annexes
     */
    public function getTotalValueWithAnnexesAttribute()
    {
        return $this->total_value + $this->annexes()->sum('additional_value');
    }

    /**
     * Add an annex from an accepted offer
     */
    public function addAnnexFromOffer(Offer $offer)
    {
        if (!$offer->isAccepted()) {
            throw new \Exception(__('Only accepted offers can be added as annexes.'));
        }

        $nextNumber = $this->annexes()->max('annex_number') + 1;

        $annex = $this->annexes()->create([
            'offer_id' => $offer->id,
            'template_id' => null,
            'annex_number' => $nextNumber,
            'annex_code' => sprintf('AN-%s-%d', $this->contract_number, $nextNumber),
            'title' => $offer->title,
            'content' => '', // Will be filled from template
            'effective_date' => now(),
            'additional_value' => $offer->total,
            'currency' => $offer->currency,
        ]);

        // Link offer to this contract
        $offer->contract_id = $this->id;
        $offer->save();

        return $annex;
    }

    /**
     * Terminate the contract.
     *
     * @throws \InvalidArgumentException If transition is not allowed
     */
    public function terminate(): self
    {
        return $this->transitionTo('terminated');
    }

    /**
     * Get statistics
     */
    public static function getStatistics()
    {
        $counts = self::selectRaw("
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_count,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN status = 'terminated' THEN 1 ELSE 0 END) as terminated_count,
            SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired_count
        ")->first();

        $values = self::where('status', 'active')
            ->selectRaw('SUM(total_value) as total_active_value')
            ->first();

        $expiringSoon = self::expiringBetween(now(), now()->addDays(30))->count();

        return [
            'draft' => (int) ($counts->draft_count ?? 0),
            'active' => (int) ($counts->active_count ?? 0),
            'completed' => (int) ($counts->completed_count ?? 0),
            'terminated' => (int) ($counts->terminated_count ?? 0),
            'expired' => (int) ($counts->expired_count ?? 0),
            'total_active_value' => (float) ($values->total_active_value ?? 0),
            'expiring_soon' => $expiringSoon,
        ];
    }

    /**
     * Get content with variables replaced by actual values.
     * Uses ContractVariableRegistry for consistent {{variable}} format.
     */
    public function getRenderedContentAttribute()
    {
        $content = $this->content ?? '';

        if (empty($content)) {
            return '';
        }

        // Load relationships if not already loaded
        if (!$this->relationLoaded('client')) {
            $this->load(['client', 'offer.items', 'items', 'organization']);
        }

        // Use centralized registry for variable replacement
        return ContractVariableRegistry::render($content, $this);
    }

    /**
     * Get content rendered for PDF export.
     * Strips blue variable styling since dompdf doesn't support complex CSS selectors.
     */
    public function getPdfContentAttribute(): string
    {
        $content = $this->rendered_content;

        if (empty($content)) {
            return '';
        }

        // Remove blue variable styling (dompdf doesn't support [style*=""] selectors)
        // Strategy: Process each style attribute and clean up blue colors

        // Pattern to match style attributes
        $content = preg_replace_callback(
            '/style="([^"]*)"/i',
            function ($matches) {
                $style = $matches[1];

                // First, remove light blue backgrounds completely (must be done before color replacement)
                $style = preg_replace(
                    '/background-color:\s*(?:rgb\(219,\s*234,\s*254\)|#dbeafe);?\s*/i',
                    '',
                    $style
                );

                // Replace blue text color with black (only match "color:" not "background-color:")
                // Use word boundary to ensure we match "color:" but not "background-color:"
                $style = preg_replace(
                    '/(?<![a-z-])color:\s*(?:rgb\(30,\s*64,\s*175\)|#1e40af)/i',
                    'color: #000000',
                    $style
                );

                // Replace light blue used as text color (wrong usage) with black
                $style = preg_replace(
                    '/(?<![a-z-])color:\s*rgb\(219,\s*234,\s*254\)/i',
                    'color: #000000',
                    $style
                );

                // Clean up any double semicolons or trailing semicolons
                $style = preg_replace('/;\s*;/', ';', $style);
                $style = trim($style, '; ');

                return 'style="' . $style . '"';
            },
            $content
        );

        return $content;
    }

    /**
     * Get variable values for this contract.
     * Delegates to ContractVariableRegistry for consistency.
     */
    public function getVariableValues(): array
    {
        return ContractVariableRegistry::resolve($this);
    }

    /**
     * Validate contract content before PDF generation.
     */
    public function validateForPdf(): array
    {
        return ContractVariableRegistry::validateContent($this->content ?? '', $this);
    }

    /**
     * Get warnings (non-blocking issues) for this contract.
     */
    public function getWarnings(): array
    {
        return ContractVariableRegistry::getWarnings($this);
    }

    /**
     * Check if contract number can be edited.
     * Contract number is locked after finalization or PDF generation.
     */
    public function canEditContractNumber(): bool
    {
        return !$this->is_finalized && empty($this->pdf_path);
    }

    /**
     * Check if contract number is unique within organization.
     */
    public static function isContractNumberUnique(string $number, int $orgId, ?int $excludeId = null): bool
    {
        $query = static::withoutGlobalScopes()
            ->where('organization_id', $orgId)
            ->where('contract_number', $number);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }
}
