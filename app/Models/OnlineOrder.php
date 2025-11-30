<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineOrder extends Model
{
    protected $fillable = [
        'user_id',
        'address',
        'location',
        'total',
        'status', // Pending, Ongoing, Delivered
        'rider_id', // optional, para malaman sino nagdeliver
        'proof_of_delivery', // optional, image path
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
