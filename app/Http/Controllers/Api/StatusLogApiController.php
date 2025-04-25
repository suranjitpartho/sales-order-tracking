<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\StatusLog;

class StatusLogApiController extends Controller
{
    // GET  /api/tasks/{task}/status-logs
    // This method retrieves all status logs for a specific task.
    public function index($taskId)
    {
        $task = Task::find($taskId);
        if (! $task) {
            return response()->json(['error'=>'Task not found'], 404);
        }
        return response()->json($task->statuslog()->get());
    }

    // POST /api/tasks/{task}/status-logs
    // This method creates a new status log for a specific task.
    public function store(Request $request, $taskId)
    {
        $task = Task::find($taskId);
        if (! $task) {
            return response()->json(['error'=>'Task not found'], 404);
        }

        $data = $request->validate([
            'status' => 'required|in:Pending,Shipped,Delivered,Cancelled'
        ]);

        $log = StatusLog::create([
            'task_id'   => $task->id,
            'status'    => $data['status'],
            'changed_at'=> now(),
        ]);

        return response()->json($log, 201);
    }

    // GET  /api/status-logs/{log}
    // This method retrieves a specific status log by its ID.
    public function show($logId)
    {
        $log = StatusLog::find($logId);
        if (! $log) {
            return response()->json(['error'=>'Log not found'], 404);
        }
        return response()->json($log);
    }

    // DELETE /api/status-logs/{log}
    // This method deletes a specific status log by its ID.
    public function destroy($logId)
    {
        $log = StatusLog::find($logId);
        if (! $log) {
            return response()->json(['error'=>'Log not found'], 404);
        }
        $log->delete();
        return response()->noContent();
    }
}
