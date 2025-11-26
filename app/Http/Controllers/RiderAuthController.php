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
    // Redirect to login if not authenticated
    if (!session()->has('rider_id')) {
        return redirect()->route('rider.login');
    }

    $rider = Rider::find(session('rider_id'));

    // For Transactions (hindi kasama ang 'Completed')
    $transactions = Transaction::where('rider_id', $rider->id)
                               ->where('delivery_status', '!=', 'Completed')->get();

    // For History (kasama lang ang 'Completed')
    $history = Transaction::where('rider_id', $rider->id)
                          ->where('delivery_status', 'Completed')->get();

    return view('rider.dashboard', compact('rider','transactions', 'history'));
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
public function setInTransit(Request $request): JsonResponse
{
    if (!session()->has('rider_id')) {
        return response()->json(['message' => 'Not authenticated'], 401);
    }

    $validator = Validator::make($request->all(), [
        'transaction_id' => 'required|integer|exists:transactions,transaction_id'
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => $validator->errors()->first()], 422);
    }

    $riderId = session('rider_id');

    // Check if rider already has an ongoing delivery
    $hasOngoing = Transaction::where('rider_id', $riderId)
                    ->where('delivery_status', 'ongoing')
                    ->exists();

    if ($hasOngoing) {
        return response()->json(['message' => 'You already have an ongoing delivery. Complete it before starting another.'], 409);
    }

    $transaction = Transaction::where('transaction_id', $request->transaction_id)->first();

    if (!$transaction) {
        return response()->json(['message' => 'Transaction not found.'], 404);
    }

    // Make sure the transaction is assigned to this rider (optional)
    if ($transaction->rider_id != $riderId) {
        // Optionally assign it to the rider
        $transaction->rider_id = $riderId;
    }

    $transaction->delivery_status = 'ongoing';
    $transaction->save();

    return response()->json(['message' => 'Transaction set to ongoing', 'transaction_id' => $transaction->transaction_id]);
}


public function markDelivered(Request $request)
{
    try {
        $request->validate([
            'transaction_id' => 'required|integer',
            'photo' => 'required|image'
        ]);
        $transaction = Transaction::where('transaction_id', $request->transaction_id)->first();
        if (!$transaction) return response()->json(['message' => 'Transaction not found'], 404);

        $path = $request->file('photo')->store('proofs', 'public');
        $transaction->update([
            'delivery_status' => 'Completed',
            'proof_of_delivery' => $path
        ]);

        return response()->json(['message' => 'Marked as delivered', 'proof_path' => $path]);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}






    
}



