<?php

namespace App\Observers;

use App\Helpers\SettingsHelper;
use App\Models\SettingOption;
use App\Services\NomenclatureService;
use Illuminate\Support\Facades\Log;

class SettingOptionObserver
{
    /**
     * Handle the SettingOption "created" event.
     */
    public function created(SettingOption $settingOption): void
    {
        $this->clearCaches($settingOption, 'created');
    }

    /**
     * Handle the SettingOption "updated" event.
     */
    public function updated(SettingOption $settingOption): void
    {
        $this->clearCaches($settingOption, 'updated');

        // Log if category was changed (unusual operation)
        if ($settingOption->isDirty('category')) {
            Log::info('SettingOption category changed', [
                'setting_id' => $settingOption->id,
                'old_category' => $settingOption->getOriginal('category'),
                'new_category' => $settingOption->category,
            ]);
        }
    }

    /**
     * Handle the SettingOption "deleted" event.
     */
    public function deleted(SettingOption $settingOption): void
    {
        $this->clearCaches($settingOption, 'deleted');
    }

    /**
     * Handle the SettingOption "restored" event.
     */
    public function restored(SettingOption $settingOption): void
    {
        $this->clearCaches($settingOption, 'restored');
    }

    /**
     * Clear all relevant caches for the setting option
     */
    protected function clearCaches(SettingOption $settingOption, string $event): void
    {
        // Clear SettingsHelper cache
        SettingsHelper::clearCache($settingOption->category, $settingOption->organization_id);

        // Clear NomenclatureService cache
        app(NomenclatureService::class)->clearCacheFor($settingOption->category);

        // If the category changed during update, clear the old category cache too
        if ($event === 'updated' && $settingOption->isDirty('category')) {
            $oldCategory = $settingOption->getOriginal('category');
            SettingsHelper::clearCache($oldCategory, $settingOption->organization_id);
            app(NomenclatureService::class)->clearCacheFor($oldCategory);
        }

        Log::debug('SettingOption cache cleared', [
            'event' => $event,
            'category' => $settingOption->category,
            'organization_id' => $settingOption->organization_id,
        ]);
    }
}
