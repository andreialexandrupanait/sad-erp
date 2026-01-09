<?php

namespace App\Services\Settings;

use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Application Settings Service
 *
 * Centralized service for managing application settings with caching support.
 * Provides organized access to settings groups and handles type conversion.
 */
class ApplicationSettingsService
{
    protected const CACHE_TTL = 3600; // 1 hour
    protected const CACHE_PREFIX = 'app_settings';

    /**
     * Get all application settings
     *
     * @return array
     */
    public function getApplicationSettings(): array
    {
        return $this->getCachedSettings('application', function() {
            return [
                'app_name' => ApplicationSetting::get('app_name', 'ERP System'),
                'app_logo' => ApplicationSetting::get('app_logo'),
                'app_favicon' => ApplicationSetting::get('app_favicon'),
                'theme_mode' => ApplicationSetting::get('theme_mode', 'light'),
                'primary_color' => ApplicationSetting::get('primary_color', '#3b82f6'),
                'language' => ApplicationSetting::get('language', 'ro'),
                'timezone' => ApplicationSetting::get('timezone', 'Europe/Bucharest'),
                'date_format' => ApplicationSetting::get('date_format', 'd/m/Y'),
            ];
        });
    }

    /**
     * Get notification settings
     *
     * @return array
     */
    public function getNotificationSettings(): array
    {
        return $this->getCachedSettings('notifications', function() {
            return [
                // Master toggle
                'notifications_enabled' => ApplicationSetting::get('notifications_enabled', false),

                // Individual notifications
                'notify_domain_expiry' => ApplicationSetting::get('notify_domain_expiry', true),
                'notify_subscription_renewal' => ApplicationSetting::get('notify_subscription_renewal', true),
                'notify_new_client' => ApplicationSetting::get('notify_new_client', false),
                'notify_payment_received' => ApplicationSetting::get('notify_payment_received', false),
                'notify_monthly_summary' => ApplicationSetting::get('notify_monthly_summary', false),

                // Timing
                'domain_expiry_days_before' => ApplicationSetting::get('domain_expiry_days_before', 30),
                'subscription_renewal_days' => ApplicationSetting::get('subscription_renewal_days', 7),
                'monthly_summary_day' => ApplicationSetting::get('monthly_summary_day', 1),

                // Recipients
                'notification_email_primary' => ApplicationSetting::get('notification_email_primary', auth()->user()->email ?? ''),
                'notification_email_cc' => ApplicationSetting::get('notification_email_cc', ''),

                // SMTP
                'smtp_enabled' => ApplicationSetting::get('smtp_enabled',
            'push_notifications_enabled',
            'push_notify_offer_accepted',
            'push_notify_offer_rejected',
            'push_notify_new_client',
            'push_notify_payment_received', false),
                'smtp_host' => ApplicationSetting::get('smtp_host', ''),
                'smtp_port' => ApplicationSetting::get('smtp_port', 587),
                'smtp_username' => ApplicationSetting::get('smtp_username', ''),
                'smtp_password' => ApplicationSetting::get('smtp_password', ''),
                'smtp_encryption' => ApplicationSetting::get('smtp_encryption', 'tls'),
                'smtp_from_email' => ApplicationSetting::get('smtp_from_email', ''),
                'smtp_from_name' => ApplicationSetting::get('smtp_from_name', ApplicationSetting::get('app_name', 'ERP System')),

                // Push Notifications
                'push_notifications_enabled' => ApplicationSetting::get('push_notifications_enabled', true),
                'push_notify_offer_accepted' => ApplicationSetting::get('push_notify_offer_accepted', true),
                'push_notify_offer_rejected' => ApplicationSetting::get('push_notify_offer_rejected', true),
                'push_notify_new_client' => ApplicationSetting::get('push_notify_new_client', false),
                'push_notify_payment_received' => ApplicationSetting::get('push_notify_payment_received', false),
            ];
        });
    }

    /**
     * Get Slack settings
     *
     * @return array
     */
    public function getSlackSettings(): array
    {
        return $this->getCachedSettings('slack', function() {
            return [
                'slack_enabled' => ApplicationSetting::get('slack_enabled', false),
                'slack_webhook_url' => ApplicationSetting::get('slack_webhook_url', ''),
                'slack_channel' => ApplicationSetting::get('slack_channel', '#general'),
                'slack_username' => ApplicationSetting::get('slack_username', 'ERP Bot'),
                'slack_icon_emoji' => ApplicationSetting::get('slack_icon_emoji', ':robot_face:'),
            ];
        });
    }

