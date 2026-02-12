@extends('admin.layout')

@section('title', 'Edit Assignment')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Assignments
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Assignment</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.assignments.update', $assignment->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                        <select class="form-select @error('user_id') is-invalid @enderror" 
                                id="user_id" name="user_id" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $assignment->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="task_id" class="form-label">Task <span class="text-danger">*</span></label>
                        <select class="form-select @error('task_id') is-invalid @enderror" 
                                id="task_id" name="task_id" required>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}" {{ old('task_id', $assignment->task_id) == $task->id ? 'selected' : '' }}>
                                    {{ $task->title }}
                                    @if($task->priority)
                                        [{{ ucfirst($task->priority) }}]
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('task_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" name="status">
                            <option value="pending" {{ old('status', $assignment->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ old('status', $assignment->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status', $assignment->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="assigned_at" class="form-label">Assigned At</label>
                        <input type="datetime-local" class="form-control @error('assigned_at') is-invalid @enderror" 
                               id="assigned_at" name="assigned_at" 
                               value="{{ old('assigned_at', $assignment->assigned_at ? $assignment->assigned_at->format('Y-m-d\TH:i') : '') }}">
                        @error('assigned_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="completed_at" class="form-label">Completed At</label>
                        <input type="datetime-local" class="form-control @error('completed_at') is-invalid @enderror" 
                               id="completed_at" name="completed_at" 
                               value="{{ old('completed_at', $assignment->completed_at ? $assignment->completed_at->format('Y-m-d\TH:i') : '') }}">
                        @error('completed_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> Update Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
