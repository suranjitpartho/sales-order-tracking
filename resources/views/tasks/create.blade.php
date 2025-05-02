@extends('layouts.app')
@section('title', 'Create New Order')

@section('content')
    <h2 class="section-title">Create New Order</h2>

    <form action="{{ route('tasks.store') }}" method="POST" class="form-wrapper">
        @csrf

        <div class="form-group">
            <label for="order_date">Order Date</label>
            <input type="date" name="order_date" id="order_date" required placeholder="Order Date">
        </div>

        <div class="form-group">
            <label for="product_id">Product ID</label>
            <select name="product_id" id="product_id">
                <option value="">-- Select Category --</option>
                <option value="SX9001">SX9001</option>
                <option value="SX9002">SX9002</option>
                <option value="SX9003">SX9003</option>
                <option value="SX9004">SX9004</option>
                <option value="SX9005">SX9005</option>
                <option value="SX9006">SX9006</option>
            </select>
            <!-- <input type="text" name="product_id" id="product_id" :value="old('product_id')" minlength="6" maxlength="6" required placeholder="Product ID"> -->
        </div>

        <div class="form-group">
            <label for="product_category">Product Category</label>
            <select name="product_category" id="product_category" required>
                <option value="">-- Select Category --</option>
                <option value="clothing">Clothing</option>
                <option value="ornaments">Ornaments</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="buyer_gender">Buyer Gender</label>
            <select name="buyer_gender" id="buyer_gender" required>
                <option value="">-- Select Gender --</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>

        <div class="form-group">
            <label for="buyer_age">Buyer Age</label>
            <input type="number" name="buyer_age" id="buyer_age" min="0" required>
        </div>

        <div class="form-group">
            <label for="order_location">Order Location</label>
            <textarea name="order_location" id="order_location" rows="1" required></textarea>
        </div>

        <div class="form-group checkbox">
            <label>
                <input type="checkbox" name="international_shipping" value="1">
                International Shipping?
            </label>
        </div>

        <div class="form-group">
            <label for="sales_price">Sales Price</label>
            <input type="number" name="sales_price" id="sales_price" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="shipping_charges">Shipping Charges (if international)</label>
            <input type="number" name="shipping_charges" id="shipping_charges" step="0.01" min="0">
        </div>

        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" name="quantity" id="quantity" min="1" required>
        </div>

        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea name="remarks" id="remarks" rows="3"></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Create Order</button>
            <a href="{{ route('tasks.index') }}" class="btn">Back to Order List</a>
        </div>

    </form>
@endsection