    /**
     * Get WhatsApp settings
     *
     * @return array
     */
    public function getWhatsAppSettings(): array
    {
        return $this->getCachedSettings('whatsapp', function() {
            return [
                'whatsapp_enabled' => ApplicationSetting::get('whatsapp_enabled', false),
                'whatsapp_api_key' => ApplicationSetting::get('whatsapp_api_key', ''),
                'whatsapp_phone_number' => ApplicationSetting::get('whatsapp_phone_number', ''),
                'whatsapp_account_sid' => ApplicationSetting::get('whatsapp_account_sid', ''),
            ];
        });
    }

    /**
     * Get SmartBill settings
     *
     * @return array
     */
    public function getSmartBillSettings(): array
    {
        return $this->getCachedSettings('smartbill', function() {
            return [
                'smartbill_enabled' => ApplicationSetting::get('smartbill_enabled', false),
                'smartbill_username' => ApplicationSetting::get('smartbill_username', ''),
                'smartbill_token' => ApplicationSetting::get('smartbill_token', ''),
                'smartbill_tax_id' => ApplicationSetting::get('smartbill_tax_id', ''),
                'smartbill_auto_sync' => ApplicationSetting::get('smartbill_auto_sync', false),
                'smartbill_sync_frequency' => ApplicationSetting::get('smartbill_sync_frequency', 'daily'),
            ];
        });
    }

    /**
     * Update multiple settings at once
     *
     * @param array $settings Key-value pairs of settings
     * @param array $booleanFields Fields that should be treated as booleans
     * @param array $integerFields Fields that should be treated as integers
     * @param array $encryptedFields Fields that should be encrypted
     * @return void
     */
    public function updateSettings(
        array $settings,
        array $booleanFields = [],
        array $integerFields = [],
        array $encryptedFields = []
    ): void {
        foreach ($settings as $key => $value) {
            $type = 'string';

            // Determine type
            if (in_array($key, $booleanFields)) {
                $type = 'boolean';
                $value = isset($value) && $value == '1';
            } elseif (in_array($key, $integerFields)) {
                $type = 'integer';
            }

            // Encrypt if needed
            if (in_array($key, $encryptedFields) && !empty($value)) {
                $value = encrypt($value);
            }

            ApplicationSetting::set($key, $value, $type);
        }

        // Clear all settings caches
        $this->clearAllCaches();
    }

    /**
     * Get a single setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return ApplicationSetting::get($key, $default);
    }

    /**
     * Set a single setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @return void
     */
    public function set(string $key, mixed $value, string $type = 'string'): void
    {
        ApplicationSetting::set($key, $value, $type);
        $this->clearAllCaches();
    }

    /**
     * Get cached settings
     *
     * @param string $group
     * @param callable $callback
     * @return array
     */
    protected function getCachedSettings(string $group, callable $callback): array
    {
        $cacheKey = $this->getCacheKey($group);
        return Cache::remember($cacheKey, self::CACHE_TTL, $callback);
    }

    /**
     * Get cache key for settings group
     *
     * @param string $group
     * @return string
     */
    protected function getCacheKey(string $group): string
    {
        $orgId = auth()->user()?->organization_id ?? 'global';
        return self::CACHE_PREFIX . ".{$group}.org.{$orgId}";
    }

    /**
     * Clear cache for specific settings group
     *
     * @param string $group
     * @return void
     */
    public function clearCache(string $group): void
    {
        $cacheKey = $this->getCacheKey($group);
        Cache::forget($cacheKey);
    }

    /**
     * Clear all settings caches
     *
     * @return void
     */
    public function clearAllCaches(): void
    {
        $groups = ['application', 'notifications', 'slack', 'whatsapp', 'smartbill'];
        foreach ($groups as $group) {
            $this->clearCache($group);
        }
    }

    /**
     * Get boolean fields for notification settings
     *
     * @return array
     */
    public function getNotificationBooleanFields(): array
    {
        return [
            'notifications_enabled',
            'notify_domain_expiry',
            'notify_subscription_renewal',
            'notify_new_client',
            'notify_payment_received',
            'notify_monthly_summary',
            'smtp_enabled',
            'push_notifications_enabled',
            'push_notify_offer_accepted',
            'push_notify_offer_rejected',
            'push_notify_new_client',
            'push_notify_payment_received',
        ];
    }

    /**
     * Get integer fields for notification settings
     *
     * @return array
     */
    public function getNotificationIntegerFields(): array
    {
        return [
            'smtp_port',
            'domain_expiry_days_before',
            'subscription_renewal_days',
            'monthly_summary_day',
        ];
    }

    /**
     * Get encrypted fields for notification settings
     *
     * @return array
     */
    public function getNotificationEncryptedFields(): array
    {
        return ['smtp_password'];
    }
}
