<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Admin webes felület elérése (védett admin middleware-rel)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    Route::get('/users', function () {
        return view('admin.users');
    })->name('admin.users');
    
    Route::get('/tasks', function () {
        return view('admin.tasks');
    })->name('admin.tasks');
    
    Route::get('/assignments', function () {
        return view('admin.assignments');
    })->name('admin.assignments');
});
