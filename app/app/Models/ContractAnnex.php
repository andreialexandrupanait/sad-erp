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
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
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
     * Generate annex code
     */
    public static function generateAnnexCode(Contract $contract, int $number)
    {
        return sprintf('AN-%s-%d', $contract->contract_number, $number);
    }
}
