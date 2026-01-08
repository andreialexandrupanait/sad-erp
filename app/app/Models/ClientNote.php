<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class ClientNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'user_id',
        'content',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    /**
     * Keywords that trigger auto-tagging
     */
    protected static array $tagKeywords = [
        'contract' => ['contract', 'contractul', 'semnare', 'semnat'],
        'factura' => ['factura', 'facturi', 'facturat', 'facturare', 'invoice'],
        'grafica' => ['grafica', 'design', 'logo', 'brand', 'adobe', 'xd', 'figma'],
        'website' => ['website', 'site', 'web', 'pagina', 'landing'],
        'mentenanta' => ['mentenanta', 'maintenance', 'update', 'actualizare', 'backup'],
        'hosting' => ['hosting', 'server', 'gazduire', 'domeniu', 'dns'],
        'email' => ['email', 'mailerlite', 'newsletter', 'smtp'],
        'seo' => ['seo', 'google', 'analytics', 'optimizare'],
        'ads' => ['ads', 'facebook', 'tiktok', 'campanie', 'promovare', 'reclama'],
        'plata' => ['plata', 'platit', 'incasat', 'bani', 'cost', 'pret', 'euro', 'lei', 'ron'],
        'urgenta' => ['urgent', 'urgenta', 'asap', 'rapid', 'imediat'],
        'oferta' => ['oferta', 'propunere', 'estimare', 'cotatie'],
        'modificare' => ['modificare', 'schimbare', 'update', 'ajustare'],
        'finalizat' => ['finalizat', 'gata', 'terminat', 'complet', 'livrat'],
        'asteptare' => ['astept', 'asteptam', 'confirmare', 'feedback', 'raspuns'],
    ];

    /**
     * Boot function to automatically scope by organization
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set organization_id and user_id when creating
        static::creating(function ($note) {
            if (auth()->check()) {
                if (empty($note->organization_id)) {
                    $note->organization_id = auth()->user()->organization_id;
                }
                if (empty($note->user_id)) {
                    $note->user_id = auth()->id();
                }
            }

            // Auto-extract tags from content
            if (empty($note->tags) && !empty($note->content)) {
                $note->tags = static::extractTags($note->content);
            }
        });

        // Re-extract tags on update if content changed
        static::updating(function ($note) {
            if ($note->isDirty('content')) {
                $note->tags = static::extractTags($note->content);
            }
        });

        // Automatically scope all queries by organization (RLS)
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where('client_notes.organization_id', auth()->user()->organization_id);
            }
        });
    }

    /**
     * Extract tags from content based on keywords
     */
    public static function extractTags(string $content): array
    {
        // Strip HTML tags before searching for keywords
        $content = strip_tags($content);
        $content = mb_strtolower($content);
        $foundTags = [];

        foreach (static::$tagKeywords as $tag => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($content, $keyword) !== false) {
                    $foundTags[] = $tag;
                    break; // Found this tag, move to next
                }
            }
        }

        return array_unique($foundTags);
    }

    /**
     * Get all available tag keywords for reference
     */
    public static function getAvailableTags(): array
    {
        return array_keys(static::$tagKeywords);
    }

    /**
     * Get the organization that owns the note
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the client this note belongs to
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user who created the note
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by client
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope to filter by tag
     */
    public function scopeWithTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Scope to filter by multiple tags (any match)
     */
    public function scopeWithAnyTags($query, array $tags)
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    /**
     * Scope to search notes by content
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('content', 'like', "%{$search}%");
    }

    /**
     * Scope to order by most recent first
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        return $query;
    }

    /**
     * Get a preview of the content (first 150 characters, stripped of HTML)
     */
    public function getPreviewAttribute(): string
    {
        $text = strip_tags($this->content);
        return mb_strlen($text) > 150
            ? mb_substr($text, 0, 150) . '...'
            : $text;
    }

    /**
     * Check if note has a specific tag
     */
    public function hasTag(string $tag): bool
    {
        return is_array($this->tags) && in_array($tag, $this->tags);
    }
}
