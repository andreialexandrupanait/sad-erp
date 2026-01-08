<?php

namespace App\Observers;

use App\Models\FinancialFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FinancialFileObserver
{
    /**
     * Handle the FinancialFile "created" event.
     */
    public function created(FinancialFile $file): void
    {
        $this->clearCaches($file, 'created');
    }

    /**
     * Handle the FinancialFile "updated" event.
     */
    public function updated(FinancialFile $file): void
    {
        $this->clearCaches($file, 'updated');

        // Log if entity association changed (unusual operation)
        if ($file->isDirty('entity_id') || $file->isDirty('entity_type')) {
            Log::info('FinancialFile entity association changed', [
                'file_id' => $file->id,
                'old_entity_type' => $file->getOriginal('entity_type'),
                'new_entity_type' => $file->entity_type,
                'old_entity_id' => $file->getOriginal('entity_id'),
                'new_entity_id' => $file->entity_id,
            ]);
        }
    }

    /**
     * Handle the FinancialFile "deleted" event.
     */
    public function deleted(FinancialFile $file): void
    {
        $this->clearCaches($file, 'deleted');

        // Delete physical file if it exists
        if ($file->file_path && Storage::disk('local')->exists($file->file_path)) {
            Storage::disk('local')->delete($file->file_path);
            Log::info('FinancialFile physical file deleted', [
                'file_id' => $file->id,
                'file_path' => $file->file_path,
            ]);
        }
    }

    /**
     * Handle the FinancialFile "restored" event.
     */
    public function restored(FinancialFile $file): void
    {
        $this->clearCaches($file, 'restored');
    }

    /**
     * Clear all relevant caches for the financial file
     */
    protected function clearCaches(FinancialFile $file, string $event): void
    {
        $orgId = $file->organization_id;

        // Clear financial file caches
        Cache::forget("financial.files.org.{$orgId}");
        Cache::forget("financial.files.year.{$file->an}.org.{$orgId}");
        Cache::forget("financial.files.month.{$file->an}.{$file->luna}.org.{$orgId}");
        Cache::forget("financial.files.type.{$file->tip}.org.{$orgId}");

        // Clear entity-specific caches
        if ($file->entity_type && $file->entity_id) {
            $entityClass = class_basename($file->entity_type);
            Cache::forget("financial.files.{$entityClass}.{$file->entity_id}");
        }

        // Clear file count and summary caches
        Cache::forget("financial.files.count.org.{$orgId}");
        Cache::forget("financial.files.summary.org.{$orgId}");
        Cache::forget("financial.files.summary.{$file->an}.org.{$orgId}");

        // Clear dashboard cache that might show file stats
        Cache::forget("dashboard.org.{$orgId}");
        Cache::forget("dashboard.financial.org.{$orgId}");

        Log::debug('FinancialFile cache cleared', [
            'event' => $event,
            'file_id' => $file->id,
            'organization_id' => $orgId,
            'year' => $file->an,
            'month' => $file->luna,
        ]);
    }
}
