<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_PROFILE_UPDATE = 'profile_update';
    public const ACTION_PASSWORD_CHANGE = 'password_change';
    public const ACTION_2FA_ENABLED = '2fa_enabled';
    public const ACTION_2FA_DISABLED = '2fa_disabled';
    public const ACTION_AVATAR_CHANGE = 'avatar_change';
    public const ACTION_PREFERENCES_UPDATE = 'preferences_update';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a user activity
     */
    public static function log(
        int $userId,
        string $action,
        ?string $description = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get human-readable action name
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_LOGIN => __('Login'),
            self::ACTION_LOGOUT => __('Logout'),
            self::ACTION_PROFILE_UPDATE => __('Profile Updated'),
            self::ACTION_PASSWORD_CHANGE => __('Password Changed'),
            self::ACTION_2FA_ENABLED => __('2FA Enabled'),
            self::ACTION_2FA_DISABLED => __('2FA Disabled'),
            self::ACTION_AVATAR_CHANGE => __('Avatar Changed'),
            self::ACTION_PREFERENCES_UPDATE => __('Preferences Updated'),
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Get action icon class
     */
    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_LOGIN => 'text-green-600',
            self::ACTION_LOGOUT => 'text-gray-600',
            self::ACTION_PROFILE_UPDATE => 'text-blue-600',
            self::ACTION_PASSWORD_CHANGE => 'text-yellow-600',
            self::ACTION_2FA_ENABLED => 'text-green-600',
            self::ACTION_2FA_DISABLED => 'text-red-600',
            self::ACTION_AVATAR_CHANGE => 'text-purple-600',
            self::ACTION_PREFERENCES_UPDATE => 'text-indigo-600',
            default => 'text-gray-600',
        };
    }
}
