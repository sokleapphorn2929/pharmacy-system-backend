<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Categories extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'categories';

    protected $fillable = [
        'category_name',
        'category_pic',
        'category_pic_public_id',
    ];

    public function products()
    {
        return $this->hasMany(Products::class, 'category_id', '_id');
    }
}
