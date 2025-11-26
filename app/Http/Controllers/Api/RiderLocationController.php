<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiderLocation;


class RiderLocationController extends Controller
{
    // 🛰️ Update or create rider’s current location
    public function updateLocation(Request $request)
    {
        $request->validate([
            'rider_id' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        RiderLocation::updateOrCreate(
            ['rider_id' => $request->rider_id],
            ['latitude' => $request->latitude, 'longitude' => $request->longitude]
        );

        return response()->json(['status' => 'success']);
    }

    // 📍 Get rider’s latest location
    


    public function getLocation($riderId)
{
    $location = \App\Models\RiderLocation::where('rider_id', $riderId)->first();

    if ($location) {
        return response()->json([
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
        ]);
    }

    return response()->json(['error' => 'Location not found'], 404);
}

public function showLiveTracking($riderId)
{

    
    return view('customer.map', compact('riderId'));
}


    
}

