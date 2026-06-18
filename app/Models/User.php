<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $connection = 'mongodb';

    protected $collection = 'users';

    protected $fillable = [
        'username',
        'email',
        'phone',
        'address',
        'profile_pic',
        'profile_pic_url',
        'profile_pic_public_id',
        'password',
        'verification_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'code_expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function favourites()
    {
        return $this->hasMany(Favourites::class, 'user_id', '_id');
    }

    public function cards()
    {
        return $this->hasMany(Cards::class, 'user_id', '_id');
    }

    public function orders()
    {
        return $this->hasMany(Orders::class, 'user_id', '_id');
    }

    public function payments()
    {
        return $this->hasMany(Payments::class, 'user_id', '_id');
    }
}
