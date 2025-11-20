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

        $list->update($validated);

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

        $list->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('List position updated successfully.'),
        ]);
    }
}
