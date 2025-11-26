<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SettingOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskStatusController extends Controller
{
    /**
     * Display task statuses management page
     */
    public function index()
    {
        $statuses = SettingOption::where('category', 'task_statuses')
            ->where(function($query) {
                $query->where('organization_id', Auth::user()->organization_id)
                      ->orWhereNull('organization_id');
            })
            ->orderBy('sort_order')
            ->get();

        return view('settings.task-statuses.index', compact('statuses'));
    }

    /**
     * Store a new task status
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'value' => 'required|string|max:100|alpha_dash',
            'color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Check if value already exists for this organization
        $exists = SettingOption::where('category', 'task_statuses')
            ->where('organization_id', Auth::user()->organization_id)
            ->where('value', $validated['value'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => __('A status with this value already exists.'),
            ], 422);
        }

        // Get the next sort order if not provided
        if (!isset($validated['sort_order'])) {
            $maxOrder = SettingOption::where('category', 'task_statuses')
                ->where('organization_id', Auth::user()->organization_id)
                ->max('sort_order');
            $validated['sort_order'] = ($maxOrder ?? 0) + 1;
        }

        $status = SettingOption::create([
            'organization_id' => Auth::user()->organization_id,
            'category' => 'task_statuses',
            'label' => $validated['label'],
            'value' => $validated['value'],
            'color_class' => $validated['color'],
            'sort_order' => $validated['sort_order'],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'status' => $status,
            'message' => __('Status created successfully.'),
        ]);
    }

    /**
     * Update an existing task status
     */
    public function update(Request $request, SettingOption $status)
    {
        // Verify status is a task status and belongs to user's organization OR is global
        if ($status->category !== 'task_statuses') {
            return response()->json([
                'success' => false,
                'message' => __('Status not found.'),
            ], 404);
        }

        // Check authorization: either owns the status or it's a global status (null organization_id)
        if ($status->organization_id !== null && $status->organization_id !== Auth::user()->organization_id) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have permission to edit this status.'),
            ], 403);
        }

        $uniqueRule = Rule::unique('settings_options', 'value')
            ->where('category', 'task_statuses')
            ->ignore($status->id);

        // Add organization scope to unique check
        if ($status->organization_id === null) {
            $uniqueRule->whereNull('organization_id');
        } else {
            $uniqueRule->where('organization_id', $status->organization_id);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'value' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                $uniqueRule,
            ],
            'color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $status->update([
            'label' => $validated['label'],
            'value' => $validated['value'],
            'color_class' => $validated['color'],
            'sort_order' => $validated['sort_order'] ?? $status->sort_order,
            'is_active' => $validated['is_active'] ?? $status->is_active,
        ]);

        return response()->json([
            'success' => true,
            'status' => $status->fresh(),
            'message' => __('Status updated successfully.'),
        ]);
    }

    /**
     * Delete a task status
     */
    public function destroy(SettingOption $status)
    {
        // Verify status is a task status
        if ($status->category !== 'task_statuses') {
            return response()->json([
                'success' => false,
                'message' => __('Status not found.'),
            ], 404);
        }

        // Check authorization: either owns the status or it's a global status
        if ($status->organization_id !== null && $status->organization_id !== Auth::user()->organization_id) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have permission to delete this status.'),
            ], 403);
        }

        // Check if status is being used by any tasks
        $tasksCount = $status->tasks()->count();

        if ($tasksCount > 0) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot delete status. It is currently assigned to :count task(s).', ['count' => $tasksCount]),
            ], 422);
        }

        $status->delete();

        return response()->json([
            'success' => true,
            'message' => __('Status deleted successfully.'),
        ]);
    }

    /**
     * Reorder task statuses
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'statuses' => 'required|array',
            'statuses.*.id' => 'required|exists:settings_options,id',
            'statuses.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['statuses'] as $statusData) {
            $status = SettingOption::find($statusData['id']);

            // Verify ownership and category
            if ($status->organization_id === Auth::user()->organization_id && $status->category === 'task_statuses') {
                $status->update(['sort_order' => $statusData['sort_order']]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('Status order updated successfully.'),
        ]);
    }
}
