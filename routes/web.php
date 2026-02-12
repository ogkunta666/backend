<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AdminWebController;
use App\Http\Controllers\Web\WebAuthController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Login routes
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Admin webes felület elérése (védett admin middleware-rel)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminWebController::class, 'dashboard'])->name('dashboard');
    
    // Users CRUD
    Route::get('/users', [AdminWebController::class, 'usersIndex'])->name('users.index');
    Route::get('/users/create', [AdminWebController::class, 'usersCreate'])->name('users.create');
    Route::post('/users', [AdminWebController::class, 'usersStore'])->name('users.store');
    Route::get('/users/{id}/edit', [AdminWebController::class, 'usersEdit'])->name('users.edit');
    Route::put('/users/{id}', [AdminWebController::class, 'usersUpdate'])->name('users.update');
    Route::delete('/users/{id}', [AdminWebController::class, 'usersDestroy'])->name('users.destroy');
    Route::post('/users/{id}/restore', [AdminWebController::class, 'usersRestore'])->name('users.restore');
    
    // Tasks CRUD
    Route::get('/tasks', [AdminWebController::class, 'tasksIndex'])->name('tasks.index');
    Route::get('/tasks/create', [AdminWebController::class, 'tasksCreate'])->name('tasks.create');
    Route::post('/tasks', [AdminWebController::class, 'tasksStore'])->name('tasks.store');
    Route::get('/tasks/{id}/edit', [AdminWebController::class, 'tasksEdit'])->name('tasks.edit');
    Route::put('/tasks/{id}', [AdminWebController::class, 'tasksUpdate'])->name('tasks.update');
    Route::delete('/tasks/{id}', [AdminWebController::class, 'tasksDestroy'])->name('tasks.destroy');
    Route::post('/tasks/{id}/restore', [AdminWebController::class, 'tasksRestore'])->name('tasks.restore');
    
    // Task Assignments CRUD
    Route::get('/assignments', [AdminWebController::class, 'assignmentsIndex'])->name('assignments.index');
    Route::get('/assignments/create', [AdminWebController::class, 'assignmentsCreate'])->name('assignments.create');
    Route::post('/assignments', [AdminWebController::class, 'assignmentsStore'])->name('assignments.store');
    Route::get('/assignments/{id}/edit', [AdminWebController::class, 'assignmentsEdit'])->name('assignments.edit');
    Route::put('/assignments/{id}', [AdminWebController::class, 'assignmentsUpdate'])->name('assignments.update');
    Route::delete('/assignments/{id}', [AdminWebController::class, 'assignmentsDestroy'])->name('assignments.destroy');
    Route::post('/assignments/{id}/restore', [AdminWebController::class, 'assignmentsRestore'])->name('assignments.restore');
});
