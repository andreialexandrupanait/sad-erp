<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

trait HandlesBulkActions
{
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "ids" => "required|array|min:1|max:100",
            "ids.*" => "required|integer",
            "action" => "required|string|in:update_status,delete,export",
            "status_id" => "nullable|exists:settings_options,id",
        ]);

        $modelClass = $this->getBulkModelClass();
        $ids = $validated["ids"];

        $query = $modelClass::whereIn("id", $ids);
        $items = $query->get();

        if ($items->count() !== count($ids)) {
            return response()->json([
                "success" => false,
                "message" => __('messages.items_not_found_or_access')
            ], 403);
        }

        foreach ($items as $item) {
            if (!Gate::allows("update", $item)) {
                return response()->json([
                    "success" => false,
                    "message" => __('messages.no_permission_update_all')
                ], 403);
            }
        }

        DB::beginTransaction();

        try {
            $count = 0;

            switch ($validated["action"]) {
                case "update_status":
                    $count = $this->bulkUpdateStatus($items, $validated["status_id"] ?? null);
                    break;

                case "delete":
                    $count = $this->bulkDelete($items);
                    break;
            }

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => __('messages.items_updated_count', ['count' => $count]),
                "count" => $count,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the full error for debugging, but don't expose it to the user
            \Log::error('Bulk action failed', [
                'action' => $validated['action'],
                'ids' => $ids,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                "success" => false,
                "message" => __('An error occurred while processing your request. Please try again.')
            ], 500);
        }
    }

    public function bulkExport(Request $request)
    {
        $validated = $request->validate([
            "ids" => "required|array|min:1|max:100",
            "ids.*" => "required|integer",
        ]);

        $modelClass = $this->getBulkModelClass();

        // Get eager load relationships for export to prevent N+1 queries
        $eagerLoads = $this->getExportEagerLoads();
        $query = $modelClass::whereIn("id", $validated["ids"]);

        if (!empty($eagerLoads)) {
            $query->with($eagerLoads);
        }

        $items = $query->get();

        foreach ($items as $item) {
            if (!Gate::allows("view", $item)) {
                return response()->json([
                    "success" => false,
                    "message" => __('messages.no_permission_export_all')
                ], 403);
            }
        }

        return $this->exportToCsv($items);
    }

    /**
     * Get relationships to eager load for export.
     * Override in controller to specify relationships.
     */
    protected function getExportEagerLoads(): array
    {
        return [];
    }

    protected function getBulkModelClass(): string
    {
        throw new \Exception("getBulkModelClass() must be implemented");
    }

    protected function bulkUpdateStatus($items, $statusId): int
    {
        if ($items->isEmpty()) {
            return 0;
        }

        // Use batch update for better performance
        $modelClass = $this->getBulkModelClass();
        $ids = $items->pluck('id')->toArray();

        $modelClass::whereIn('id', $ids)->update([
            'status_id' => $statusId,
            'updated_at' => now(),
        ]);

        return count($ids);
    }

    protected function bulkDelete($items): int
    {
        if ($items->isEmpty()) {
            return 0;
        }

        // Filter items user can delete and collect IDs
        $deletableIds = $items->filter(fn($item) => Gate::allows("delete", $item))
            ->pluck('id')
            ->toArray();

        if (empty($deletableIds)) {
            return 0;
        }

        // Use batch delete for better performance
        $modelClass = $this->getBulkModelClass();
        $modelClass::whereIn('id', $deletableIds)->delete();

        return count($deletableIds);
    }

    protected function exportToCsv($items)
    {
        throw new \Exception("exportToCsv() must be implemented");
    }
}
