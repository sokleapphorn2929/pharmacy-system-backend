<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Invoices extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'invoices';

    protected $fillable = [
        'payment_id',
        'admin_id',
        'invoice_number',
        'invoice_create_at'
    ];

    public function payments(){
        return $this->belongsTo(Payments::class, 'payment_id', '_id');
    }

    public function admins(){
        return $this->belongsTo(Admins::class, 'admin_id', '_id');
    }
}
