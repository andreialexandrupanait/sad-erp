<?php

namespace App\Observers;

use App\Http\View\Composers\SettingsComposer;
use App\Models\SettingOption;

/**
 * Setting Option Observer
 *
 * Automatically clears cache when settings are modified
 */
class ClientSettingObserver
{
    /**
     * Handle the SettingOption "created" event.
     */
    public function created(SettingOption $settingOption): void
    {
        SettingsComposer::clearCache();
    }

    /**
     * Handle the SettingOption "updated" event.
     */
    public function updated(SettingOption $settingOption): void
    {
        SettingsComposer::clearCache();
    }

    /**
     * Handle the SettingOption "deleted" event.
     */
    public function deleted(SettingOption $settingOption): void
    {
        SettingsComposer::clearCache();
    }

    /**
     * Handle the SettingOption "restored" event.
     */
    public function restored(SettingOption $settingOption): void
    {
        SettingsComposer::clearCache();
    }
}
