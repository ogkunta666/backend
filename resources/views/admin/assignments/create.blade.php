@extends('admin.layout')

@section('title', 'Assign Task')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Assignments
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-link-45deg"></i> Assign Task to User</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.assignments.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                        <select class="form-select @error('user_id') is-invalid @enderror" 
                                id="user_id" name="user_id" required>
                            <option value="">-- Select User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
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
                            <option value="">-- Select Task --</option>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}" {{ old('task_id') == $task->id ? 'selected' : '' }}>
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
                            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="assigned_at" class="form-label">Assigned At</label>
                        <input type="datetime-local" class="form-control @error('assigned_at') is-invalid @enderror" 
                               id="assigned_at" name="assigned_at" value="{{ old('assigned_at') }}">
                        <small class="form-text text-muted">Leave blank to use current time</small>
                        @error('assigned_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Assign Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
