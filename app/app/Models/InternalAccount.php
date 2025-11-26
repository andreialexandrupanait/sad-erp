<?php

namespace App\Models;

use App\Traits\EncryptsPasswords;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class InternalAccount extends Model
{
    use HasFactory, SoftDeletes, EncryptsPasswords;

    protected $fillable = [
        'organization_id',
        'user_id',
        'account_name',
        'url',
        'username',
        'password',
        'team_accessible',
        'notes',
    ];

    protected $casts = [
        'team_accessible' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set organization_id and user_id when creating
        static::creating(function ($account) {
            if (auth()->check()) {
                if (empty($account->organization_id)) {
                    $orgId = auth()->user()->organization_id;
                    if (!$orgId) {
                        throw new \RuntimeException('User must belong to an organization to create internal accounts.');
                    }
                    $account->organization_id = $orgId;
                }
                if (empty($account->user_id)) {
                    $account->user_id = auth()->id();
                }
            }
        });

        // Global scope: Show only user's own accounts OR team-accessible accounts within same org
        static::addGlobalScope('accessible', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization_id) {
                $builder->where(function ($query) {
                    $query->where('user_id', auth()->id())
                          ->orWhere('team_accessible', true);
                })
                ->where('organization_id', auth()->user()->organization_id);
            }
        });
    }

    /**
     * Relationships
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Password encryption/decryption handled by EncryptsPasswords trait

    /**
     * Check if current user is the owner
     */
    public function isOwner()
    {
        return auth()->check() && $this->user_id === auth()->id();
    }

    /**
     * Check if account is accessible to current user
     */
    public function isAccessible()
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->user_id === auth()->id() || $this->team_accessible;
    }

    /**
     * Search scope
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('account_name', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('url', 'like', "%{$search}%");
        });
    }

    /**
     * Filter by team accessibility
     */
    public function scopeTeamAccessible($query, $teamAccessible = null)
    {
        if ($teamAccessible !== null) {
            return $query->where('team_accessible', (bool) $teamAccessible);
        }
        return $query;
    }

    /**
     * Filter by ownership (my accounts only)
     */
    public function scopeOwnedByMe($query)
    {
        if (auth()->check()) {
            return $query->where('user_id', auth()->id());
        }
        return $query;
    }

    /**
     * Get display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->account_name;
    }

    /**
     * Get ownership status text
     */
    public function getOwnershipStatusAttribute()
    {
        if ($this->isOwner()) {
            return __('Your Account');
        }
        if ($this->team_accessible) {
            return __('Team Account (by :name)', ['name' => $this->user->name]);
        }
        return __('Unknown');
    }
}
