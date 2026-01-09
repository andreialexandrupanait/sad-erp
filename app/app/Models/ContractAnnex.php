<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractAnnex extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_id',
        'offer_id',
        'template_id',
        'annex_number',
        'annex_code',
        'title',
        'content',
        'effective_date',
        'additional_value',
        'currency',
        'pdf_path',
        'active_draft_file_id',
        'active_signed_file_id',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'additional_value' => 'decimal:2',
        'annex_number' => 'integer',
    ];

    /**
     * Relationships
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function template()
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    /**
     * Document files (versioned PDFs) relationship.
     */
    public function documentFiles()
    {
        return $this->morphMany(DocumentFile::class, 'documentable');
    }

    /**
     * Active draft file relationship.
     */
    public function activeDraftFile()
    {
        return $this->belongsTo(DocumentFile::class, 'active_draft_file_id');
    }

    /**
     * Active signed file relationship.
     */
    public function activeSignedFile()
    {
        return $this->belongsTo(DocumentFile::class, 'active_signed_file_id');
    }

    /**
     * Get all draft document versions.
     */
    public function getDraftVersions()
    {
        return $this->documentFiles()
            ->where('document_type', DocumentFile::TYPE_DRAFT)
            ->orderBy('version', 'desc')
            ->get();
    }

    /**
     * Get all signed document versions.
     */
    public function getSignedVersions()
    {
        return $this->documentFiles()
            ->where('document_type', DocumentFile::TYPE_SIGNED)
            ->orderBy('version', 'desc')
            ->get();
    }

    /**
     * Check if annex has an active draft document.
     */
    public function hasActiveDraft(): bool
    {
        return $this->active_draft_file_id !== null;
    }

    /**
     * Check if annex has an active signed document.
     */
    public function hasActiveSigned(): bool
    {
        return $this->active_signed_file_id !== null;
    }

    /**
     * Get the client through contract
     */
    public function getClientAttribute()
    {
        return $this->contract->client;
    }

    /**
     * Get display title
     */
    public function getDisplayTitleAttribute()
    {
        return sprintf('%s - %s', $this->annex_code, $this->title);
    }

    /**
     * Get the annex code formatted for filenames (spaces replaced with dashes).
     */
    public function getFilenameCodeAttribute(): string
    {
        return str_replace(' ', '-', $this->annex_code);
    }

    /**
     * Generate annex code
     */
    public static function generateAnnexCode(Contract $contract, int $number)
    {
        return sprintf('AN-%s-%d', $contract->contract_number, $number);
    }
}
