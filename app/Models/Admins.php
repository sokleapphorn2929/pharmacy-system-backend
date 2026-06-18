<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admins extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = "mongodb";

    protected $collection = "admins";

    protected $fillable = [
        'username',
        'password',
        'admin_pic',
        'admin_pic_public_id',
        'role',
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function products()
    {
        return $this->hasMany(Products::class, 'admin_id', '_id');
    }

    public function invoices()
    {
        return $this->hasMany(Products::class, 'admin_id', '_id');
    }
}
