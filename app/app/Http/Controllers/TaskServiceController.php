<?php

namespace App\Http\Controllers;

use App\Models\TaskService;
use Illuminate\Http\Request;

class TaskServiceController extends Controller
{
    /**
     * Display a listing of task services
     */
    public function index(Request $request)
    {
        $query = TaskService::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $services = $query->ordered()->paginate(50)->withQueryString();

        return view('task-services.index', compact('services'));
    }

    /**
     * Show the form for creating a new service
     */
    public function create()
    {
        return view('task-services.create');
    }

    /**
     * Store a newly created service in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_hourly_rate' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Set is_active default value if not provided
        $validated['is_active'] = $validated['is_active'] ?? true;

        $service = TaskService::create($validated);

        return redirect()
            ->route('task-services.index')
            ->with('success', __('Service created successfully.'));
    }

    /**
     * Display the specified service
     */
    public function show(TaskService $taskService)
    {
        $taskService->load(['tasks', 'clientRates.client']);

        return view('task-services.show', compact('taskService'));
    }

    /**
     * Show the form for editing the specified service
     */
    public function edit(TaskService $taskService)
    {
        return view('task-services.edit', compact('taskService'));
    }

    /**
     * Update the specified service in storage
     */
    public function update(Request $request, TaskService $taskService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_hourly_rate' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $taskService->update($validated);

        return redirect()
            ->route('task-services.index')
            ->with('success', __('Service updated successfully.'));
    }

    /**
     * Remove the specified service from storage
     */
    public function destroy(TaskService $taskService)
    {
        $taskService->delete();

        return redirect()
            ->route('task-services.index')
            ->with('success', __('Service deleted successfully.'));
    }
}
