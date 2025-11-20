<?php

namespace App\Http\Controllers;

use App\Models\TaskFolder;
use Illuminate\Http\Request;

class TaskFolderController extends Controller
{
    /**
     * Store a newly created folder
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'space_id' => 'required|exists:task_spaces,id',
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'position' => 'nullable|integer',
        ]);

        // Get the next position if not provided
        if (!isset($validated['position'])) {
            $maxPosition = TaskFolder::where('space_id', $validated['space_id'])->max('position');
            $validated['position'] = ($maxPosition ?? -1) + 1;
        }

        $folder = TaskFolder::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'folder' => $folder->load('space'),
                'message' => __('Folder created successfully.')
            ]);
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', __('Folder created successfully.'));
    }

    /**
     * Update the specified folder
     */
    public function update(Request $request, TaskFolder $folder)
    {
        $validated = $request->validate([
            'space_id' => 'required|exists:task_spaces,id',
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'position' => 'nullable|integer',
        ]);

        $folder->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'folder' => $folder->fresh(['space']),
                'message' => __('Folder updated successfully.')
            ]);
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', __('Folder updated successfully.'));
    }

    /**
     * Remove the specified folder
     */
    public function destroy(TaskFolder $folder)
    {
        $folder->delete();

        return response()->json([
            'success' => true,
            'message' => __('Folder deleted successfully.'),
        ]);
    }

    /**
     * Update folder position
     */
    public function updatePosition(Request $request, TaskFolder $folder)
    {
        $validated = $request->validate([
            'position' => 'required|integer|min:0',
            'space_id' => 'nullable|exists:task_spaces,id',
        ]);

        $folder->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('Folder position updated successfully.'),
        ]);
    }
}
