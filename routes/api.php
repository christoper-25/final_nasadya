<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\RiderLocationController;

Route::post('/rider/update-location', [RiderLocationController::class, 'updateLocation']);
Route::get('/rider/location/{rider_id}', [RiderLocationController::class, 'getLocation']);

