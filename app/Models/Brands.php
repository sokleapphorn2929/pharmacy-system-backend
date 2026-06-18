<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Brands extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'brands';

    protected $fillable = [
        'brand_name',
        'brand_location',
        'brand_detail',
        'brand_pic',
        'brand_pic_public_id',
    ];

    public function brands()
    {
        return $this->hasMany(Brands::class, 'brand_id', '_id');
    }
}
