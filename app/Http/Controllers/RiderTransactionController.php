<?php

namespace App\Http\Controllers;

use App\Models\OnlineOrder;

class RiderTransactionController extends Controller
{
    public function transactions()
    {
        // Get all online orders + user info
        $orders = OnlineOrder::with('user:id,name,telephone_number')
            ->get(['id','user_id','address','location','total','status']);

        return view('rider.dashboard', compact('orders'));
    }
}
