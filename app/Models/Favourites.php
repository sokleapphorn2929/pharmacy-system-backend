<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Favourites extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'favourites';

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    public function users(){
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    public function products(){
        return $this->belongsTo(Products::class, 'product_id', '_id');
    }
}
