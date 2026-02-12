@extends('admin.layout')

@section('title', 'Tasks Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-list-check"></i> Tasks Management</h2>
    <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Task
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Assignments</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr class="{{ $task->deleted_at ? 'table-secondary' : '' }}">
                            <td>{{ $task->id }}</td>
                            <td>
                                <strong>{{ $task->title }}</strong>
                                @if($task->description)
                                    <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($task->priority === 'high')
                                    <span class="badge bg-danger">High</span>
                                @elseif($task->priority === 'medium')
                                    <span class="badge bg-warning text-dark">Medium</span>
                                @else
                                    <span class="badge bg-info">Low</span>
                                @endif
                            </td>
                            <td>
                                @if($task->due_date)
                                    {{ $task->due_date->format('Y-m-d') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($task->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($task->status === 'in_progress')
                                    <span class="badge bg-primary">In Progress</span>
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-dark">{{ $task->taskAssignments->count() }} users</span>
                            </td>
                            <td>
                                @if($task->deleted_at)
                                    <form action="{{ route('admin.tasks.restore', $task->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.tasks.edit', $task->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.tasks.destroy', $task->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure?')" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2">No tasks found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $tasks->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
