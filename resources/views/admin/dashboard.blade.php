@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="mb-4">
    <h2><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
    <p class="text-muted">Welcome to the Task Manager admin panel</p>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-white-50 mb-2">Total Users</h6>
                    <h3>{{ $stats['total_users'] }}</h3>
                    <small>{{ $stats['active_users'] }} active</small>
                </div>
                <div>
                    <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-white-50 mb-2">Total Tasks</h6>
                    <h3>{{ $stats['total_tasks'] }}</h3>
                    <small>{{ $stats['active_tasks'] }} active</small>
                </div>
                <div>
                    <i class="bi bi-list-check" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-white-50 mb-2">Assignments</h6>
                    <h3>{{ $stats['total_assignments'] }}</h3>
                    <small>{{ $stats['completed_assignments'] }} completed</small>
                </div>
                <div>
                    <i class="bi bi-link-45deg" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-person-plus" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Add User</p>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.tasks.create') }}" class="btn btn-outline-success w-100 py-3">
                            <i class="bi bi-plus-circle" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Create Task</p>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.assignments.create') }}" class="btn btn-outline-info w-100 py-3">
                            <i class="bi bi-link-45deg" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Assign Task</p>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100 py-3">
                            <i class="bi bi-list-ul" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">View All</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overview -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-people"></i> User Statistics</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Active Users</span>
                        <span class="text-success">{{ $stats['active_users'] }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" 
                             style="width: {{ $stats['total_users'] > 0 ? ($stats['active_users'] / $stats['total_users'] * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Deleted Users</span>
                        <span class="text-danger">{{ $stats['total_users'] - $stats['active_users'] }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-danger" 
                             style="width: {{ $stats['total_users'] > 0 ? (($stats['total_users'] - $stats['active_users']) / $stats['total_users'] * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-list-check"></i> Task Completion</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Completed</span>
                        <span class="text-success">{{ $stats['completed_assignments'] }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" 
                             style="width: {{ $stats['total_assignments'] > 0 ? ($stats['completed_assignments'] / $stats['total_assignments'] * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>In Progress / Pending</span>
                        <span class="text-warning">{{ $stats['total_assignments'] - $stats['completed_assignments'] }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" 
                             style="width: {{ $stats['total_assignments'] > 0 ? (($stats['total_assignments'] - $stats['completed_assignments']) / $stats['total_assignments'] * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
