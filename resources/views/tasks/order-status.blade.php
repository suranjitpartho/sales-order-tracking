@extends('layouts.app')
@section('title', 'Change Order Status')

@section('content')
    <h2 class="section-title">Change Order Status</h2>

    <form action="{{ route('tasks.status.store', $task->id) }}" method="POST" class="form-wrapper">
        @csrf
        @php
            // Fetch the very latest status-log (by changed_at, or by id if you prefer)
            $latestLog = $task
                ->statuslog()          // the Eloquent relation
                ->orderByDesc('changed_at')
                ->first();

            // If there isn’t one for some reason, fall back to “Pending”
            $current = $latestLog 
                ? $latestLog->status 
                : 'Pending';

            // Build your options array
            $statuses = ['Pending','Shipped','Delivered','Cancelled'];
        @endphp

        <div class="form-group">
            <label for="status">Order Status:</label>
            <select name="status" id="status" required>
                @foreach($statuses as $status)
                    <option value="{{ $status }}"
                        {{ old('status', $current) === $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </form>

    <br>

    <h3 class="section-title">Status Update History</h3>
    <table class="order-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($task->statuslog()->orderByDesc('changed_at')->get() as $log)
                <tr>
                    <td>{{ $log->task_id }}</td>
                    <td>{{ $log->created_at }}</td>
                    <td>{{ $log->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
