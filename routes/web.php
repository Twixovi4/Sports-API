<?php

use App\Http\Controllers\Api\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Sports API']);
});

Route::middleware('auth:api')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);

    Route::prefix('/bookings/{booking}')->group(function () {
        Route::patch('/slots/{slot}', [BookingController::class, 'updateSlot']);
        Route::post('/slots', [BookingController::class, 'addSlot']);
    });
});
