<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Products extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'products';

    protected $fillable = [
        'product_name',
        'product_price',
        'product_discount',
        'product_status',
        'product_expired_date',
        'product_detail',
        'product_pic',
        'product_pic_public_id',
        'category_id',
        'brand_id',
        'admin_id',
        'updated_by',
    ];

    public function categories(){
        return $this->belongsTo(Categories::class, 'category_id', '_id');
    }

    public function brands(){
        return $this->belongsTo(Brands::class, 'brand_id', '_id');
    }

    public function admins(){
        return $this->belongsTo(Admins::class, 'admin_id', '_id');
    }

    public function order_items()
    {
        return $this->hasMany(OrderItems::class, 'product_id', '_id');
    }
}
