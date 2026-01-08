<?php

namespace App\Services\Dashboard;

use App\Models\SettingOption;
use Illuminate\Support\Facades\Cache;

/**
 * Nomenclature Provider Service
 *
 * Provides cached nomenclature data for form dropdowns and lookups
 * including statuses, categories, platforms, and other reference data.
 */
class NomenclatureProvider
{
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get cache key with organization prefix
     */
    private function cacheKey(string $key): string
    {
        $orgId = auth()->user()->organization_id ?? 'default';
        return "org.{$orgId}.{$key}";
    }

    /**
     * Get all nomenclature data
     */
    public function getNomenclature(): array
    {
        return [
            'clientStatuses' => $this->getClientStatuses(),
            'expenseCategories' => $this->getExpenseCategories(),
            'billingCycles' => $this->getBillingCycles(),
            'statuses' => $this->getSubscriptionStatuses(),
            'platforms' => $this->getPlatforms(),
            'registrars' => $this->getRegistrars(),
            'domainStatuses' => $this->getDomainStatuses(),
            'currencies' => $this->getCurrencies(),
            'quickActions' => $this->getQuickActions(),
        ];
    }

    /**
     * Get client statuses
     */
    public function getClientStatuses(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            $this->cacheKey('nomenclature.client_statuses'),
            self::CACHE_TTL,
            fn() => SettingOption::clientStatuses()->get()
        );
    }

    /**
     * Get expense categories
     */
    public function getExpenseCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            $this->cacheKey('nomenclature.expense_categories'),
            self::CACHE_TTL,
            fn() => SettingOption::rootCategories()->with('children')->get()
        );
    }

    /**
     * Get billing cycles
     */
    public function getBillingCycles(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            $this->cacheKey('nomenclature.billing_cycles'),
            self::CACHE_TTL,
            fn() => SettingOption::billingCycles()->get()
        );
    }

    /**
     * Get subscription statuses
     */
    public function getSubscriptionStatuses(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            $this->cacheKey('nomenclature.subscription_statuses'),
            self::CACHE_TTL,
            fn() => SettingOption::subscriptionStatuses()->get()
        );
    }

    /**
     * Get access platforms
     */
    public function getPlatforms(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            $this->cacheKey('nomenclature.platforms'),
            self::CACHE_TTL,
            fn() => SettingOption::accessPlatforms()->get()
        );
    }

    /**
     * Get domain registrars
     */
    public function getRegistrars(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            $this->cacheKey('nomenclature.registrars'),
            self::CACHE_TTL,
            fn() => SettingOption::domainRegistrars()->get()
        );
    }

    /**
     * Get domain statuses
     */
    public function getDomainStatuses(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            $this->cacheKey('nomenclature.domain_statuses'),
            self::CACHE_TTL,
            fn() => SettingOption::domainStatuses()->get()
        );
    }

    /**
     * Get currencies
     */
    public function getCurrencies(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            $this->cacheKey('nomenclature.currencies'),
            self::CACHE_TTL,
            fn() => SettingOption::currencies()->get()
        );
    }

    /**
     * Get quick actions for dashboard
     */
    public function getQuickActions(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(
            $this->cacheKey('nomenclature.quick_actions'),
            self::CACHE_TTL,
            fn() => SettingOption::dashboardQuickActions()->get()
        );
    }

    /**
     * Clear all nomenclature caches
     */
    public function clearCache(): void
    {
        $keys = [
            'nomenclature.client_statuses',
            'nomenclature.expense_categories',
            'nomenclature.billing_cycles',
            'nomenclature.subscription_statuses',
            'nomenclature.platforms',
            'nomenclature.registrars',
            'nomenclature.domain_statuses',
            'nomenclature.currencies',
            'nomenclature.quick_actions',
        ];

        foreach ($keys as $key) {
            Cache::forget($this->cacheKey($key));
        }
    }
}
