<?php

namespace App\Services\Mail;

use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\Log;

class SmtpConfigurationService
{
    /**
     * Cache TTL in seconds (24 hours).
     */
    private const CACHE_TTL = 86400;

    /**
     * Cache key for SMTP settings.
     */
    private const CACHE_KEY = 'smtp_configuration_settings';

    /**
     * Configure SMTP from database settings.
     */
    public function configure(): void
    {
        $settings = $this->getSettings();

        if (!$settings['enabled']) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $settings['host'],
            'mail.mailers.smtp.port' => (int) $settings['port'],
            'mail.mailers.smtp.username' => $settings['username'],
            'mail.mailers.smtp.password' => $settings['password'],
            'mail.mailers.smtp.encryption' => $settings['encryption'] === 'none' ? null : $settings['encryption'],
            'mail.from.address' => $settings['from_email'] ?: $settings['username'],
            'mail.from.name' => $settings['from_name'],
        ]);
    }

    /**
     * Get SMTP settings from cache or database.
     */
    public function getSettings(): array
    {
        return cache()->remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->fetchSettings();
        });
    }

    /**
     * Fetch all SMTP settings from database in a single query.
     */
    private function fetchSettings(): array
    {
        $keys = [
            'smtp_enabled',
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_encryption',
            'smtp_from_email',
            'smtp_from_name',
        ];

        $settings = ApplicationSetting::whereIn('key', $keys)
            ->pluck('value', 'key')
            ->toArray();

        $password = $settings['smtp_password'] ?? null;
        if ($password) {
            try {
                $password = decrypt($password);
            } catch (\Exception $e) {
                // Password might not be encrypted
                Log::debug('SMTP password decryption skipped - possibly not encrypted');
            }
        }

        return [
            'enabled' => filter_var($settings['smtp_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'host' => $settings['smtp_host'] ?? null,
            'port' => $settings['smtp_port'] ?? 587,
            'username' => $settings['smtp_username'] ?? null,
            'password' => $password,
            'encryption' => $settings['smtp_encryption'] ?? 'tls',
            'from_email' => $settings['smtp_from_email'] ?? null,
            'from_name' => $settings['smtp_from_name'] ?? config('app.name'),
        ];
    }

    /**
     * Clear the SMTP settings cache.
     */
    public function clearCache(): void
    {
        cache()->forget(self::CACHE_KEY);
    }

    /**
     * Check if SMTP is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->getSettings()['enabled'];
    }
}
