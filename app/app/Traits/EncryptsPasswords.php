<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Trait for models that need to encrypt/decrypt password fields.
 *
 * This trait provides automatic encryption on save and decryption on retrieval
 * for password fields, along with a masked password accessor.
 */
trait EncryptsPasswords
{
    /**
     * Encrypt password before saving to database.
     */
    public function setPasswordAttribute($value): void
    {
        if (!empty($value)) {
            $this->attributes['password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt password when retrieving from database.
     */
    public function getPasswordAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::error('Failed to decrypt password', [
                'model' => static::class,
                'id' => $this->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
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
}
