<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'documentable_type',
        'documentable_id',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'file_size' => 'integer',
    ];

    /**
     * Document types.
     */
    public const TYPE_OFFER_SENT = 'offer_sent';
    public const TYPE_OFFER_ACCEPTED = 'offer_accepted';
    public const TYPE_CONTRACT = 'contract';
    public const TYPE_ANNEX = 'annex';

    /**
     * Boot function - auto-scope by organization.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            if (auth()->check()) {
                if (empty($document->organization_id)) {
                    $document->organization_id = auth()->user()->organization_id;
                }
                if (empty($document->generated_by)) {
                    $document->generated_by = auth()->id();
                }
            }
            if (empty($document->generated_at)) {
                $document->generated_at = now();
            }
        });

        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('documents.organization_id', auth()->user()->organization_id);
            }
        });
    }

    /**
     * Relationships
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Scopes
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForOffer(Builder $query, int $offerId): Builder
    {
        return $query->where('documentable_type', Offer::class)
            ->where('documentable_id', $offerId);
    }

    public function scopeForContract(Builder $query, int $contractId): Builder
    {
        return $query->where('documentable_type', Contract::class)
            ->where('documentable_id', $contractId);
    }

    /**
     * Get the full storage path.
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->file_path);
    }

    /**
     * Check if file exists.
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Get file contents.
     */
    public function getContents(): ?string
    {
        if (!$this->fileExists()) {
            return null;
        }

        return Storage::get($this->file_path);
    }

    /**
     * Get download URL (temporary signed URL for private storage).
     */
    public function getDownloadUrl(int $expirationMinutes = 60): ?string
    {
        if (!$this->fileExists()) {
            return null;
        }

        // For local disk, return a route
        return route('documents.download', $this);
    }

    /**
     * Get human-readable file size.
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size ?? 0;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            self::TYPE_OFFER_SENT => __('Offer (Sent)'),
            self::TYPE_OFFER_ACCEPTED => __('Offer (Accepted)'),
            self::TYPE_CONTRACT => __('Contract'),
            self::TYPE_ANNEX => __('Annex'),
        ];

        return $labels[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Generate storage path for a document.
     */
    public static function generatePath(string $type, int $organizationId, string $number, int $year = null): string
    {
        $year = $year ?? date('Y');

        $typeFolder = match ($type) {
            self::TYPE_OFFER_SENT, self::TYPE_OFFER_ACCEPTED => 'offers',
            self::TYPE_CONTRACT => 'contracts',
            self::TYPE_ANNEX => 'annexes',
            default => 'documents',
        };

        return "documents/{$typeFolder}/{$year}/{$number}.pdf";
    }

    /**
     * Delete the document and its file.
     */
    public function deleteWithFile(): bool
    {
        if ($this->fileExists()) {
            Storage::delete($this->file_path);
        }

        return $this->delete();
    }
}
