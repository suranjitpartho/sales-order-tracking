@extends('layouts.app')
@section('title', 'Order Details')

@section('content')
    <div class="main-card">
        <h2 class="section-title">Order: {{ $order->id }}</h2>
        <div class="order-info">
            <div><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}</div>
            <div><strong>Product ID:</strong> {{ $order->product_id }}</div>
            <div><strong>Product Category:</strong> {{ ucfirst($order->product_category) }}</div>
            <div><strong>Buyer Gender:</strong> {{ ucfirst($order->buyer_gender) }}</div>
            <div><strong>Buyer Age:</strong> {{ $order->buyer_age }}</div>
            <div><strong>Order Location:</strong> {{ $order->order_location }}</div>
            <div><strong>International Shipping:</strong> {{ $order->international_shipping ? 'Yes' : 'No' }}</div>
            <div><strong>Base Price:</strong> ${{ number_format($order->base_price, 2) }}</div>
            <div><strong>Shipping Fee:</strong> ${{ number_format($order->shipping_fee, 2) }}</div>
            <div><strong>Unit Price:</strong> ${{ number_format($order->unit_price, 2) }}</div>
            <div><strong>Quantity:</strong> {{ $order->quantity }}</div>
            <div><strong>Final Amount:</strong> <span class="highlight">${{ number_format($order->final_amount, 2) }}</span></div>
            <div><strong>Status:</strong> {{ $order->status }}</div>
            <div><strong>Remarks:</strong> {{ $order->remarks ?? 'N/A' }}</div>
        </div>

        <div class="form-actions">
            <a class="btn" href="{{ route('orders.edit', $order->id) }}">Edit Order</a>
            <form action="{{ route('orders.destroy', $order->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button class="btn" type="submit">Delete Order</button>
            </form>
            <a class="btn" href="{{ route('orders.index') }}">Order List</a>
        </div>
    </div>
@endsection
