<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_id',
        'customer_name',
        'customer_address',
        'customer_contact',
        'delivery_status',
        'proof_of_delivery',
        'rider_id'
    ];
}

