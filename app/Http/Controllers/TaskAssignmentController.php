<?php

namespace App\Http\Controllers;

use App\Models\Task_assignment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskAssignmentController extends Controller
{
    /**
     * Admin: List all task assignments
     */
    public function index()
    {
        $assignments = Task_assignment::withTrashed()
            ->with(['user', 'task'])
            ->get();

        return response()->json([
            'assignments' => $assignments
        ]);
    }

    /**
     * Admin: Assign a task to a user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'task_id' => 'required|exists:tasks,id',
            'assigned_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'status' => 'nullable|string|in:pending,in_progress,completed',
        ]);

        // Check if assignment already exists
        $existingAssignment = Task_assignment::withTrashed()
            ->where('user_id', $validated['user_id'])
            ->where('task_id', $validated['task_id'])
            ->first();

        if ($existingAssignment) {
            return response()->json([
                'message' => 'Task assignment already exists',
                'assignment' => $existingAssignment
            ], 409);
        }

        $assignment = Task_assignment::create([
            'user_id' => $validated['user_id'],
            'task_id' => $validated['task_id'],
            'assigned_at' => $validated['assigned_at'] ?? now(),
            'completed_at' => $validated['completed_at'] ?? null,
            'status' => $validated['status'] ?? 'pending',
        ]);

        return response()->json([
            'message' => 'Task assigned successfully',
            'assignment' => $assignment->load(['user', 'task'])
        ], 201);
    }

    /**
     * Admin: Show a specific task assignment
     */
    public function show($id)
    {
        $assignment = Task_assignment::withTrashed()
            ->with(['user', 'task'])
            ->findOrFail($id);

        return response()->json([
            'assignment' => $assignment
        ]);
    }

    /**
     * Admin: Update a task assignment
     */
    public function update(Request $request, $id)
    {
        $assignment = Task_assignment::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'task_id' => 'sometimes|exists:tasks,id',
            'assigned_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'status' => 'nullable|string|in:pending,in_progress,completed',
        ]);

        $assignment->update($validated);

        return response()->json([
            'message' => 'Task assignment updated successfully',
            'assignment' => $assignment->fresh(['user', 'task'])
        ]);
    }

    /**
     * Admin: Soft delete a task assignment
     */
    public function destroy($id)
    {
        $assignment = Task_assignment::findOrFail($id);
        $assignment->delete();

        return response()->json([
            'message' => 'Task assignment deleted successfully'
        ]);
    }

    /**
     * Admin: Restore a soft deleted task assignment
     */
    public function restore($id)
    {
        $assignment = Task_assignment::withTrashed()->findOrFail($id);
        $assignment->restore();

        return response()->json([
            'message' => 'Task assignment restored successfully',
            'assignment' => $assignment->fresh(['user', 'task'])
        ]);
    }

    /**
     * Admin: Get all assignments for a specific task
     */
    public function byTask($taskId)
    {
        Task::findOrFail($taskId); // Verify task exists

        $assignments = Task_assignment::withTrashed()
            ->where('task_id', $taskId)
            ->with('user')
            ->get();

        return response()->json([
            'assignments' => $assignments
        ]);
    }

    /**
     * Admin: Get all assignments for a specific user
     */
    public function byUser($userId)
    {
        User::findOrFail($userId); // Verify user exists

        $assignments = Task_assignment::withTrashed()
            ->where('user_id', $userId)
            ->with('task')
            ->get();

        return response()->json([
            'assignments' => $assignments
        ]);
    }
}
