<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderStatusLog extends Model
{
    use HasFactory;

    protected $table = 'order_status_log';

    protected $fillable = [
        'order_id',
        'previous_status',
        'changed_status',
        'changed_at',
        'changed_by',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
