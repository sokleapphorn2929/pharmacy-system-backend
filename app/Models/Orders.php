<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Orders extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'orders';

    protected $fillable = [
        'user_id',
        'order_date',
        'order_status',
    ];

    public function users(){
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    public function order_items()
    {
        return $this->hasOne(OrderItems::class, 'order_id', '_id');
    }

    public function payments()
    {
        return $this->hasOne(Payments::class, 'order_id', '_id');
    }
}
