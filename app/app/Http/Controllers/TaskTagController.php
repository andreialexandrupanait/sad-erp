<?php

namespace App\Http\Controllers;

use App\Models\TaskTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskTagController extends Controller
{
    /**
     * Display a listing of tags
     */
    public function index()
    {
        $tags = TaskTag::forOrganization(Auth::user()->organization_id)
                       ->ordered()
                       ->withCount('tasks')
                       ->get();

        return view('settings.task-tags.index', compact('tags'));
    }

    /**
     * Store a newly created tag
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $tag = TaskTag::create([
            'organization_id' => Auth::user()->organization_id,
            'name' => $validated['name'],
            'color' => strtoupper($validated['color']),
        ]);

        return response()->json([
            'success' => true,
            'tag' => $tag,
            'message' => 'Tag created successfully',
        ]);
    }

    /**
     * Update the specified tag
     */
    public function update(Request $request, TaskTag $tag)
    {
        // Ensure tag belongs to user's organization
        if ($tag->organization_id !== Auth::user()->organization_id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $tag->update([
            'name' => $validated['name'],
            'color' => strtoupper($validated['color']),
        ]);

        return response()->json([
            'success' => true,
            'tag' => $tag,
            'message' => 'Tag updated successfully',
        ]);
    }

    /**
     * Remove the specified tag
     */
    public function destroy(TaskTag $tag)
    {
        // Ensure tag belongs to user's organization
        if ($tag->organization_id !== Auth::user()->organization_id) {
            abort(403, 'Unauthorized');
        }

        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully',
        ]);
    }
}
