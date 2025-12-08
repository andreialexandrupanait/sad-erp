<?php

namespace App\Observers;

use App\Events\Client\ClientStatusChanged;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ClientObserver
{
    /**
     * Handle the Client "updated" event.
     */
    /**
     * Handle the Client "saved" event - clear dashboard cache.
     */
    public function saved(Client $client): void
    {
        if (Auth::check()) {
            Cache::forget('dashboard_stats_' . Auth::user()->organization_id);
        }
    }

    public function updated(Client $client): void
    {
        // Fire event if status_id was changed
        if ($client->isDirty('status_id')) {
            $oldStatusId = $client->getOriginal('status_id');
            $newStatusId = $client->status_id;

            // Only fire if there's actually a change (including from null to value)
            if ($oldStatusId !== $newStatusId) {
                event(new ClientStatusChanged(
                    $client,
                    $oldStatusId,
                    $newStatusId
                ));
            }
        }
    }
}
