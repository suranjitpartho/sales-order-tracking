<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

// Home route (redirecting to tasks.index)
Route::get('/', [TaskController::class, 'index']);

// Dashboard Route
Route::get('/dashboard', [TaskController::class, 'dashboard'])->name('dashboard');

// CRUD routes for tasks
Route::resource('tasks', TaskController::class);
