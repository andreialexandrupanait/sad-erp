<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class ApplicationSetting extends Model
{
    protected $table = 'settings_app';

    protected $fillable = ['key', 'value', 'type'];

    /**
     * Keys that should be encrypted when stored.
     */
    protected static array $sensitiveKeys = [
        'anthropic_api_key',
        'clickup_api_key',
        'openai_api_key',
        'slack_webhook_url',
    ];

    /**
     * Check if a key should be encrypted.
     */
    protected static function isSensitive(string $key): bool
    {
        return in_array($key, self::$sensitiveKeys) || str_contains($key, '_api_key') || str_contains($key, '_secret') || str_contains($key, '_token');
    }

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("app_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            // Decrypt sensitive values
            if (self::isSensitive($key) && !empty($setting->value)) {
                try {
                    return Crypt::decryptString($setting->value);
                } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                    // Value might be stored unencrypted (legacy), return as-is
                    return $setting->value;
                }
            }

            return $setting->value;
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $type = 'string'): void
    {
        // Encrypt sensitive values
        $storedValue = $value;
        if (self::isSensitive($key) && !empty($value)) {
            $storedValue = Crypt::encryptString($value);
        }

        self::updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue, 'type' => $type]
        );
        Cache::forget("app_setting_{$key}");
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget("app_setting_{$setting->key}");
        }
    }
}
