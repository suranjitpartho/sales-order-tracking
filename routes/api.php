<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\StatusLogApiController;
use App\Http\Controllers\Api\AuthController;


/* -----------------
AUTHENTICATION
Here is the API for authentication.
*/

Route::post('/login',  [AuthController::class, 'login']);   // get a token
Route::post('/logout', [AuthController::class, 'logout'])
     ->middleware('auth:sanctum');                         // revoke token



Route::middleware('auth:sanctum')->group(function () {
    
    /* ---------------
    ORDER CRUD
    Here is the API for the order CRUD operations.
    The API is designed to handle the order data in a RESTful manner.
    */

    Route::get('/tasks', [TaskApiController::class, 'index']);           // GET all
    Route::get('/tasks/{id}', [TaskApiController::class, 'show']);       // GET one

    Route::post('/tasks', [TaskApiController::class, 'store']);                                 // POST an order
    Route::match(['put', 'patch'], '/tasks/{id}', [TaskApiController::class, 'update']);        // UPDATE an order
    Route::delete('/tasks/{id}', [TaskApiController::class, 'destroy']);                        // DELETE an order 


    /* -----------------
    ORDER STATUS LOGS
    Here is the API for changing the order status.
    The API is designed to handle the order status change in a RESTful manner.
    */

    Route::get('/tasks/{task}/status-logs', [StatusLogApiController::class, 'index']);      // GET all status logs
    Route::post('/tasks/{task}/status-logs', [StatusLogApiController::class, 'store']);     // POST a status log
    Route::get('/status-logs/{log}', [StatusLogApiController::class, 'show']);               // GET a status log
    Route::delete('/status-logs/{log}', [StatusLogApiController::class, 'destroy']);         // DELETE a status log

});