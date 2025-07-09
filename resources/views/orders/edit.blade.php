@extends('layouts.app')
@section('title', 'Edit Order')

@section('content')
<div class="main-card">

  <h2 class="section-title">Edit Order: {{ $order->id }}</h2>

  @if($errors->any())
    <ul>
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  @endif

  <form action="{{ route('orders.update', $order->id) }}" method="POST" class="form-wrapper">
      @csrf
      @method('PUT')
      <div class="form-group">
      <label for="order_date">Order Date</label>
      <input type="date" name="order_date" id="order_date" value="{{ $order->order_date }}" required>
    </div>

    <div class="form-group">
      <label for="product_id">Product ID</label>
      <select name="product_id" id="product_id" required>
        <option value="SX9001" {{ $order->product_id == 'SX9001' ? 'selected' : '' }}>SX9001</option>
        <option value="SX9002" {{ $order->product_id == 'SX9002' ? 'selected' : '' }}>SX9002</option>
        <option value="SX9003" {{ $order->product_id == 'SX9003' ? 'selected' : '' }}>SX9003</option>
        <option value="SX9004" {{ $order->product_id == 'SX9004' ? 'selected' : '' }}>SX9004</option>
        <option value="SX9005" {{ $order->product_id == 'SX9005' ? 'selected' : '' }}>SX9005</option>
        <option value="SX9006" {{ $order->product_id == 'SX9006' ? 'selected' : '' }}>SX9006</option>
      </select>
      <!-- <input type="text" name="product_id" id="product_id" maxlength="6" value="{{ $order->product_id }}" required> -->
    </div>

    <div class="form-group">
      <label for="product_category">Product Category</label>
      <select name="product_category" id="product_category" required>
        <option value="clothing" {{ $order->product_category == 'clothing' ? 'selected' : '' }}>Clothing</option>
        <option value="ornaments" {{ $order->product_category == 'ornaments' ? 'selected' : '' }}>Ornaments</option>
        <option value="other" {{ $order->product_category == 'other' ? 'selected' : '' }}>Other</option>
      </select>
    </div>

    <div class="form-group">
      <label for="buyer_gender">Buyer Gender</label>
      <select name="buyer_gender" id="buyer_gender" required>
        <option value="male" {{ $order->buyer_gender == 'male' ? 'selected' : '' }}>Male</option>
        <option value="female" {{ $order->buyer_gender == 'female' ? 'selected' : '' }}>Female</option>
      </select>
    </div>

    <div class="form-group">
      <label for="buyer_age">Buyer Age</label>
      <input type="number" name="buyer_age" id="buyer_age" min="0" value="{{ $order->buyer_age }}" required>
    </div>

    <div class="form-group">
      <label for="order_location">Order Location</label>
      <textarea name="order_location" id="order_location" rows="1" required>{{ $order->order_location }}</textarea>
    </div>

    <div class="form-group checkbox">
      <label>
        <input type="checkbox" name="international_shipping" value="1" {{ $order->international_shipping ? 'checked' : '' }}>
        International Shipping?
      </label>
    </div>

    <div class="form-group">
      <label for="base_price">Base Price</label>
      <input type="number" name="base_price" id="base_price" step="0.01" min="0" value="{{ $order->base_price }}" required>
    </div>

    <div class="form-group">
      <label for="shipping_fee">Shipping Fee</label>
      <input type="number" name="shipping_fee" id="shipping_fee" step="0.01" min="0" value="{{ $order->shipping_fee }}">
    </div>

    <div class="form-group">
      <label for="quantity">Quantity</label>
      <input type="number" name="quantity" id="quantity" min="1" value="{{ $order->quantity }}" required>
    </div>

    <div class="form-group">
      <label for="remarks">Remarks</label>
      <textarea name="remarks" id="remarks" rows="3">{{ $order->remarks }}</textarea>
    </div>
      

    <div class="form-actions">
      <button class="btn" type="submit">Update Order</button>
      <a class="btn" href="{{ route('orders.index') }}">Order List</a>
    </div>


  </form>
</div>

@endsection
