<?php

namespace App\Http\Controllers;

use App\Models\TaskList;
use Illuminate\Http\Request;

class TaskListController extends Controller
{
    /**
     * Store a newly created list
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'folder_id' => 'required|exists:task_folders,id',
            'client_id' => 'nullable|exists:clients,id',
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'position' => 'nullable|integer',
        ]);

        // Get the next position if not provided
        if (!isset($validated['position'])) {
            $maxPosition = TaskList::where('folder_id', $validated['folder_id'])->max('position');
            $validated['position'] = ($maxPosition ?? -1) + 1;
        }

        $list = TaskList::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'list' => $list->load('folder.space', 'client'),
                'message' => __('List created successfully.')
            ]);
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', __('List created successfully.'));
    }

    /**
     * Update the specified list
     */
    public function update(Request $request, TaskList $list)
    {
        $validated = $request->validate([
            'folder_id' => 'required|exists:task_folders,id',
            'client_id' => 'nullable|exists:clients,id',
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'position' => 'nullable|integer',
        ]);

        // Check if folder_id changed
        $folderChanged = $validated['folder_id'] != $list->folder_id;

        $list->update($validated);

        // Sync client status when folder changes
        if ($folderChanged && $list->client_id) {
            $this->syncClientStatusFromFolder($list->fresh(['folder', 'client']));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'list' => $list->fresh(['folder.space', 'client']),
                'message' => __('List updated successfully.')
            ]);
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', __('List updated successfully.'));
    }

    /**
     * Remove the specified list
     */
    public function destroy(TaskList $list)
    {
        $list->delete();

        return response()->json([
            'success' => true,
            'message' => __('List deleted successfully.'),
        ]);
    }

    /**
     * Update list position
     */
    public function updatePosition(Request $request, TaskList $list)
    {
        $validated = $request->validate([
            'position' => 'required|integer|min:0',
            'folder_id' => 'nullable|exists:task_folders,id',
        ]);

        // Check if folder_id changed (list moved to different folder)
        $folderChanged = isset($validated['folder_id']) && $validated['folder_id'] != $list->folder_id;

        $list->update($validated);

        // Sync client status when list moves to different folder
        if ($folderChanged && $list->client_id) {
            $this->syncClientStatusFromFolder($list->fresh(['folder', 'client']));
        }

        return response()->json([
            'success' => true,
            'message' => __('List position updated successfully.'),
        ]);
    }

    /**
     * Synchronize client status based on folder name
     */
    protected function syncClientStatusFromFolder(TaskList $list)
    {
        $folder = $list->folder;
        if (!$folder) {
            return;
        }

        // Map folder names to client status names
        // This mapping can be customized based on your folder naming convention
        $folderToStatusMap = [
            'mentenanta' => 'maintenance',
            'mentenanță' => 'maintenance',
            'maintenance' => 'maintenance',
            'activ' => 'active',
            'activi' => 'active',
            'active' => 'active',
            'vechi' => 'inactive',
            'old' => 'inactive',
            'inactive' => 'inactive',
            'arhiva' => 'archived',
            'archive' => 'archived',
            'archived' => 'archived',
        ];

        $folderNameLower = strtolower($folder->name);

        // Find matching status name from folder name
        $statusName = null;
        foreach ($folderToStatusMap as $folderKey => $statusValue) {
            if (str_contains($folderNameLower, $folderKey)) {
                $statusName = $statusValue;
                break;
            }
        }

        if (!$statusName) {
            return; // No matching status found
        }

        // Find the status option by label
        $status = \App\Models\SettingOption::where('category', 'client_statuses')
            ->where('is_active', true)
            ->where(function($query) use ($statusName) {
                $query->whereRaw('LOWER(label) LIKE ?', ['%' . $statusName . '%'])
                      ->orWhereRaw('LOWER(value) LIKE ?', ['%' . $statusName . '%']);
            })
            ->first();

        if ($status) {
            $client = $list->client;
            $client->status_id = $status->id;
            $client->save();
        }
    }
}
