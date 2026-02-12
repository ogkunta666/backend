<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Get authenticated user's tasks
     */
    public function myTasks(Request $request)
    {
        $userId = $request->user()->id;
        
        $tasks = Task::whereHas('taskAssignments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['taskAssignments' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    /**
     * Update task status (completed_at) for authenticated user
     */
    public function updateStatus(Request $request, $id)
    {
        $userId = $request->user()->id;
        
        $task = Task::whereHas('taskAssignments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|string|in:pending,in_progress,completed',
            'completed_at' => 'nullable|date',
        ]);

        // Find the user's task assignment
        $assignment = $task->taskAssignments()->where('user_id', $userId)->first();
        
        if ($assignment) {
            if (isset($validated['completed_at'])) {
                $assignment->completed_at = $validated['completed_at'];
            }
            
            if (isset($validated['status'])) {
                $assignment->status = $validated['status'];
            }
            
            // Auto-set completed_at if status is completed
            if (isset($validated['status']) && $validated['status'] === 'completed' && !$assignment->completed_at) {
                $assignment->completed_at = now();
            }
            
            $assignment->save();
        }

        return response()->json([
            'message' => 'Task status updated successfully',
            'task' => $task->fresh(['taskAssignments'])
        ]);
    }

    /**
     * Admin: List all tasks
     */
    public function index()
    {
        $tasks = Task::withTrashed()->with('taskAssignments.user')->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    /**
     * Admin: Create a new task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string|in:pending,in_progress,completed',
        ]);

        $task = Task::create($validated);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task
        ], 201);
    }

    /**
     * Admin: Show a specific task
     */
    public function show($id)
    {
        $task = Task::withTrashed()->with('taskAssignments.user')->findOrFail($id);

        return response()->json([
            'task' => $task
        ]);
    }

    /**
     * Admin: Update a task
     */
    public function update(Request $request, $id)
    {
        $task = Task::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string|in:pending,in_progress,completed',
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task->fresh(['taskAssignments'])
        ]);
    }

    /**
     * Admin: Soft delete a task
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }

    /**
     * Admin: Restore a soft deleted task
     */
    public function restore($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $task->restore();

        return response()->json([
            'message' => 'Task restored successfully',
            'task' => $task->fresh(['taskAssignments'])
        ]);
    }
}
