<?php

namespace App\Models;

use App\Traits\EncryptsPasswords;
use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Credential extends Model
{
    use HasFactory, SoftDeletes, EncryptsPasswords, HasOrganization;

    protected $table = 'access_credentials';

    protected $fillable = [
        'organization_id',
        'client_id',
        'site_name',
        'platform',
        'credential_type',
        'username',
        'password',
        'url',
        'website',
        'notes',
        'last_accessed_at',
        'access_count',
    ];

    protected $casts = [
        'last_accessed_at' => 'datetime',
        'access_count' => 'integer',
    ];

    // Credential type options for categorization
    public const CREDENTIAL_TYPES = [
        'admin-panel' => 'Admin Panel',
        'database' => 'Database',
        'hosting' => 'Hosting',
        'marketing' => 'Marketing',
        'other' => 'Other',
    ];

    // Platform options
    public const PLATFORMS = [
        'Facebook' => 'Facebook',
        'Google Ads' => 'Google Ads',
        'Google Analytics' => 'Google Analytics',
        'WordPress' => 'WordPress',
        'LinkedIn' => 'LinkedIn',
        'Instagram' => 'Instagram',
        'Twitter/X' => 'Twitter/X',
        'TikTok' => 'TikTok',
        'YouTube' => 'YouTube',
        'Mailchimp' => 'Mailchimp',
        'AWS' => 'AWS',
        'Azure' => 'Azure',
        'cPanel' => 'cPanel',
        'FTP' => 'FTP',
        'SSH' => 'SSH',
        'Other' => 'Other',
    ];

    // Organization scoping handled by HasOrganization trait
    // Password encryption handled by EncryptsPasswords trait

    /**
     * Relationships
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Password encryption/decryption handled by EncryptsPasswords trait

    /**
     * Search scope - optimized for the fulltext index if available
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('site_name', 'like', "%{$search}%")
              ->orWhere('platform', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('url', 'like', "%{$search}%")
              ->orWhereHas('client', function ($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Filter by platform
     */
    public function scopePlatform($query, $platform)
    {
        if (!empty($platform)) {
            return $query->where('platform', $platform);
        }
        return $query;
    }

    /**
     * Filter by client
     */
    public function scopeClient($query, $clientId)
    {
        if (!empty($clientId)) {
            return $query->where('client_id', $clientId);
        }
        return $query;
    }

    /**
     * Filter by credential type
     */
    public function scopeOfType($query, $type)
    {
        if (!empty($type)) {
            return $query->where('credential_type', $type);
        }
        return $query;
    }

    /**
     * Filter by site name
     */
    public function scopeSite($query, $siteName)
    {
        if (!empty($siteName)) {
            return $query->where('site_name', $siteName);
        }
        return $query;
    }

    /**
     * Get credentials grouped by client and site for accordion view
     */
    public static function getGroupedByClientAndSite($query = null)
    {
        $query = $query ?? static::query();

        return $query->with('client')
            ->orderBy('client_id')
            ->orderByRaw('COALESCE(site_name, "") ASC')
            ->orderBy('credential_type')
            ->get()
            ->groupBy('client_id')
            ->map(function ($clientCredentials) {
                return $clientCredentials->groupBy(function ($credential) {
                    return $credential->site_name ?: '__no_site__';
                });
            });
    }

    /**
     * Get unique site names for a client
     */
    public static function getSitesForClient($clientId)
    {
        return static::where('client_id', $clientId)
            ->whereNotNull('site_name')
            ->where('site_name', '!=', '')
            ->distinct()
            ->pluck('site_name')
            ->sort();
    }

    /**
     * Get all unique site names (for autocomplete dropdown)
     */
    public static function getUniqueSites()
    {
        return static::whereNotNull('site_name')
            ->where('site_name', '!=', '')
            ->distinct()
            ->orderBy('site_name')
            ->pluck('site_name')
            ->values()
            ->toArray();
    }

    /**
     * Get sites with summary data for cards view
     * Returns collection with site_name, client info, credential counts, types, platforms
     */
    public static function getSitesWithSummary($filters = [])
    {
        $query = static::query()
            ->whereNotNull('site_name')
            ->where('site_name', '!=', '');

        // Apply filters
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['credential_type'])) {
            $query->where('credential_type', $filters['credential_type']);
        }
        if (!empty($filters['platform'])) {
            $query->where('platform', $filters['platform']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('site_name', 'like', "%{$search}%")
                  ->orWhere('website', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%");
                  });
            });
        }

        $credentials = $query->with('client:id,name,company')->get();

        // Group by site_name and aggregate
        return $credentials->groupBy('site_name')->map(function ($siteCredentials, $siteName) {
            $first = $siteCredentials->first();
            return (object) [
                'site_name' => $siteName,
                'client' => $first->client,
                'client_id' => $first->client_id,
                'website' => $first->website,
                'credential_count' => $siteCredentials->count(),
                'types' => $siteCredentials->pluck('credential_type')->unique()->filter()->values()->toArray(),
                'platforms' => $siteCredentials->pluck('platform')->unique()->filter()->values()->toArray(),
                'last_updated' => $siteCredentials->max('updated_at'),
            ];
        })->sortBy('site_name')->values();
    }

    /**
     * Get credentials for a specific site, grouped by credential type
     */
    public static function getCredentialsForSite($siteName)
    {
        return static::where('site_name', $siteName)
            ->with('client:id,name,company')
            ->orderBy('credential_type')
            ->orderBy('platform')
            ->get()
            ->groupBy('credential_type');
    }

    /**
     * Get site info (first credential data for site)
     */
    public static function getSiteInfo($siteName)
    {
        $credential = static::where('site_name', $siteName)
            ->with('client:id,name,company')
            ->first();

        if (!$credential) {
            return null;
        }

        return (object) [
            'site_name' => $siteName,
            'client' => $credential->client,
            'website' => $credential->website,
        ];
    }

    /**
     * Static helper to get badge color for a credential type
     */
    public static function getTypeBadgeColor($type): string
    {
        return match ($type) {
            'admin-panel' => 'blue',
            'database' => 'purple',
            'hosting' => 'orange',
            'marketing' => 'green',
            default => 'slate',
        };
    }

    /**
     * Get credential type badge color
     */
    public function getTypeBadgeColorAttribute(): string
    {
        return match ($this->credential_type) {
            'admin-panel' => 'blue',
            'database' => 'purple',
            'hosting' => 'orange',
            'marketing' => 'green',
            default => 'slate',
        };
    }

    /**
     * Get credential type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::CREDENTIAL_TYPES[$this->credential_type] ?? 'Other';
    }

    /**
     * Track access
     */
    public function trackAccess()
    {
        $this->increment('access_count');
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * Get display name - prioritizes site name if set
     */
    public function getDisplayNameAttribute()
    {
        if ($this->site_name) {
            return $this->site_name . ' - ' . $this->platform;
        }
        return $this->client->display_name . ' - ' . $this->platform;
    }

    /**
     * Get the quick login URL (website or url field)
     */
    public function getQuickLoginUrlAttribute()
    {
        return $this->website ?: $this->url;
    }

    /**
     * Generate bookmarklet JavaScript for auto-login
     * This creates a bookmark that when clicked, fills login forms
     *
     * Security: Uses json_encode() for proper JavaScript escaping to prevent injection attacks
     */
    public function generateBookmarklet(): string
    {
        // Use JSON encoding for proper JavaScript escaping - prevents XSS/injection
        $username = json_encode($this->username ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $password = json_encode($this->password ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        // JavaScript bookmarklet that fills common login form fields
        // Note: json_encode already includes quotes, so we use the values directly
        $js = <<<JS
(function(){
    var u={$username};
    var p={$password};
    var uf=document.querySelector('input[type="email"],input[type="text"][name*="user"],input[type="text"][name*="login"],input[type="text"][name*="email"],input[name="username"],input[name="user"],input[name="email"],input[name="log"],#username,#email,#user,#login-email,#login-username');
    var pf=document.querySelector('input[type="password"]');
    if(uf){uf.value=u;uf.dispatchEvent(new Event('input',{bubbles:true}));}
    if(pf){pf.value=p;pf.dispatchEvent(new Event('input',{bubbles:true}));}
    if(!uf&&!pf){alert('Login form not found!');}
})();
JS;

        // Minify and encode for URL
        $minified = preg_replace('/\s+/', ' ', trim($js));
        return 'javascript:' . rawurlencode($minified);
    }
}
