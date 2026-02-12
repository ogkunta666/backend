<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use App\Models\Task_assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminWebController extends Controller
{
    // ==================== Dashboard ====================
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::whereNull('deleted_at')->count(),
            'total_tasks' => Task::count(),
            'active_tasks' => Task::whereNull('deleted_at')->count(),
            'total_assignments' => Task_assignment::count(),
            'completed_assignments' => Task_assignment::where('status', 'completed')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    // ==================== Users ====================
    public function usersIndex()
    {
        $users = User::withTrashed()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function usersCreate()
    {
        return view('admin.users.create');
    }

    public function usersStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'is_admin' => 'nullable|boolean',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'department' => $validated['department'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_admin' => $request->has('is_admin'),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    public function usersEdit($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function usersUpdate(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'is_admin' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'department' => $validated['department'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_admin' => $request->has('is_admin'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function usersDestroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }

    public function usersRestore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.index')->with('success', 'User restored successfully!');
    }

    // ==================== Tasks ====================
    public function tasksIndex()
    {
        $tasks = Task::withTrashed()->with('taskAssignments')->paginate(15);
        return view('admin.tasks.index', compact('tasks'));
    }

    public function tasksCreate()
    {
        return view('admin.tasks.create');
    }

    public function tasksStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string|in:pending,in_progress,completed',
        ]);

        Task::create($validated);

        return redirect()->route('admin.tasks.index')->with('success', 'Task created successfully!');
    }

    public function tasksEdit($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        return view('admin.tasks.edit', compact('task'));
    }

    public function tasksUpdate(Request $request, $id)
    {
        $task = Task::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string|in:pending,in_progress,completed',
        ]);

        $task->update($validated);

        return redirect()->route('admin.tasks.index')->with('success', 'Task updated successfully!');
    }

    public function tasksDestroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return redirect()->route('admin.tasks.index')->with('success', 'Task deleted successfully!');
    }

    public function tasksRestore($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $task->restore();

        return redirect()->route('admin.tasks.index')->with('success', 'Task restored successfully!');
    }

    // ==================== Assignments ====================
    public function assignmentsIndex()
    {
        $assignments = Task_assignment::withTrashed()->with(['user', 'task'])->paginate(15);
        return view('admin.assignments.index', compact('assignments'));
    }

    public function assignmentsCreate()
    {
        $users = User::all();
        $tasks = Task::all();
        return view('admin.assignments.create', compact('users', 'tasks'));
    }

    public function assignmentsStore(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'task_id' => 'required|exists:tasks,id',
            'status' => 'nullable|string|in:pending,in_progress,completed',
            'assigned_at' => 'nullable|date',
        ]);

        // Check if assignment already exists
        $exists = Task_assignment::withTrashed()
            ->where('user_id', $validated['user_id'])
            ->where('task_id', $validated['task_id'])
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'This task is already assigned to the user!');
        }

        Task_assignment::create([
            'user_id' => $validated['user_id'],
            'task_id' => $validated['task_id'],
            'status' => $validated['status'] ?? 'pending',
            'assigned_at' => $validated['assigned_at'] ?? now(),
        ]);

        return redirect()->route('admin.assignments.index')->with('success', 'Task assigned successfully!');
    }

    public function assignmentsEdit($id)
    {
        $assignment = Task_assignment::withTrashed()->with(['user', 'task'])->findOrFail($id);
        $users = User::all();
        $tasks = Task::all();
        return view('admin.assignments.edit', compact('assignment', 'users', 'tasks'));
    }

    public function assignmentsUpdate(Request $request, $id)
    {
        $assignment = Task_assignment::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'task_id' => 'required|exists:tasks,id',
            'status' => 'nullable|string|in:pending,in_progress,completed',
            'assigned_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
        ]);

        $assignment->update($validated);

        return redirect()->route('admin.assignments.index')->with('success', 'Assignment updated successfully!');
    }

    public function assignmentsDestroy($id)
    {
        $assignment = Task_assignment::findOrFail($id);
        $assignment->delete();

        return redirect()->route('admin.assignments.index')->with('success', 'Assignment deleted successfully!');
    }

    public function assignmentsRestore($id)
    {
        $assignment = Task_assignment::withTrashed()->findOrFail($id);
        $assignment->restore();

        return redirect()->route('admin.assignments.index')->with('success', 'Assignment restored successfully!');
    }
}
