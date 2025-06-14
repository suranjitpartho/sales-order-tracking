@extends('layouts.app')
@section('title', 'Order List')

@section('content')
<div class="main-card">
    @if (session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif
    
    <h2 class="section-title">Order List</h2>
    <div class="table-wrapper">
        <table class="order-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product ID</th>
                    <th>Order Date</th>
                    <th>Category</th>
                    <th>Gender</th>
                    <th>Total Sales</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $task)
                    <tr>
                        <td>{{ $task->id }}</td>
                        <td>{{ $task->product_id }}</td>
                        <td>{{ \Carbon\Carbon::parse($task->order_date)->format('M d, Y') }}</td>
                        <td>{{ ucfirst($task->product_category) }}</td>
                        <td>{{ ucfirst($task->buyer_gender) }}</td>
                        <td>${{ number_format($task->total_sales, 2) }}</td>
                        <td>
                            <a href="{{ route('tasks.status.show', $task->id) }}" class="btn-sm-outline">
                                {{ $task->statuslog->last()->status ?? 'Pending' }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('tasks.show', $task) }}" class="btn-sm">View</a>
                            <a href="{{ route('tasks.edit', $task) }}" class="btn-sm">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
