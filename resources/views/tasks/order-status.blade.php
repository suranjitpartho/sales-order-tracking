@extends('layouts.app')
@section('title', 'Change Order Status')

@section('content')
    <h2 class="section-title">Change Order Status</h2>

    <form action="{{ route('tasks.status.store', $task->id) }}" method="POST" class="form-wrapper">
        @csrf
        <div class="form-group">
            <label for="status">Order Status:</label>
            <select name="status" id="status" required>
                <option value="">-- Select Category --</option>
                <option value="Pending">Pending</option>
                <option value="Shipped">Shipped</option>
                <option value="Delivered">Delivered</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </form>

    <br>

    <h3 class="section-title">Status Upate History</h3>
    <table class="order-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($task->statuslog as $log)
                <tr>
                    <td>{{ $log->task_id }}</td>
                    <td>{{ $log->created_at }}</td>
                    <td>{{ $log->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
