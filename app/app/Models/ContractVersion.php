<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Contract Version - Content versioning for contracts.
 *
 * Stores snapshots of contract content to enable:
 * - Viewing historical versions
 * - Comparing changes between versions
 * - Restoring previous versions
 */
class ContractVersion extends Model
{
    protected $fillable = [
        'contract_id',
        'user_id',
        'version_number',
        'content',
        'blocks',
        'reason',
        'content_hash',
    ];

    protected $casts = [
        'blocks' => 'array',
    ];

    /**
     * Relationships
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the author name.
     */
    public function getAuthorNameAttribute(): string
    {
        return $this->user?->name ?? __('System');
    }

    /**
     * Get a short preview of the content.
     */
    public function getContentPreviewAttribute(): string
    {
        $text = strip_tags($this->content ?? '');
        return mb_substr($text, 0, 200) . (mb_strlen($text) > 200 ? '...' : '');
    }

    /**
     * Check if this is the current version.
     */
    public function isCurrent(): bool
    {
        return $this->version_number === $this->contract->current_version;
    }

    /**
     * Create a new version for a contract.
     *
     * @param Contract $contract The contract to version
     * @param string|null $reason Optional reason for the new version
     * @param int|null $userId The user creating the version
     * @return static|null The new version, or null if content hasn't changed
     */
    public static function createVersion(
        Contract $contract,
        ?string $reason = null,
        ?int $userId = null
    ): ?self {
        $contentHash = hash('sha256', $contract->content ?? '');

        // Check if content has actually changed from the latest version
        $latestVersion = static::where('contract_id', $contract->id)
            ->orderBy('version_number', 'desc')
            ->first();

        if ($latestVersion && $latestVersion->content_hash === $contentHash) {
            // Content hasn't changed, don't create duplicate version
            return null;
        }

        // Get next version number
        $nextVersion = ($latestVersion?->version_number ?? 0) + 1;

        $version = static::create([
            'contract_id' => $contract->id,
            'user_id' => $userId ?? auth()->id(),
            'version_number' => $nextVersion,
            'content' => $contract->content,
            'blocks' => $contract->blocks,
            'reason' => $reason,
            'content_hash' => $contentHash,
        ]);

        // Update contract's current version number
        $contract->update(['current_version' => $nextVersion]);

        return $version;
    }

    /**
     * Restore this version to the contract.
     *
     * @return Contract The updated contract
     */
    public function restore(): Contract
    {
        $contract = $this->contract;

        $contract->update([
            'content' => $this->content,
            'blocks' => $this->blocks,
        ]);

        // Create a new version marking the restoration
        self::createVersion(
            $contract,
            __('Restored from version :version', ['version' => $this->version_number])
        );

        // Log the restoration
        ContractActivity::log($contract, 'version_restored', [
            'restored_from_version' => $this->version_number,
            'restored_from_date' => $this->created_at->toISOString(),
        ]);

        return $contract->fresh();
    }

    /**
     * Compare this version with another version.
     *
     * @param ContractVersion $other The version to compare with
     * @return array Diff information
     */
    public function compareWith(ContractVersion $other): array
    {
        return [
            'version_a' => $this->version_number,
            'version_b' => $other->version_number,
            'content_changed' => $this->content_hash !== $other->content_hash,
            'blocks_changed' => $this->blocks !== $other->blocks,
            'author_a' => $this->author_name,
            'author_b' => $other->author_name,
            'date_a' => $this->created_at,
            'date_b' => $other->created_at,
        ];
    }

    /**
     * Scope to get versions in order.
     */
    public function scopeOrdered($query, string $direction = 'desc')
    {
        return $query->orderBy('version_number', $direction);
    }
}
