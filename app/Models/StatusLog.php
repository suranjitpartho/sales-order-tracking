<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*

STATUS LOG MODEL
-------------------------------------
This model represents a Order Status Log Table in the application.
It contains the properties and methods to interact with the database.

*/

class StatusLog extends Model
{
    protected $table = 'statuslog'; 
    
    protected $fillable = [
        'task_id', 
        'status', 
        'changed_at'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}