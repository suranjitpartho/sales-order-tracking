<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    public function index() {
        return response()->json(Task::latest()->get());
    }

    public function show($id) {
        $task = Task::find($id);
        return $task
            ? response()->json($task)
            : response()->json(['error' => 'Not found'], 404);
    }
}
