<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AIAgentController;
use App\Http\Controllers\MCPServerController;


/**
WEB ROUTES
These routes are used for web requests.
To handle HTTP requests and responses.
The routes are grouped under the 'auth' middleware, which means that only authenticated users can access them.
*/

Route::middleware(['auth'])->group(function() {
    Route::get('/', [TaskController::class, 'index']);
    Route::get('/dashboard', [TaskController::class, 'dashboard'])->name('dashboard');
    Route::resource('tasks', TaskController::class);    // CRUD Routes for tasks
    Route::get('/tasks/{task}/order-status', [TaskController::class, 'showStatus'])->name('tasks.status.show');
    Route::post('/tasks/{task}/order-status', [TaskController::class, 'storeStatus'])->name('tasks.status.store');

    Route::get('/ai-agent', [AIAgentController::class, 'index'])->name('ai-agent.index');
    Route::post('/ai-agent/ask', [AIAgentController::class, 'ask'])->name('ai-agent.ask');
    Route::post('/ai-agent/download-csv', [AIAgentController::class, 'downloadCsv'])->name('ai-agent.download.csv');

    Route::get('/mcp', [MCPServerController::class, 'index'])->name('mcp.index');
    Route::post('/mcp', [MCPServerController::class, 'ask'])->name('mcp.ask');
});


/* 
BREEZE AUTH ROUTE
Linking Breeze Auth defautl routes, which are defined in the auth.php file.
These routes are used for user authentication, including registration, login, password reset, email verification, etc.
*/

require __DIR__.'/auth.php';