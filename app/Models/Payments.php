<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Payments extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'payments';

    protected $fillable = [
        'order_id',
        'user_id',
        'total_price',
        'total_discount',
        'tax',
        'payment_method',
        'payment_status',
    ];

    public function users(){
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    public function orders(){
        return $this->belongsTo(Orders::class, 'order_id', '_id');
    }

    public function invoices()
    {
        return $this->hasOne(Invoices::class, 'payment_id', '_id');
    }
}
