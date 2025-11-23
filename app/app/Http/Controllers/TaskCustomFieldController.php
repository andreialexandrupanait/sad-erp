<?php

namespace App\Http\Controllers;

use App\Models\TaskCustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskCustomFieldController extends Controller
{
    /**
     * Display a listing of custom fields
     */
    public function index()
    {
        $customFields = TaskCustomField::forOrganization(Auth::user()->organization_id)
            ->ordered()
            ->get();

        return view('settings.task-custom-fields.index', compact('customFields'));
    }

    /**
     * Show the form for creating a new custom field
     */
    public function create()
    {
        return view('settings.task-custom-fields.create');
    }

    /**
     * Store a newly created custom field
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,number,date,dropdown,checkbox,email,url,phone',
            'description' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*' => 'string',
            'is_required' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        $validated['organization_id'] = Auth::user()->organization_id;
        $validated['is_active'] = true;
        $validated['order'] = $validated['order'] ?? TaskCustomField::forOrganization(Auth::user()->organization_id)->max('order') + 1;

        $customField = TaskCustomField::create($validated);

        return redirect()
            ->route('task-custom-fields.index')
            ->with('success', __('Custom field created successfully.'));
    }

    /**
     * Show the form for editing a custom field
     */
    public function edit(TaskCustomField $taskCustomField)
    {
        // Check organization match
        if ($taskCustomField->organization_id !== Auth::user()->organization_id) {
            abort(403);
        }

        return view('settings.task-custom-fields.edit', compact('taskCustomField'));
    }

    /**
     * Update a custom field
     */
    public function update(Request $request, TaskCustomField $taskCustomField)
    {
        // Check organization match
        if ($taskCustomField->organization_id !== Auth::user()->organization_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,number,date,dropdown,checkbox,email,url,phone',
            'description' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*' => 'string',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        $taskCustomField->update($validated);

        return redirect()
            ->route('task-custom-fields.index')
            ->with('success', __('Custom field updated successfully.'));
    }

    /**
     * Remove a custom field
     */
    public function destroy(TaskCustomField $taskCustomField)
    {
        // Check organization match
        if ($taskCustomField->organization_id !== Auth::user()->organization_id) {
            abort(403);
        }

        $taskCustomField->delete();

        return redirect()
            ->route('task-custom-fields.index')
            ->with('success', __('Custom field deleted successfully.'));
    }

    /**
     * Reorder custom fields (AJAX)
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'fields' => 'required|array',
            'fields.*.id' => 'required|exists:task_custom_fields,id',
            'fields.*.order' => 'required|integer',
        ]);

        foreach ($validated['fields'] as $field) {
            TaskCustomField::where('id', $field['id'])
                ->where('organization_id', Auth::user()->organization_id)
                ->update(['order' => $field['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => __('Custom fields reordered successfully.'),
        ]);
    }
}
