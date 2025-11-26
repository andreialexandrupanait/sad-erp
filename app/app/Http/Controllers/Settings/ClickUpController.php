<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\ClickUpSync;
use App\Services\ClickUp\ClickUpAuthService;
use App\Services\ClickUp\ClickUpImporter;
use App\Jobs\ImportClickUpWorkspaceJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickUpController extends Controller
{
    /**
     * Show ClickUp settings and import page
     */
    public function index()
    {
        $organization = auth()->user()->organization;
        $clickUpSettings = $organization->settings['clickup'] ?? [];

        // Check if credentials are configured
        $hasCredentials = !empty($clickUpSettings['token']) || !empty(config('services.clickup.personal_token'));

        // Get recent sync history
        $recentSyncs = ClickUpSync::where('organization_id', $organization->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('settings.clickup.index', compact('hasCredentials', 'clickUpSettings', 'recentSyncs'));
    }

    /**
     * Update ClickUp credentials
     */
    public function updateCredentials(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|max:255',
            'workspace_id' => 'nullable|string|max:100',
        ]);

        $organization = auth()->user()->organization;
        $settings = $organization->settings;
        $settings['clickup'] = $validated;
        $organization->settings = $settings;
        $organization->save();

        return redirect()->route('settings.clickup.index')
            ->with('success', 'ClickUp credentials updated successfully!');
    }

    /**
     * Test ClickUp API connection
     */
    public function testConnection()
    {
        try {
            $organization = auth()->user()->organization;
            $clickUpSettings = $organization->settings['clickup'] ?? [];

            $token = $clickUpSettings['token'] ?? config('services.clickup.personal_token');

            if (empty($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ClickUp token not configured'
                ], 400);
            }

            $authService = new ClickUpAuthService();
            $client = $authService->getClientWithPersonalToken($token);

            $user = $client->testConnection();

            return response()->json([
                'success' => true,
                'message' => 'Connection successful!',
                'user' => [
                    'username' => $user['user']['username'] ?? 'Unknown',
                    'email' => $user['user']['email'] ?? 'Unknown',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show import form
     */
    public function showImportForm()
    {
        $organization = auth()->user()->organization;
        $clickUpSettings = $organization->settings['clickup'] ?? [];

        $hasCredentials = !empty($clickUpSettings['token']) || !empty(config('services.clickup.personal_token'));

        if (!$hasCredentials) {
            return redirect()->route('settings.clickup.index')
                ->with('error', 'Please configure your ClickUp credentials first.');
        }

        return view('settings.clickup.import', compact('clickUpSettings'));
    }

    /**
     * Start workspace import
     */
    public function startImport(Request $request)
    {
        $validated = $request->validate([
            'workspace_id' => 'required|string|max:100',
            'import_tasks' => 'nullable|boolean',
            'import_time_entries' => 'nullable|boolean',
            'import_comments' => 'nullable|boolean',
            'import_attachments' => 'nullable|boolean',
            'download_attachments' => 'nullable|boolean',
            'update_existing' => 'nullable|boolean',
        ]);

        try {
            $organization = auth()->user()->organization;
            $clickUpSettings = $organization->settings['clickup'] ?? [];
            $token = $clickUpSettings['token'] ?? config('services.clickup.personal_token');

            if (empty($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ClickUp token not configured'
                ], 400);
            }

            // Build import options
            $options = [
                'import_tasks' => $validated['import_tasks'] ?? true,
                'import_time_entries' => $validated['import_time_entries'] ?? false,
                'import_comments' => $validated['import_comments'] ?? false,
                'import_attachments' => $validated['import_attachments'] ?? false,
                'download_attachments' => $validated['download_attachments'] ?? false,
                'update_existing' => $validated['update_existing'] ?? false,
                'include_closed' => true,
                'import_assignees' => true,
                'import_watchers' => true,
                'import_tags' => true,
                'import_checklists' => true,
            ];

            // Create sync record
            $sync = ClickUpSync::create([
                'organization_id' => $organization->id,
                'user_id' => auth()->id(),
                'sync_type' => 'workspace',
                'clickup_workspace_id' => $validated['workspace_id'],
                'status' => 'pending',
                'options' => $options,
            ]);

            // Dispatch background job
            ImportClickUpWorkspaceJob::dispatch(
                $organization->id,
                auth()->id(),
                $validated['workspace_id'],
                $options,
                $sync->id,
                $token
            );

            return response()->json([
                'success' => true,
                'sync_id' => $sync->id,
                'message' => 'Import started successfully! You can monitor progress below.'
            ]);

        } catch (\Exception $e) {
            Log::error('ClickUp import start failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start import: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sync status (for polling)
     */
    public function getSyncStatus($syncId)
    {
        $sync = ClickUpSync::find($syncId);

        if (!$sync) {
            return response()->json([
                'success' => false,
                'message' => 'Sync not found'
            ], 404);
        }

        // Check authorization
        if ($sync->organization_id !== auth()->user()->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'sync' => [
                'id' => $sync->id,
                'status' => $sync->status,
                'stats' => $sync->stats,
                'errors' => $sync->errors,
                'started_at' => $sync->started_at?->format('Y-m-d H:i:s'),
                'completed_at' => $sync->completed_at?->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Get sync progress via Server-Sent Events
     */
    public function getSyncProgress($syncId)
    {
        return response()->stream(function () use ($syncId) {
            $sync = ClickUpSync::find($syncId);

            if (!$sync) {
                echo "data: " . json_encode(['error' => 'Sync not found']) . "\n\n";
                ob_flush();
                flush();
                return;
            }

            $lastStatus = null;
            $maxDuration = 1800; // 30 minutes max
            $startTime = time();

            while (true) {
                if (time() - $startTime > $maxDuration) {
                    echo "data: " . json_encode(['error' => 'Timeout']) . "\n\n";
                    ob_flush();
                    flush();
                    break;
                }

                // Reload sync from database
                $sync->refresh();

                $currentData = [
                    'status' => $sync->status,
                    'stats' => $sync->stats,
                    'errors' => $sync->errors,
                    'started_at' => $sync->started_at?->toIso8601String(),
                    'completed_at' => $sync->completed_at?->toIso8601String(),
                ];

                // Only send if status changed
                if ($currentData !== $lastStatus) {
                    echo "data: " . json_encode($currentData) . "\n\n";
                    ob_flush();
                    flush();
                    $lastStatus = $currentData;
                }

                // Stop if completed or failed
                if (in_array($sync->status, ['completed', 'failed'])) {
                    break;
                }

                sleep(2); // Check every 2 seconds
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Get list of workspaces (helper endpoint)
     */
    public function getWorkspaces()
    {
        try {
            $organization = auth()->user()->organization;
            $clickUpSettings = $organization->settings['clickup'] ?? [];
            $token = $clickUpSettings['token'] ?? config('services.clickup.personal_token');

            if (empty($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ClickUp token not configured'
                ], 400);
            }

            // Try to get teams using the ClickUp API
            // Note: The /team endpoint may return 404 for some tokens/accounts
            // In that case, users need to manually enter their workspace ID
            try {
                $response = Http::withHeaders([
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ])
                    ->timeout(30)
                    ->get('https://api.clickup.com/api/v2/team');

                if ($response->successful()) {
                    $data = $response->json();
                    $teams = $data['teams'] ?? [];

                    return response()->json([
                        'success' => true,
                        'workspaces' => $teams
                    ]);
                }

                // If 404, return helpful message
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to automatically fetch workspaces. Please enter your Workspace ID manually. You can find it in ClickUp under Settings → Workspace → Workspace ID, or in the URL when you open ClickUp (e.g., https://app.clickup.com/[WORKSPACE_ID]/...)'
                ], 400);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to fetch workspaces: ' . $e->getMessage() . '. Please enter your Workspace ID manually.'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a running sync
     */
    public function cancelSync($syncId)
    {
        $sync = ClickUpSync::find($syncId);

        if (!$sync) {
            return response()->json([
                'success' => false,
                'message' => 'Sync not found'
            ], 404);
        }

        // Check authorization
        if ($sync->organization_id !== auth()->user()->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Only running or pending syncs can be cancelled
        if (!in_array($sync->status, ['running', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only running or pending syncs can be cancelled'
            ], 400);
        }

        $sync->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'errors' => array_merge($sync->errors ?? [], ['Cancelled by user']),
        ]);

        Log::info('ClickUp sync cancelled by user', [
            'sync_id' => $sync->id,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sync cancelled successfully'
        ]);
    }

    /**
     * Delete a sync record
     */
    public function deleteSync($syncId)
    {
        $sync = ClickUpSync::find($syncId);

        if (!$sync) {
            return response()->json([
                'success' => false,
                'message' => 'Sync not found'
            ], 404);
        }

        // Check authorization
        if ($sync->organization_id !== auth()->user()->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Don't delete running syncs - cancel them first
        if ($sync->status === 'running') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a running sync. Cancel it first.'
            ], 400);
        }

        $sync->delete();

        Log::info('ClickUp sync deleted', [
            'sync_id' => $syncId,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sync deleted successfully'
        ]);
    }

    /**
     * Retry a failed sync
     */
    public function retrySync($syncId)
    {
        $sync = ClickUpSync::find($syncId);

        if (!$sync) {
            return response()->json([
                'success' => false,
                'message' => 'Sync not found'
            ], 404);
        }

        // Check authorization
        if ($sync->organization_id !== auth()->user()->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Only failed or cancelled syncs can be retried
        if (!in_array($sync->status, ['failed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only failed or cancelled syncs can be retried'
            ], 400);
        }

        $organization = auth()->user()->organization;
        $clickUpSettings = $organization->settings['clickup'] ?? [];
        $token = $clickUpSettings['token'] ?? config('services.clickup.personal_token');

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'ClickUp token not configured'
            ], 400);
        }

        // Create new sync record (keep the old one for history)
        $newSync = ClickUpSync::create([
            'organization_id' => $sync->organization_id,
            'user_id' => auth()->id(),
            'sync_type' => $sync->sync_type,
            'clickup_workspace_id' => $sync->clickup_workspace_id,
            'clickup_list_id' => $sync->clickup_list_id,
            'status' => 'pending',
            'options' => $sync->options,
        ]);

        // Dispatch background job
        ImportClickUpWorkspaceJob::dispatch(
            $sync->organization_id,
            auth()->id(),
            $sync->clickup_workspace_id,
            $sync->options ?? [],
            $newSync->id,
            $token
        );

        Log::info('ClickUp sync retried', [
            'original_sync_id' => $sync->id,
            'new_sync_id' => $newSync->id,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'sync_id' => $newSync->id,
            'message' => 'Sync restarted successfully'
        ]);
    }
}
