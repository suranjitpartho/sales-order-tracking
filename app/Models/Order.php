<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ORDER MODEL
 * 
 * This model represents a Order Table.
 * The model is used to perform CRUD operations on the Order Table.
 */

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'order_date',
        'product_id',
        'product_category',
        'buyer_gender',
        'buyer_age',
        'order_location',
        'international_shipping',
        'base_price',
        'shipping_fee',
        'unit_price',
        'quantity',
        'final_amount',
        'status',
        'remarks',
    ];

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }

}
