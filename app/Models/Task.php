<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TASK MODEL
 * 
 * This model represents a Order Table in the application.
 * It contains the properties and methods to interact with the database.
 * The model is used to perform CRUD operations on the Order Table.
 */

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

    public function statuslog()
    {
        return $this->hasMany(StatusLog::class);
    }
}
