<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Casts\ObjectId;
use MongoDB\Laravel\Eloquent\Model;

class Favourites extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'favourites';

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    protected $casts = [
        'product_id' => ObjectId::class,
        'user_id' => ObjectId::class,
    ];

    public function users(){
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    public function products(){
        return $this->belongsTo(Products::class, 'product_id', '_id');
    }
}
