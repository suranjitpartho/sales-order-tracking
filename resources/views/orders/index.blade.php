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
                    <th>Final Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->product_id }}</td>
                        <td>{{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}</td>
                        <td>{{ ucfirst($order->product_category) }}</td>
                        <td>{{ ucfirst($order->buyer_gender) }}</td>
                        <td>${{ number_format($order->final_amount, 2) }}</td>
                        <td>
                            <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="status-update-form">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="status-select">
                                    <option value="pending" @if($order->status == 'pending') selected @endif>Pending</option>
                                    <option value="processing" @if($order->status == 'processing') selected @endif>Processing</option>
                                    <option value="shipped" @if($order->status == 'shipped') selected @endif>Shipped</option>
                                    <option value="delivered" @if($order->status == 'delivered') selected @endif>Delivered</option>
                                    <option value="cancelled" @if($order->status == 'cancelled') selected @endif>Cancelled</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('orders.show', $order) }}" class="btn-sm">View</a>
                            <a href="{{ route('orders.edit', $order) }}" class="btn-sm">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
