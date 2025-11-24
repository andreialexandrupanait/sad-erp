<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskChecklist;
use App\Models\TaskChecklistItem;
use Illuminate\Http\Request;

class TaskChecklistController extends Controller
{
    /**
     * Create a new checklist for a task
     */
    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $position = $task->checklists()->max('position') + 1;

        $checklist = $task->checklists()->create([
            'name' => $validated['name'],
            'position' => $position,
        ]);

        $checklist->load('items');

        return response()->json([
            'success' => true,
            'checklist' => $checklist,
        ]);
    }

    /**
     * Update a checklist name
     */
    public function update(Request $request, Task $task, TaskChecklist $checklist)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $checklist->update($validated);

        return response()->json([
            'success' => true,
            'checklist' => $checklist,
        ]);
    }

    /**
     * Delete a checklist
     */
    public function destroy(Task $task, TaskChecklist $checklist)
    {
        $checklist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Checklist deleted successfully',
        ]);
    }

    /**
     * Add an item to a checklist
     */
    public function storeItem(Request $request, Task $task, TaskChecklist $checklist)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $position = $checklist->items()->max('position') + 1;

        $item = $checklist->items()->create([
            'text' => $validated['text'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'position' => $position,
        ]);

        $item->load('assignedUser');

        return response()->json([
            'success' => true,
            'item' => $item,
        ]);
    }

    /**
     * Update a checklist item
     */
    public function updateItem(Request $request, TaskChecklistItem $item)
    {
        $validated = $request->validate([
            'text' => 'sometimes|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $item->update($validated);
        $item->load('assignedUser');

        return response()->json([
            'success' => true,
            'item' => $item,
        ]);
    }

    /**
     * Toggle checklist item completion
     */
    public function toggleItem(TaskChecklistItem $item)
    {
        $item->toggle();
        $item->load('assignedUser');

        return response()->json([
            'success' => true,
            'item' => $item,
        ]);
    }

    /**
     * Delete a checklist item
     */
    public function destroyItem(TaskChecklistItem $item)
    {
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully',
        ]);
    }

    /**
     * Reorder checklist items
     */
    public function reorderItems(Request $request, TaskChecklist $checklist)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:task_checklist_items,id',
            'items.*.position' => 'required|integer',
        ]);

        foreach ($validated['items'] as $itemData) {
            TaskChecklistItem::where('id', $itemData['id'])
                ->update(['position' => $itemData['position']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Items reordered successfully',
        ]);
    }
}
