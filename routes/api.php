<?php

use App\Http\Controllers\Api\TaskApiController;

Route::get('/tasks', [TaskApiController::class, 'index']);           // GET all tasks
Route::get('/tasks/{id}', [TaskApiController::class, 'show']);       // GET one task
