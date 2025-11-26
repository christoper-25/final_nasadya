<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RiderAuthController;
use App\Http\Controllers\Api\RiderLocationController;
use App\Http\Controllers\CustomerController;



// Redirect root to rider login
Route::get('/', function () {
    return redirect('/rider/login');
});

// Rider authentication + dashboard
Route::middleware('web')->group(function () {
    Route::get('/rider/login', [RiderAuthController::class, 'showLoginForm'])->name('rider.login');
    Route::post('/rider/login', [RiderAuthController::class, 'login'])->name('rider.login.submit');
    Route::get('/rider/dashboard', [RiderAuthController::class, 'dashboard'])->name('rider.dashboard');
    Route::get('/customer/rider-location/{id}', [CustomerController::class, 'getRiderLocation']);
    Route::get('/rider/{riderId}/dashboard', [RiderAuthController::class, 'dashboard'])
     ->name('rider.dashboard');
    Route::post('/set-in-transit', [RiderAuthController::class, 'setInTransit'])->name('set_in_transit');
    Route::post('/mark-delivered', [RiderAuthController::class, 'markDelivered'])->name('mark_delivered');

    // Rider logout
    Route::post('/rider/logout', [RiderAuthController::class, 'logout'])->name('rider.logout');

    // Rider delivery history
    Route::get('/history', [RiderAuthController::class, 'history'])->name('rider.history');

    // ✅ Rider location API + live tracking page
    Route::get('/customer/rider-location/{rider}', [RiderLocationController::class, 'getLocation'])
        ->name('rider.location');

    Route::get('/customer/live-tracking/{rider}', [RiderLocationController::class, 'showLiveTracking'])
        ->name('rider.liveTracking');
});
