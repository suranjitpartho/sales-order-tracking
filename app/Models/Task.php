<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_date',
        'product_id',
        'product_category',
        'buyer_gender',
        'buyer_age',
        'order_location',
        'international_shipping',
        'sales_price',
        'shipping_charges',
        'sales_per_unit',
        'quantity',
        'total_sales',
        'remarks',
    ];
}
