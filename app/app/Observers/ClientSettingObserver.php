<?php

namespace App\Observers;

use App\Http\View\Composers\SettingsComposer;
use App\Models\ClientSetting;

/**
 * Client Setting Observer
 *
 * Automatically clears cache when client settings are modified
 */
class ClientSettingObserver
{
    /**
     * Handle the ClientSetting "created" event.
     */
    public function created(ClientSetting $clientSetting): void
    {
        SettingsComposer::clearCache($clientSetting->user_id);
    }

    /**
     * Handle the ClientSetting "updated" event.
     */
    public function updated(ClientSetting $clientSetting): void
    {
        SettingsComposer::clearCache($clientSetting->user_id);
    }

    /**
     * Handle the ClientSetting "deleted" event.
     */
    public function deleted(ClientSetting $clientSetting): void
    {
        SettingsComposer::clearCache($clientSetting->user_id);
    }

    /**
     * Handle the ClientSetting "restored" event.
     */
    public function restored(ClientSetting $clientSetting): void
    {
        SettingsComposer::clearCache($clientSetting->user_id);
    }
}
