<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class OrderItems extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'qty',
        'price',
        'discount'
    ];

    public function orders(){
        return $this->belongsTo(Orders::class, 'order_id', '_id');
    }

    public function products(){
        return $this->belongsTo(Products::class, 'product_id', '_id');
    }
}
