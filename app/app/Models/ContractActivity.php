<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Contract Activity - Audit trail for contract actions.
 *
 * Tracks all significant actions performed on contracts including:
 * - CRUD operations (created, updated, deleted)
 * - Status transitions (activated, terminated, completed, expired)
 * - Content changes (content_updated, template_applied)
 * - PDF generation (pdf_generated)
 * - Annex management (annex_added)
 */
class ContractActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'contract_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'metadata',
        'changes',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Boot function
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($activity) {
            $activity->created_at = $activity->created_at ?? now();
        });
    }

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
     * Get action label
     */
    public function getActionLabelAttribute()
    {
        $labels = [
            'created' => __('Created'),
            'updated' => __('Updated'),
            'content_updated' => __('Content updated'),
            'template_applied' => __('Template applied'),
            'activated' => __('Activated'),
            'terminated' => __('Terminated'),
            'completed' => __('Completed'),
            'expired' => __('Expired'),
            'renewed' => __('Renewed'),
            'finalized' => __('Finalized'),
            'pdf_generated' => __('PDF generated'),
            'annex_added' => __('Annex added'),
            'number_changed' => __('Number changed'),
            'deleted' => __('Deleted'),
            'restored' => __('Restored'),
        ];

        return $labels[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    /**
     * Get action icon (Heroicon name)
     */
    public function getActionIconAttribute()
    {
        $icons = [
            'created' => 'document-plus',
            'updated' => 'pencil',
            'content_updated' => 'document-text',
            'template_applied' => 'template',
            'activated' => 'check-badge',
            'terminated' => 'x-mark',
            'completed' => 'check-circle',
            'expired' => 'clock',
            'renewed' => 'arrow-path',
            'finalized' => 'lock-closed',
            'pdf_generated' => 'document-arrow-down',
            'annex_added' => 'document-duplicate',
            'number_changed' => 'hashtag',
            'deleted' => 'trash',
            'restored' => 'arrow-uturn-left',
        ];

        return $icons[$this->action] ?? 'information-circle';
    }

    /**
     * Get action color for UI
     */
    public function getActionColorAttribute()
    {
        $colors = [
            'created' => 'gray',
            'updated' => 'blue',
            'content_updated' => 'blue',
            'template_applied' => 'purple',
            'activated' => 'green',
            'terminated' => 'red',
            'completed' => 'green',
            'expired' => 'yellow',
            'renewed' => 'green',
            'finalized' => 'indigo',
            'pdf_generated' => 'blue',
            'annex_added' => 'purple',
            'number_changed' => 'orange',
            'deleted' => 'red',
            'restored' => 'green',
        ];

        return $colors[$this->action] ?? 'gray';
    }

    /**
     * Get performer name
     */
    public function getPerformerNameAttribute()
    {
        if ($this->user) {
            return $this->user->name;
        }

        return __('System');
    }

    /**
     * Get formatted description
     */
    public function getDescriptionAttribute()
    {
        $performer = $this->performer_name;
        $metadata = $this->metadata ?? [];

        switch ($this->action) {
            case 'created':
                return __(':name created the contract', ['name' => $performer]);

            case 'activated':
                return __(':name activated the contract', ['name' => $performer]);

            case 'terminated':
                $reason = $metadata['reason'] ?? null;
                return $reason
                    ? __(':name terminated the contract: :reason', ['name' => $performer, 'reason' => $reason])
                    : __(':name terminated the contract', ['name' => $performer]);

            case 'completed':
                return __(':name marked the contract as completed', ['name' => $performer]);

            case 'expired':
                return __('Contract expired');

            case 'renewed':
                $newNumber = $metadata['new_contract_number'] ?? null;
                return $newNumber
                    ? __(':name renewed the contract as :number', ['name' => $performer, 'number' => $newNumber])
                    : __(':name renewed the contract', ['name' => $performer]);

            case 'finalized':
                return __(':name finalized the contract', ['name' => $performer]);

            case 'pdf_generated':
                return __(':name generated the PDF', ['name' => $performer]);

            case 'template_applied':
                $templateName = $metadata['template_name'] ?? __('unknown');
                return __(':name applied template ":template"', ['name' => $performer, 'template' => $templateName]);

            case 'content_updated':
                return __(':name updated the contract content', ['name' => $performer]);

            case 'number_changed':
                $from = $metadata['from'] ?? '?';
                $to = $metadata['to'] ?? '?';
                return __(':name changed contract number from :from to :to', ['name' => $performer, 'from' => $from, 'to' => $to]);

            case 'annex_added':
                $annexCode = $metadata['annex_code'] ?? null;
                return $annexCode
                    ? __(':name added annex :code', ['name' => $performer, 'code' => $annexCode])
                    : __(':name added an annex', ['name' => $performer]);

            default:
                return __(':name :action', ['name' => $performer, 'action' => strtolower($this->action_label)]);
        }
    }

    /**
     * Scope to get recent activities.
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope to filter by action type.
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Log a contract activity.
     */
    public static function log(
        Contract $contract,
        string $action,
        ?array $metadata = null,
        ?array $changes = null,
        ?int $userId = null
    ): self {
        return static::create([
            'contract_id' => $contract->id,
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
            'changes' => $changes,
        ]);
    }
}
