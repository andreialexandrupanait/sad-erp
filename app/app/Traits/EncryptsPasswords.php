<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Trait for models that need to encrypt/decrypt password fields.
 *
 * This trait provides automatic encryption on save and decryption on retrieval
 * for password fields, along with a masked password accessor.
 *
 * Performance: Uses in-memory caching to avoid repeated decryption of the same password
 * when accessed multiple times within the same request.
 */
trait EncryptsPasswords
{
    /**
     * In-memory cache for decrypted password to avoid repeated decryption overhead.
     */
    private ?string $decryptedPasswordCache = null;

    /**
     * Flag to indicate if the cached value is set (allows caching null values).
     */
    private bool $passwordCacheSet = false;

    /**
     * Encrypt password before saving to database.
     */
    public function setPasswordAttribute($value): void
    {
        if (!empty($value)) {
            $this->attributes['password'] = Crypt::encryptString($value);
            // Clear the cache when password is updated
            $this->clearPasswordCache();
        }
    }

    /**
     * Decrypt password when retrieving from database.
     *
     * Performance: Caches the decrypted value to avoid repeated decryption
     * when accessed multiple times (e.g., in loops or templates).
     */
    public function getPasswordAttribute($value): ?string
    {
        // Return cached value if available
        if ($this->passwordCacheSet) {
            return $this->decryptedPasswordCache;
        }

        if (empty($value)) {
            $this->decryptedPasswordCache = null;
            $this->passwordCacheSet = true;
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($value);
            $this->decryptedPasswordCache = $decrypted;
            $this->passwordCacheSet = true;
            return $decrypted;
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::error('Failed to decrypt password', [
                'model' => static::class,
                'id' => $this->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            $this->decryptedPasswordCache = null;
            $this->passwordCacheSet = true;
            return null;
        }
    }

    /**
     * Get masked password for display (e.g., "••••••••••••").
     */
    public function getMaskedPasswordAttribute(): string
    {
        if (!empty($this->attributes['password'])) {
            return str_repeat('•', 12);
        }
        return '';
    }

    /**
     * Check if the model has a password set.
     */
    public function hasPassword(): bool
    {
        return !empty($this->attributes['password']);
    }

    /**
     * Clear the decrypted password cache.
     *
     * Called automatically when password is updated.
     * Can also be called manually after model refresh.
     */
    public function clearPasswordCache(): void
    {
        $this->decryptedPasswordCache = null;
        $this->passwordCacheSet = false;
    }

    /**
     * Override refresh to clear password cache.
     */
    public function refresh()
    {
        $this->clearPasswordCache();
        return parent::refresh();
    }
}
