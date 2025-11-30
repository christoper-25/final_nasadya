<?php

namespace App\Http\Controllers;

use App\Models\Transaction; // make sure this exists

use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\OnlineOrder;


class RiderAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('rider.login');
    }

    

    public function login(Request $request)
{
    // 1️⃣ Validate input
    $request->validate([
    'email' => 'required|email',
    'password' => 'required|min:6',
], [
    'email.required' => 'Please enter your email address.',
    'password.required' => 'Please enter your password.',
]);


    // 2️⃣ Try to find rider by email
    $rider = Rider::where('email', $request->email)->first();

    // 3️⃣ Check if rider exists and password matches
    if ($rider && Hash::check($request->password, $rider->password)) {

        // 4️⃣ Log in the rider by setting session
        session()->put('rider_id', $rider->id);
        session()->put('rider_name', $rider->name); // optional, for display in dashboard

        // 5️⃣ Redirect to rider dashboard using route name
        return redirect()->route('rider.dashboard');
    }

    // 6️⃣ If login fails, redirect back with error
    return back()->withErrors([
        'login_error' => 'Invalid email or password, or you are not registered as a Rider.',
    ])->withInput(); // keeps old email in form
}



public function dashboard()
{
    if (!session()->has('rider_id')) {
        return redirect()->route('rider.login');
    }

    $rider = Rider::find(session('rider_id'));

    // Fetch lahat ng online orders + user info
    $orders = OnlineOrder::with('user:id,name,telephone_number')
        ->whereIn('status', ['Prepared', 'Ongoing']) // or show all
        ->get();

    $history = OnlineOrder::with('user:id,name,telephone_number')
    ->where('rider_id', $rider->id)
    ->where('status', 'Delivered')
    ->get();

    return view('rider.dashboard', compact('rider', 'orders', 'history'));
}






    public function history()
{
    if (!session()->has('rider_id')) {
        return redirect()->route('rider.login');
    }

    $rider = Rider::find(session('rider_id'));

   

    return view('rider.dashboard', compact('rider', 'history'));
}


    public function logout()
{
    session()->flush(); // wipe all session data
    return redirect()->route('rider.login');
}

// Set selected transaction to "ongoing" (in transit)
public function setInTransit(Request $request)
{
    if (!session()->has('rider_id')) {
        return response()->json(['message' => 'Not authenticated'], 401);
    }

    $request->validate([
        'order_id' => 'required|exists:online_orders,id'
    ]);

    $riderId = session('rider_id');

    $order = OnlineOrder::find($request->order_id);

    if (!$order) return response()->json(['message' => 'Order not found'], 404);

    // Check if rider already has ongoing delivery
    $ongoing = OnlineOrder::where('rider_id', $riderId)
                ->where('status', 'Ongoing')
                ->exists();

    if ($ongoing) {
        return response()->json(['message' => 'You already have an ongoing delivery.'], 409);
    }

    $order->status = 'Ongoing';
    $order->rider_id = $riderId;
    $order->save();

    return response()->json(['message' => 'Order set to Ongoing', 'order_id' => $order->id]);
}



public function markDelivered(Request $request)
{
    if (!session()->has('rider_id')) {
        return response()->json(['message' => 'Not authenticated'], 401);
    }

    $request->validate([
        'order_id' => 'required|exists:online_orders,id',
        'photo' => 'required|image'
    ]);

    $order = OnlineOrder::find($request->order_id);

    if (!$order) return response()->json(['message' => 'Order not found'], 404);

    // Save proof photo
    $path = $request->file('photo')->store('proofs', 'public');

    $order->update([
        'status' => 'Delivered',
        'proof_of_delivery' => $path
    ]);

    return response()->json(['message' => 'Marked as Delivered', 'proof_path' => $path]);
}







    
}



