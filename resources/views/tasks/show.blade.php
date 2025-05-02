@extends('layouts.app')
@section('title', 'Order Details')

@section('content')

    <h2 class="section-title">Order: {{ $task->id }}</h2>
    <div class="order-info">
        <div><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($task->order_date)->format('M d, Y') }}</div>
        <div><strong>Product ID:</strong> {{ $task->product_id }}</div>
        <div><strong>Product Category:</strong> {{ ucfirst($task->product_category) }}</div>
        <div><strong>Buyer Gender:</strong> {{ ucfirst($task->buyer_gender) }}</div>
        <div><strong>Buyer Age:</strong> {{ $task->buyer_age }}</div>
        <div><strong>Order Location:</strong> {{ $task->order_location }}</div>
        <div><strong>International Shipping:</strong> {{ $task->international_shipping ? 'Yes' : 'No' }}</div>
        <div><strong>Sales Price:</strong> ${{ number_format($task->sales_price, 2) }}</div>
        <div><strong>Shipping Charges:</strong> ${{ number_format($task->shipping_charges, 2) }}</div>
        <div><strong>Sales Per Unit:</strong> ${{ number_format($task->sales_per_unit, 2) }}</div>
        <div><strong>Quantity:</strong> {{ $task->quantity }}</div>
        <div><strong>Total Sales:</strong> <span class="highlight">${{ number_format($task->total_sales, 2) }}</span></div>
        <div><strong>Status:</strong> {{ $task->statuslog->last()->status ?? 'Pending' }}</div>
        <div><strong>Remarks:</strong> {{ $task->remarks ?? 'N/A' }}</div>
    </div>

    <div class="form-actions">
        <a class="btn" href="{{ route('tasks.edit', $task->id) }}">Edit Order</a>
        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button class="btn" type="submit">Delete Order</button>
        </form>
        <a class="btn" href="{{ route('tasks.index') }}">Order List</a>
    </div>
    
@endsection
