<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateVersion extends Model
{
    /**
     * Disable default timestamps - we only track created_at
     */
    public $timestamps = false;

    protected $fillable = [
        'template_id',
        'version_number',
        'blocks',
        'theme',
        'content_hash',
        'reason',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'blocks' => 'array',
        'theme' => 'array',
        'version_number' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Boot function.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $version) {
            if (empty($version->created_at)) {
                $version->created_at = now();
            }

            if (auth()->check() && empty($version->created_by)) {
                $version->created_by = auth()->id();
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Get a human-readable label for this version.
     */
    public function getLabel(): string
    {
        $label = "v{$this->version_number}";

        if ($this->reason) {
            $label .= " - {$this->reason}";
        }

        return $label;
    }

    /**
     * Get formatted creation date.
     */
    public function getFormattedDate(): string
    {
        return $this->created_at->format('d.m.Y H:i');
    }

    /**
     * Compare this version with another and return differences.
     */
    public function diffWith(TemplateVersion $other): array
    {
        return [
            'blocks_changed' => $this->content_hash !== $other->content_hash,
            'theme_changed' => json_encode($this->theme) !== json_encode($other->theme),
        ];
    }

    /**
     * Check if this is the current active version.
     */
    public function isCurrent(): bool
    {
        return $this->template->current_version === $this->version_number + 1;
    }
}
