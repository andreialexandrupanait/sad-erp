<?php

namespace App\Http\Controllers\Financial\Concerns;

use App\Models\SmartbillImport;
use Illuminate\Support\Facades\Storage;

trait ManagesImports
{
    /**
     * Get import status (for polling)
     */
    public function getImportStatus($importId)
    {
        $import = SmartbillImport::find($importId);

        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found'
            ], 404);
        }

        // Check authorization
        if ($import->organization_id !== auth()->user()->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'import' => [
                'id' => $import->id,
                'status' => $import->status,
                'file_name' => $import->file_name,
                'total_rows' => $import->total_rows,
                'processed_rows' => $import->processed_rows,
                'progress_percentage' => $import->progress_percentage,
                'stats' => $import->stats,
                'errors' => $import->errors,
                'started_at' => $import->started_at?->format('Y-m-d H:i:s'),
                'completed_at' => $import->completed_at?->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Cancel a running import
     */
    public function cancelImport($importId)
    {
        $import = SmartbillImport::find($importId);

        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found'
            ], 404);
        }

        // Check authorization
        if ($import->organization_id !== auth()->user()->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Only running or pending imports can be cancelled
        if (!in_array($import->status, ['running', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only running or pending imports can be cancelled'
            ], 400);
        }

        $import->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'errors' => array_merge($import->errors ?? [], ['Cancelled by user']),
        ]);

        // Clean up the temp file
        if ($import->file_path && Storage::disk('local')->exists($import->file_path)) {
            Storage::disk('local')->delete($import->file_path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Import cancelled successfully'
        ]);
    }

    /**
     * Delete an import record
     */
    public function deleteImport($importId)
    {
        $import = SmartbillImport::find($importId);

        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found'
            ], 404);
        }

        // Check authorization
        if ($import->organization_id !== auth()->user()->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Don't delete running imports - cancel them first
        if ($import->status === 'running') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a running import. Cancel it first.'
            ], 400);
        }

        // Clean up the temp file
        if ($import->file_path && Storage::disk('local')->exists($import->file_path)) {
            Storage::disk('local')->delete($import->file_path);
        }

        $import->delete();

        return response()->json([
            'success' => true,
            'message' => 'Import deleted successfully'
        ]);
    }

    /**
     * Format client name to Title Case
     */
    protected function formatClientName($name)
    {
        if (empty($name)) {
            return $name;
        }

        // Convert to Title Case using multibyte string function
        return mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8');
    }
}
