<?php

namespace App\Http\Controllers;

use App\Models\TaskSpace;
use Illuminate\Http\Request;

class TaskSpaceController extends Controller
{
    /**
     * Store a newly created space
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'position' => 'nullable|integer',
        ]);

        // Get the next position if not provided
        if (!isset($validated['position'])) {
            $maxPosition = TaskSpace::max('position');
            $validated['position'] = ($maxPosition ?? -1) + 1;
        }

        $space = TaskSpace::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'space' => $space,
                'message' => __('Space created successfully.')
            ]);
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', __('Space created successfully.'));
    }

    /**
     * Update the specified space
     */
    public function update(Request $request, TaskSpace $space)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'position' => 'nullable|integer',
        ]);

        $space->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'space' => $space->fresh(),
                'message' => __('Space updated successfully.')
            ]);
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', __('Space updated successfully.'));
    }

    /**
     * Remove the specified space
     */
    public function destroy(TaskSpace $space)
    {
        $space->delete();

        return response()->json([
            'success' => true,
            'message' => __('Space deleted successfully.'),
        ]);
    }

    /**
     * Update space position (for drag-drop reordering)
     */
    public function updatePosition(Request $request, TaskSpace $space)
    {
        $validated = $request->validate([
            'position' => 'required|integer|min:0',
        ]);

        $space->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('Space position updated successfully.'),
        ]);
    }
}
