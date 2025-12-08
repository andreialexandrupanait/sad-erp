<?php

namespace App\Services;

use App\Models\SettingOption;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

/**
 * Nomenclature Service
 *
 * Centralized service for managing application nomenclature (reference data)
 * with caching support. Provides organized access to all SettingOption categories.
 *
 * This service eliminates duplicate SettingOption queries across controllers
 * and services, providing a single source of truth for nomenclature data.
 */
class NomenclatureService
{
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get cache key with organization prefix
     */
    private function cacheKey(string $key): string
    {
        $orgId = auth()->user()?->organization_id ?? 'default';
        return "nomenclature.{$key}.org.{$orgId}";
    }

    /**
     * Get all nomenclature data (for views that need multiple categories)
     */
    public function getAll(): array
    {
        return [
            'client_statuses' => $this->getClientStatuses(),
            'domain_statuses' => $this->getDomainStatuses(),
            'domain_registrars' => $this->getDomainRegistrars(),
            'subscription_statuses' => $this->getSubscriptionStatuses(),
            'access_platforms' => $this->getAccessPlatforms(),
            'billing_cycles' => $this->getBillingCycles(),
            'currencies' => $this->getCurrencies(),
            'payment_methods' => $this->getPaymentMethods(),
            'expense_categories' => $this->getExpenseCategories(),
        ];
    }

    /**
     * Get client statuses
     */
    public function getClientStatuses(): Collection
    {
        return Cache::remember(
            $this->cacheKey('client_statuses'),
            self::CACHE_TTL,
            fn() => SettingOption::clientStatuses()->get()
        );
    }

    /**
     * Get domain statuses
     */
    public function getDomainStatuses(): Collection
    {
        return Cache::remember(
            $this->cacheKey('domain_statuses'),
            self::CACHE_TTL,
            fn() => SettingOption::domainStatuses()->get()
        );
    }

    /**
     * Get domain registrars
     */
    public function getDomainRegistrars(): Collection
    {
        return Cache::remember(
            $this->cacheKey('domain_registrars'),
            self::CACHE_TTL,
            fn() => SettingOption::domainRegistrars()->get()
        );
    }

    /**
     * Get subscription statuses
     */
    public function getSubscriptionStatuses(): Collection
    {
        return Cache::remember(
            $this->cacheKey('subscription_statuses'),
            self::CACHE_TTL,
            fn() => SettingOption::subscriptionStatuses()->get()
        );
    }

    /**
     * Get access platforms
     */
    public function getAccessPlatforms(): Collection
    {
        return Cache::remember(
            $this->cacheKey('access_platforms'),
            self::CACHE_TTL,
            fn() => SettingOption::accessPlatforms()->get()
        );
    }

    /**
     * Get billing cycles
     */
    public function getBillingCycles(): Collection
    {
        return Cache::remember(
            $this->cacheKey('billing_cycles'),
            self::CACHE_TTL,
            fn() => SettingOption::billingCycles()->get()
        );
    }

    /**
     * Get currencies
     */
    public function getCurrencies(): Collection
    {
        return Cache::remember(
            $this->cacheKey('currencies'),
            self::CACHE_TTL,
            fn() => SettingOption::currencies()->get()
        );
    }

    /**
     * Get payment methods
     */
    public function getPaymentMethods(): Collection
    {
        return Cache::remember(
            $this->cacheKey('payment_methods'),
            self::CACHE_TTL,
            fn() => SettingOption::paymentMethods()->get()
        );
    }

    /**
     * Get expense categories (root level with children)
     */
    public function getExpenseCategories(): Collection
    {
        return Cache::remember(
            $this->cacheKey('expense_categories'),
            self::CACHE_TTL,
            fn() => SettingOption::rootCategories()->with('children')->get()
        );
    }

    /**
     * Get all expense categories (flat list)
     */
    public function getAllExpenseCategories(): Collection
    {
        return Cache::remember(
            $this->cacheKey('expense_categories_flat'),
            self::CACHE_TTL,
            fn() => SettingOption::where('category', 'expense_categories')->get()
        );
    }

    /**
     * Get dashboard quick actions
     */
    public function getQuickActions(): Collection
    {
        return Cache::remember(
            $this->cacheKey('quick_actions'),
            self::CACHE_TTL,
            fn() => SettingOption::dashboardQuickActions()->get()
        );
    }

    /**
     * Get nomenclature counts (for settings dashboard)
     */
    public function getCounts(): array
    {
        return Cache::remember(
            $this->cacheKey('counts'),
            self::CACHE_TTL,
            fn() => [
                'client_statuses' => SettingOption::clientStatuses()->count(),
                'domain_statuses' => SettingOption::domainStatuses()->count(),
                'subscription_statuses' => SettingOption::subscriptionStatuses()->count(),
                'access_platforms' => SettingOption::accessPlatforms()->count(),
                'expense_categories' => SettingOption::where('category', 'expense_categories')->count(),
                'payment_methods' => SettingOption::paymentMethods()->count(),
                'billing_cycles' => SettingOption::billingCycles()->count(),
                'currencies' => SettingOption::currencies()->count(),
                'domain_registrars' => SettingOption::domainRegistrars()->count(),
            ]
        );
    }

    /**
     * Clear all nomenclature caches
     */
    public function clearCache(): void
    {
        $keys = [
            'client_statuses',
            'domain_statuses',
            'domain_registrars',
            'subscription_statuses',
            'access_platforms',
            'billing_cycles',
            'currencies',
            'payment_methods',
            'expense_categories',
            'expense_categories_flat',
            'quick_actions',
            'counts',
        ];

        foreach ($keys as $key) {
            Cache::forget($this->cacheKey($key));
        }
    }

    /**
     * Clear specific nomenclature cache
     */
    public function clearCacheFor(string $category): void
    {
        Cache::forget($this->cacheKey($category));
        Cache::forget($this->cacheKey('counts')); // Also clear counts cache
    }
}
