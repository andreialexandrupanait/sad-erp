<?php

namespace App\Observers;

use App\Models\Service;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ServiceObserver
{
    /**
     * Handle the Service "created" event.
     */
    public function created(Service $service): void
    {
        $this->clearServiceCaches();

        Log::info('Service created', [
            'service_id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug,
        ]);
    }

    /**
     * Handle the Service "updated" event.
     */
    public function updated(Service $service): void
    {
        $this->clearServiceCaches();

        // Log important changes
        $changes = [];
        if ($service->isDirty('name')) {
            $changes['name'] = [
                'old' => $service->getOriginal('name'),
                'new' => $service->name,
            ];
        }

        if ($service->isDirty('is_active')) {
            $changes['is_active'] = [
                'old' => $service->getOriginal('is_active'),
                'new' => $service->is_active,
            ];
        }

        if (!empty($changes)) {
            Log::info('Service updated', [
                'service_id' => $service->id,
                'changes' => $changes,
            ]);
        }
    }

    /**
     * Handle the Service "deleted" event.
     */
    public function deleted(Service $service): void
    {
        $this->clearServiceCaches();

        Log::info('Service deleted', [
            'service_id' => $service->id,
            'name' => $service->name,
        ]);
    }

    /**
     * Handle the Service "restored" event.
     */
    public function restored(Service $service): void
    {
        $this->clearServiceCaches();

        Log::info('Service restored', [
            'service_id' => $service->id,
            'name' => $service->name,
        ]);
    }

    /**
     * Handle the Service "force deleted" event.
     */
    public function forceDeleted(Service $service): void
    {
        $this->clearServiceCaches();

        Log::warning('Service permanently deleted', [
            'service_id' => $service->id,
            'name' => $service->name,
        ]);
    }

    /**
     * Clear all service-related caches.
     */
    protected function clearServiceCaches(): void
    {
        // Clear global services list
        Cache::forget('services.all');
        Cache::forget('services.active');

        // Clear services dropdown cache (used in forms)
        Cache::forget('settings.services');

        // Clear user services cache for all users
        // Note: This is a pattern match, might need tag-based caching for efficiency
        Cache::flush(); // Consider using cache tags in production
    }
}
