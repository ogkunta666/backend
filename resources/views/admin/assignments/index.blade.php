@extends('admin.layout')

@section('title', 'Task Assignments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-link-45deg"></i> Task Assignments</h2>
    <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Assign Task
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Task</th>
                        <th>Status</th>
                        <th>Assigned At</th>
                        <th>Completed At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr class="{{ $assignment->deleted_at ? 'table-secondary' : '' }}">
                            <td>{{ $assignment->id }}</td>
                            <td>
                                <strong>{{ $assignment->user->name }}</strong>
                                <br><small class="text-muted">{{ $assignment->user->email }}</small>
                            </td>
                            <td>
                                <strong>{{ $assignment->task->title }}</strong>
                                @if($assignment->task->priority)
                                    <br>
                                    @if($assignment->task->priority === 'high')
                                        <span class="badge bg-danger">High</span>
                                    @elseif($assignment->task->priority === 'medium')
                                        <span class="badge bg-warning text-dark">Medium</span>
                                    @else
                                        <span class="badge bg-info">Low</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($assignment->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($assignment->status === 'in_progress')
                                    <span class="badge bg-primary">In Progress</span>
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif
                            </td>
                            <td>{{ $assignment->assigned_at ? $assignment->assigned_at->format('Y-m-d H:i') : '-' }}</td>
                            <td>{{ $assignment->completed_at ? $assignment->completed_at->format('Y-m-d H:i') : '-' }}</td>
                            <td>
                                @if($assignment->deleted_at)
                                    <form action="{{ route('admin.assignments.restore', $assignment->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.assignments.edit', $assignment->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.assignments.destroy', $assignment->id) }}" method="POST" class="d-inline">
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
                                <p class="mt-2">No assignments found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $assignments->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
