<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PowerBankController;
use App\Http\Controllers\Api\RentalController;

// PowerBank API Routes (no auth required for device communication)
Route::prefix('rentbox')->group(function () {
    // Device authentication endpoint
    Route::post('client/connect', [PowerBankController::class, 'connect']);
    
    // Device status upload
    Route::post('device/upload', [PowerBankController::class, 'upload']);
    
    // Device return powerbank
    Route::post('device/return', [PowerBankController::class, 'returnPowerBank']);
});

// Client Rental Routes
Route::prefix('rental')->group(function () {
    Route::post('start', [RentalController::class, 'start']);
    Route::post('webhook', [RentalController::class, 'webhook']); // Webhook Wave
    
    // Callbacks de redirection Wave
    Route::get('callback/success', [RentalController::class, 'callbackSuccess']);
    Route::get('callback/error', [RentalController::class, 'callbackError']);
});

