<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentFile extends Model
{
    use HasFactory;

    // Document categories
    const CATEGORY_OFFER = 'offer';
    const CATEGORY_CONTRACT = 'contract';
    const CATEGORY_ANNEX = 'annex';

    // Document types
    const TYPE_DRAFT = 'draft';
    const TYPE_SIGNED = 'signed';

    protected $fillable = [
        'uuid',
        'documentable_type',
        'documentable_id',
        'category',
        'document_type',
        'version',
        'is_active',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'file_hash',
        'organization_id',
        'created_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * Get the parent documentable model (Contract, ContractAnnex, or Offer)
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the organization that owns this document
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user who created this document
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Scope to get only active documents
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only inactive (archived) documents
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to get only draft documents
     */
    public function scopeDrafts($query)
    {
        return $query->where('document_type', self::TYPE_DRAFT);
    }

    /**
     * Scope to get only signed documents
     */
    public function scopeSigned($query)
    {
        return $query->where('document_type', self::TYPE_SIGNED);
    }

    /**
     * Scope to get documents for a specific category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get documents for a specific documentable
     */
    public function scopeForDocumentable($query, Model $documentable)
    {
        return $query->where('documentable_type', get_class($documentable))
                     ->where('documentable_id', $documentable->id);
    }

    /**
     * Scope to order by version descending
     */
    public function scopeLatestVersion($query)
    {
        return $query->orderBy('version', 'desc');
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Get the storage disk name
     */
    public function getDiskAttribute(): string
    {
        return config('filesystems.documents_disk', 'documents');
    }

    /**
     * Check if the file exists in storage
     */
    public function getExistsAttribute(): bool
    {
        return Storage::disk($this->disk)->exists($this->file_path);
    }

    /**
     * Get the full URL to the file (for private files, this returns a signed URL or route)
     */
    public function getUrlAttribute(): string
    {
        return route('documents.view', $this);
    }

    /**
     * Get the download URL
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('documents.download', $this);
    }

    /**
     * Get human-readable file size
     */
    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Alias for file_size_human (for backward compatibility)
     */
    public function getFileSizeFormattedAttribute(): string
    {
        return $this->file_size_human;
    }

    /**
     * Get display filename (for download)
     */
    public function getDisplayFilenameAttribute(): string
    {
        if ($this->original_filename) {
            return $this->original_filename;
        }

        // Generate a meaningful filename from the documentable
        $documentable = $this->documentable;
        $prefix = '';

        if ($documentable instanceof Contract) {
            $prefix = $documentable->contract_number;
        } elseif ($documentable instanceof ContractAnnex) {
            $prefix = $documentable->annex_code;
        } elseif ($documentable instanceof Offer) {
            $prefix = $documentable->offer_number;
        }

        $suffix = $this->document_type === self::TYPE_SIGNED ? '-signed' : '-draft';
        
        return $prefix . $suffix . '.pdf';
    }

    // =========================================================================
    // Methods
    // =========================================================================

    /**
     * Get the file contents
     */
    public function getContents(): ?string
    {
        if (!$this->exists) {
            return null;
        }

        return Storage::disk($this->disk)->get($this->file_path);
    }

    /**
     * Get a stream to the file
     */
    public function getStream()
    {
        if (!$this->exists) {
            return null;
        }

        return Storage::disk($this->disk)->readStream($this->file_path);
    }

    /**
     * Delete the physical file from storage
     */
    public function deleteFile(): bool
    {
        if ($this->exists) {
            return Storage::disk($this->disk)->delete($this->file_path);
        }

        return true;
    }

    /**
     * Deactivate this document (marks as not active)
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Activate this document
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Check if this is a draft document
     */
    public function isDraft(): bool
    {
        return $this->document_type === self::TYPE_DRAFT;
    }

    /**
     * Check if this is a signed document
     */
    public function isSigned(): bool
    {
        return $this->document_type === self::TYPE_SIGNED;
    }

    // =========================================================================
    // Static Helpers
    // =========================================================================

    /**
     * Generate a storage path for a new document
     * 
     * @param string $category The document category (contract, annex, offer)
     * @param string|null $filename The base filename (e.g., contract number). If null, uses UUID.
     * @param string|null $suffix Optional suffix (e.g., 'signed', 'v2')
     * @param \DateTimeInterface|null $date Date for year folder. Defaults to now.
     * @return string The full storage path
     */
    public static function generatePath(string $category, ?string $filename = null, ?string $suffix = null, ?\DateTimeInterface $date = null): string
    {
        $date = $date ?? now();
        $year = $date->format('Y');
        
        // Use provided filename or fall back to UUID
        $baseName = $filename ?? (string) Str::uuid();
        
        // Sanitize filename (remove special chars, keep alphanumeric and dashes)
        $baseName = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $baseName);
        
        // Add suffix if provided
        if ($suffix) {
            $baseName .= '-' . $suffix;
        }
        
        return "documents/{$category}/{$year}/{$baseName}.pdf";
    }

    /**
     * Get the category for a documentable model
     */
    public static function getCategoryForModel(Model $documentable): string
    {
        return match (get_class($documentable)) {
            Contract::class => self::CATEGORY_CONTRACT,
            ContractAnnex::class => self::CATEGORY_ANNEX,
            Offer::class => self::CATEGORY_OFFER,
            default => throw new \InvalidArgumentException('Unknown documentable type: ' . get_class($documentable)),
        };
    }

    /**
     * Get the next version number for a documentable and type
     */
    public static function getNextVersion(Model $documentable, string $documentType): int
    {
        $maxVersion = static::forDocumentable($documentable)
            ->where('document_type', $documentType)
            ->max('version');

        return ($maxVersion ?? 0) + 1;
    }
}
