<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;


/**
WEB ROUTES
These routes are used for web requests.
To handle HTTP requests and responses.
The routes are grouped under the 'auth' middleware, which means that only authenticated users can access them.
*/

Route::middleware(['auth'])->group(function() {
    Route::get('/', [TaskController::class, 'index']);
    Route::get('/dashboard', [TaskController::class, 'dashboard'])->name('dashboard');
    // CRUD Routes for tasks
    Route::resource('tasks', TaskController::class);
    Route::get('/tasks/{task}/order-status', [TaskController::class, 'showStatus'])->name('tasks.status.show');
    Route::post('/tasks/{task}/order-status', [TaskController::class, 'storeStatus'])->name('tasks.status.store');

});

/* 
BREEZE AUTH ROUTE
Linking Breeze Auth defautl routes, which are defined in the auth.php file.
These routes are used for user authentication, including registration, login, password reset, email verification, etc.
*/

require __DIR__.'/auth.php';