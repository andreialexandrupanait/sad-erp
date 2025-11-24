<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskTimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskTimeEntryController extends Controller
{
    /**
     * Get all time entries for a task
     */
    public function index(Task $task)
    {
        $entries = $task->timeEntries()
                       ->with('user')
                       ->latest()
                       ->get();

        $totalMinutes = $entries->sum('minutes');
        $billableMinutes = $entries->where('billable', true)->sum('minutes');
        $nonBillableMinutes = $entries->where('billable', false)->sum('minutes');

        return response()->json([
            'success' => true,
            'entries' => $entries,
            'summary' => [
                'total_minutes' => $totalMinutes,
                'billable_minutes' => $billableMinutes,
                'non_billable_minutes' => $nonBillableMinutes,
                'total_formatted' => $this->formatMinutes($totalMinutes),
                'billable_formatted' => $this->formatMinutes($billableMinutes),
                'non_billable_formatted' => $this->formatMinutes($nonBillableMinutes),
            ],
        ]);
    }

    /**
     * Store a new time entry
     */
    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'description' => 'nullable|string|max:1000',
            'minutes' => 'required_without:started_at|integer|min:1',
            'billable' => 'nullable|boolean',
            'started_at' => 'nullable|date',
            'ended_at' => 'required_with:started_at|date|after:started_at',
        ]);

        // Calculate minutes from timestamps if provided
        if (isset($validated['started_at']) && isset($validated['ended_at'])) {
            $validated['minutes'] = TaskTimeEntry::calculateMinutesFromTimestamps(
                $validated['started_at'],
                $validated['ended_at']
            );
        }

        $entry = $task->timeEntries()->create([
            'user_id' => Auth::id(),
            'description' => $validated['description'] ?? null,
            'minutes' => $validated['minutes'],
            'billable' => $validated['billable'] ?? true,
            'started_at' => $validated['started_at'] ?? null,
            'ended_at' => $validated['ended_at'] ?? null,
        ]);

        // Update task's total time_tracked
        $task->update([
            'time_tracked' => $task->timeEntries()->sum('minutes'),
        ]);

        // Load user relationship for response
        $entry->load('user');

        return response()->json([
            'success' => true,
            'entry' => $entry,
            'message' => __('Time entry added successfully.'),
        ]);
    }

    /**
     * Update a time entry
     */
    public function update(Request $request, Task $task, TaskTimeEntry $entry)
    {
        // Verify entry belongs to task
        if ($entry->task_id !== $task->id) {
            return response()->json([
                'success' => false,
                'message' => __('Time entry not found.'),
            ], 404);
        }

        // Only the user who created the entry or admins can edit
        if ($entry->user_id !== Auth::id()) {
            // TODO: Add admin check here if needed
            return response()->json([
                'success' => false,
                'message' => __('You can only edit your own time entries.'),
            ], 403);
        }

        $validated = $request->validate([
            'description' => 'nullable|string|max:1000',
            'minutes' => 'required|integer|min:1',
            'billable' => 'nullable|boolean',
        ]);

        $entry->update($validated);

        // Update task's total time_tracked
        $task->update([
            'time_tracked' => $task->timeEntries()->sum('minutes'),
        ]);

        return response()->json([
            'success' => true,
            'entry' => $entry->fresh('user'),
            'message' => __('Time entry updated successfully.'),
        ]);
    }

    /**
     * Delete a time entry
     */
    public function destroy(Task $task, TaskTimeEntry $entry)
    {
        // Verify entry belongs to task
        if ($entry->task_id !== $task->id) {
            return response()->json([
                'success' => false,
                'message' => __('Time entry not found.'),
            ], 404);
        }

        // Only the user who created the entry or admins can delete
        if ($entry->user_id !== Auth::id()) {
            // TODO: Add admin check here if needed
            return response()->json([
                'success' => false,
                'message' => __('You can only delete your own time entries.'),
            ], 403);
        }

        $entry->delete();

        // Update task's total time_tracked
        $task->update([
            'time_tracked' => $task->timeEntries()->sum('minutes'),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Time entry deleted successfully.'),
        ]);
    }

    /**
     * Start a timer for a task
     */
    public function startTimer(Request $request, Task $task)
    {
        // Check if user already has a running timer
        $runningTimer = TaskTimeEntry::where('user_id', Auth::id())
                                    ->whereNull('ended_at')
                                    ->whereNotNull('started_at')
                                    ->first();

        if ($runningTimer) {
            return response()->json([
                'success' => false,
                'message' => __('You already have a running timer. Please stop it first.'),
                'running_timer' => $runningTimer->load('task', 'user'),
            ], 422);
        }

        $validated = $request->validate([
            'description' => 'nullable|string|max:1000',
        ]);

        $entry = $task->timeEntries()->create([
            'user_id' => Auth::id(),
            'description' => $validated['description'] ?? null,
            'minutes' => 0, // Will be calculated when timer stops
            'billable' => true,
            'started_at' => now(),
            'ended_at' => null,
        ]);

        $entry->load('user');

        return response()->json([
            'success' => true,
            'entry' => $entry,
            'message' => __('Timer started.'),
        ]);
    }

    /**
     * Stop a running timer
     */
    public function stopTimer(Request $request, Task $task, TaskTimeEntry $entry)
    {
        // Verify entry belongs to task and user
        if ($entry->task_id !== $task->id || $entry->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => __('Timer not found.'),
            ], 404);
        }

        // Verify timer is running
        if (!$entry->started_at || $entry->ended_at) {
            return response()->json([
                'success' => false,
                'message' => __('Timer is not running.'),
            ], 422);
        }

        $endedAt = now();
        $minutes = TaskTimeEntry::calculateMinutesFromTimestamps($entry->started_at, $endedAt);

        $entry->update([
            'ended_at' => $endedAt,
            'minutes' => $minutes,
        ]);

        // Update task's total time_tracked
        $task->update([
            'time_tracked' => $task->timeEntries()->sum('minutes'),
        ]);

        return response()->json([
            'success' => true,
            'entry' => $entry->fresh('user'),
            'message' => __('Timer stopped. Logged :duration.', ['duration' => $entry->formatted_duration]),
        ]);
    }

    /**
     * Get user's currently running timer
     */
    public function getRunningTimer()
    {
        $timer = TaskTimeEntry::where('user_id', Auth::id())
                              ->whereNull('ended_at')
                              ->whereNotNull('started_at')
                              ->with('task', 'user')
                              ->first();

        return response()->json([
            'success' => true,
            'timer' => $timer,
        ]);
    }

    /**
     * Helper to format minutes
     */
    private function formatMinutes(int $minutes): string
    {
        if ($minutes === 0) {
            return '0m';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$mins}m";
        }
    }
}
