<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rider;

class CustomerController extends Controller
{
    public function getRiderLocation($id)
    {
        $rider = Rider::find($id);

        if (!$rider) {
            return response()->json(['error' => 'Rider not found'], 404);
        }

        return response()->json([
            'latitude' => $rider->latitude,
            'longitude' => $rider->longitude,
        ]);
    }
}
