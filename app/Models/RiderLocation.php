<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiderLocation extends Model
{
    protected $fillable = ['rider_id', 'latitude', 'longitude'];
}

